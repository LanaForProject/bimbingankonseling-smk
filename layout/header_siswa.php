<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit;
}

$siswa_id = (int) ($_SESSION['siswa_id'] ?? 0);

$siswa_nama = $_SESSION['siswa_nama'] ?? 'Siswa';
$siswa_nis = $_SESSION['siswa_nis'] ?? '-';
$siswa_kelas = $_SESSION['siswa_kelas'] ?? '-';
$siswa_jurusan = $_SESSION['siswa_jurusan'] ?? '-';
$siswa_foto = $_SESSION['siswa_foto'] ?? '';

if ($siswa_id <= 0) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

/*
|--------------------------------------------------------------------------
| FOTO SISWA
|--------------------------------------------------------------------------
*/

$foto_ada = false;
$foto_url = '';

if ($siswa_foto !== '') {

    $foto_path = __DIR__ . "/../uploads/siswa/" . $siswa_foto;

    if (file_exists($foto_path)) {

        $foto_ada = true;

        $foto_url = "uploads/siswa/" . rawurlencode($siswa_foto);
    }
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($page_title ?? 'BK Online'); ?>
    </title>


    <!-- =====================================================
         THEME
    ====================================================== -->

    <script>

        (function () {

            const key = 'bk_theme';

            const theme =
                localStorage.getItem(key) || 'dark';

            document.documentElement.setAttribute(
                'data-theme',
                theme
            );

            document.documentElement.setAttribute(
                'data-bs-theme',
                theme
            );

        })();

    </script>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


    <!-- =====================================================
         STYLE SISWA
    ====================================================== -->

    <style>
        :root {

            --bg-body: #f5f7fb;

            --bg-card: #ffffff;

            --bg-sidebar: #34304f;

            --bg-sidebar-hover: rgba(255, 255, 255, .08);

            --bg-sidebar-active: rgba(255, 255, 255, .12);

            --text-main: #1f2937;

            --text-muted: #6b7280;

            --border-color: #e5e7eb;

            --shadow:
                0 4px 18px rgba(0, 0, 0, .04);
        }


        /* =====================================================
       DARK MODE
    ====================================================== */

        html[data-theme="dark"] {

            --bg-body: #101426;

            --bg-card: #0c1837;

            --bg-sidebar: #34304f;

            --bg-sidebar-hover:
                rgba(255, 255, 255, .08);

            --bg-sidebar-active:
                rgba(255, 255, 255, .12);

            --text-main: #f8fafc;

            --text-muted: #8fa0c0;

            --border-color:
                rgba(255, 255, 255, .08);

            --shadow:
                0 4px 18px rgba(0, 0, 0, .15);
        }


        /* =====================================================
       RESET
    ====================================================== */

        * {
            box-sizing: border-box;
        }


        html,
        body {

            margin: 0;

            padding: 0;

            min-height: 100%;
        }


        body {

            background: var(--bg-body);

            color: var(--text-main);

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }


        a {
            text-decoration: none;
        }


        /* =====================================================
       SIDEBAR
    ====================================================== */

        .student-sidebar {

            width: 220px;

            min-height: 100vh;

            position: fixed;

            left: 0;

            top: 0;

            bottom: 0;

            z-index: 1000;

            display: flex;

            flex-direction: column;

            background: var(--bg-sidebar);

            color: #fff;
        }


        /* LOGO */

        .sidebar-logo {

            height: 78px;

            padding: 0 15px;

            display: flex;

            align-items: center;

            gap: 9px;

            border-bottom:
                1px solid rgba(255, 255, 255, .08);

            color: #fff;

            font-size: 14px;

            font-weight: 700;
        }


        .sidebar-logo i {

            font-size: 19px;
        }


        /* =====================================================
       PROFILE SISWA
    ====================================================== */

        .student-profile {

            padding: 15px 14px;

            display: flex;

            align-items: center;

            gap: 10px;

            border-bottom:
                1px solid rgba(255, 255, 255, .08);
        }


        .student-avatar {

            width: 38px;

            height: 38px;

            min-width: 38px;

            border-radius: 50%;

            overflow: hidden;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #fff;

            color: #34304f;

            font-size: 18px;
        }


        .student-avatar img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;
        }


        .student-profile-info {

            min-width: 0;
        }


        .student-profile-name {

            display: block;

            color: #fff;

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        .student-profile-class {

            display: block;

            margin-top: 2px;

            color:
                rgba(255, 255, 255, .65);

            font-size: 11px;
        }


        /* =====================================================
       MENU
    ====================================================== */

        .menu-title {

            padding:
                18px 14px 7px;

            color:
                rgba(255, 255, 255, .55);

            font-size: 10px;

            font-weight: 700;

            text-transform: uppercase;
        }


        .sidebar-menu {

            padding: 0 7px;
        }


        .sidebar-menu a {

            display: flex;

            align-items: center;

            gap: 11px;

            padding: 11px 14px;

            margin-bottom: 3px;

            border-radius: 6px;

            color: #fff;

            font-size: 12px;

            font-weight: 600;

            transition: .2s ease;
        }


        .sidebar-menu a:hover,
        .sidebar-menu a.active {

            background:
                var(--bg-sidebar-active);

            color: #fff;
        }


        .sidebar-menu a i {

            width: 17px;

            font-size: 14px;
        }


        /* =====================================================
       SIDEBAR BOTTOM
    ====================================================== */

        .sidebar-bottom {

            margin-top: auto;

            padding:
                10px 7px 15px;

            border-top:
                1px solid rgba(255, 255, 255, .08);
        }


        .sidebar-bottom a {

            display: flex;

            align-items: center;

            gap: 11px;

            padding: 11px 14px;

            border-radius: 6px;

            color: #fff;

            font-size: 12px;

            font-weight: 600;
        }


        .sidebar-bottom a:hover {

            background:
                rgba(220, 53, 69, .15);

            color: #ff8b95;
        }


        /* =====================================================
       MAIN
    ====================================================== */

        .student-main {

            width:
                calc(100% - 220px);

            margin-left: 220px;

            min-height: 100vh;
        }


        /* =====================================================
       TOPBAR
    ====================================================== */

        .student-topbar {

            height: 55px;

            display: flex;

            align-items: center;

            justify-content: flex-end;

            padding: 0 18px;

            background: var(--bg-card);

            border-bottom:
                1px solid var(--border-color);
        }


        .theme-toggle {

            width: 35px;

            height: 35px;

            border: 0;

            border-radius: 8px;

            background: transparent;

            color: var(--text-main);

            display: flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            font-size: 17px;
        }


        .theme-toggle:hover {

            background: var(--bg-body);
        }


        /* =====================================================
       CONTENT
    ====================================================== */

        .student-content {

            max-width: 1250px;

            margin: 0 auto;

            padding:
                28px 18px 30px;
        }


        /* =====================================================
       FOOTER
    ====================================================== */

        .student-footer {

            margin-left: 220px;

            padding: 14px 18px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            background: var(--bg-card);

            border-top:
                1px solid var(--border-color);

            color: var(--text-muted);

            font-size: 11px;
        }


        /* =====================================================
       RESPONSIVE
    ====================================================== */

        @media (max-width: 800px) {

            .student-sidebar {

                width: 70px;
            }


            .sidebar-logo span,

            .student-profile-info,

            .menu-title,

            .sidebar-menu a span,

            .sidebar-bottom a span {

                display: none;
            }


            .sidebar-logo {

                justify-content: center;

                padding: 0;
            }


            .student-profile {

                justify-content: center;

                padding: 15px 5px;
            }


            .sidebar-menu {

                padding: 0 6px;
            }


            .sidebar-menu a,

            .sidebar-bottom a {

                justify-content: center;

                padding: 12px;
            }


            .student-main {

                width:
                    calc(100% - 70px);

                margin-left: 70px;
            }


            .student-footer {

                margin-left: 70px;
            }
        }


        @media (max-width: 550px) {

            .student-content {

                padding:
                    20px 13px;
            }


            .student-footer {

                flex-direction: column;

                align-items: flex-start;
            }
        }
    </style>

</head>


<body>


    <!-- =====================================================
     SIDEBAR SISWA
====================================================== -->

    <aside class="student-sidebar">


        <!-- LOGO -->

        <div class="sidebar-logo">

            <i class="bi bi-heart-pulse-fill"></i>

            <span>BK ONLINE</span>

        </div>


        <!-- PROFILE -->

        <div class="student-profile">

            <div class="student-avatar">

                <?php if ($foto_ada): ?>

                    <img src="<?= htmlspecialchars($foto_url); ?>" alt="Foto <?= htmlspecialchars($siswa_nama); ?>">

                <?php else: ?>

                    <i class="bi bi-person-fill"></i>

                <?php endif; ?>

            </div>


            <div class="student-profile-info">

                <span class="student-profile-name">

                    <?= htmlspecialchars($siswa_nama); ?>

                </span>


                <span class="student-profile-class">

                    <?= htmlspecialchars($siswa_kelas); ?>

                </span>

            </div>

        </div>


        <!-- MENU TITLE -->

        <div class="menu-title">

            Menu

        </div>


        <!-- MENU -->

        <nav class="sidebar-menu">


            <!-- DASHBOARD -->

            <a href="dashboard_siswa.php" class="<?= $current_page === 'dashboard_siswa.php' ? 'active' : ''; ?>">

                <i class="bi bi-grid-fill"></i>

                <span>
                    Dashboard
                </span>

            </a>


            <!-- AJUKAN -->

            <a href="konseling_tambah.php" class="<?= $current_page === 'konseling_tambah.php' ? 'active' : ''; ?>">

                <i class="bi bi-chat-square-text-fill"></i>

                <span>
                    Ajukan Konseling
                </span>

            </a>


            <!-- RIWAYAT -->

            <a href="riwayat_konseling_siswa.php"
                class="<?= $current_page === 'riwayat_konseling_siswa.php' ? 'active' : ''; ?>">

                <i class="bi bi-clock-history"></i>

                <span>
                    Riwayat Konseling
                </span>

            </a>


            <!-- PROFIL -->

            <a href="profil_saya.php"
                class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'profil_saya.php' ? 'active' : ''; ?>">
                <i class="bi bi-person-vcard-fill"></i>
                <span>Profil Saya</span>
            </a>


        </nav>


        <!-- LOGOUT -->

        <div class="sidebar-bottom">

            <a href="logout.php">

                <i class="bi bi-box-arrow-right"></i>

                <span>
                    Logout
                </span>

            </a>

        </div>

    </aside>


    <!-- =====================================================
     MAIN
====================================================== -->

    <main class="student-main">


        <!-- TOPBAR -->

        <div class="student-topbar">

            <button type="button" class="theme-toggle" id="themeToggle" title="Ganti tema">

                <i class="bi bi-sun-fill"></i>

            </button>

        </div>


        <!-- CONTENT -->

        <section class="student-content">