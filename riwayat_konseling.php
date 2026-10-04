<?php

session_start();

require_once __DIR__ . '/config/database.php';

/*
 * |--------------------------------------------------------------------------
 * | CEK LOGIN
 * |--------------------------------------------------------------------------
 */
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

/*
 * |--------------------------------------------------------------------------
 * | FILTER
 * |--------------------------------------------------------------------------
 */
$keyword = isset($_GET['keyword'])
    ? trim($_GET['keyword'])
    : '';

$status_filter = isset($_GET['status'])
    ? trim($_GET['status'])
    : '';

/*
 * |--------------------------------------------------------------------------
 * | QUERY RIWAYAT KONSELING
 * |--------------------------------------------------------------------------
 * | LEFT JOIN digunakan supaya data riwayat tetap tampil walaupun
 * | siswa atau guru BK sudah dihapus.
 * |--------------------------------------------------------------------------
 */

$sql = '
    SELECT
        k.id_konseling,
        k.id_siswa,
        k.id_guru,
        k.masalah,
        k.solusi,
        k.tanggal,
        k.status,

        s.nama AS nama_siswa,
        s.nis,
        s.kelas,

        g.nama AS nama_guru,
        g.nip

    FROM konseling k

    LEFT JOIN siswa s
        ON k.id_siswa = s.id_siswa

    LEFT JOIN guru_bk g
        ON k.id_guru = g.id_guru

    WHERE 1=1
';

/*
 * |--------------------------------------------------------------------------
 * | FILTER PENCARIAN
 * |--------------------------------------------------------------------------
 */
if ($keyword !== '') {
    $keyword_safe = mysqli_real_escape_string(
        $conn,
        $keyword
    );

    $sql .= "
        AND (
            s.nama LIKE '%$keyword_safe%'
            OR s.nis LIKE '%$keyword_safe%'
            OR s.kelas LIKE '%$keyword_safe%'
            OR g.nama LIKE '%$keyword_safe%'
            OR k.masalah LIKE '%$keyword_safe%'
        )
    ";
}

/*
 * |--------------------------------------------------------------------------
 * | FILTER STATUS
 * |--------------------------------------------------------------------------
 */
if ($status_filter !== '') {
    $status_safe = mysqli_real_escape_string(
        $conn,
        $status_filter
    );

    $sql .= "
        AND k.status = '$status_safe'
    ";
}

/*
 * |--------------------------------------------------------------------------
 * | URUTKAN
 * |--------------------------------------------------------------------------
 */
$sql .= '
    ORDER BY
        k.tanggal DESC,
        k.id_konseling DESC
';

$query = mysqli_query($conn, $sql);

if (!$query) {
    die(
        'Query Riwayat Konseling gagal: '
        . mysqli_error($conn)
    );
}

/*
 * |--------------------------------------------------------------------------
 * | TOTAL DATA
 * |--------------------------------------------------------------------------
 */
$total_riwayat = mysqli_num_rows($query);

$title = 'Riwayat Konseling';

require_once __DIR__ . '/layout/header.php';

?>

<style>

/* =========================================================
   RIWAYAT KONSELING
========================================================= */

.riwayat-page {
    width: 100%;
    padding-bottom: 30px;
}


/* =========================================================
   HEADER
========================================================= */

.riwayat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 15px;

    margin-bottom: 24px;
}

.riwayat-header-left {
    display: flex;
    align-items: center;

    gap: 13px;
}

.riwayat-title-icon {
    width: 48px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: rgba(13, 110, 253, .12);
    color: #0d6efd;

    border-radius: 12px;

    font-size: 21px;
}

.riwayat-title {
    margin: 0;

    color: var(--text-main);

    font-size: 28px;
    font-weight: 700;
}

.riwayat-subtitle {
    margin: 5px 0 0;

    color: var(--text-muted);

    font-size: 14px;
}


/* =========================================================
   FILTER CARD
========================================================= */

.filter-card {
    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    padding: 20px 22px;

    margin-bottom: 20px;

    box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
}

.filter-form {
    display: grid;

    grid-template-columns:
        minmax(250px, 1fr)
        190px
        auto
        auto;

    align-items: end;

    gap: 14px;
}

.filter-group {
    display: flex;
    flex-direction: column;

    gap: 7px;
}

.filter-group label {
    color: var(--text-main);

    font-size: 13px;
    font-weight: 600;
}

.filter-control {
    width: 100%;
    min-height: 42px;

    padding: 9px 12px;

    background: var(--bg-card);
    color: var(--text-main);

    border: 1px solid var(--border-color);
    border-radius: 10px;

    outline: none;

    font-size: 14px;

    transition: .2s ease;
}

.filter-control::placeholder {
    color: var(--text-muted);
}

.filter-control:focus {
    border-color: #0d6efd;

    box-shadow:
        0 0 0 3px rgba(13, 110, 253, .10);
}


/* =========================================================
   BUTTON FILTER
========================================================= */

.btn-filter,
.btn-reset {
    min-height: 42px;

    padding: 9px 16px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    border-radius: 10px;

    font-size: 14px;
    font-weight: 600;

    text-decoration: none;

    transition: .2s ease;

    cursor: pointer;
}

.btn-filter {
    border: none;

    background: #0d6efd;
    color: #fff;
}

.btn-filter:hover {
    background: #0b5ed7;
    color: #fff;

    transform: translateY(-1px);
}

.btn-reset {
    background: var(--bg-card);
    color: var(--text-main);

    border: 1px solid var(--border-color);
}

.btn-reset:hover {
    background: var(--bg-body);
    color: var(--text-main);

    transform: translateY(-1px);
}


/* =========================================================
   DATA CARD
========================================================= */

.riwayat-card {
    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    overflow: hidden;

    box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
}


/* =========================================================
   CARD HEADER
========================================================= */

.riwayat-card-header {
    padding: 18px 22px;

    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 15px;

    border-bottom: 1px solid var(--border-color);
}

.riwayat-card-title {
    display: flex;
    align-items: center;

    gap: 12px;
}

.riwayat-card-icon {
    width: 40px;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: rgba(13, 110, 253, .12);
    color: #0d6efd;

    border-radius: 10px;

    font-size: 17px;
}

.riwayat-card-title h5 {
    margin: 0;

    color: var(--text-main);

    font-size: 16px;
    font-weight: 700;
}

.riwayat-card-title p {
    margin: 3px 0 0;

    color: var(--text-muted);

    font-size: 12px;
}

.total-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 7px 12px;

    background: rgba(13, 110, 253, .12);
    color: #0d6efd;

    border-radius: 8px;

    font-size: 12px;
    font-weight: 700;

    white-space: nowrap;
}


/* =========================================================
   TABLE
========================================================= */

.riwayat-table-wrapper {
    width: 100%;

    overflow-x: auto;
}

.riwayat-table {
    width: 100%;

    min-width: 1000px;

    margin: 0;

    border-collapse: collapse;

    color: var(--text-main);
}

.riwayat-table thead th {
    padding: 15px 18px;

    background: var(--bg-body);
    color: var(--text-main);

    border-bottom: 1px solid var(--border-color);

    font-size: 12px;
    font-weight: 700;

    white-space: nowrap;

    text-align: left;
}

.riwayat-table tbody td {
    padding: 15px 18px;

    color: var(--text-main);

    border-bottom: 1px solid var(--border-color);

    font-size: 13px;

    vertical-align: middle;
}

.riwayat-table tbody tr {
    transition: .2s ease;
}

.riwayat-table tbody tr:hover {
    background: rgba(13, 110, 253, .04);
}

.riwayat-table tbody tr:last-child td {
    border-bottom: none;
}


/* =========================================================
   NOMOR
========================================================= */

.nomor-riwayat {
    color: var(--text-muted);

    font-weight: 600;
}


/* =========================================================
   SISWA
========================================================= */

.siswa-info {
    display: flex;
    align-items: center;

    gap: 10px;

    min-width: 190px;
}

.siswa-avatar {
    width: 38px;
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: rgba(13, 110, 253, .12);
    color: #0d6efd;

    border-radius: 10px;

    font-size: 15px;
    font-weight: 700;
}

.siswa-detail {
    min-width: 0;
}

.siswa-name {
    color: var(--text-main);

    font-size: 13px;
    font-weight: 600;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.siswa-nis {
    margin-top: 2px;

    color: var(--text-muted);

    font-size: 11px;
}


/* =========================================================
   KELAS
========================================================= */

.badge-kelas {
    display: inline-block;

    padding: 6px 9px;

    background: rgba(13, 110, 253, .10);
    color: #0d6efd;

    border-radius: 7px;

    font-size: 11px;
    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   GURU
========================================================= */

.guru-info {
    min-width: 150px;
}

.guru-name {
    color: var(--text-main);

    font-size: 13px;
    font-weight: 600;
}

.guru-nip {
    margin-top: 2px;

    color: var(--text-muted);

    font-size: 11px;
}


/* =========================================================
   MASALAH
========================================================= */

.masalah-text {
    max-width: 230px;

    color: var(--text-main);

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


/* =========================================================
   TANGGAL
========================================================= */

.tanggal-riwayat {
    color: var(--text-main);

    font-size: 13px;

    white-space: nowrap;
}

.tanggal-riwayat i {
    margin-right: 5px;

    color: #0d6efd;
}


/* =========================================================
   STATUS
========================================================= */

.status-badge {
    display: inline-flex;
    align-items: center;

    gap: 5px;

    padding: 6px 10px;

    border-radius: 7px;

    font-size: 11px;
    font-weight: 700;

    white-space: nowrap;
}

.status-selesai {
    background: rgba(25, 135, 84, .10);
    color: #198754;
}

.status-batal {
    background: rgba(220, 53, 69, .10);
    color: #dc3545;
}

.status-terjadwal {
    background: rgba(13, 110, 253, .10);
    color: #0d6efd;
}

.status-menunggu {
    background: rgba(255, 193, 7, .12);
    color: #b58100;
}

.status-lain {
    background: rgba(108, 117, 125, .10);
    color: #6c757d;
}


/* =========================================================
   AKSI
========================================================= */

.btn-aksi {
    width: 34px;
    height: 34px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    background: rgba(13, 110, 253, .10);
    color: #0d6efd;

    border-radius: 8px;

    text-decoration: none;

    font-size: 14px;

    transition: .2s ease;
}

.btn-aksi:hover {
    background: #0d6efd;
    color: #fff;

    transform: translateY(-1px);
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {
    padding: 60px 20px;

    text-align: center;

    color: var(--text-muted);
}

.empty-icon {
    width: 64px;
    height: 64px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin: 0 auto 16px;

    background: rgba(13, 110, 253, .12);
    color: #0d6efd;

    border-radius: 16px;

    font-size: 27px;
}

.empty-state h5 {
    margin: 0 0 7px;

    color: var(--text-main);

    font-size: 16px;
    font-weight: 700;
}

.empty-state p {
    margin: 0;

    color: var(--text-muted);

    font-size: 13px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 992px) {

    .filter-form {
        grid-template-columns:
            1fr
            1fr;
    }

    .btn-filter,
    .btn-reset {
        width: 100%;
    }
}

@media (max-width: 768px) {

    .riwayat-header {
        align-items: flex-start;
    }

    .riwayat-title {
        font-size: 24px;
    }

    .riwayat-title-icon {
        width: 44px;
        height: 44px;
    }

    .filter-form {
        grid-template-columns: 1fr;
    }

    .filter-card {
        padding: 16px;
    }

    .riwayat-card-header {
        padding: 16px;

        align-items: flex-start;

        flex-direction: column;
    }

    .total-badge {
        align-self: flex-start;
    }
}

</style>


<div class="riwayat-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="riwayat-header">

        <div class="riwayat-header-left">

            <div class="riwayat-title-icon">
                <i class="bi bi-clock-history"></i>
            </div>

            <div>

                <h1 class="riwayat-title">
                    Riwayat Konseling
                </h1>

                <p class="riwayat-subtitle">
                    Arsip data bimbingan dan konseling siswa
                </p>

            </div>

        </div>

    </div>


    <!-- =====================================================
         FILTER
    ====================================================== -->

    <div class="filter-card">

        <form
            method="GET"
            action="riwayat_konseling.php"
        >

            <div class="filter-form">


                <!-- SEARCH -->

                <div class="filter-group">

                    <label for="keyword">
                        Cari Riwayat
                    </label>

                    <input
                        type="text"
                        name="keyword"
                        id="keyword"
                        class="filter-control"
                        placeholder="Nama siswa, NIS, kelas, guru..."
                        value="<?= htmlspecialchars($keyword); ?>"
                    >

                </div>


                <!-- STATUS -->

                <div class="filter-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="filter-control"
                    >

                        <option value="">
                            Semua Status
                        </option>

                        <option
                            value="selesai"
                            <?= $status_filter === 'selesai'
                                ? 'selected'
                                : ''; ?>
                        >
                            Selesai
                        </option>

                        <option
                            value="batal"
                            <?= $status_filter === 'batal'
                                ? 'selected'
                                : ''; ?>
                        >
                            Batal
                        </option>

                        <option
                            value="terjadwal"
                            <?= $status_filter === 'terjadwal'
                                ? 'selected'
                                : ''; ?>
                        >
                            Terjadwal
                        </option>

                        <option
                            value="menunggu"
                            <?= $status_filter === 'menunggu'
                                ? 'selected'
                                : ''; ?>
                        >
                            Menunggu
                        </option>

                    </select>

                </div>


                <!-- CARI -->

                <button
                    type="submit"
                    class="btn-filter"
                >

                    <i class="bi bi-search"></i>

                    Cari

                </button>


                <!-- RESET -->

                <a
                    href="riwayat_konseling.php"
                    class="btn-reset"
                >

                    <i class="bi bi-arrow-clockwise"></i>

                    Reset

                </a>

            </div>

        </form>

    </div>


    <!-- =====================================================
         DATA
    ====================================================== -->

    <div class="riwayat-card">


        <!-- CARD HEADER -->

        <div class="riwayat-card-header">

            <div class="riwayat-card-title">

                <div class="riwayat-card-icon">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div>

                    <h5>
                        Data Riwayat Konseling
                    </h5>

                    <p>
                        Riwayat pengajuan dan proses konseling
                    </p>

                </div>

            </div>

            <span class="total-badge">
                <?= $total_riwayat; ?> Data
            </span>

        </div>


        <?php if ($total_riwayat > 0): ?>

            <!-- =================================================
                 TABLE
            ================================================== -->

            <div class="riwayat-table-wrapper">

                <table class="riwayat-table">

                    <thead>

                        <tr>

                            <th width="60">
                                No
                            </th>

                            <th>
                                Siswa
                            </th>

                            <th>
                                Kelas
                            </th>

                            <th>
                                Guru BK
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

                            <th width="70">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php $no = 1; ?>

                    <?php while ($row = mysqli_fetch_assoc($query)): ?>

                        <?php

                        /*
                         * |--------------------------------------------------------------------------
                         * | STATUS
                         * |--------------------------------------------------------------------------
                         */

                        $status = strtolower(
                            trim($row['status'] ?? '')
                        );

                        switch ($status) {
                            case 'selesai':
                                $status_class =
                                    'status-selesai';

                                $status_icon =
                                    'bi-check-circle-fill';

                                $status_text =
                                    'Selesai';

                                break;

                            case 'batal':
                                $status_class =
                                    'status-batal';

                                $status_icon =
                                    'bi-x-circle-fill';

                                $status_text =
                                    'Batal';

                                break;

                            case 'terjadwal':
                                $status_class =
                                    'status-terjadwal';

                                $status_icon =
                                    'bi-calendar-check-fill';

                                $status_text =
                                    'Terjadwal';

                                break;

                            case 'menunggu':
                                $status_class =
                                    'status-menunggu';

                                $status_icon =
                                    'bi-hourglass-split';

                                $status_text =
                                    'Menunggu';

                                break;

                            default:
                                $status_class =
                                    'status-lain';

                                $status_icon =
                                    'bi-info-circle-fill';

                                $status_text =
                                    !empty($row['status'])
                                        ? ucfirst($row['status'])
                                        : 'Tidak diketahui';

                                break;
                        }

                        /*
                         * |--------------------------------------------------------------------------
                         * | TANGGAL
                         * |--------------------------------------------------------------------------
                         */

                        $tanggal = !empty($row['tanggal'])
                            ? date(
                                'd/m/Y',
                                strtotime($row['tanggal'])
                            )
                            : '-';

                        /*
                         * |--------------------------------------------------------------------------
                         * | NAMA SISWA
                         * |--------------------------------------------------------------------------
                         */

                        $nama_siswa = !empty($row['nama_siswa'])
                            ? $row['nama_siswa']
                            : 'Siswa Sudah Dihapus';

                        $nis = !empty($row['nis'])
                            ? $row['nis']
                            : '-';

                        /*
                         * |--------------------------------------------------------------------------
                         * | NAMA GURU
                         * |--------------------------------------------------------------------------
                         */

                        $nama_guru = !empty($row['nama_guru'])
                            ? $row['nama_guru']
                            : 'Guru Sudah Dihapus';

                        $nip = !empty($row['nip'])
                            ? $row['nip']
                            : '-';

                        /*
                         * |--------------------------------------------------------------------------
                         * | INITIAL
                         * |--------------------------------------------------------------------------
                         */

                        $initial = !empty($row['nama_siswa'])
                            ? strtoupper(
                                substr(
                                    $row['nama_siswa'],
                                    0,
                                    1
                                )
                            )
                            : '?';

                        ?>

                        <tr>


                            <!-- NO -->

                            <td>

                                <span class="nomor-riwayat">
                                    <?= $no++; ?>
                                </span>

                            </td>


                            <!-- SISWA -->

                            <td>

                                <div class="siswa-info">

                                    <div class="siswa-avatar">
                                        <?= htmlspecialchars($initial); ?>
                                    </div>

                                    <div class="siswa-detail">

                                        <div class="siswa-name">
                                            <?= htmlspecialchars(
                                                $nama_siswa
                                            ); ?>
                                        </div>

                                        <div class="siswa-nis">
                                            NIS:
                                            <?= htmlspecialchars($nis); ?>
                                        </div>

                                    </div>

                                </div>

                            </td>


                            <!-- KELAS -->

                            <td>

                                <?php if (!empty($row['kelas'])): ?>

                                    <span class="badge-kelas">

                                        <?= htmlspecialchars(
                                            $row['kelas']
                                        ); ?>

                                    </span>

                                <?php else: ?>

                                    <span class="text-muted">
                                        -
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- GURU BK -->

                            <td>

                                <div class="guru-info">

                                    <div class="guru-name">

                                        <?= htmlspecialchars(
                                            $nama_guru
                                        ); ?>

                                    </div>

                                    <?php if ($nip !== '-'): ?>

                                        <div class="guru-nip">

                                            NIP:
                                            <?= htmlspecialchars($nip); ?>

                                        </div>

                                    <?php endif; ?>

                                </div>

                            </td>


                            <!-- MASALAH -->

                            <td>

                                <div
                                    class="masalah-text"
                                    title="<?= htmlspecialchars(
            $row['masalah'] ?? '-'
        ); ?>"
                                >

                                    <?=
                                    !empty($row['masalah'])
                                        ? htmlspecialchars(
                                            $row['masalah']
                                        )
                                        : '-';
                                    ?>

                                </div>

                            </td>


                            <!-- TANGGAL -->

                            <td>

                                <div class="tanggal-riwayat">

                                    <i class="bi bi-calendar3"></i>

                                    <?= htmlspecialchars($tanggal); ?>

                                </div>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="status-badge <?= $status_class; ?>"
                                >

                                    <i
                                        class="bi <?= $status_icon; ?>"
                                    ></i>

                                    <?= htmlspecialchars(
                                        $status_text
                                    ); ?>

                                </span>

                            </td>


                            <!-- AKSI -->

                            <td>
    <a href="konseling_detail.php?id=<?= $row['id_konseling']; ?>&from=riwayat"
       class="btn-aksi btn-detail"
       title="Detail Konseling">
        <i class="bi bi-eye"></i>
    </a>
</td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <!-- =================================================
                 EMPTY STATE
            ================================================== -->

            <div class="empty-state">

                <div class="empty-icon">

                    <i class="bi bi-clock-history"></i>

                </div>

                <h5>
                    Belum Ada Riwayat Konseling
                </h5>

                <p>
                    Data konseling yang sudah tercatat
                    akan muncul di halaman ini.
                </p>

            </div>


        <?php endif; ?>


    </div>

</div>


<?php

require_once __DIR__ . '/layout/footer.php';

?>