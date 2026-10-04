<?php

session_start();

require_once __DIR__ . "/config/database.php";

/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'siswa') {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'guru') {
        header("Location: dashboard.php");
        exit;
    }

    session_destroy();
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| DATA SISWA
|--------------------------------------------------------------------------
*/

$siswa_id = (int) ($_SESSION['siswa_id'] ?? 0);

if ($siswa_id <= 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA SISWA
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT 
        id_siswa,
        nis,
        nama,
        kelas,
        jurusan,
        foto
     FROM siswa
     WHERE id_siswa = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$siswa) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| DATA SISWA
|--------------------------------------------------------------------------
*/

$siswa_nama = $siswa['nama'] ?? 'Siswa';
$siswa_nis = $siswa['nis'] ?? '-';
$siswa_kelas = $siswa['kelas'] ?? '-';
$siswa_jurusan = $siswa['jurusan'] ?? '-';
$siswa_foto = $siswa['foto'] ?? '';

/*
|--------------------------------------------------------------------------
| FOTO SISWA
|--------------------------------------------------------------------------
*/

$foto_path = __DIR__ . "/uploads/siswa/" . $siswa_foto;

$foto_url = "";

if (
    !empty($siswa_foto) &&
    file_exists($foto_path)
) {
    $foto_url = "uploads/siswa/" . rawurlencode($siswa_foto);
}

/*
|--------------------------------------------------------------------------
| STATISTIK KONSELING
|--------------------------------------------------------------------------
*/

$total_konseling = 0;
$total_terjadwal = 0;
$total_selesai = 0;

/*
|--------------------------------------------------------------------------
| TOTAL KONSELING
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM konseling
     WHERE id_siswa = ?"
);

mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

$total_konseling = (int) ($data['total'] ?? 0);

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| TOTAL TERJADWAL
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM konseling
     WHERE id_siswa = ?
     AND status = 'terjadwal'"
);

mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

$total_terjadwal = (int) ($data['total'] ?? 0);

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| TOTAL SELESAI
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM konseling
     WHERE id_siswa = ?
     AND status = 'selesai'"
);

mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

$total_selesai = (int) ($data['total'] ?? 0);

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| HEADER SISWA
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/layout/header_siswa.php";

?>

<style>
    /* =========================================================
   HALAMAN PROFIL
========================================================= */

    .profile-page {
        padding-bottom: 30px;
    }


    /* =========================================================
   HEADER HALAMAN
========================================================= */

    .profile-page-header {
        margin-bottom: 18px;
    }

    .profile-page-title {
        margin: 0;
        font-size: 28px;
        line-height: 1.2;
        font-weight: 700;
        color: var(--text-main, #172033);
    }

    .profile-page-subtitle {
        margin: 7px 0 0;
        font-size: 13px;
        color: var(--text-muted, #687386);
    }


    /* =========================================================
   CARD PROFIL UTAMA
========================================================= */

    .profile-card {
        width: 100%;
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-color, #e1e5eb);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
        margin-bottom: 18px;
    }


    /* =========================================================
   BAGIAN ATAS PROFIL
========================================================= */

    .profile-header {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 24px;
        background: var(--bg-card, #ffffff);
        border-bottom: 1px solid var(--border-color, #e1e5eb);
    }

    .profile-avatar {
        width: 82px;
        height: 82px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        background: #1769e8;
        border: 4px solid rgba(13, 110, 253, .12);
    }

    .profile-avatar-icon {
        width: 82px;
        height: 82px;
        border-radius: 50%;
        background: #1769e8;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        flex-shrink: 0;
    }

    .profile-info {
        min-width: 0;
    }

    .profile-name {
        margin: 0 0 5px;
        color: var(--text-main, #172033);
        font-size: 22px;
        line-height: 1.2;
        font-weight: 700;
    }

    .profile-nis {
        margin: 0 0 8px;
        color: var(--text-muted, #687386);
        font-size: 13px;
    }

    .profile-role {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: 6px;
        background: rgba(13, 110, 253, .10);
        color: #0d6efd;
        font-size: 11px;
        font-weight: 600;
    }


    /* =========================================================
   BODY PROFIL
========================================================= */

    .profile-body {
        padding: 20px;
        background: var(--bg-card, #ffffff);
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .profile-item {
        min-width: 0;
        padding: 17px;
        border-radius: 10px;
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-color, #e1e5eb);
    }

    .profile-label {
        display: block;
        margin-bottom: 7px;
        color: #1769e8;
        font-size: 10px;
        line-height: 1.2;
        font-weight: 600;
        text-transform: uppercase;
    }

    .profile-value {
        color: var(--text-main, #172033);
        font-size: 13px;
        line-height: 1.5;
        font-weight: 600;
        word-break: break-word;
    }


    /* =========================================================
   STATISTIK
========================================================= */

    .profile-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .stat-card {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
        padding: 18px;
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-color, #e1e5eb);
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: rgba(13, 110, 253, .10);
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .stat-content {
        min-width: 0;
    }

    .stat-label {
        margin: 0 0 3px;
        color: var(--text-muted, #687386);
        font-size: 10px;
        line-height: 1.3;
    }

    .stat-number {
        margin: 0;
        color: var(--text-main, #172033);
        font-size: 18px;
        line-height: 1.2;
        font-weight: 700;
    }


    /* =========================================================
   DARK MODE
========================================================= */

    [data-theme="dark"] .profile-card,
    [data-theme="dark"] .profile-header,
    [data-theme="dark"] .profile-body,
    [data-theme="dark"] .profile-item,
    [data-theme="dark"] .stat-card {
        background: #0d1a3a;
        border-color: #20335f;
    }

    [data-theme="dark"] .profile-header {
        border-bottom-color: #20335f;
    }

    [data-theme="dark"] .profile-page-title,
    [data-theme="dark"] .profile-name,
    [data-theme="dark"] .profile-value,
    [data-theme="dark"] .stat-number {
        color: #ffffff;
    }

    [data-theme="dark"] .profile-page-subtitle,
    [data-theme="dark"] .profile-nis,
    [data-theme="dark"] .stat-label {
        color: #9da8bd;
    }

    [data-theme="dark"] .profile-label {
        color: #6da8ff;
    }

    [data-theme="dark"] .profile-role {
        background: rgba(13, 110, 253, .18);
        color: #4d9aff;
    }

    [data-theme="dark"] .profile-avatar {
        border-color: rgba(77, 154, 255, .18);
    }

    [data-theme="dark"] .profile-item {
        background: #101f43;
    }

    [data-theme="dark"] .stat-icon {
        background: rgba(13, 110, 253, .18);
        color: #4d9aff;
    }


    /* =========================================================
   LIGHT MODE
========================================================= */

    [data-theme="light"] .profile-card,
    [data-theme="light"] .profile-header,
    [data-theme="light"] .profile-body,
    [data-theme="light"] .profile-item,
    [data-theme="light"] .stat-card {
        background: #ffffff;
    }

    [data-theme="light"] .profile-page-title,
    [data-theme="light"] .profile-name,
    [data-theme="light"] .profile-value,
    [data-theme="light"] .stat-number {
        color: #172033;
    }

    [data-theme="light"] .profile-page-subtitle,
    [data-theme="light"] .profile-nis,
    [data-theme="light"] .stat-label {
        color: #687386;
    }

    [data-theme="light"] .profile-label {
        color: #1769e8;
    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 900px) {

        .profile-stats {
            grid-template-columns: repeat(3, 1fr);
        }

    }

    @media (max-width: 768px) {

        .profile-page-title {
            font-size: 24px;
        }

        .profile-header {
            padding: 20px;
        }

        .profile-avatar,
        .profile-avatar-icon {
            width: 68px;
            height: 68px;
        }

        .profile-avatar-icon {
            font-size: 27px;
        }

        .profile-name {
            font-size: 19px;
        }

        .profile-grid {
            grid-template-columns: 1fr;
        }

        .profile-stats {
            grid-template-columns: 1fr;
        }

        .stat-card {
            padding: 15px;
        }

    }

    @media (max-width: 480px) {

        .profile-header {
            align-items: flex-start;
        }

        .profile-avatar,
        .profile-avatar-icon {
            width: 60px;
            height: 60px;
        }

        .profile-avatar-icon {
            font-size: 24px;
        }

        .profile-name {
            font-size: 18px;
        }

        .profile-body {
            padding: 15px;
        }

        .profile-item {
            padding: 14px;
        }

    }


    /* =========================================================
   ANIMASI
========================================================= */

    .profile-card,
    .stat-card {
        transition:
            background-color .25s ease,
            border-color .25s ease,
            box-shadow .25s ease;
    }

    .stat-card:hover {
        box-shadow: 0 6px 22px rgba(0, 0, 0, .07);
    }

    [data-theme="dark"] .stat-card:hover {
        box-shadow: 0 6px 22px rgba(0, 0, 0, .20);
    }
</style>


<main class="main-content">

    <div class="page-container profile-page">

        <!-- =====================================================
             HEADER HALAMAN
        ====================================================== -->

        <div class="profile-page-header">

            <h1 class="profile-page-title">
                Profil Saya
            </h1>

            <p class="profile-page-subtitle">
                Informasi akun dan data pribadi saya
            </p>

        </div>


        <!-- =====================================================
             CARD PROFIL
        ====================================================== -->

        <div class="profile-card">

            <!-- HEADER PROFIL -->

            <div class="profile-header">

                <?php if (!empty($foto_url)): ?>

                    <img src="<?= htmlspecialchars($foto_url); ?>" alt="Foto <?= htmlspecialchars($siswa_nama); ?>"
                        class="profile-avatar">

                <?php else: ?>

                    <div class="profile-avatar-icon">
                        <i class="bi bi-person-fill"></i>
                    </div>

                <?php endif; ?>


                <div class="profile-info">

                    <h2 class="profile-name">
                        <?= htmlspecialchars($siswa_nama); ?>
                    </h2>

                    <p class="profile-nis">
                        NIS <?= htmlspecialchars($siswa_nis); ?>
                    </p>

                    <span class="profile-role">
                        <i class="bi bi-person-fill"></i>
                        Siswa
                    </span>

                </div>

            </div>


            <!-- DATA PROFIL -->

            <div class="profile-body">

                <div class="profile-grid">

                    <!-- NAMA -->

                    <div class="profile-item">

                        <span class="profile-label">
                            Nama Lengkap
                        </span>

                        <div class="profile-value">
                            <?= htmlspecialchars($siswa_nama); ?>
                        </div>

                    </div>


                    <!-- NIS -->

                    <div class="profile-item">

                        <span class="profile-label">
                            NIS
                        </span>

                        <div class="profile-value">
                            <?= htmlspecialchars($siswa_nis); ?>
                        </div>

                    </div>


                    <!-- KELAS -->

                    <div class="profile-item">

                        <span class="profile-label">
                            Kelas
                        </span>

                        <div class="profile-value">
                            <?= htmlspecialchars($siswa_kelas); ?>
                        </div>

                    </div>


                    <!-- JURUSAN -->

                    <div class="profile-item">

                        <span class="profile-label">
                            Jurusan
                        </span>

                        <div class="profile-value">
                            <?= htmlspecialchars($siswa_jurusan); ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             STATISTIK KONSELING
        ====================================================== -->

        <div class="profile-stats">

            <!-- TOTAL -->

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-chat-square-text-fill"></i>
                </div>

                <div class="stat-content">

                    <p class="stat-label">
                        Total Konseling
                    </p>

                    <p class="stat-number">
                        <?= $total_konseling; ?>
                    </p>

                </div>

            </div>


            <!-- TERJADWAL -->

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>

                <div class="stat-content">

                    <p class="stat-label">
                        Terjadwal
                    </p>

                    <p class="stat-number">
                        <?= $total_terjadwal; ?>
                    </p>

                </div>

            </div>


            <!-- SELESAI -->

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>

                <div class="stat-content">

                    <p class="stat-label">
                        Selesai
                    </p>

                    <p class="stat-number">
                        <?= $total_selesai; ?>
                    </p>

                </div>

            </div>

        </div>

    </div>

</main>


<?php

/*
|--------------------------------------------------------------------------
| FOOTER SISWA
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/layout/footer_siswa.php";

?>