<?php

/* =========================================================
   DATA KONSELING GURU BK
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

$page_title = "Data Konseling | BK Online";


/* =========================================================
   FILTER
========================================================= */

$status_filter = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');


/* =========================================================
   VALIDASI STATUS
========================================================= */

$status_valid = [
    'menunggu',
    'terjadwal',
    'selesai',
    'batal'
];

if (
    $status_filter !== '' &&
    !in_array($status_filter, $status_valid, true)
) {
    $status_filter = '';
}


/* =========================================================
   STATISTIK
========================================================= */

$total_konseling = 0;
$total_menunggu = 0;
$total_terjadwal = 0;
$total_selesai = 0;


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
    $total_konseling = (int) ($row['total'] ?? 0);
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
    $total_menunggu = (int) ($row['total'] ?? 0);
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
    $total_terjadwal = (int) ($row['total'] ?? 0);
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
    $total_selesai = (int) ($row['total'] ?? 0);
}

$stmt->close();


/* =========================================================
   DATA KONSELING
========================================================= */

$data_konseling = [];


/* =========================================================
   QUERY DATA
========================================================= */

$sql = "
    SELECT
        k.id_konseling,
        k.id_siswa,
        k.masalah,
        k.solusi,
        k.tanggal,
        k.status,
        s.nis,
        s.nama AS nama_siswa,
        s.kelas,
        s.jurusan,
        s.foto AS foto_siswa
    FROM konseling k
    LEFT JOIN siswa s
        ON k.id_siswa = s.id_siswa
    WHERE k.id_guru = ?
";

$params = [$guru_id];
$types = "i";


/* =========================================================
   FILTER STATUS
========================================================= */

if ($status_filter !== '') {

    $sql .= "
        AND k.status = ?
    ";

    $params[] = $status_filter;
    $types .= "s";
}


/* =========================================================
   SEARCH
========================================================= */

if ($search !== '') {

    $sql .= "
        AND (
            s.nama LIKE ?
            OR s.nis LIKE ?
            OR s.kelas LIKE ?
            OR k.masalah LIKE ?
        )
    ";

    $search_param = "%" . $search . "%";

    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;

    $types .= "ssss";
}


/* =========================================================
   ORDER
========================================================= */

$sql .= "
    ORDER BY
        k.tanggal DESC,
        k.id_konseling DESC
";


/* =========================================================
   EXECUTE QUERY
========================================================= */

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param(
        $types,
        ...$params
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        while ($row = $result->fetch_assoc()) {
            $data_konseling[] = $row;
        }

    }

    $stmt->close();
}


/* =========================================================
   HELPER STATUS
========================================================= */

function statusBadgeGuru($status)
{
    switch ($status) {

        case 'menunggu':

            return '
                <span class="status-badge status-menunggu">
                    <i class="bi bi-clock"></i>
                    Menunggu
                </span>
            ';

        case 'terjadwal':

            return '
                <span class="status-badge status-terjadwal">
                    <i class="bi bi-calendar-check"></i>
                    Terjadwal
                </span>
            ';

        case 'selesai':

            return '
                <span class="status-badge status-selesai">
                    <i class="bi bi-check-circle"></i>
                    Selesai
                </span>
            ';

        case 'batal':

            return '
                <span class="status-badge status-batal">
                    <i class="bi bi-x-circle"></i>
                    Batal
                </span>
            ';

        default:

            return '
                <span class="status-badge status-menunggu">
                    <i class="bi bi-question-circle"></i>
                    -
                </span>
            ';
    }
}


/* =========================================================
   FORMAT TANGGAL
========================================================= */

function formatTanggalKonselingGuru($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    $timestamp = strtotime($tanggal);

    if (!$timestamp) {
        return '-';
    }

    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $hari = date('d', $timestamp);
    $bulan_nama = $bulan[(int) date('m', $timestamp)];
    $tahun = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);

    return $hari . ' ' .
        $bulan_nama . ' ' .
        $tahun . ', ' .
        $jam . ' WIB';
}


/* =========================================================
   HEADER GURU
========================================================= */

require_once __DIR__ . "/layout/header_guru.php";

?>


<style>
    /* =========================================================
   DATA KONSELING GURU BK
   MENGIKUTI WARNA DASHBOARD GURU
========================================================= */

    .konseling-page-header {
        margin-bottom: 24px;
    }

    .konseling-page-title {
        margin: 0;
        font-size: 28px;
        line-height: 1.3;
        font-weight: 700;
        color: var(--text-main);
    }

    .konseling-page-subtitle {
        margin: 7px 0 0;
        font-size: 13px;
        color: var(--text-muted);
    }


    /* =========================================================
   STATISTIK
========================================================= */

    .konseling-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .konseling-stat-card {
        min-height: 118px;
        padding: 20px;

        display: flex;
        align-items: center;
        gap: 15px;

        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        box-shadow: var(--shadow);

        transition:
            transform .2s ease,
            border-color .2s ease;
    }

    .konseling-stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(77, 145, 255, .35);
    }

    .konseling-stat-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 12px;

        background: rgba(57, 133, 255, .12);
        color: #4d91ff;

        font-size: 20px;
    }

    .konseling-stat-number {
        display: block;

        font-size: 27px;
        line-height: 1.1;
        font-weight: 700;

        color: var(--text-main);
    }

    .konseling-stat-label {
        display: block;

        margin-top: 6px;

        font-size: 12px;
        color: var(--text-muted);
    }


    /* =========================================================
   FILTER
========================================================= */

    .konseling-filter-card {
        margin-bottom: 18px;
        padding: 18px;

        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        box-shadow: var(--shadow);
    }

    .konseling-filter-form {
        width: 100%;

        display: flex;
        align-items: center;
        gap: 10px;
    }


    /* =========================================================
   SEARCH
========================================================= */

    .konseling-search {
        position: relative;
        flex: 1;
    }

    .konseling-search i {
        position: absolute;

        left: 13px;
        top: 50%;

        transform: translateY(-50%);

        color: var(--text-muted);
        font-size: 14px;

        pointer-events: none;
    }

    .konseling-search input {
        width: 100%;
        height: 40px;

        padding: 0 13px 0 37px;

        border: 1px solid var(--border-color);
        border-radius: 8px;

        outline: none;

        background: var(--bg-body);
        color: var(--text-main);

        font-size: 12px;

        transition: .2s ease;
    }

    .konseling-search input::placeholder {
        color: var(--text-muted);
    }

    .konseling-search input:focus {
        border-color: #4d91ff;

        box-shadow:
            0 0 0 3px rgba(77, 145, 255, .10);
    }


    /* =========================================================
   SELECT
========================================================= */

    .konseling-filter-select {
        width: 180px;
        height: 40px;

        padding: 0 12px;

        border: 1px solid var(--border-color);
        border-radius: 8px;

        outline: none;

        background: var(--bg-body);
        color: var(--text-main);

        font-size: 12px;

        cursor: pointer;
    }

    .konseling-filter-select:focus {
        border-color: #4d91ff;

        box-shadow:
            0 0 0 3px rgba(77, 145, 255, .10);
    }


    /* =========================================================
   BUTTON FILTER
========================================================= */

    .konseling-filter-button {
        height: 40px;

        padding: 0 16px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 6px;

        border: 0;
        border-radius: 8px;

        background: #1769e8;
        color: #fff;

        font-size: 11px;
        font-weight: 600;

        cursor: pointer;

        transition: .2s ease;
    }

    .konseling-filter-button:hover {
        background: #4d91ff;
        color: #fff;
    }


    /* =========================================================
   RESET
========================================================= */

    .konseling-reset-button {
        height: 40px;

        padding: 0 14px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 6px;

        border: 1px solid var(--border-color);
        border-radius: 8px;

        background: transparent;
        color: var(--text-main);

        font-size: 11px;
        font-weight: 600;

        text-decoration: none;

        transition: .2s ease;
    }

    .konseling-reset-button:hover {
        background: rgba(57, 133, 255, .10);
        border-color: rgba(77, 145, 255, .35);
        color: #4d91ff;
    }


    /* =========================================================
   TABLE CARD
========================================================= */

    .konseling-table-card {
        width: 100%;

        background: var(--bg-card);

        border: 1px solid var(--border-color);
        border-radius: 14px;

        overflow: hidden;

        box-shadow: var(--shadow);
    }


    /* =========================================================
   TABLE HEADER
========================================================= */

    .konseling-table-header {
        min-height: 62px;

        padding: 16px 18px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 12px;

        border-bottom: 1px solid var(--border-color);
    }

    .konseling-table-title {
        display: flex;
        align-items: center;

        gap: 10px;

        color: var(--text-main);

        font-size: 14px;
        font-weight: 700;
    }

    .konseling-table-title i {
        color: #4d91ff;
        font-size: 17px;
    }

    .konseling-total-data {
        color: var(--text-muted);
        font-size: 11px;
    }


    /* =========================================================
   TABLE WRAPPER
========================================================= */

    .konseling-table-wrapper {
        width: 100%;

        overflow-x: auto;

        -webkit-overflow-scrolling: touch;
    }


    /* =========================================================
   TABLE
========================================================= */

    .konseling-table {
        width: 100%;
        min-width: 950px;

        border-collapse: collapse;
    }

    .konseling-table th {
        padding: 13px 14px;

        text-align: left;
        white-space: nowrap;

        background: rgba(57, 133, 255, .035);

        color: var(--text-muted);

        font-size: 10px;
        font-weight: 700;

        text-transform: uppercase;
        letter-spacing: .3px;

        border-bottom: 1px solid var(--border-color);
    }

    .konseling-table td {
        padding: 14px;

        vertical-align: middle;

        color: var(--text-main);

        font-size: 11px;

        border-bottom: 1px solid var(--border-color);
    }

    .konseling-table tbody tr {
        transition: background .2s ease;
    }

    .konseling-table tbody tr:hover {
        background: rgba(57, 133, 255, .035);
    }

    .konseling-table tbody tr:last-child td {
        border-bottom: 0;
    }


    /* =========================================================
   SISWA
========================================================= */

    .student-cell {
        display: flex;
        align-items: center;

        gap: 10px;

        min-width: 190px;
    }

    .student-table-avatar {
        width: 42px;
        height: 42px;
        min-width: 42px;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;

        border-radius: 50%;

        background: #1769e8;
        color: #fff;

        font-size: 17px;
    }

    .student-table-avatar img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;
    }

    .student-table-name {
        display: block;

        max-width: 180px;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;

        color: var(--text-main);

        font-size: 12px;
        font-weight: 700;
    }

    .student-table-nis {
        display: block;

        margin-top: 4px;

        color: var(--text-muted);

        font-size: 10px;
    }


    /* =========================================================
   KELAS
========================================================= */

    .student-class {
        color: var(--text-muted);
        font-size: 10px;

        white-space: nowrap;
    }


    /* =========================================================
   MASALAH
========================================================= */

    .problem-text {
        max-width: 220px;

        display: -webkit-box;

        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;

        overflow: hidden;

        color: var(--text-main);

        font-size: 11px;
        line-height: 1.5;
    }


    /* =========================================================
   TANGGAL
========================================================= */

    .date-text {
        min-width: 145px;

        color: var(--text-muted);

        font-size: 10px;
        line-height: 1.6;
    }


    /* =========================================================
   STATUS
========================================================= */

    .status-badge {
        display: inline-flex;
        align-items: center;

        gap: 5px;

        padding: 5px 9px;

        border-radius: 20px;

        font-size: 9px;
        font-weight: 700;

        white-space: nowrap;
    }

    .status-badge i {
        font-size: 9px;
    }


    /* TERJADWAL */

    .status-terjadwal {
        color: #65a2ff;
        background: rgba(57, 133, 255, .12);
    }


    /* SELESAI */

    .status-selesai {
        color: #45d69a;
        background: rgba(25, 135, 84, .12);
    }


    /* BATAL */

    .status-batal {
        color: #ff7180;
        background: rgba(220, 53, 69, .12);
    }


    /* MENUNGGU */

    .status-menunggu {
        color: #ffc65c;
        background: rgba(255, 193, 7, .12);
    }


    /* =========================================================
   DETAIL
========================================================= */

    .btn-detail-konseling {
        height: 31px;

        padding: 0 11px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 5px;

        border: 1px solid var(--border-color);
        border-radius: 7px;

        background: transparent;
        color: var(--text-main);

        font-size: 10px;
        font-weight: 600;

        text-decoration: none;
        white-space: nowrap;

        transition: .2s ease;
    }

    .btn-detail-konseling:hover {
        background: #1769e8;

        border-color: #1769e8;

        color: #fff;
    }


    /* =========================================================
   EMPTY
========================================================= */

    .empty-konseling {
        padding: 55px 20px;

        text-align: center;

        color: var(--text-muted);
    }

    .empty-konseling-icon {
        width: 55px;
        height: 55px;

        margin: 0 auto 12px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: rgba(57, 133, 255, .12);

        color: #4d91ff;

        font-size: 23px;
    }

    .empty-konseling-title {
        margin: 0;

        color: var(--text-main);

        font-size: 13px;
        font-weight: 700;
    }

    .empty-konseling-text {
        margin: 5px 0 0;

        color: var(--text-muted);

        font-size: 10px;
    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 1100px) {

        .konseling-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }


    @media (max-width: 800px) {

        .konseling-filter-form {
            flex-wrap: wrap;
        }

        .konseling-search {
            flex-basis: 100%;
        }

        .konseling-filter-select {
            flex: 1;
        }

    }


    @media (max-width: 600px) {

        .konseling-page-title {
            font-size: 23px;
        }

        .konseling-stats {
            grid-template-columns: 1fr;
        }

        .konseling-filter-select {
            width: 100%;
            flex-basis: 100%;
        }

        .konseling-filter-button,
        .konseling-reset-button {
            flex: 1;
        }

        .konseling-table-header {
            align-items: flex-start;
            flex-direction: column;
        }

    }
</style>


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="konseling-page-header">

    <h1 class="konseling-page-title">
        Data Konseling
    </h1>

    <p class="konseling-page-subtitle">
        Kelola dan pantau data konseling siswa yang ditangani.
    </p>

</div>


<!-- =========================================================
     STATISTIK
========================================================= -->

<div class="konseling-stats">

    <!-- TOTAL -->

    <div class="konseling-stat-card">

        <div class="konseling-stat-icon">
            <i class="bi bi-chat-square-text-fill"></i>
        </div>

        <div>

            <div class="konseling-stat-number">
                <?= $total_konseling; ?>
            </div>

            <div class="konseling-stat-label">
                Total Konseling
            </div>

        </div>

    </div>


    <!-- MENUNGGU -->

    <div class="konseling-stat-card">

        <div class="konseling-stat-icon">
            <i class="bi bi-clock-fill"></i>
        </div>

        <div>

            <div class="konseling-stat-number">
                <?= $total_menunggu; ?>
            </div>

            <div class="konseling-stat-label">
                Menunggu
            </div>

        </div>

    </div>


    <!-- TERJADWAL -->

    <div class="konseling-stat-card">

        <div class="konseling-stat-icon">
            <i class="bi bi-calendar-check-fill"></i>
        </div>

        <div>

            <div class="konseling-stat-number">
                <?= $total_terjadwal; ?>
            </div>

            <div class="konseling-stat-label">
                Terjadwal
            </div>

        </div>

    </div>


    <!-- SELESAI -->

    <div class="konseling-stat-card">

        <div class="konseling-stat-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>

        <div>

            <div class="konseling-stat-number">
                <?= $total_selesai; ?>
            </div>

            <div class="konseling-stat-label">
                Selesai
            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     FILTER
========================================================= -->

<div class="konseling-filter-card">

    <form method="GET" action="konseling_guru.php" class="konseling-filter-form">

        <!-- SEARCH -->

        <div class="konseling-search">

            <i class="bi bi-search"></i>

            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>"
                placeholder="Cari nama siswa, NIS, kelas, atau masalah...">

        </div>


        <!-- STATUS -->

        <select name="status" class="konseling-filter-select">

            <option value="">
                Semua Status
            </option>

            <option value="menunggu" <?= $status_filter === 'menunggu' ? 'selected' : ''; ?>>
                Menunggu
            </option>

            <option value="terjadwal" <?= $status_filter === 'terjadwal' ? 'selected' : ''; ?>>
                Terjadwal
            </option>

            <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : ''; ?>>
                Selesai
            </option>

            <option value="batal" <?= $status_filter === 'batal' ? 'selected' : ''; ?>>
                Batal
            </option>

        </select>


        <!-- FILTER -->

        <button type="submit" class="konseling-filter-button">

            <i class="bi bi-funnel-fill"></i>

            Filter

        </button>


        <!-- RESET -->

        <?php if ($search !== '' || $status_filter !== ''): ?>

            <a href="konseling_guru.php" class="konseling-reset-button">

                <i class="bi bi-arrow-counterclockwise"></i>

                Reset

            </a>

        <?php endif; ?>

    </form>

</div>


<!-- =========================================================
     TABLE
========================================================= -->

<div class="konseling-table-card">


    <!-- TABLE HEADER -->

    <div class="konseling-table-header">

        <div class="konseling-table-title">

            <i class="bi bi-chat-square-dots-fill"></i>

            Data Konseling

        </div>


        <div class="konseling-total-data">

            <?= count($data_konseling); ?>

            data ditampilkan

        </div>

    </div>


    <?php if (!empty($data_konseling)): ?>


        <!-- TABLE -->

        <div class="konseling-table-wrapper">

            <table class="konseling-table">

                <thead>

                    <tr>

                        <th>
                            Siswa
                        </th>

                        <th>
                            Kelas
                        </th>

                        <th>
                            Masalah
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach ($data_konseling as $row): ?>


                        <tr>


                            <!-- SISWA -->

                            <td>

                                <div class="student-cell">


                                    <div class="student-table-avatar">

                                        <?php

                                        $foto_siswa_ada = false;
                                        $foto_siswa_url = '';

                                        if (!empty($row['foto_siswa'])) {

                                            $foto_siswa_nama = basename(
                                                str_replace(
                                                    '\\',
                                                    '/',
                                                    $row['foto_siswa']
                                                )
                                            );

                                            $foto_siswa_path =
                                                __DIR__ .
                                                '/uploads/siswa/' .
                                                $foto_siswa_nama;

                                            if (is_file($foto_siswa_path)) {

                                                $foto_siswa_ada = true;

                                                $foto_siswa_url =
                                                    'uploads/siswa/' .
                                                    rawurlencode(
                                                        $foto_siswa_nama
                                                    );
                                            }
                                        }

                                        ?>


                                        <?php if ($foto_siswa_ada): ?>

                                            <img src="<?= htmlspecialchars($foto_siswa_url); ?>" alt="Foto siswa">

                                        <?php else: ?>

                                            <i class="bi bi-person-fill"></i>

                                        <?php endif; ?>


                                    </div>


                                    <div>

                                        <div class="student-table-name">

                                            <?= htmlspecialchars(
                                                $row['nama_siswa']
                                                ?? 'Siswa tidak ditemukan'
                                            ); ?>

                                        </div>


                                        <div class="student-table-nis">

                                            NIS:

                                            <?= htmlspecialchars(
                                                $row['nis']
                                                ?? '-'
                                            ); ?>

                                        </div>

                                    </div>


                                </div>

                            </td>


                            <!-- KELAS -->

                            <td>

                                <div class="student-class">

                                    <?= htmlspecialchars(
                                        $row['kelas'] ?? '-'
                                    ); ?>

                                </div>

                            </td>


                            <!-- MASALAH -->

                            <td>

                                <div class="problem-text" title="<?= htmlspecialchars(
                                    $row['masalah'] ?? '-'
                                ); ?>">

                                    <?= htmlspecialchars(
                                        $row['masalah'] ?? '-'
                                    ); ?>

                                </div>

                            </td>


                            <!-- TANGGAL -->

                            <td>

                                <div class="date-text">

                                    <?= formatTanggalKonselingGuru(
                                        $row['tanggal'] ?? ''
                                    ); ?>

                                </div>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?= statusBadgeGuru(
                                    $row['status'] ?? ''
                                ); ?>

                            </td>


                            <!-- AKSI -->

                            <td>

                                <a href="konseling_detail_guru.php?id=<?= $row['id_konseling']; ?>&from=konseling"
                                    class="btn-detail-konseling">
                                    <i class="bi bi-eye"></i>
                                    Detail
                                </a>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>

            </table>

        </div>


    <?php else: ?>


        <!-- EMPTY -->

        <div class="empty-konseling">


            <div class="empty-konseling-icon">

                <i class="bi bi-chat-square-text"></i>

            </div>


            <h3 class="empty-konseling-title">

                Tidak ada data konseling

            </h3>


            <p class="empty-konseling-text">

                <?php if ($search !== '' || $status_filter !== ''): ?>

                    Tidak ditemukan data yang sesuai dengan
                    pencarian atau filter yang dipilih.

                <?php else: ?>

                    Belum ada data konseling yang ditangani
                    oleh Anda.

                <?php endif; ?>

            </p>


        </div>


    <?php endif; ?>


</div>


<?php

/* =========================================================
   FOOTER GURU
========================================================= */

require_once __DIR__ . "/layout/footer_guru.php";

?>