<?php
/* =========================================================
   RIWAYAT KONSELING GURU BK
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

$page_title = "Riwayat Konseling | BK Online";

/* =========================================================
   FILTER
========================================================= */

$status_filter = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');

/* =========================================================
   STATUS VALID
   RIWAYAT HANYA SELESAI DAN BATAL
========================================================= */

$status_valid = [
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
   STATISTIK RIWAYAT
========================================================= */

$total_riwayat = 0;
$total_selesai = 0;
$total_batal = 0;

/* =========================================================
   TOTAL RIWAYAT
========================================================= */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM konseling
    WHERE id_guru = ?
    AND status IN ('selesai', 'batal')
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {
    $row = $result->fetch_assoc();
    $total_riwayat = (int) ($row['total'] ?? 0);
}

$stmt->close();

/* =========================================================
   TOTAL SELESAI
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
   TOTAL BATAL
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
    $total_batal = (int) ($row['total'] ?? 0);
}

$stmt->close();

/* =========================================================
   DATA RIWAYAT
========================================================= */

$data_riwayat = [];

/* =========================================================
   QUERY DASAR
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

    AND k.status IN ('selesai', 'batal')
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
            $data_riwayat[] = $row;
        }

    }

    $stmt->close();
}

/* =========================================================
   HELPER STATUS
========================================================= */

function statusBadgeRiwayatGuru($status)
{
    switch ($status) {

        case 'selesai':

            return '
                <span class="status-badge status-selesai">
                    <i class="bi bi-check-circle-fill"></i>
                    Selesai
                </span>
            ';

        case 'batal':

            return '
                <span class="status-badge status-batal">
                    <i class="bi bi-x-circle-fill"></i>
                    Batal
                </span>
            ';

        default:

            return '
                <span class="status-badge status-menunggu">
                    <i class="bi bi-question-circle-fill"></i>
                    -
                </span>
            ';
    }
}

/* =========================================================
   FORMAT TANGGAL
========================================================= */

function formatTanggalRiwayatGuru($tanggal)
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

    return $hari .
        ' ' .
        $bulan_nama .
        ' ' .
        $tahun .
        ', ' .
        $jam .
        ' WIB';
}

/* =========================================================
   HEADER GURU
========================================================= */

require_once __DIR__ . "/layout/header_guru.php";

?>

<style>
    /* =========================================================
   RIWAYAT KONSELING GURU BK
   WARNA MENGIKUTI DASHBOARD GURU
========================================================= */

    .riwayat-page-header {
        margin-bottom: 24px;
    }

    .riwayat-page-title {
        margin: 0;
        font-size: 28px;
        line-height: 1.3;
        font-weight: 700;
        color: var(--text-main);
    }

    .riwayat-page-subtitle {
        margin: 7px 0 0;
        font-size: 13px;
        color: var(--text-muted);
    }

    /* =========================================================
   STATISTIK
========================================================= */

    .riwayat-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .riwayat-stat-card {
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

    .riwayat-stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(77, 145, 255, .35);
    }

    .riwayat-stat-icon {
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

    .riwayat-stat-number {
        display: block;

        font-size: 27px;
        line-height: 1.1;
        font-weight: 700;

        color: var(--text-main);
    }

    .riwayat-stat-label {
        display: block;

        margin-top: 6px;

        font-size: 12px;
        color: var(--text-muted);
    }

    /* =========================================================
   FILTER
========================================================= */

    .riwayat-filter-card {
        margin-bottom: 18px;
        padding: 18px;

        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;

        box-shadow: var(--shadow);
    }

    .riwayat-filter-form {
        width: 100%;

        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* =========================================================
   SEARCH
========================================================= */

    .riwayat-search {
        position: relative;
        flex: 1;
    }

    .riwayat-search i {
        position: absolute;

        left: 13px;
        top: 50%;

        transform: translateY(-50%);

        color: var(--text-muted);
        font-size: 14px;

        pointer-events: none;
    }

    .riwayat-search input {
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

    .riwayat-search input::placeholder {
        color: var(--text-muted);
    }

    .riwayat-search input:focus {
        border-color: #4d91ff;

        box-shadow:
            0 0 0 3px rgba(77, 145, 255, .10);
    }

    /* =========================================================
   SELECT
========================================================= */

    .riwayat-filter-select {
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

    .riwayat-filter-select:focus {
        border-color: #4d91ff;

        box-shadow:
            0 0 0 3px rgba(77, 145, 255, .10);
    }

    /* =========================================================
   BUTTON FILTER
========================================================= */

    .riwayat-filter-button {
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

    .riwayat-filter-button:hover {
        background: #4d91ff;
        color: #fff;
    }

    /* =========================================================
   RESET
========================================================= */

    .riwayat-reset-button {
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

    .riwayat-reset-button:hover {
        background: rgba(57, 133, 255, .10);
        border-color: rgba(77, 145, 255, .35);
        color: #4d91ff;
    }

    /* =========================================================
   TABLE CARD
========================================================= */

    .riwayat-table-card {
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

    .riwayat-table-header {
        min-height: 62px;

        padding: 16px 18px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 12px;

        border-bottom: 1px solid var(--border-color);
    }

    .riwayat-table-title {
        display: flex;
        align-items: center;

        gap: 10px;

        color: var(--text-main);

        font-size: 14px;
        font-weight: 700;
    }

    .riwayat-table-title i {
        color: #4d91ff;
        font-size: 17px;
    }

    .riwayat-total-data {
        color: var(--text-muted);
        font-size: 11px;
    }

    /* =========================================================
   TABLE WRAPPER
========================================================= */

    .riwayat-table-wrapper {
        width: 100%;

        overflow-x: auto;

        -webkit-overflow-scrolling: touch;
    }

    /* =========================================================
   TABLE
========================================================= */

    .riwayat-table {
        width: 100%;
        min-width: 950px;

        border-collapse: collapse;
    }

    .riwayat-table th {
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

    .riwayat-table td {
        padding: 14px;

        vertical-align: middle;

        color: var(--text-main);

        font-size: 11px;

        border-bottom: 1px solid var(--border-color);
    }

    .riwayat-table tbody tr {
        transition: background .2s ease;
    }

    .riwayat-table tbody tr:hover {
        background: rgba(57, 133, 255, .035);
    }

    .riwayat-table tbody tr:last-child td {
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

    .empty-riwayat {
        padding: 55px 20px;

        text-align: center;

        color: var(--text-muted);
    }

    .empty-riwayat-icon {
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

    .empty-riwayat-title {
        margin: 0;

        color: var(--text-main);

        font-size: 13px;
        font-weight: 700;
    }

    .empty-riwayat-text {
        margin: 5px 0 0;

        color: var(--text-muted);

        font-size: 10px;
    }

    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 1100px) {

        .riwayat-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }

    @media (max-width: 800px) {

        .riwayat-filter-form {
            flex-wrap: wrap;
        }

        .riwayat-search {
            flex-basis: 100%;
        }

        .riwayat-filter-select {
            flex: 1;
        }

    }

    @media (max-width: 600px) {

        .riwayat-page-title {
            font-size: 23px;
        }

        .riwayat-stats {
            grid-template-columns: 1fr;
        }

        .riwayat-filter-select {
            width: 100%;
            flex-basis: 100%;
        }

        .riwayat-filter-button,
        .riwayat-reset-button {
            flex: 1;
        }

        .riwayat-table-header {
            align-items: flex-start;
            flex-direction: column;
        }

    }
</style>


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="riwayat-page-header">

    <h1 class="riwayat-page-title">
        Riwayat Konseling
    </h1>

    <p class="riwayat-page-subtitle">
        Riwayat konseling siswa yang telah selesai atau dibatalkan.
    </p>

</div>


<!-- =========================================================
     STATISTIK
========================================================= -->

<div class="riwayat-stats">

    <!-- TOTAL -->
    <div class="riwayat-stat-card">

        <div class="riwayat-stat-icon">
            <i class="bi bi-clock-history"></i>
        </div>

        <div>

            <div class="riwayat-stat-number">
                <?= $total_riwayat; ?>
            </div>

            <div class="riwayat-stat-label">
                Total Riwayat
            </div>

        </div>

    </div>


    <!-- SELESAI -->
    <div class="riwayat-stat-card">

        <div class="riwayat-stat-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>

        <div>

            <div class="riwayat-stat-number">
                <?= $total_selesai; ?>
            </div>

            <div class="riwayat-stat-label">
                Selesai
            </div>

        </div>

    </div>


    <!-- BATAL -->
    <div class="riwayat-stat-card">

        <div class="riwayat-stat-icon">
            <i class="bi bi-x-circle-fill"></i>
        </div>

        <div>

            <div class="riwayat-stat-number">
                <?= $total_batal; ?>
            </div>

            <div class="riwayat-stat-label">
                Batal
            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     FILTER
========================================================= -->

<div class="riwayat-filter-card">

    <form method="GET" action="riwayat_konseling_guru.php" class="riwayat-filter-form">

        <!-- SEARCH -->

        <div class="riwayat-search">

            <i class="bi bi-search"></i>

            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>"
                placeholder="Cari nama siswa, NIS, kelas, atau masalah...">

        </div>


        <!-- STATUS -->

        <select name="status" class="riwayat-filter-select">

            <option value="">
                Semua Riwayat
            </option>

            <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : ''; ?>>
                Selesai
            </option>

            <option value="batal" <?= $status_filter === 'batal' ? 'selected' : ''; ?>>
                Batal
            </option>

        </select>


        <!-- FILTER -->

        <button type="submit" class="riwayat-filter-button">

            <i class="bi bi-funnel-fill"></i>

            Filter

        </button>


        <!-- RESET -->

        <?php if ($search !== '' || $status_filter !== ''): ?>

            <a href="riwayat_konseling_guru.php" class="riwayat-reset-button">

                <i class="bi bi-arrow-counterclockwise"></i>

                Reset

            </a>

        <?php endif; ?>

    </form>

</div>


<!-- =========================================================
     TABLE
========================================================= -->

<div class="riwayat-table-card">


    <!-- TABLE HEADER -->

    <div class="riwayat-table-header">

        <div class="riwayat-table-title">

            <i class="bi bi-clock-history"></i>

            Riwayat Konseling

        </div>


        <div class="riwayat-total-data">

            <?= count($data_riwayat); ?>

            data ditampilkan

        </div>

    </div>


    <?php if (!empty($data_riwayat)): ?>


        <!-- TABLE -->

        <div class="riwayat-table-wrapper">

            <table class="riwayat-table">

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


                    <?php foreach ($data_riwayat as $row): ?>


                        <tr>


                            <!-- SISWA -->

                            <td>

                                <div class="student-cell">


                                    <div class="student-table-avatar">

                                        <?php

                                        $foto_siswa_ada = false;
                                        $foto_siswa_url = '';

                                        if (!empty($row['foto_siswa'])) {

                                            $foto_siswa_nama =
                                                basename(
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
                                        $row['kelas']
                                        ?? '-'
                                    ); ?>

                                </div>

                            </td>


                            <!-- MASALAH -->

                            <td>

                                <div class="problem-text" title="<?= htmlspecialchars(
                                    $row['masalah']
                                    ?? '-'
                                ); ?>">

                                    <?= htmlspecialchars(
                                        $row['masalah']
                                        ?? '-'
                                    ); ?>

                                </div>

                            </td>


                            <!-- TANGGAL -->

                            <td>

                                <div class="date-text">

                                    <?= formatTanggalRiwayatGuru(
                                        $row['tanggal']
                                        ?? ''
                                    ); ?>

                                </div>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?= statusBadgeRiwayatGuru(
                                    $row['status']
                                    ?? ''
                                ); ?>

                            </td>


                            <!-- AKSI -->

                            <td>

                                <a href="konseling_detail_guru.php?id=<?= $row['id_konseling']; ?>&from=riwayat"
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

        <div class="empty-riwayat">


            <div class="empty-riwayat-icon">

                <i class="bi bi-clock-history"></i>

            </div>


            <h3 class="empty-riwayat-title">

                Tidak ada riwayat konseling

            </h3>


            <p class="empty-riwayat-text">

                <?php if ($search !== '' || $status_filter !== ''): ?>

                    Tidak ditemukan riwayat yang sesuai
                    dengan pencarian atau filter yang dipilih.

                <?php else: ?>

                    Belum ada riwayat konseling
                    yang selesai atau dibatalkan.

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