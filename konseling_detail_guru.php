<?php

session_start();

require_once __DIR__ . "/config/database.php";

/*
|--------------------------------------------------------------------------
| CEK LOGIN GURU BK
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'guru' ||
    !isset($_SESSION['guru_id']) ||
    (int) $_SESSION['guru_id'] <= 0
) {
    header("Location: login.php");
    exit;
}

$guru_id = (int) $_SESSION['guru_id'];


/*
|--------------------------------------------------------------------------
| AMBIL ID KONSELING
|--------------------------------------------------------------------------
*/

$id_konseling = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id_konseling <= 0) {
    header("Location: konseling_guru.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| MENENTUKAN HALAMAN ASAL
|--------------------------------------------------------------------------
*/

$from = $_GET['from'] ?? 'konseling';

if ($from === 'dashboard') {

    $back_url = 'dashboard_guru.php';
    $back_text = 'Kembali ke Dashboard';

} elseif ($from === 'riwayat') {

    $back_url = 'riwayat_konseling_guru.php';
    $back_text = 'Kembali ke Riwayat Konseling';

} else {

    $back_url = 'konseling_guru.php';
    $back_text = 'Kembali ke Data Konseling';

}


/*
|--------------------------------------------------------------------------
| VARIABEL PESAN
|--------------------------------------------------------------------------
*/

$error = '';
$success = '';


/*
|--------------------------------------------------------------------------
| AMBIL DATA KONSELING
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        k.id_konseling,
        k.id_siswa,
        k.id_guru,
        k.masalah,
        k.solusi,
        k.tanggal,
        k.status,

        s.nis,
        s.nama AS nama_siswa,
        s.kelas,
        s.jurusan,
        s.foto AS foto_siswa,

        g.nip,
        g.nama AS nama_guru,
        g.foto AS foto_guru

    FROM konseling k

    LEFT JOIN siswa s
        ON k.id_siswa = s.id_siswa

    LEFT JOIN guru_bk g
        ON k.id_guru = g.id_guru

    WHERE
        k.id_konseling = ?
        AND k.id_guru = ?

    LIMIT 1
");

if (!$stmt) {
    die("Query gagal: " . $conn->error);
}

$stmt->bind_param(
    "ii",
    $id_konseling,
    $guru_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: konseling_guru.php");
    exit;
}

$data = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| SIMPAN SOLUSI DAN STATUS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $solusi_input = trim(
        $_POST['solusi'] ?? ''
    );

    $status_input = $_POST['status'] ?? '';

    $status_valid = [
        'menunggu',
        'terjadwal',
        'selesai',
        'batal'
    ];


    if (!in_array($status_input, $status_valid, true)) {

        $error = 'Status konseling tidak valid.';

    } else {

        $update = $conn->prepare("
            UPDATE konseling
            SET
                solusi = ?,
                status = ?
            WHERE
                id_konseling = ?
                AND id_guru = ?
        ");

        if (!$update) {

            $error = 'Query update gagal: ' . $conn->error;

        } else {

            $update->bind_param(
                "ssii",
                $solusi_input,
                $status_input,
                $id_konseling,
                $guru_id
            );

            if ($update->execute()) {

                $update->close();

                header(
                    "Location: konseling_detail_guru.php?id="
                    . $id_konseling
                    . "&from="
                    . urlencode($from)
                    . "&success=1"
                );

                exit;

            } else {

                $error = 'Gagal menyimpan data konseling.';

                $update->close();
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | TAMPILKAN KEMBALI INPUT JIKA ERROR
    |--------------------------------------------------------------------------
    */

    $data['solusi'] = $solusi_input;
    $data['status'] = $status_input;
}


/*
|--------------------------------------------------------------------------
| PESAN BERHASIL
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['success']) &&
    $_GET['success'] === '1'
) {

    $success =
        'Solusi / catatan Guru BK berhasil disimpan.';
}


/*
|--------------------------------------------------------------------------
| FUNGSI STATUS
|--------------------------------------------------------------------------
*/

function getStatusGuruDetail($status)
{
    switch ($status) {

        case 'terjadwal':

            return [
                'class' => 'status-terjadwal',
                'icon' => 'bi-calendar-check-fill',
                'text' => 'Terjadwal'
            ];

        case 'selesai':

            return [
                'class' => 'status-selesai',
                'icon' => 'bi-check-circle-fill',
                'text' => 'Selesai'
            ];

        case 'batal':

            return [
                'class' => 'status-batal',
                'icon' => 'bi-x-circle-fill',
                'text' => 'Batal'
            ];

        case 'menunggu':

            return [
                'class' => 'status-menunggu',
                'icon' => 'bi-clock-fill',
                'text' => 'Menunggu'
            ];

        default:

            return [
                'class' => 'status-menunggu',
                'icon' => 'bi-clock-fill',
                'text' => 'Menunggu'
            ];
    }
}


/*
|--------------------------------------------------------------------------
| FUNGSI FORMAT TANGGAL
|--------------------------------------------------------------------------
*/

function formatTanggalGuruDetail($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    $timestamp = strtotime($tanggal);

    if (!$timestamp) {
        return '-';
    }

    return date(
        'd M Y, H:i',
        $timestamp
    ) . ' WIB';
}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

$status_data = getStatusGuruDetail(
    $data['status'] ?? ''
);


/*
|--------------------------------------------------------------------------
| DATA SISWA
|--------------------------------------------------------------------------
*/

$nama_siswa = $data['nama_siswa'] ?? 'Siswa';

$inisial_siswa = '';

$nama_parts = preg_split(
    '/\s+/',
    trim($nama_siswa)
);

if (!empty($nama_parts[0])) {

    $inisial_siswa .= strtoupper(
        substr(
            $nama_parts[0],
            0,
            1
        )
    );
}

if (
    count($nama_parts) > 1
) {

    $inisial_siswa .= strtoupper(
        substr(
            $nama_parts[count($nama_parts) - 1],
            0,
            1
        )
    );
}

if ($inisial_siswa === '') {
    $inisial_siswa = 'S';
}


/*
|--------------------------------------------------------------------------
| FOTO SISWA
|--------------------------------------------------------------------------
*/

$foto_siswa_ada = false;
$foto_siswa_url = '';

if (!empty($data['foto_siswa'])) {

    $foto_siswa_nama = basename(
        str_replace(
            '\\',
            '/',
            $data['foto_siswa']
        )
    );

    $foto_siswa_path =
        __DIR__ .
        '/uploads/siswa/' .
        $foto_siswa_nama;

    if (file_exists($foto_siswa_path)) {

        $foto_siswa_ada = true;

        $foto_siswa_url =
            'uploads/siswa/' .
            rawurlencode(
                $foto_siswa_nama
            );
    }
}


/*
|--------------------------------------------------------------------------
| FOTO GURU
|--------------------------------------------------------------------------
*/

$foto_guru_ada = false;
$foto_guru_url = '';

if (!empty($data['foto_guru'])) {

    $foto_guru_nama = basename(
        str_replace(
            '\\',
            '/',
            $data['foto_guru']
        )
    );

    $foto_guru_path =
        __DIR__ .
        '/uploads/guru/' .
        $foto_guru_nama;

    if (file_exists($foto_guru_path)) {

        $foto_guru_ada = true;

        $foto_guru_url =
            'uploads/guru/' .
            rawurlencode(
                $foto_guru_nama
            );
    }
}


/*
|--------------------------------------------------------------------------
| MASALAH & SOLUSI
|--------------------------------------------------------------------------
*/

$masalah = trim(
    (string) (
        $data['masalah'] ?? ''
    )
);

$solusi = trim(
    (string) (
        $data['solusi'] ?? ''
    )
);

?>

<?php require_once __DIR__ . "/layout/header_guru.php"; ?>


<style>

/* =========================================================
   RESET KHUSUS HALAMAN DETAIL
========================================================= */

.guru-detail-wrapper,
.guru-detail-wrapper *,
.guru-detail-wrapper *::before,
.guru-detail-wrapper *::after {
    box-sizing: border-box;
}


/* =========================================================
   WRAPPER
========================================================= */

.guru-detail-wrapper {
    display: block !important;

    width: 100% !important;
    max-width: 1100px !important;

    margin: 0 auto !important;
    padding: 0 !important;

    position: static !important;

    text-align: left !important;

    transform: none !important;
}


/* =========================================================
   PAGE HEADER
========================================================= */

.guru-detail-page-header {
    display: block !important;

    width: 100% !important;

    margin: 0 0 24px 0 !important;
    padding: 0 !important;

    text-align: left !important;

    position: static !important;

    transform: none !important;
}

.guru-detail-page-title {
    display: block !important;

    margin: 0 0 6px 0 !important;
    padding: 0 !important;

    color: var(--text-main) !important;

    font-size: 26px !important;
    font-weight: 700 !important;

    line-height: 1.3 !important;

    text-align: left !important;
}

.guru-detail-page-subtitle {
    display: block !important;

    margin: 0 !important;
    padding: 0 !important;

    color: var(--text-muted) !important;

    font-size: 14px !important;

    line-height: 1.6 !important;

    text-align: left !important;
}


/* =========================================================
   ALERT
========================================================= */

.guru-detail-alert {
    display: block !important;

    width: 100% !important;

    margin: 0 0 18px 0 !important;
    padding: 12px 15px !important;

    border-radius: 10px !important;

    font-size: 13px !important;
    font-weight: 600 !important;

    text-align: left !important;
}

.guru-detail-alert.success {
    color: #45d69a !important;

    background: rgba(25, 135, 84, .12) !important;

    border: 1px solid rgba(25, 135, 84, .25) !important;
}

.guru-detail-alert.error {
    color: #ff7180 !important;

    background: rgba(220, 53, 69, .12) !important;

    border: 1px solid rgba(220, 53, 69, .25) !important;
}


/* =========================================================
   CARD
========================================================= */

.guru-detail-card {
    display: block !important;

    width: 100% !important;

    margin: 0 !important;
    padding: 0 !important;

    background: var(--bg-card) !important;

    border: 1px solid var(--border-color) !important;

    border-radius: 14px !important;

    box-shadow: var(--shadow) !important;

    overflow: hidden !important;

    position: static !important;

    transform: none !important;
}


/* =========================================================
   STUDENT HEADER
========================================================= */

.guru-detail-student-header {
    display: flex !important;

    align-items: center !important;

    width: 100% !important;

    gap: 16px !important;

    margin: 0 !important;

    padding: 22px 24px !important;

    border-bottom: 1px solid var(--border-color) !important;

    text-align: left !important;

    position: static !important;

    transform: none !important;
}

.guru-detail-student-avatar {
    width: 58px !important;
    height: 58px !important;

    min-width: 58px !important;

    display: flex !important;

    align-items: center !important;
    justify-content: center !important;

    overflow: hidden !important;

    border-radius: 50% !important;

    background: #1769e8 !important;

    color: #fff !important;

    font-size: 18px !important;

    font-weight: 700 !important;

    flex-shrink: 0 !important;

    position: static !important;

    transform: none !important;
}

.guru-detail-student-avatar img {
    display: block !important;

    width: 100% !important;
    height: 100% !important;

    object-fit: cover !important;
}

.guru-detail-student-info {
    display: block !important;

    min-width: 0 !important;

    margin: 0 !important;
    padding: 0 !important;

    text-align: left !important;
}

.guru-detail-student-name {
    display: block !important;

    margin: 0 0 5px 0 !important;
    padding: 0 !important;

    color: var(--text-main) !important;

    font-size: 18px !important;

    font-weight: 700 !important;

    line-height: 1.4 !important;

    text-align: left !important;
}

.guru-detail-student-meta {
    display: flex !important;

    flex-wrap: wrap !important;

    gap: 6px 14px !important;

    margin: 0 !important;
    padding: 0 !important;

    color: var(--text-muted) !important;

    font-size: 13px !important;

    text-align: left !important;
}

.guru-detail-student-meta span {
    display: inline-flex !important;

    align-items: center !important;

    gap: 5px !important;

    margin: 0 !important;
    padding: 0 !important;

    text-align: left !important;
}


/* =========================================================
   BODY
========================================================= */

.guru-detail-body {
    display: block !important;

    width: 100% !important;

    margin: 0 !important;

    padding: 24px !important;

    text-align: left !important;

    position: static !important;

    transform: none !important;
}


/* =========================================================
   INFO GRID
========================================================= */

.guru-detail-info-grid {
    display: grid !important;

    grid-template-columns:
        repeat(3, minmax(0, 1fr)) !important;

    gap: 14px !important;

    width: 100% !important;

    margin: 0 0 24px 0 !important;
    padding: 0 !important;

    text-align: left !important;
}

.guru-detail-info-item {
    display: block !important;

    width: 100% !important;

    margin: 0 !important;

    padding: 15px !important;

    background: rgba(127, 127, 127, .04) !important;

    border: 1px solid var(--border-color) !important;

    border-radius: 11px !important;

    text-align: left !important;

    position: static !important;

    transform: none !important;
}

.guru-detail-info-label {
    display: block !important;

    margin: 0 0 7px 0 !important;
    padding: 0 !important;

    color: var(--text-muted) !important;

    font-size: 12px !important;

    font-weight: 600 !important;

    line-height: 1.5 !important;

    text-align: left !important;
}

.guru-detail-info-value {
    display: block !important;

    margin: 0 !important;
    padding: 0 !important;

    color: var(--text-main) !important;

    font-size: 14px !important;

    font-weight: 600 !important;

    line-height: 1.5 !important;

    text-align: left !important;

    word-break: break-word !important;
}


/* =========================================================
   STATUS
========================================================= */

.guru-detail-status {
    display: inline-flex !important;

    align-items: center !important;
    justify-content: center !important;

    gap: 6px !important;

    margin: 0 !important;
    padding: 6px 10px !important;

    border-radius: 999px !important;

    font-size: 12px !important;

    font-weight: 700 !important;

    line-height: 1.3 !important;
}

.status-terjadwal {
    color: #65a2ff !important;

    background: rgba(57, 133, 255, .12) !important;
}

.status-selesai {
    color: #45d69a !important;

    background: rgba(25, 135, 84, .12) !important;
}

.status-batal {
    color: #ff7180 !important;

    background: rgba(220, 53, 69, .12) !important;
}

.status-menunggu {
    color: #ffc65c !important;

    background: rgba(255, 193, 7, .12) !important;
}


/* =========================================================
   SECTION TITLE
========================================================= */

.guru-detail-section-title {
    display: block !important;

    width: 100% !important;

    margin: 0 0 18px 0 !important;
    padding: 0 !important;

    color: var(--text-main) !important;

    font-size: 16px !important;

    font-weight: 700 !important;

    line-height: 1.5 !important;

    text-align: left !important;

    position: static !important;

    transform: none !important;
}

.guru-detail-section-title i {
    display: inline-block !important;

    margin-right: 7px !important;

    color: #4d91ff !important;

    vertical-align: middle !important;

    position: static !important;

    transform: none !important;
}


/* =========================================================
   BAGIAN MASALAH
========================================================= */

.guru-problem-section {
    display: block !important;

    width: 100% !important;

    height: auto !important;

    margin: 0 0 24px 0 !important;
    padding: 0 !important;

    text-align: left !important;

    position: static !important;

    float: none !important;

    clear: both !important;

    transform: none !important;

    flex: none !important;

    flex-direction: initial !important;

    align-items: initial !important;

    align-content: initial !important;

    justify-content: initial !important;

    justify-items: initial !important;

    place-items: initial !important;

    place-content: initial !important;

    grid-template-columns: initial !important;

    grid-template-rows: initial !important;
}


/* =========================================================
   JUDUL MASALAH
========================================================= */

.guru-problem-title {
    display: block !important;

    width: 100% !important;

    height: auto !important;

    margin: 0 0 10px 0 !important;
    padding: 0 !important;

    color: var(--text-main) !important;

    font-size: 14px !important;

    font-weight: 700 !important;

    line-height: 1.5 !important;

    text-align: left !important;

    position: static !important;

    transform: none !important;

    float: none !important;

    clear: both !important;
}

.guru-problem-title i {
    display: inline-block !important;

    width: auto !important;
    height: auto !important;

    margin: 0 7px 0 0 !important;
    padding: 0 !important;

    color: #4d91ff !important;

    position: static !important;

    transform: none !important;

    vertical-align: middle !important;
}


/* =========================================================
   ISI MASALAH
========================================================= */

.guru-problem-content {
    display: block !important;

    width: 100% !important;

    height: auto !important;

    min-height: 120px !important;

    margin: 0 !important;

    padding: 16px !important;

    box-sizing: border-box !important;

    border: 1px solid var(--border-color) !important;

    border-radius: 11px !important;

    background: rgba(127, 127, 127, .04) !important;

    color: var(--text-main) !important;

    font-family: inherit !important;

    font-size: 14px !important;

    font-weight: 400 !important;

    line-height: 1.7 !important;

    text-align: left !important;

    text-align-last: left !important;

    text-indent: 0 !important;

    vertical-align: top !important;

    white-space: pre-wrap !important;

    overflow-wrap: anywhere !important;

    word-break: break-word !important;

    writing-mode: horizontal-tb !important;

    direction: ltr !important;

    position: static !important;

    float: none !important;

    clear: both !important;

    top: auto !important;
    right: auto !important;
    bottom: auto !important;
    left: auto !important;

    inset: auto !important;

    transform: none !important;

    rotate: none !important;

    scale: none !important;

    translate: none !important;

    flex: none !important;

    flex-grow: 0 !important;

    flex-shrink: 0 !important;

    align-self: auto !important;

    justify-self: auto !important;

    align-items: initial !important;

    justify-content: initial !important;

    align-content: initial !important;

    justify-items: initial !important;

    place-items: initial !important;

    place-content: initial !important;
}


/* =========================================================
   SOLUSI SECTION
========================================================= */

.guru-solution-section {
    display: block !important;

    width: 100% !important;

    margin: 0 0 22px 0 !important;
    padding: 0 !important;

    text-align: left !important;

    position: static !important;

    transform: none !important;
}

.guru-solution-title {
    display: block !important;

    width: 100% !important;

    margin: 0 0 10px 0 !important;
    padding: 0 !important;

    color: var(--text-main) !important;

    font-size: 14px !important;

    font-weight: 700 !important;

    line-height: 1.5 !important;

    text-align: left !important;
}

.guru-solution-title i {
    display: inline-block !important;

    margin-right: 7px !important;

    color: #4d91ff !important;

    vertical-align: middle !important;
}


/* =========================================================
   TEXTAREA SOLUSI
========================================================= */

.guru-solution-box {
    display: block !important;

    width: 100% !important;

    margin: 0 !important;
    padding: 15px !important;

    border: 1px solid var(--border-color) !important;

    border-radius: 11px !important;

    background: rgba(127, 127, 127, .04) !important;

    text-align: left !important;

    position: static !important;

    transform: none !important;
}

.guru-solution-textarea {
    display: block !important;

    width: 100% !important;

    min-height: 120px !important;

    height: auto !important;

    margin: 0 !important;
    padding: 0 !important;

    border: 0 !important;

    outline: none !important;

    resize: vertical !important;

    background: transparent !important;

    color: var(--text-main) !important;

    font-family: inherit !important;

    font-size: 14px !important;

    font-weight: 400 !important;

    line-height: 1.7 !important;

    text-align: left !important;

    vertical-align: top !important;

    direction: ltr !important;

    position: static !important;

    transform: none !important;
}

.guru-solution-textarea::placeholder {
    color: var(--text-muted) !important;
}

.guru-solution-textarea:focus {
    outline: none !important;

    border: 0 !important;

    box-shadow: none !important;
}


/* =========================================================
   FORM STATUS
========================================================= */

.guru-form-status {
    display: block !important;

    width: 100% !important;

    margin: 0 0 20px 0 !important;
    padding: 0 !important;

    text-align: left !important;
}

.guru-form-status-label {
    display: block !important;

    margin: 0 0 9px 0 !important;
    padding: 0 !important;

    color: var(--text-main) !important;

    font-size: 14px !important;

    font-weight: 700 !important;

    line-height: 1.5 !important;

    text-align: left !important;
}

.guru-form-status-select {
    display: block !important;

    width: 100% !important;

    min-height: 44px !important;

    margin: 0 !important;

    padding: 10px 13px !important;

    border: 1px solid var(--border-color) !important;

    border-radius: 9px !important;

    background: var(--bg-card) !important;

    color: var(--text-main) !important;

    font-family: inherit !important;

    font-size: 14px !important;

    outline: none !important;

    cursor: pointer !important;

    text-align: left !important;
}

.guru-form-status-select:focus {
    border-color: #4d91ff !important;

    box-shadow:
        0 0 0 3px
        rgba(77, 145, 255, .12) !important;
}


/* =========================================================
   TOMBOL SIMPAN
========================================================= */

.guru-detail-save-area {
    display: flex !important;

    width: 100% !important;

    justify-content: flex-end !important;

    align-items: center !important;

    margin: 0 0 24px 0 !important;

    padding: 0 !important;

    text-align: right !important;

    position: static !important;

    transform: none !important;
}

.guru-detail-save-button {
    display: inline-flex !important;

    align-items: center !important;

    justify-content: center !important;

    gap: 8px !important;

    min-height: 42px !important;

    margin: 0 !important;

    padding: 10px 17px !important;

    border: 1px solid #1769e8 !important;

    border-radius: 9px !important;

    background: #1769e8 !important;

    color: #fff !important;

    font-family: inherit !important;

    font-size: 13px !important;

    font-weight: 600 !important;

    line-height: 1.3 !important;

    cursor: pointer !important;

    transition:
        background .2s ease,
        border-color .2s ease,
        transform .2s ease !important;
}

.guru-detail-save-button:hover {
    background: #4d91ff !important;

    border-color: #4d91ff !important;

    color: #fff !important;

    transform: translateY(-1px) !important;
}


/* =========================================================
   GURU PENANGGUNG JAWAB
========================================================= */

.guru-detail-responsible {
    display: flex !important;

    align-items: center !important;

    width: 100% !important;

    gap: 12px !important;

    margin: 0 !important;

    padding: 14px 16px !important;

    border: 1px solid var(--border-color) !important;

    border-radius: 11px !important;

    background: rgba(127, 127, 127, .04) !important;

    text-align: left !important;

    position: static !important;

    transform: none !important;
}

.guru-detail-responsible-avatar {
    width: 46px !important;
    height: 46px !important;

    min-width: 46px !important;

    display: flex !important;

    align-items: center !important;

    justify-content: center !important;

    overflow: hidden !important;

    border-radius: 50% !important;

    background: #1769e8 !important;

    color: #fff !important;

    font-size: 18px !important;

    flex-shrink: 0 !important;
}

.guru-detail-responsible-avatar img {
    display: block !important;

    width: 100% !important;
    height: 100% !important;

    object-fit: cover !important;
}

.guru-detail-responsible-text {
    display: block !important;

    min-width: 0 !important;

    margin: 0 !important;
    padding: 0 !important;

    text-align: left !important;
}

.guru-detail-responsible-label {
    display: block !important;

    margin: 0 0 2px 0 !important;
    padding: 0 !important;

    color: var(--text-muted) !important;

    font-size: 11px !important;

    line-height: 1.4 !important;

    text-align: left !important;
}

.guru-detail-responsible-name {
    display: block !important;

    margin: 0 !important;
    padding: 0 !important;

    color: var(--text-main) !important;

    font-size: 13px !important;

    font-weight: 600 !important;

    line-height: 1.5 !important;

    text-align: left !important;

    word-break: break-word !important;
}


/* =========================================================
   BACK BUTTON
========================================================= */

.guru-detail-back-area {
    display: block !important;

    width: 100% !important;

    margin: 22px 0 0 0 !important;
    padding: 0 0 4px 0 !important;

    text-align: left !important;
}

.guru-detail-back-button {
    display: inline-flex !important;

    align-items: center !important;

    justify-content: center !important;

    gap: 8px !important;

    min-height: 40px !important;

    margin: 0 !important;

    padding: 9px 15px !important;

    border: 1px solid var(--border-color) !important;

    border-radius: 9px !important;

    background: var(--bg-card) !important;

    color: var(--text-muted) !important;

    font-size: 13px !important;

    font-weight: 600 !important;

    line-height: 1.3 !important;

    text-decoration: none !important;

    transition:
        background .2s ease,
        border-color .2s ease,
        color .2s ease,
        transform .2s ease !important;
}

.guru-detail-back-button:hover {
    background: #1769e8 !important;

    border-color: #1769e8 !important;

    color: #fff !important;

    transform: translateY(-1px) !important;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .guru-detail-info-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr)) !important;
    }

}


@media (max-width: 700px) {

    .guru-detail-page-title {
        font-size: 23px !important;
    }

    .guru-detail-body {
        padding: 18px !important;
    }

    .guru-detail-student-header {
        padding: 18px !important;
    }

    .guru-detail-info-grid {
        grid-template-columns: 1fr !important;
    }

    .guru-detail-student-avatar {
        width: 50px !important;
        height: 50px !important;

        min-width: 50px !important;
    }

    .guru-detail-student-name {
        font-size: 16px !important;
    }

    .guru-detail-save-area {
        display: block !important;

        text-align: left !important;
    }

    .guru-detail-save-button {
        width: 100% !important;
    }

}


@media (max-width: 480px) {

    .guru-detail-page-title {
        font-size: 21px !important;
    }

    .guru-detail-body {
        padding: 15px !important;
    }

    .guru-detail-student-header {
        gap: 12px !important;
    }

    .guru-detail-student-meta {
        display: block !important;
    }

    .guru-detail-student-meta span {
        display: flex !important;

        margin-bottom: 4px !important;
    }

    .guru-detail-back-button {
        width: 100% !important;
    }

}

</style>


<div class="guru-detail-wrapper">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="guru-detail-page-header">

        <h1 class="guru-detail-page-title">
            Detail Konseling
        </h1>

        <p class="guru-detail-page-subtitle">
            Informasi lengkap mengenai konseling siswa.
        </p>

    </div>


    <!-- =====================================================
         SUCCESS
    ====================================================== -->

    <?php if ($success !== ''): ?>

            <div class="guru-detail-alert success">

                <i class="bi bi-check-circle-fill"></i>

                <?= htmlspecialchars($success); ?>

            </div>

    <?php endif; ?>


    <!-- =====================================================
         ERROR
    ====================================================== -->

    <?php if ($error !== ''): ?>

            <div class="guru-detail-alert error">

                <i class="bi bi-exclamation-circle-fill"></i>

                <?= htmlspecialchars($error); ?>

            </div>

    <?php endif; ?>


    <!-- =====================================================
         CARD
    ====================================================== -->

    <div class="guru-detail-card">


        <!-- =================================================
             DATA SISWA
        ================================================== -->

        <div class="guru-detail-student-header">


            <div class="guru-detail-student-avatar">

                <?php if ($foto_siswa_ada): ?>

                        <img
                            src="<?= htmlspecialchars($foto_siswa_url); ?>"
                            alt="Foto siswa"
                        >

                <?php else: ?>

                        <?= htmlspecialchars($inisial_siswa); ?>

                <?php endif; ?>

            </div>


            <div class="guru-detail-student-info">

                <h2 class="guru-detail-student-name">

                    <?= htmlspecialchars($nama_siswa); ?>

                </h2>


                <div class="guru-detail-student-meta">

                    <span>

                        <i class="bi bi-person-vcard"></i>

                        NIS:

                        <?= htmlspecialchars(
                            $data['nis'] ?? '-'
                        ); ?>

                    </span>


                    <span>

                        <i class="bi bi-mortarboard"></i>

                        Kelas:

                        <?= htmlspecialchars(
                            $data['kelas'] ?? '-'
                        ); ?>

                    </span>


                    <span>

                        <i class="bi bi-code-slash"></i>

                        Jurusan:

                        <?= htmlspecialchars(
                            $data['jurusan'] ?? '-'
                        ); ?>

                    </span>

                </div>

            </div>

        </div>


        <!-- =================================================
             BODY
        ================================================== -->

        <div class="guru-detail-body">


            <!-- =================================================
                 INFO
            ================================================== -->

            <div class="guru-detail-info-grid">


                <div class="guru-detail-info-item">

                    <p class="guru-detail-info-label">
                        Tanggal Konseling
                    </p>

                    <p class="guru-detail-info-value">

                        <?= htmlspecialchars(
                            formatTanggalGuruDetail(
                                $data['tanggal'] ?? ''
                            )
                        ); ?>

                    </p>

                </div>


                <div class="guru-detail-info-item">

                    <p class="guru-detail-info-label">
                        Status Saat Ini
                    </p>

                    <p class="guru-detail-info-value">

                        <span
                            class="guru-detail-status <?= htmlspecialchars(
                                $status_data['class']
                            ); ?>"
                        >

                            <i
                                class="bi <?= htmlspecialchars(
                                    $status_data['icon']
                                ); ?>"
                            ></i>

                            <?= htmlspecialchars(
                                $status_data['text']
                            ); ?>

                        </span>

                    </p>

                </div>


                <div class="guru-detail-info-item">

                    <p class="guru-detail-info-label">
                        ID Konseling
                    </p>

                    <p class="guru-detail-info-value">

                        #<?= htmlspecialchars(
                            $data['id_konseling']
                        ); ?>

                    </p>

                </div>


            </div>


            <!-- =================================================
                 JUDUL
            ================================================== -->

            <h3 class="guru-detail-section-title">

                <i class="bi bi-chat-left-text-fill"></i>

                Detail Konseling

            </h3>


            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
                action="konseling_detail_guru.php?id=<?= $id_konseling; ?>&from=<?= urlencode($from); ?>"
            >


                <!-- =============================================
                     MASALAH / KELUHAN
                ============================================== -->

                <div class="guru-problem-section">


                    <div class="guru-problem-title">

                        <i class="bi bi-exclamation-circle-fill"></i>

                        Masalah / Keluhan

                    </div>


                    <div class="guru-problem-content"><?php

                    if ($masalah !== '') {

                        echo nl2br(
                            htmlspecialchars(
                                $masalah,
                                ENT_QUOTES,
                                'UTF-8'
                            )
                        );

                    } else {

                        echo 'Belum ada keterangan masalah.';

                    }

                    ?></div>


                </div>


                <!-- =============================================
                     SOLUSI / CATATAN
                ============================================== -->

                <div class="guru-solution-section">


                    <div class="guru-solution-title">

                        <i class="bi bi-journal-text"></i>

                        Solusi / Catatan Guru BK

                    </div>


                    <div class="guru-solution-box">

                        <textarea
                            name="solusi"
                            class="guru-solution-textarea"
                            placeholder="Tulis solusi atau catatan untuk siswa di sini..."
                        ><?= htmlspecialchars(
                            $solusi,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?></textarea>

                    </div>


                </div>


                <!-- =============================================
                     STATUS
                ============================================== -->

                <div class="guru-form-status">


                    <label
                        for="status"
                        class="guru-form-status-label"
                    >

                        <i class="bi bi-flag-fill"></i>

                        Status Konseling

                    </label>


                    <select
                        name="status"
                        id="status"
                        class="guru-form-status-select"
                        required
                    >

                        <option
                            value="menunggu"
                            <?= (
                                $data['status'] === 'menunggu'
                            )
                                ? 'selected'
                                : ''; ?>
                        >
                            Menunggu
                        </option>


                        <option
                            value="terjadwal"
                            <?= (
                                $data['status'] === 'terjadwal'
                            )
                                ? 'selected'
                                : ''; ?>
                        >
                            Terjadwal
                        </option>


                        <option
                            value="selesai"
                            <?= (
                                $data['status'] === 'selesai'
                            )
                                ? 'selected'
                                : ''; ?>
                        >
                            Selesai
                        </option>


                        <option
                            value="batal"
                            <?= (
                                $data['status'] === 'batal'
                            )
                                ? 'selected'
                                : ''; ?>
                        >
                            Batal
                        </option>

                    </select>

                </div>


                <!-- =============================================
                     SIMPAN
                ============================================== -->

                <div class="guru-detail-save-area">

                    <button
                        type="submit"
                        class="guru-detail-save-button"
                    >

                        <i class="bi bi-save"></i>

                        Simpan Solusi & Status

                    </button>

                </div>


            </form>


            <!-- =================================================
                 GURU PENANGGUNG JAWAB
            ================================================== -->

            <div class="guru-detail-responsible">


                <div class="guru-detail-responsible-avatar">

                    <?php if ($foto_guru_ada): ?>

                            <img
                                src="<?= htmlspecialchars(
                                    $foto_guru_url
                                ); ?>"
                                alt="Foto Guru BK"
                            >

                    <?php else: ?>

                            <i class="bi bi-person-badge-fill"></i>

                    <?php endif; ?>

                </div>


                <div class="guru-detail-responsible-text">

                    <p class="guru-detail-responsible-label">

                        Guru BK Penanggung Jawab

                    </p>


                    <p class="guru-detail-responsible-name">

                        <?= htmlspecialchars(
                            $data['nama_guru'] ?? '-'
                        ); ?>


                        <?php if (!empty($data['nip'])): ?>

                                · NIP

                                <?= htmlspecialchars(
                                    $data['nip']
                                ); ?>

                        <?php endif; ?>

                    </p>

                </div>


            </div>


        </div>


    </div>


    <!-- =====================================================
         SATU TOMBOL KEMBALI
    ====================================================== -->

    <div class="guru-detail-back-area">

        <a
            href="<?= htmlspecialchars($back_url); ?>"
            class="guru-detail-back-button"
        >

            <i class="bi bi-arrow-left"></i>

            <?= htmlspecialchars($back_text); ?>

        </a>

    </div>


</div>


<?php require_once __DIR__ . "/layout/footer_guru.php"; ?>