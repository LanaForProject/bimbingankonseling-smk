<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nama_admin = $_SESSION['admin_nama'] ?? 'Administrator BK';
$current_page = basename($_SERVER['PHP_SELF']);
$title = $title ?? 'BK Online';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title); ?> | BK Online</title>


    <!-- BOOTSTRAP -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- BOOTSTRAP ICONS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <script>
        (function () {

            const STORAGE_KEY = 'bk_theme';

            const savedTheme =
                localStorage.getItem(STORAGE_KEY);

            const theme =
                savedTheme || 'dark';

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

    <!-- CSS UTAMA -->

    <link rel="stylesheet" href="assets/css/style.css">


    <style>
        /* =====================================================
           RESET LAYOUT
        ===================================================== */

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;

            width: 100% !important;
            min-height: 100% !important;

            overflow-x: hidden;
        }

        body {
            min-height: 100vh !important;
        }


        /* =====================================================
           LAYOUT
        ===================================================== */

        .bk-layout {
            width: 100% !important;

            min-height: 100vh !important;

            display: flex !important;

            align-items: stretch !important;
        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {
            position: fixed !important;

            left: 0 !important;
            top: 0 !important;
            bottom: 0 !important;

            width: 234px !important;

            height: 100vh !important;

            background: #302d4d !important;

            color: #fff;

            z-index: 1000;

            display: flex !important;

            flex-direction: column !important;

            overflow-y: auto;

            flex-shrink: 0 !important;
        }


        /* =====================================================
           BRAND
        ===================================================== */

        .sidebar-brand {
            height: 50px !important;

            min-height: 50px !important;

            display: flex !important;

            align-items: center !important;

            padding: 0 15px !important;

            border-bottom: 1px solid rgba(255, 255, 255, .12) !important;

            color: #fff !important;

            font-size: 14px;

            font-weight: 600;

            text-decoration: none;
        }

        .sidebar-brand i {
            margin-right: 7px;

            font-size: 17px;
        }


        /* =====================================================
           PROFILE
        ===================================================== */

        .sidebar-profile {
            padding: 13px;

            display: flex;

            align-items: center;

            gap: 10px;

            border-bottom: 1px solid rgba(255, 255, 255, .12);
        }

        .profile-icon {
            width: 36px;
            height: 36px;

            min-width: 36px;

            border-radius: 50%;

            background: #fff;

            color: #302d4d;

            display: flex;

            align-items: center;

            justify-content: center;
        }

        .profile-name {
            color: #fff;

            font-size: 13px;

            font-weight: 600;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        /* =====================================================
           SEARCH SIDEBAR
        ===================================================== */

        .sidebar-search {
            padding: 12px 13px 10px;
        }

        .sidebar-search-box {
            height: 34px;

            background: #fff;

            border-radius: 9px;

            display: flex;

            align-items: center;

            padding: 0 10px;

            color: #8c8c8c;
        }

        .sidebar-search-box i {
            font-size: 14px;

            margin-right: 7px;
        }

        .sidebar-search-box input {
            width: 100%;

            border: 0;

            outline: none;

            background: transparent;

            font-size: 11px;

            color: #333;
        }


        /* =====================================================
           MENU
        ===================================================== */

        .sidebar-menu-title {
            padding: 2px 20px 7px;

            color: #ddd;

            font-size: 11px;
        }

        .sidebar-menu {
            padding: 0 5px;
        }

        .sidebar-menu a {
            display: flex !important;

            align-items: center !important;

            gap: 11px;

            min-height: 42px;

            padding: 0 17px;

            margin-bottom: 2px;

            border-radius: 4px;

            color: #fff !important;

            text-decoration: none;

            font-size: 12px;

            transition: .2s;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, .09);
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, .12);
        }

        .sidebar-menu a i {
            width: 15px;

            font-size: 14px;

            text-align: center;
        }


        /* =====================================================
           MAIN WRAPPER
        ===================================================== */

        .main-wrapper {
            margin-left: 234px !important;

            width: calc(100% - 234px) !important;

            min-height: 100vh !important;

            height: 100vh !important;

            display: flex !important;

            flex-direction: column !important;

            flex: 1 1 auto !important;

            background: #f4f6f9;

            overflow: hidden;
        }


        /* =====================================================
           TOPBAR
        ===================================================== */

        .topbar {
            height: 50px !important;

            min-height: 50px !important;

            flex-shrink: 0 !important;

            background: #fff;

            border-bottom: 1px solid #e5e7eb;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 15px;
        }

        .topbar-left {
            display: flex;

            align-items: center;

            gap: 25px;
        }

        .topbar-menu {
            width: 28px;
            height: 28px;

            border: 0;

            background: transparent;

            color: #68717d;

            display: flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;
        }

        .topbar-home {
            color: #6b7280;

            font-size: 13px;

            text-decoration: none;
        }

        .topbar-right {
            display: flex;

            align-items: center;

            gap: 18px;
        }

        .topbar-icon {
            position: relative;

            color: #68717d;

            font-size: 17px;

            text-decoration: none;
        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .content {
            width: 100% !important;

            flex: 1 1 auto !important;

            min-height: 0 !important;

            padding: 20px !important;

            overflow-y: auto;

            overflow-x: hidden;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .main-footer {
            width: 100% !important;

            min-height: 50px !important;

            height: 50px !important;

            flex-shrink: 0 !important;

            margin: 0 !important;

            padding: 0 18px !important;

            background: #fff;

            border-top: 1px solid #e5e7eb;

            display: flex !important;

            align-items: center !important;

            justify-content: space-between !important;

            color: #6b7280;

            font-size: 12px;
        }


        /* =====================================================
           DARK MODE
        ===================================================== */

        [data-theme="dark"] body {
            background: #171725 !important;

            color: #e5e7eb;
        }

        [data-theme="dark"] .main-wrapper {
            background: #171725 !important;
        }

        [data-theme="dark"] .topbar {
            background: #222235 !important;

            border-color: #39394d !important;
        }

        [data-theme="dark"] .topbar-home,
        [data-theme="dark"] .topbar-icon,
        [data-theme="dark"] .topbar-menu {
            color: #d1d5db !important;
        }

        [data-theme="dark"] .main-footer {
            background: #222235 !important;

            border-color: #39394d !important;

            color: #c5c9d2 !important;
        }


        /* =====================================================
           DARK MODE UNTUK CARD DATA SISWA
        ===================================================== */

        [data-theme="dark"] .siswa-card {
            background: #222235 !important;

            border-color: #39394d !important;
        }

        [data-theme="dark"] .siswa-card-header {
            background: #222235 !important;

            border-color: #39394d !important;
        }

        [data-theme="dark"] .siswa-title {
            color: #f1f3f5 !important;
        }

        [data-theme="dark"] .siswa-table thead th {
            background: #292940 !important;

            color: #e9edf3 !important;

            border-color: #39394d !important;
        }

        [data-theme="dark"] .siswa-table tbody td {
            color: #dfe3ea !important;

            border-color: #39394d !important;
        }

        [data-theme="dark"] .siswa-table tbody tr:hover {
            background: rgba(255, 255, 255, .035) !important;
        }

        [data-theme="dark"] .jurusan {
            color: #b9bfca !important;
        }

        [data-theme="dark"] .nomor {
            color: #aeb5c1 !important;
        }

        [data-theme="dark"] .siswa-card-footer {
            background: #222235 !important;

            border-color: #39394d !important;

            color: #aeb5c1 !important;
        }

        [data-theme="dark"] .search-siswa input {
            background: #292940 !important;

            border-color: #45455c !important;

            color: #fff !important;
        }

        [data-theme="dark"] .search-siswa input::placeholder {
            color: #9ca3af !important;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 768px) {

            .sidebar {
                transform: translateX(-100%) !important;

                transition: transform .25s ease;
            }

            .sidebar.show {
                transform: translateX(0) !important;
            }

            .main-wrapper {
                margin-left: 0 !important;

                width: 100% !important;
            }

            .content {
                padding: 15px !important;
            }

            .main-footer {
                height: auto !important;

                min-height: 60px !important;

                padding: 12px 15px !important;

                flex-direction: column !important;

                align-items: flex-start !important;

                justify-content: center !important;

                gap: 4px;
            }

        }

        /* =====================================================
   THEME TOGGLE
===================================================== */

        .theme-toggle {
            width: 32px;
            height: 32px;

            border: 0;

            border-radius: 50%;

            background: transparent;

            color: #68717d;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 15px;

            cursor: pointer;

            transition: 0.2s ease;
        }

        .theme-toggle:hover {
            background: #f0f2f5;

            color: #0d6efd;

            transform: rotate(15deg);
        }


        /* DARK */

        [data-theme="dark"] .theme-toggle {
            color: #f8d66d;
        }

        [data-theme="dark"] .theme-toggle:hover {
            background: #303047;

            color: #ffd966;
        }
    </style>

</head>


<body>


    <div class="bk-layout">


        <!-- =====================================================
         SIDEBAR
    ====================================================== -->

        <aside class="sidebar" id="sidebar">


            <a href="dashboard.php" class="sidebar-brand">

                <i class="bi bi-heart-pulse-fill"></i>

                BK ONLINE

            </a>


            <div class="sidebar-profile">

                <div class="profile-icon">

                    <i class="bi bi-person-fill"></i>

                </div>

                <div>

                    <div class="profile-name">

                        <?= htmlspecialchars($nama_admin); ?>

                    </div>

                </div>

            </div>


            <div class="sidebar-search">

                <div class="sidebar-search-box">

                    <i class="bi bi-search"></i>

                    <input type="text" id="sidebarSearch" placeholder="Search">

                </div>

            </div>


            <div class="sidebar-menu-title">

                Daftar Menu

            </div>


            <nav class="sidebar-menu" id="sidebarMenu">


                <a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : ''; ?>">

                    <i class="bi bi-grid-fill"></i>

                    <span>Dashboard</span>

                </a>


                <a href="siswa.php" class="<?= $current_page === 'siswa.php' ? 'active' : ''; ?>">

                    <i class="bi bi-person-fill"></i>

                    <span>Data Siswa</span>

                </a>


                <a href="guru.php" class="<?= $current_page === 'guru.php' ? 'active' : ''; ?>">

                    <i class="bi bi-mortarboard-fill"></i>

                    <span>Guru BK</span>

                </a>


                <a href="konseling.php" class="<?= $current_page === 'konseling.php' ? 'active' : ''; ?>">

                    <i class="bi bi-chat-dots-fill"></i>

                    <span>Data Konseling</span>

                </a>


                <a href="/bk/riwayat_konseling.php"
                    class="<?= $current_page === 'riwayat_konseling.php' ? 'active' : ''; ?>">

                    <i class="bi bi-clock-history"></i>

                    <span>Riwayat Konseling</span>

                </a>


                <a href="akun.php" class="<?= $current_page === 'akun.php' ? 'active' : ''; ?>">

                    <i class="bi bi-person-circle"></i>

                    <span>Akun</span>

                </a>


                <a href="logout.php">

                    <i class="bi bi-box-arrow-right"></i>

                    <span>Logout</span>

                </a>


            </nav>

        </aside>


        <!-- =====================================================
         MAIN
    ====================================================== -->

        <div class="main-wrapper">


            <!-- TOPBAR -->

            <div class="topbar-actions">

                <button type="button" class="theme-toggle" id="themeToggle" title="Ubah Tema" aria-label="Ubah Tema">
                    <i class="bi bi-sun-fill"></i>
                </button>

            </div>


            <!-- CONTENT -->

            <main class="content">