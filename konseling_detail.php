<?php
session_start();

require_once __DIR__ . '/config/database.php';

/*
 * |--------------------------------------------------------------------------
 * | CEK LOGIN ADMIN
 * |--------------------------------------------------------------------------
 */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

/*
 * |--------------------------------------------------------------------------
 * | AMBIL ID KONSELING
 * |--------------------------------------------------------------------------
 */
$id_konseling = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_konseling <= 0) {
    header('Location: konseling.php?error=id');
    exit;
}

/*
 * |--------------------------------------------------------------------------
 * | AMBIL DATA KONSELING
 * |--------------------------------------------------------------------------
 * | LEFT JOIN digunakan agar data tetap bisa ditampilkan meskipun
 * | siswa atau guru BK sudah dihapus.
 * |--------------------------------------------------------------------------
 */

$stmt = mysqli_prepare($conn, '
    SELECT
        k.id_konseling,
        k.id_siswa,
        k.id_guru,
        k.tanggal,
        k.status,
        k.masalah,
        k.solusi,

        s.nis,
        s.nama AS nama_siswa,
        s.kelas,
        s.jurusan,

        g.nip,
        g.nama AS nama_guru

    FROM konseling k

    LEFT JOIN siswa s
        ON k.id_siswa = s.id_siswa

    LEFT JOIN guru_bk g
        ON k.id_guru = g.id_guru

    WHERE k.id_konseling = ?

    LIMIT 1
');

if (!$stmt) {
    die('Query gagal: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'i', $id_konseling);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/*
 * |--------------------------------------------------------------------------
 * | DATA TIDAK DITEMUKAN
 * |--------------------------------------------------------------------------
 */

if (!$data) {
    header('Location: konseling.php?error=notfound');
    exit;
}

/*
 * |--------------------------------------------------------------------------
 * | DATA SISWA
 * |--------------------------------------------------------------------------
 */

$nama_siswa = !empty($data['nama_siswa'])
    ? $data['nama_siswa']
    : 'Siswa Sudah Dihapus';

$nis = !empty($data['nis'])
    ? $data['nis']
    : '-';

$kelas = !empty($data['kelas'])
    ? $data['kelas']
    : '-';

$jurusan = !empty($data['jurusan'])
    ? $data['jurusan']
    : '-';

/*
 * |--------------------------------------------------------------------------
 * | DATA GURU
 * |--------------------------------------------------------------------------
 */

$nama_guru = !empty($data['nama_guru'])
    ? $data['nama_guru']
    : 'Belum Ditentukan';

$nip = !empty($data['nip'])
    ? $data['nip']
    : '-';

/*
 * |--------------------------------------------------------------------------
 * | STATUS
 * |--------------------------------------------------------------------------
 */

$status = strtolower(trim($data['status'] ?? ''));

switch ($status) {
    case 'menunggu':
        $status_class = 'status-menunggu';
        $status_icon = 'bi-hourglass-split';
        $status_text = 'Menunggu';
        break;

    case 'terjadwal':
        $status_class = 'status-terjadwal';
        $status_icon = 'bi-calendar-check';
        $status_text = 'Terjadwal';
        break;

    case 'selesai':
        $status_class = 'status-selesai';
        $status_icon = 'bi-check-circle';
        $status_text = 'Selesai';
        break;

    case 'batal':
        $status_class = 'status-batal';
        $status_icon = 'bi-x-circle';
        $status_text = 'Batal';
        break;

    default:
        $status_class = 'status-lain';
        $status_icon = 'bi-question-circle';
        $status_text = !empty($data['status'])
            ? ucfirst($data['status'])
            : 'Belum Ada';
        break;
}

/*
 * |--------------------------------------------------------------------------
 * | TANGGAL
 * |--------------------------------------------------------------------------
 */

$tanggal = !empty($data['tanggal'])
    ? date('d F Y', strtotime($data['tanggal']))
    : '-';

/*
 * |--------------------------------------------------------------------------
 * | INITIAL SISWA
 * |--------------------------------------------------------------------------
 */

$initial = !empty($data['nama_siswa'])
    ? strtoupper(substr($data['nama_siswa'], 0, 1))
    : '?';

$title = 'Detail Konseling';

require_once __DIR__ . '/layout/header.php';
?>

<style>

/* =========================================================
   DETAIL KONSELING
========================================================= */

.detail-konseling-page {
    width: 100%;
    padding-bottom: 30px;
}

/* =========================================================
   HEADER
========================================================= */

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 15px;

    margin-bottom: 24px;
}

.detail-header-left h1 {
    margin: 0;

    color: var(--text-main);

    font-size: 28px;
    font-weight: 700;
}

.detail-header-left h1 i {
    color: #0d6efd;
    margin-right: 8px;
}

.detail-header-left p {
    margin: 6px 0 0;

    color: var(--text-muted);

    font-size: 14px;
}

.btn-kembali {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 10px 16px;

    background: var(--bg-card);
    color: var(--text-main);

    border: 1px solid var(--border-color);
    border-radius: 10px;

    text-decoration: none;

    font-size: 13px;
    font-weight: 600;

    transition: .2s ease;
}

.btn-kembali:hover {
    background: var(--bg-body);
    color: var(--text-main);
}

/* =========================================================
   GRID
========================================================= */

.detail-grid {
    display: grid;

    grid-template-columns: minmax(0, 1fr) minmax(280px, 360px);

    gap: 20px;
}

/* =========================================================
   CARD
========================================================= */

.detail-card {
    background: var(--bg-card);

    border: 1px solid var(--border-color);
    border-radius: 16px;

    overflow: hidden;

    box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
}

.detail-card-header {
    padding: 18px 22px;

    border-bottom: 1px solid var(--border-color);
}

.detail-card-header-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.detail-icon {
    width: 40px;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: rgba(13, 110, 253, .12);
    color: #0d6efd;

    border-radius: 10px;

    font-size: 18px;
}

.detail-card-header h5 {
    margin: 0;

    color: var(--text-main);

    font-size: 16px;
    font-weight: 700;
}

.detail-card-header p {
    margin: 3px 0 0;

    color: var(--text-muted);

    font-size: 12px;
}

.detail-card-body {
    padding: 22px;
}

/* =========================================================
   PROFIL SISWA
========================================================= */

.profil-siswa {
    display: flex;
    align-items: center;

    gap: 15px;

    padding: 18px;

    margin-bottom: 22px;

    background: var(--bg-body);

    border: 1px solid var(--border-color);
    border-radius: 14px;
}

.profil-avatar {
    width: 58px;
    height: 58px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: rgba(13, 110, 253, .12);
    color: #0d6efd;

    border-radius: 14px;

    font-size: 22px;
    font-weight: 700;
}

.profil-nama {
    color: var(--text-main);

    font-size: 17px;
    font-weight: 700;
}

.profil-nis {
    margin-top: 3px;

    color: var(--text-muted);

    font-size: 12px;
}

/* =========================================================
   DETAIL ITEM
========================================================= */

.detail-list {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 18px;
}

.detail-item {
    min-width: 0;
}

.detail-item.full {
    grid-column: 1 / -1;
}

.detail-label {
    display: flex;
    align-items: center;
    gap: 7px;

    margin-bottom: 7px;

    color: var(--text-muted);

    font-size: 12px;
    font-weight: 600;
}

.detail-label i {
    color: #0d6efd;
}

.detail-value {
    color: var(--text-main);

    font-size: 14px;
    font-weight: 600;

    word-break: break-word;
}

/* =========================================================
   MASALAH & SOLUSI
========================================================= */

.detail-text-box {
    padding: 15px;

    background: var(--bg-body);

    border: 1px solid var(--border-color);
    border-radius: 12px;

    color: var(--text-main);

    font-size: 13px;
    line-height: 1.7;

    white-space: pre-wrap;
}

.detail-text-box.empty {
    color: var(--text-muted);
    font-style: italic;
}

/* =========================================================
   GURU BK CARD
========================================================= */

.guru-profile {
    display: flex;
    align-items: center;

    gap: 13px;

    margin-bottom: 22px;
}

.guru-avatar {
    width: 50px;
    height: 50px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: rgba(13, 110, 253, .12);
    color: #0d6efd;

    border-radius: 12px;

    font-size: 20px;
}

.guru-profile-name {
    color: var(--text-main);

    font-size: 15px;
    font-weight: 700;
}

.guru-profile-nip {
    margin-top: 3px;

    color: var(--text-muted);

    font-size: 11px;
}

.info-list {
    display: flex;
    flex-direction: column;

    gap: 14px;
}

.info-item {
    display: flex;
    align-items: flex-start;

    gap: 12px;

    padding-bottom: 14px;

    border-bottom: 1px solid var(--border-color);
}

.info-item:last-child {
    padding-bottom: 0;
    border-bottom: none;
}

.info-item-icon {
    width: 34px;
    height: 34px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: rgba(13, 110, 253, .10);
    color: #0d6efd;

    border-radius: 9px;

    font-size: 14px;
}

.info-item-label {
    color: var(--text-muted);

    font-size: 11px;

    margin-bottom: 2px;
}

.info-item-value {
    color: var(--text-main);

    font-size: 13px;
    font-weight: 600;
}

/* =========================================================
   STATUS
========================================================= */

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    padding: 7px 11px;

    border-radius: 8px;

    font-size: 11px;
    font-weight: 700;
}

.status-menunggu {
    background: rgba(255, 193, 7, .12);
    color: #b58100;
}

.status-terjadwal {
    background: rgba(13, 110, 253, .10);
    color: #0d6efd;
}

.status-selesai {
    background: rgba(25, 135, 84, .10);
    color: #198754;
}

.status-batal {
    background: rgba(220, 53, 69, .10);
    color: #dc3545;
}

.status-lain {
    background: rgba(108, 117, 125, .10);
    color: #6c757d;
}

/* =========================================================
   FOOTER INFO
========================================================= */

.detail-note {
    margin-top: 20px;

    padding: 14px 16px;

    background: rgba(13, 110, 253, .06);

    border: 1px solid rgba(13, 110, 253, .12);
    border-radius: 12px;

    color: var(--text-muted);

    font-size: 12px;
    line-height: 1.6;
}

.detail-note i {
    color: #0d6efd;
    margin-right: 5px;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 992px) {

    .detail-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {

    .detail-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .btn-kembali {
        width: 100%;
        justify-content: center;
    }

    .detail-list {
        grid-template-columns: 1fr;
    }

    .detail-item.full {
        grid-column: auto;
    }

    .profil-siswa {
        align-items: flex-start;
    }
}

</style>


<div class="detail-konseling-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="detail-header">

        <div class="detail-header-left">

            <h1>
                <i class="bi bi-chat-left-text"></i>
                Detail Konseling
            </h1>

            <p>
                Informasi lengkap pengajuan bimbingan dan konseling
            </p>

        </div>

        <?php
        $from = $_GET['from'] ?? 'konseling';

        if ($from === 'riwayat') {
            $kembali = 'riwayat_konseling.php';
        } else {
            $kembali = 'konseling.php';
        }
        ?>

<a href="<?= $kembali; ?>" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Kembali
</a>

    </div>


    <!-- =====================================================
         GRID DETAIL
    ====================================================== -->

    <div class="detail-grid">


        <!-- =================================================
             DETAIL UTAMA
        ================================================== -->

        <div class="detail-card">

            <div class="detail-card-header">

                <div class="detail-card-header-content">

                    <div class="detail-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <div>

                        <h5>
                            Informasi Konseling
                        </h5>

                        <p>
                            Detail pengajuan dan proses konseling
                        </p>

                    </div>

                </div>

            </div>


            <div class="detail-card-body">


                <!-- PROFIL SISWA -->

                <div class="profil-siswa">

                    <div class="profil-avatar">
                        <?= htmlspecialchars($initial); ?>
                    </div>

                    <div>

                        <div class="profil-nama">
                            <?= htmlspecialchars($nama_siswa); ?>
                        </div>

                        <div class="profil-nis">
                            NIS: <?= htmlspecialchars($nis); ?>
                        </div>

                    </div>

                </div>


                <!-- INFORMASI -->

                <div class="detail-list">


                    <!-- KELAS -->

                    <div class="detail-item">

                        <div class="detail-label">
                            <i class="bi bi-mortarboard"></i>
                            Kelas
                        </div>

                        <div class="detail-value">
                            <?= htmlspecialchars($kelas); ?>
                        </div>

                    </div>


                    <!-- JURUSAN -->

                    <div class="detail-item">

                        <div class="detail-label">
                            <i class="bi bi-book"></i>
                            Jurusan
                        </div>

                        <div class="detail-value">
                            <?= htmlspecialchars($jurusan); ?>
                        </div>

                    </div>


                    <!-- TANGGAL -->

                    <div class="detail-item">

                        <div class="detail-label">
                            <i class="bi bi-calendar3"></i>
                            Tanggal Konseling
                        </div>

                        <div class="detail-value">
                            <?= htmlspecialchars($tanggal); ?>
                        </div>

                    </div>


                    <!-- STATUS -->

                    <div class="detail-item">

                        <div class="detail-label">
                            <i class="bi bi-flag"></i>
                            Status
                        </div>

                        <div class="detail-value">

                            <span class="status-badge <?= $status_class; ?>">

                                <i class="bi <?= $status_icon; ?>"></i>

                                <?= htmlspecialchars($status_text); ?>

                            </span>

                        </div>

                    </div>


                    <!-- MASALAH -->

                    <div class="detail-item full">

                        <div class="detail-label">
                            <i class="bi bi-chat-left-text"></i>
                            Masalah / Keluhan
                        </div>

                        <?php if (!empty($data['masalah'])): ?>

                            <div class="detail-text-box">
                                <?= htmlspecialchars($data['masalah']); ?>
                            </div>

                        <?php else: ?>

                            <div class="detail-text-box empty">
                                Tidak ada masalah atau keluhan yang dicatat.
                            </div>

                        <?php endif; ?>

                    </div>


                    <!-- SOLUSI -->

                    <div class="detail-item full">

                        <div class="detail-label">
                            <i class="bi bi-lightbulb"></i>
                            Solusi / Catatan Guru BK
                        </div>

                        <?php if (!empty($data['solusi'])): ?>

                            <div class="detail-text-box">
                                <?= htmlspecialchars($data['solusi']); ?>
                            </div>

                        <?php else: ?>

                            <div class="detail-text-box empty">
                                Belum ada solusi atau catatan dari Guru BK.
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             GURU BK
        ================================================== -->

        <div>

            <div class="detail-card">

                <div class="detail-card-header">

                    <div class="detail-card-header-content">

                        <div class="detail-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>

                        <div>

                            <h5>
                                Guru BK
                            </h5>

                            <p>
                                Guru yang menangani konseling
                            </p>

                        </div>

                    </div>

                </div>


                <div class="detail-card-body">


                    <!-- PROFIL GURU -->

                    <div class="guru-profile">

                        <div class="guru-avatar">
                            <i class="bi bi-person"></i>
                        </div>

                        <div>

                            <div class="guru-profile-name">
                                <?= htmlspecialchars($nama_guru); ?>
                            </div>

                            <div class="guru-profile-nip">
                                NIP: <?= htmlspecialchars($nip); ?>
                            </div>

                        </div>

                    </div>


                    <!-- INFO -->

                    <div class="info-list">


                        <!-- NIP -->

                        <div class="info-item">

                            <div class="info-item-icon">
                                <i class="bi bi-person-vcard"></i>
                            </div>

                            <div>

                                <div class="info-item-label">
                                    NIP
                                </div>

                                <div class="info-item-value">
                                    <?= htmlspecialchars($nip); ?>
                                </div>

                            </div>

                        </div>


                        <!-- TANGGAL -->

                        <div class="info-item">

                            <div class="info-item-icon">
                                <i class="bi bi-calendar-event"></i>
                            </div>

                            <div>

                                <div class="info-item-label">
                                    Tanggal Pengajuan
                                </div>

                                <div class="info-item-value">
                                    <?= htmlspecialchars($tanggal); ?>
                                </div>

                            </div>

                        </div>


                        <!-- STATUS -->

                        <div class="info-item">

                            <div class="info-item-icon">
                                <i class="bi bi-activity"></i>
                            </div>

                            <div>

                                <div class="info-item-label">
                                    Status Konseling
                                </div>

                                <div class="info-item-value">

                                    <span class="status-badge <?= $status_class; ?>">

                                        <i class="bi <?= $status_icon; ?>"></i>

                                        <?= htmlspecialchars($status_text); ?>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- CATATAN -->

                    <div class="detail-note">

                        <i class="bi bi-info-circle"></i>

                        Halaman ini hanya digunakan untuk melihat
                        detail konseling. Perubahan data konseling
                        dilakukan oleh Guru BK.

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/layout/footer.php';
?>