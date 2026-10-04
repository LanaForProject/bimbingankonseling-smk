<?php

/* =========================================================
   PROFIL GURU BK
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   CEK LOGIN
========================================================= */

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'guru'
) {
    header("Location: login.php");
    exit;
}


$guru_id = (int) ($_SESSION['guru_id'] ?? 0);

if ($guru_id <= 0) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php");
    exit;
}


/* =========================================================
   DATABASE
========================================================= */

require_once __DIR__ . "/config/database.php";


/* =========================================================
   PAGE TITLE
========================================================= */

$page_title = "Profil Saya | BK Online";


/* =========================================================
   DATA GURU
========================================================= */

$guru = null;

$stmt = $conn->prepare("
    SELECT
        id_guru,
        nip,
        nama,
        foto
    FROM guru_bk
    WHERE id_guru = ?
    LIMIT 1
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {
    $guru = $result->fetch_assoc();
}

$stmt->close();


/* =========================================================
   JIKA DATA GURU TIDAK DITEMUKAN
========================================================= */

if (!$guru) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php");
    exit;
}


/* =========================================================
   DATA GURU
========================================================= */

$guru_nama = $guru['nama'] ?? 'Guru BK';
$guru_nip = $guru['nip'] ?? '-';
$guru_foto = $guru['foto'] ?? '';


/* =========================================================
   FOTO GURU
========================================================= */

$guru_foto_ada = false;
$guru_foto_url = '';

if (!empty($guru_foto)) {

    $guru_foto_nama = basename(
        str_replace(
            '\\',
            '/',
            $guru_foto
        )
    );

    $guru_foto_path =
        __DIR__ .
        '/uploads/guru/' .
        $guru_foto_nama;

    if (is_file($guru_foto_path)) {

        $guru_foto_ada = true;

        $guru_foto_url =
            'uploads/guru/' .
            rawurlencode($guru_foto_nama);
    }
}


/* =========================================================
   STATISTIK KONSELING
========================================================= */

$total_konseling = 0;
$total_menunggu = 0;
$total_terjadwal = 0;
$total_selesai = 0;
$total_batal = 0;


/* =========================================================
   TOTAL KONSELING
========================================================= */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM konseling
    WHERE id_guru = ?
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {

    $row = $result->fetch_assoc();

    $total_konseling =
        (int) ($row['total'] ?? 0);
}

$stmt->close();


/* =========================================================
   MENUNGGU
========================================================= */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM konseling
    WHERE id_guru = ?
    AND status = 'menunggu'
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {

    $row = $result->fetch_assoc();

    $total_menunggu =
        (int) ($row['total'] ?? 0);
}

$stmt->close();


/* =========================================================
   TERJADWAL
========================================================= */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM konseling
    WHERE id_guru = ?
    AND status = 'terjadwal'
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {

    $row = $result->fetch_assoc();

    $total_terjadwal =
        (int) ($row['total'] ?? 0);
}

$stmt->close();


/* =========================================================
   SELESAI
========================================================= */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM konseling
    WHERE id_guru = ?
    AND status = 'selesai'
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {

    $row = $result->fetch_assoc();

    $total_selesai =
        (int) ($row['total'] ?? 0);
}

$stmt->close();


/* =========================================================
   BATAL
========================================================= */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM konseling
    WHERE id_guru = ?
    AND status = 'batal'
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {

    $row = $result->fetch_assoc();

    $total_batal =
        (int) ($row['total'] ?? 0);
}

$stmt->close();


/* =========================================================
   HEADER GURU
========================================================= */

require_once __DIR__ . "/layout/header_guru.php";

?>


<style>
    /* =========================================================
   PROFIL GURU
========================================================= */

    .profil-guru-page {
        width: 100%;
    }


    /* =========================================================
   HEADER
========================================================= */

    .profil-guru-header {
        margin-bottom: 24px;
    }

    .profil-guru-title {
        margin: 0;

        color: var(--text-main);

        font-size: 28px;
        line-height: 1.3;
        font-weight: 700;
    }

    .profil-guru-subtitle {
        margin: 7px 0 0;

        color: var(--text-muted);

        font-size: 13px;
    }


    /* =========================================================
   PROFILE HERO
========================================================= */

    .profil-guru-card {
        width: 100%;

        padding: 28px;

        display: flex;
        align-items: center;

        gap: 24px;

        background: var(--bg-card);

        border: 1px solid var(--border-color);
        border-radius: 14px;

        box-shadow: var(--shadow);

        margin-bottom: 20px;
    }


    /* =========================================================
   FOTO
========================================================= */

    .profil-guru-photo {
        width: 105px;
        height: 105px;

        min-width: 105px;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;

        border-radius: 50%;

        background: #1769e8;

        color: #fff;

        font-size: 42px;

        box-shadow:
            0 0 0 5px rgba(77, 145, 255, .10);
    }

    .profil-guru-photo img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;
    }


    /* =========================================================
   PROFILE INFO
========================================================= */

    .profil-guru-info {
        min-width: 0;
    }

    .profil-guru-name {
        margin: 0;

        color: var(--text-main);

        font-size: 24px;
        font-weight: 700;
    }

    .profil-guru-role {
        margin-top: 7px;

        display: inline-flex;
        align-items: center;

        gap: 6px;

        padding: 6px 10px;

        border-radius: 20px;

        background: rgba(57, 133, 255, .12);

        color: #4d91ff;

        font-size: 10px;
        font-weight: 700;
    }

    .profil-guru-role i {
        font-size: 10px;
    }

    .profil-guru-nip {
        margin-top: 10px;

        color: var(--text-muted);

        font-size: 12px;
    }

    .profil-guru-nip strong {
        color: var(--text-main);

        font-weight: 600;
    }


    /* =========================================================
   GRID
========================================================= */

    .profil-guru-grid {
        display: grid;

        grid-template-columns:
            minmax(0, 1fr) minmax(0, 1fr);

        gap: 20px;
    }


    /* =========================================================
   CARD
========================================================= */

    .profil-guru-section {
        background: var(--bg-card);

        border: 1px solid var(--border-color);
        border-radius: 14px;

        box-shadow: var(--shadow);

        overflow: hidden;
    }

    .profil-guru-section-header {
        min-height: 58px;

        padding: 15px 18px;

        display: flex;
        align-items: center;

        gap: 9px;

        border-bottom: 1px solid var(--border-color);

        color: var(--text-main);

        font-size: 14px;
        font-weight: 700;
    }

    .profil-guru-section-header i {
        color: #4d91ff;

        font-size: 17px;
    }

    .profil-guru-section-body {
        padding: 20px;
    }


    /* =========================================================
   DETAIL
========================================================= */

    .profil-guru-detail {
        display: flex;
        flex-direction: column;

        gap: 14px;
    }

    .profil-guru-detail-row {
        min-height: 45px;

        padding: 11px 13px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 15px;

        border: 1px solid var(--border-color);
        border-radius: 9px;

        background: var(--bg-body);
    }

    .profil-guru-detail-label {
        display: flex;
        align-items: center;

        gap: 8px;

        color: var(--text-muted);

        font-size: 11px;
    }

    .profil-guru-detail-label i {
        color: #4d91ff;

        font-size: 14px;
    }

    .profil-guru-detail-value {
        color: var(--text-main);

        font-size: 11px;
        font-weight: 600;

        text-align: right;
    }


    /* =========================================================
   STATISTIK
========================================================= */

    .profil-guru-stats {
        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 12px;
    }

    .profil-guru-stat {
        min-height: 105px;

        padding: 16px;

        display: flex;
        align-items: center;

        gap: 12px;

        border: 1px solid var(--border-color);
        border-radius: 11px;

        background: var(--bg-body);

        transition:
            transform .2s ease,
            border-color .2s ease;
    }

    .profil-guru-stat:hover {
        transform: translateY(-2px);

        border-color:
            rgba(77, 145, 255, .35);
    }

    .profil-guru-stat-icon {
        width: 43px;
        height: 43px;

        min-width: 43px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 11px;

        background: rgba(57, 133, 255, .12);

        color: #4d91ff;

        font-size: 18px;
    }

    .profil-guru-stat-number {
        color: var(--text-main);

        font-size: 22px;
        font-weight: 700;

        line-height: 1.1;
    }

    .profil-guru-stat-label {
        margin-top: 5px;

        color: var(--text-muted);

        font-size: 10px;
    }


    /* =========================================================
   STATUS STAT
========================================================= */

    .profil-guru-stat.selesai .profil-guru-stat-icon {
        color: #45d69a;

        background: rgba(25, 135, 84, .12);
    }

    .profil-guru-stat.batal .profil-guru-stat-icon {
        color: #ff7180;

        background: rgba(220, 53, 69, .12);
    }

    .profil-guru-stat.menunggu .profil-guru-stat-icon {
        color: #ffc65c;

        background: rgba(255, 193, 7, .12);
    }


    /* =========================================================
   TOTAL STAT
========================================================= */

    .profil-guru-total-card {
        margin-top: 16px;

        padding: 16px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        border-radius: 11px;

        background: rgba(57, 133, 255, .08);

        border: 1px solid rgba(77, 145, 255, .18);
    }

    .profil-guru-total-label {
        color: var(--text-muted);

        font-size: 11px;
    }

    .profil-guru-total-value {
        color: #4d91ff;

        font-size: 20px;
        font-weight: 700;
    }


    /* =========================================================
   INFO
========================================================= */

    .profil-guru-info-box {
        margin-top: 16px;

        padding: 14px;

        display: flex;

        gap: 10px;

        border-radius: 9px;

        background: rgba(57, 133, 255, .08);

        border: 1px solid rgba(77, 145, 255, .15);
    }

    .profil-guru-info-box i {
        color: #4d91ff;

        font-size: 16px;
    }

    .profil-guru-info-box p {
        margin: 0;

        color: var(--text-muted);

        font-size: 10px;

        line-height: 1.6;
    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 900px) {

        .profil-guru-grid {
            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 600px) {

        .profil-guru-title {
            font-size: 23px;
        }

        .profil-guru-card {
            padding: 22px;

            flex-direction: column;

            text-align: center;
        }

        .profil-guru-role {
            justify-content: center;
        }

        .profil-guru-name {
            font-size: 21px;
        }

        .profil-guru-stats {
            grid-template-columns: 1fr;
        }

        .profil-guru-detail-row {
            align-items: flex-start;

            flex-direction: column;

            gap: 5px;
        }

        .profil-guru-detail-value {
            text-align: left;
        }

    }
</style>


<!-- =========================================================
     PAGE
========================================================= -->

<div class="profil-guru-page">


    <!-- =====================================================
         HEADER
    ===================================================== -->

    <div class="profil-guru-header">

        <h1 class="profil-guru-title">
            Profil Saya
        </h1>

        <p class="profil-guru-subtitle">
            Informasi profil dan aktivitas konseling Anda.
        </p>

    </div>


    <!-- =====================================================
         PROFILE HERO
    ===================================================== -->

    <div class="profil-guru-card">


        <!-- FOTO -->

        <div class="profil-guru-photo">

            <?php if ($guru_foto_ada): ?>

                <img src="<?= htmlspecialchars($guru_foto_url); ?>" alt="Foto <?= htmlspecialchars($guru_nama); ?>">

            <?php else: ?>

                <i class="bi bi-person-fill"></i>

            <?php endif; ?>

        </div>


        <!-- INFO -->

        <div class="profil-guru-info">

            <h2 class="profil-guru-name">

                <?= htmlspecialchars($guru_nama); ?>

            </h2>


            <div class="profil-guru-role">

                <i class="bi bi-person-badge-fill"></i>

                Guru BK

            </div>


            <div class="profil-guru-nip">

                NIP:

                <strong>
                    <?= htmlspecialchars($guru_nip); ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- =====================================================
         GRID
    ===================================================== -->

    <div class="profil-guru-grid">


        <!-- =================================================
             INFORMASI PROFIL
        ================================================= -->

        <div class="profil-guru-section">


            <div class="profil-guru-section-header">

                <i class="bi bi-person-vcard-fill"></i>

                Informasi Profil

            </div>


            <div class="profil-guru-section-body">


                <div class="profil-guru-detail">


                    <!-- NAMA -->

                    <div class="profil-guru-detail-row">

                        <div class="profil-guru-detail-label">

                            <i class="bi bi-person-fill"></i>

                            Nama Lengkap

                        </div>

                        <div class="profil-guru-detail-value">

                            <?= htmlspecialchars($guru_nama); ?>

                        </div>

                    </div>


                    <!-- NIP -->

                    <div class="profil-guru-detail-row">

                        <div class="profil-guru-detail-label">

                            <i class="bi bi-card-text"></i>

                            NIP

                        </div>

                        <div class="profil-guru-detail-value">

                            <?= htmlspecialchars($guru_nip); ?>

                        </div>

                    </div>


                    <!-- ROLE -->

                    <div class="profil-guru-detail-row">

                        <div class="profil-guru-detail-label">

                            <i class="bi bi-shield-check"></i>

                            Jabatan

                        </div>

                        <div class="profil-guru-detail-value">

                            Guru BK

                        </div>

                    </div>


                    <!-- STATUS -->

                    <div class="profil-guru-detail-row">

                        <div class="profil-guru-detail-label">

                            <i class="bi bi-check-circle-fill"></i>

                            Status Akun

                        </div>

                        <div class="profil-guru-detail-value">

                            Aktif

                        </div>

                    </div>


                </div>


                <div class="profil-guru-info-box">

                    <i class="bi bi-info-circle-fill"></i>

                    <p>
                        Halaman ini menampilkan informasi profil
                        Guru BK yang sedang login.
                    </p>

                </div>


            </div>

        </div>


        <!-- =================================================
             AKTIVITAS KONSELING
        ================================================= -->

        <div class="profil-guru-section">


            <div class="profil-guru-section-header">

                <i class="bi bi-bar-chart-fill"></i>

                Aktivitas Konseling

            </div>


            <div class="profil-guru-section-body">


                <div class="profil-guru-stats">


                    <!-- MENUNGGU -->

                    <div class="profil-guru-stat menunggu">

                        <div class="profil-guru-stat-icon">

                            <i class="bi bi-clock-fill"></i>

                        </div>

                        <div>

                            <div class="profil-guru-stat-number">

                                <?= $total_menunggu; ?>

                            </div>

                            <div class="profil-guru-stat-label">

                                Menunggu

                            </div>

                        </div>

                    </div>


                    <!-- TERJADWAL -->

                    <div class="profil-guru-stat">

                        <div class="profil-guru-stat-icon">

                            <i class="bi bi-calendar-check-fill"></i>

                        </div>

                        <div>

                            <div class="profil-guru-stat-number">

                                <?= $total_terjadwal; ?>

                            </div>

                            <div class="profil-guru-stat-label">

                                Terjadwal

                            </div>

                        </div>

                    </div>


                    <!-- SELESAI -->

                    <div class="profil-guru-stat selesai">

                        <div class="profil-guru-stat-icon">

                            <i class="bi bi-check-circle-fill"></i>

                        </div>

                        <div>

                            <div class="profil-guru-stat-number">

                                <?= $total_selesai; ?>

                            </div>

                            <div class="profil-guru-stat-label">

                                Selesai

                            </div>

                        </div>

                    </div>


                    <!-- BATAL -->

                    <div class="profil-guru-stat batal">

                        <div class="profil-guru-stat-icon">

                            <i class="bi bi-x-circle-fill"></i>

                        </div>

                        <div>

                            <div class="profil-guru-stat-number">

                                <?= $total_batal; ?>

                            </div>

                            <div class="profil-guru-stat-label">

                                Batal

                            </div>

                        </div>

                    </div>


                </div>


                <!-- TOTAL -->

                <div class="profil-guru-total-card">

                    <div class="profil-guru-total-label">

                        Total Konseling Ditangani

                    </div>

                    <div class="profil-guru-total-value">

                        <?= $total_konseling; ?>

                    </div>

                </div>


            </div>

        </div>


    </div>


</div>


<?php

/* =========================================================
   FOOTER GURU
========================================================= */

require_once __DIR__ . "/layout/footer_guru.php";

?>