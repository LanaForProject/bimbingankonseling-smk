<?php

session_start();

require_once "config/database.php";


/*
|--------------------------------------------------------------------------
| CEK LOGIN SISWA
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'siswa'
) {
    header("Location: login.php");
    exit;
}

$siswa_id = (int) ($_SESSION['siswa_id'] ?? 0);

if ($siswa_id <= 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL ID KONSELING
|--------------------------------------------------------------------------
*/

$id_konseling = (int) ($_GET['id'] ?? 0);

if ($id_konseling <= 0) {
    header("Location: riwayat_konseling_siswa.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| TENTUKAN HALAMAN ASAL
|--------------------------------------------------------------------------
*/

$from = $_GET['from'] ?? 'riwayat';

if ($from === 'dashboard') {

    $back_url = 'dashboard_siswa.php';
    $back_text = 'Kembali ke Dashboard';

} else {

    $back_url = 'riwayat_konseling_siswa.php';
    $back_text = 'Kembali ke Riwayat Konseling';

}


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

        g.nama AS nama_guru,
        g.nip AS nip_guru,
        g.foto AS foto_guru

    FROM konseling k

    LEFT JOIN guru_bk g
        ON k.id_guru = g.id_guru

    WHERE
        k.id_konseling = ?
        AND k.id_siswa = ?

    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $id_konseling,
    $siswa_id
);

$stmt->execute();

$result = $stmt->get_result();

$konseling = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| JIKA DATA TIDAK DITEMUKAN
|--------------------------------------------------------------------------
*/

if (!$konseling) {

    header("Location: " . $back_url);
    exit;

}


/*
|--------------------------------------------------------------------------
| DATA
|--------------------------------------------------------------------------
*/

$nama_guru = $konseling['nama_guru'] ?? '';
$nip_guru = $konseling['nip_guru'] ?? '';
$foto_guru = $konseling['foto_guru'] ?? '';

$masalah = $konseling['masalah'] ?? '';
$solusi = $konseling['solusi'] ?? '';

$status = strtolower(
    trim($konseling['status'] ?? '')
);


/*
|--------------------------------------------------------------------------
| FOTO GURU
|--------------------------------------------------------------------------
*/

$foto_guru_url = '';

if (!empty($foto_guru)) {

    $foto_guru_url = 'uploads/guru/' . $foto_guru;

}


/*
|--------------------------------------------------------------------------
| FORMAT TANGGAL
|--------------------------------------------------------------------------
*/

$tanggal_formatted = '-';

if (!empty($konseling['tanggal'])) {

    $timestamp = strtotime($konseling['tanggal']);

    if ($timestamp !== false) {

        $tanggal_formatted = date(
            'd F Y, H:i',
            $timestamp
        ) . ' WIB';

    }

}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

$status_class = 'status-menunggu';
$status_icon = 'bi-clock-fill';
$status_text = 'Menunggu';

switch ($status) {

    case 'terjadwal':

        $status_class = 'status-terjadwal';
        $status_icon = 'bi-calendar-check-fill';
        $status_text = 'Terjadwal';

        break;


    case 'selesai':

        $status_class = 'status-selesai';
        $status_icon = 'bi-check-circle-fill';
        $status_text = 'Selesai';

        break;


    case 'batal':

        $status_class = 'status-batal';
        $status_icon = 'bi-x-circle-fill';
        $status_text = 'Batal';

        break;


    case 'menunggu':

        $status_class = 'status-menunggu';
        $status_icon = 'bi-clock-fill';
        $status_text = 'Menunggu';

        break;


    default:

        $status_class = 'status-menunggu';
        $status_icon = 'bi-clock-fill';

        $status_text = !empty($konseling['status'])
            ? ucfirst($konseling['status'])
            : 'Menunggu';

        break;
}


/*
|--------------------------------------------------------------------------
| HEADER SISWA
|--------------------------------------------------------------------------
*/

require_once "layout/header_siswa.php";

?>

<style>
    /* =========================================================
   DETAIL PAGE
========================================================= */

    .detail-page {
        width: 100%;
        max-width: 900px;

        margin: 0 auto;

        padding-bottom: 25px;
    }


    /* =========================================================
   HEADER
========================================================= */

    .detail-header {
        margin-bottom: 18px;
    }

    .detail-title {
        margin: 0;

        font-size: 23px;
        font-weight: 700;

        color: var(--text-primary, #ffffff);
    }

    .detail-subtitle {
        margin: 5px 0 0;

        font-size: 11px;
        line-height: 1.6;

        color: var(--text-muted, #9aa4bd);
    }


    /* =========================================================
   CARD
========================================================= */

    .detail-card {
        width: 100%;

        overflow: hidden;

        background: var(--card-bg, #0d1a3a);

        border: 1px solid var(--border-color, #20335f);

        border-radius: 14px;

        box-shadow: 0 8px 25px rgba(0, 0, 0, .10);
    }


    /* =========================================================
   CARD HEADER
========================================================= */

    .detail-card-header {
        min-height: 70px;

        padding: 15px 18px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        border-bottom: 1px solid var(--border-color, #20335f);
    }

    .detail-card-title {
        display: flex;

        align-items: center;

        gap: 10px;
    }

    .detail-card-icon {
        width: 36px;
        height: 36px;

        flex-shrink: 0;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 9px;

        background: rgba(13, 110, 253, .13);

        color: #4d91ff;

        font-size: 15px;
    }

    .detail-card-title h3 {
        margin: 0;

        font-size: 13px;
        font-weight: 700;

        color: var(--text-primary, #ffffff);
    }

    .detail-card-title p {
        margin: 3px 0 0;

        font-size: 9px;

        color: var(--text-muted, #9aa4bd);
    }


    /* =========================================================
   STATUS
========================================================= */

    .status-badge {
        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 6px 9px;

        border-radius: 7px;

        font-size: 9px;
        font-weight: 600;

        white-space: nowrap;
    }

    .status-terjadwal {
        background: rgba(13, 110, 253, .13);
        color: #4d91ff;
    }

    .status-selesai {
        background: rgba(25, 135, 84, .14);
        color: #45c982;
    }

    .status-batal {
        background: rgba(220, 53, 69, .14);
        color: #ff6b7a;
    }

    .status-menunggu {
        background: rgba(255, 193, 7, .14);
        color: #ffc107;
    }


    /* =========================================================
   CONTENT
========================================================= */

    .detail-content {
        padding: 18px;
    }


    /* =========================================================
   INFO GRID
========================================================= */

    .info-grid {
        display: grid;

        grid-template-columns: repeat(2, 1fr);

        gap: 12px;

        margin-bottom: 16px;
    }

    .info-box {
        padding: 13px 14px;

        border-radius: 10px;

        background: rgba(255, 255, 255, .025);

        border: 1px solid var(--border-color, #20335f);
    }

    .info-label {
        display: flex;

        align-items: center;

        gap: 6px;

        margin-bottom: 6px;

        font-size: 8px;
        font-weight: 600;

        text-transform: uppercase;

        letter-spacing: .3px;

        color: var(--text-muted, #9aa4bd);
    }

    .info-label i {
        font-size: 10px;

        color: #4d91ff;
    }

    .info-value {
        font-size: 11px;

        font-weight: 600;

        color: var(--text-primary, #ffffff);
    }


    /* =========================================================
   GURU BK
========================================================= */

    .guru-box {
        width: 100%;

        min-height: 74px;

        box-sizing: border-box;

        display: flex;

        align-items: center;

        gap: 12px;

        padding: 13px 14px;

        margin-bottom: 17px;

        border-radius: 11px;

        background: rgba(13, 110, 253, .055);

        border: 1px solid rgba(13, 110, 253, .18);
    }


    /* =========================================================
   FOTO GURU
========================================================= */

    .guru-photo {
        width: 48px;
        height: 48px;

        flex-shrink: 0;

        border-radius: 50%;

        object-fit: cover;

        border: 2px solid rgba(77, 145, 255, .45);

        background: #1769e8;
    }

    .guru-photo-fallback {
        width: 48px;
        height: 48px;

        flex-shrink: 0;

        border-radius: 50%;

        display: flex;

        align-items: center;

        justify-content: center;

        background: #1769e8;

        color: #ffffff;

        font-size: 19px;
    }


    /* =========================================================
   INFO GURU
========================================================= */

    .guru-info {
        min-width: 0;
    }

    .guru-label {
        margin-bottom: 2px;

        font-size: 8px;

        color: var(--text-muted, #9aa4bd);
    }

    .guru-name {
        font-size: 13px;

        font-weight: 700;

        color: var(--text-primary, #ffffff);
    }

    .guru-nip {
        margin-top: 2px;

        font-size: 9px;

        color: var(--text-muted, #9aa4bd);
    }


    /* =========================================================
   BAGIAN PESAN
========================================================= */

    .message-section {
        width: 100%;

        margin-top: 16px;
    }


    /* =========================================================
   JUDUL PESAN
========================================================= */

    .message-title {
        width: 100%;

        margin: 0 0 7px 0;

        padding: 0;

        display: flex;

        align-items: center;

        gap: 7px;

        font-size: 10px;

        font-weight: 700;

        color: var(--text-primary, #ffffff);
    }

    .message-title i {
        color: #4d91ff;

        font-size: 11px;
    }


    /* =========================================================
   KOTAK PESAN
   TIDAK MENGGUNAKAN FLEX
========================================================= */

    .message-box {
        width: 100%;

        min-height: 120px;

        box-sizing: border-box;

        margin: 0;

        padding: 13px;

        position: relative;

        display: block !important;

        border-radius: 10px;

        background: rgba(255, 255, 255, .025);

        border: 1px solid var(--border-color, #20335f);

        overflow: hidden;

        text-align: left !important;

        vertical-align: top !important;
    }


    /* =========================================================
   ISI PESAN
   POSISI DIKUNCI KE ATAS
========================================================= */

    .message-box-content {
        position: absolute !important;

        top: 13px !important;

        left: 13px !important;

        right: 13px !important;

        bottom: auto !important;

        width: auto !important;

        height: auto !important;

        min-height: 0 !important;

        max-height: none !important;

        margin: 0 !important;

        padding: 0 !important;

        display: block !important;

        align-items: initial !important;

        justify-content: initial !important;

        flex-direction: initial !important;

        text-align: left !important;

        vertical-align: top !important;

        transform: none !important;

        translate: none !important;

        color: var(--text-secondary, #c5cbe0);

        font-size: 10px;

        font-weight: 400;

        line-height: 1.7;

        white-space: normal;

        overflow-wrap: break-word;

        word-break: normal;
    }


    /* =========================================================
   PARAGRAPH DI DALAM PESAN
========================================================= */

    .message-box-content p {
        margin: 0 !important;

        padding: 0 !important;

        text-align: left !important;

        line-height: 1.7 !important;
    }


    /* =========================================================
   TOMBOL KEMBALI
========================================================= */

    .detail-footer {
        display: flex;

        justify-content: flex-start;

        margin-top: 18px;
    }

    .btn-kembali-bottom {
        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 9px 13px;

        border-radius: 8px;

        border: 1px solid var(--border-color, #20335f);

        background: transparent;

        color: var(--text-primary, #ffffff);

        font-size: 10px;

        font-weight: 600;

        text-decoration: none;

        transition: .2s ease;
    }

    .btn-kembali-bottom:hover {
        background: rgba(255, 255, 255, .06);

        color: var(--text-primary, #ffffff);

        transform: translateX(-2px);
    }

    .btn-kembali-bottom i {
        font-size: 11px;
    }


    /* =========================================================
   LIGHT MODE
========================================================= */

    [data-theme="light"] .detail-title,
    [data-theme="light"] .detail-card-title h3,
    [data-theme="light"] .info-value,
    [data-theme="light"] .guru-name,
    [data-theme="light"] .message-title {
        color: #20242d;
    }

    [data-theme="light"] .detail-subtitle,
    [data-theme="light"] .detail-card-title p,
    [data-theme="light"] .info-label,
    [data-theme="light"] .guru-label,
    [data-theme="light"] .guru-nip {
        color: #6b7280;
    }

    [data-theme="light"] .detail-card {
        background: #ffffff;

        border-color: #e5e7eb;
    }

    [data-theme="light"] .detail-card-header {
        border-color: #e5e7eb;
    }

    [data-theme="light"] .info-box {
        background: #f8f9fb;

        border-color: #e5e7eb;
    }

    [data-theme="light"] .message-box {
        background: #f8f9fb;

        border-color: #e5e7eb;
    }

    [data-theme="light"] .message-box-content {
        color: #4b5563;
    }

    [data-theme="light"] .guru-box {
        background: #f5f9ff;

        border-color: #dbeafe;
    }

    [data-theme="light"] .btn-kembali-bottom {
        color: #20242d;

        border-color: #d8dce3;
    }

    [data-theme="light"] .btn-kembali-bottom:hover {
        background: #f3f4f6;

        color: #20242d;
    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 700px) {

        .detail-page {
            max-width: 100%;
        }

        .detail-card-header {
            align-items: flex-start;

            flex-direction: column;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 480px) {

        .detail-title {
            font-size: 20px;
        }

        .detail-content {
            padding: 14px;
        }

        .detail-card-header {
            padding: 14px;
        }

        .guru-photo,
        .guru-photo-fallback {
            width: 43px;
            height: 43px;
        }

        .message-box {
            min-height: 110px;
        }

    }
</style>


<main class="main-content">

    <div class="detail-page">


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="detail-header">

            <h1 class="detail-title">
                Detail Konseling
            </h1>

            <p class="detail-subtitle">
                Informasi lengkap mengenai pengajuan bimbingan konseling kamu.
            </p>

        </div>


        <!-- =====================================================
             CARD
        ====================================================== -->

        <div class="detail-card">


            <!-- =================================================
                 CARD HEADER
            ================================================== -->

            <div class="detail-card-header">

                <div class="detail-card-title">

                    <div class="detail-card-icon">

                        <i class="bi bi-chat-square-text-fill"></i>

                    </div>

                    <div>

                        <h3>
                            Konseling #<?= (int) $konseling['id_konseling']; ?>
                        </h3>

                        <p>
                            Detail sesi bimbingan konseling
                        </p>

                    </div>

                </div>


                <!-- STATUS -->

                <div class="status-badge <?= htmlspecialchars($status_class); ?>">

                    <i class="bi <?= htmlspecialchars($status_icon); ?>"></i>

                    <?= htmlspecialchars($status_text); ?>

                </div>

            </div>


            <!-- =================================================
                 CONTENT
            ================================================== -->

            <div class="detail-content">


                <!-- =================================================
                     TANGGAL & STATUS
                ================================================== -->

                <div class="info-grid">


                    <div class="info-box">

                        <div class="info-label">

                            <i class="bi bi-calendar3"></i>

                            Tanggal Konseling

                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars($tanggal_formatted); ?>

                        </div>

                    </div>


                    <div class="info-box">

                        <div class="info-label">

                            <i class="bi bi-info-circle"></i>

                            Status

                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars($status_text); ?>

                        </div>

                    </div>


                </div>


                <!-- =================================================
                     GURU BK
                ================================================== -->

                <div class="guru-box">


                    <?php if (!empty($foto_guru_url)): ?>

                        <img src="<?= htmlspecialchars($foto_guru_url); ?>"
                            alt="Foto <?= htmlspecialchars($nama_guru ?: 'Guru BK'); ?>" class="guru-photo"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                        <div class="guru-photo-fallback" style="display:none;">

                            <i class="bi bi-person-badge-fill"></i>

                        </div>

                    <?php else: ?>

                        <div class="guru-photo-fallback">

                            <i class="bi bi-person-badge-fill"></i>

                        </div>

                    <?php endif; ?>


                    <div class="guru-info">

                        <div class="guru-label">
                            Guru BK
                        </div>

                        <div class="guru-name">

                            <?php if (!empty($nama_guru)): ?>

                                <?= htmlspecialchars($nama_guru); ?>

                            <?php else: ?>

                                Belum ditentukan

                            <?php endif; ?>

                        </div>

                        <div class="guru-nip">

                            <?php if (!empty($nip_guru)): ?>

                                NIP:
                                <?= htmlspecialchars($nip_guru); ?>

                            <?php else: ?>

                                NIP belum tersedia

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     MASALAH / CERITA
                ================================================== -->

                <div class="message-section">

                    <div class="message-title">

                        <i class="bi bi-chat-left-text-fill"></i>

                        <span>
                            Masalah / Cerita
                        </span>

                    </div>


                    <div class="message-box">

                        <div class="message-box-content">

                            <?php if (trim($masalah) !== ''): ?>

                                <?= nl2br(htmlspecialchars($masalah)); ?>

                            <?php else: ?>

                                Belum ada keterangan masalah.

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     SOLUSI / CATATAN GURU BK
                ================================================== -->

                <div class="message-section">

                    <div class="message-title">

                        <i class="bi bi-lightbulb-fill"></i>

                        <span>
                            Solusi / Catatan Guru BK
                        </span>

                    </div>


                    <div class="message-box">

                        <div class="message-box-content">

                            <?php if (trim($solusi) !== ''): ?>

                                <?= nl2br(htmlspecialchars($solusi)); ?>

                            <?php else: ?>

                                Belum ada solusi atau catatan dari Guru BK.

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     TOMBOL KEMBALI
                ================================================== -->

                <div class="detail-footer">

                    <a href="<?= htmlspecialchars($back_url); ?>" class="btn-kembali-bottom">

                        <i class="bi bi-arrow-left"></i>

                        <?= htmlspecialchars($back_text); ?>

                    </a>

                </div>


            </div>

        </div>

    </div>

</main>


<?php

require_once "layout/footer_siswa.php";

?>