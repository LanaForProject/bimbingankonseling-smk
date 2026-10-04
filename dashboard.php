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

$title = 'Dashboard BK Online';

/*
 * |--------------------------------------------------------------------------
 * | FUNGSI TOTAL DATA
 * |--------------------------------------------------------------------------
 */

function total_data($conn, $sql)
{
  $result = mysqli_query($conn, $sql);

  if (!$result) {
    return 0;
  }

  $row = mysqli_fetch_assoc($result);

  return (int) ($row['total'] ?? 0);
}

/*
 * |--------------------------------------------------------------------------
 * | STATISTIK
 * |--------------------------------------------------------------------------
 */

$total_siswa = total_data(
  $conn,
  'SELECT COUNT(*) AS total FROM siswa'
);

$total_guru = total_data(
  $conn,
  'SELECT COUNT(*) AS total FROM guru_bk'
);

$total_konseling = total_data(
  $conn,
  'SELECT COUNT(*) AS total FROM konseling'
);

$total_selesai = total_data(
  $conn,
  "SELECT COUNT(*) AS total
     FROM konseling
     WHERE status = 'selesai'"
);

/*
 * |--------------------------------------------------------------------------
 * | KONSELING TERBARU
 * |--------------------------------------------------------------------------
 */

$sql_terbaru = '
    SELECT
        k.id_konseling,
        k.masalah,
        k.tanggal,
        k.status,

        s.nama AS siswa_nama,
        s.kelas,

        g.nama AS guru_nama

    FROM konseling k

    LEFT JOIN siswa s
        ON k.id_siswa = s.id_siswa

    LEFT JOIN guru_bk g
        ON k.id_guru = g.id_guru

    ORDER BY
        k.tanggal DESC,
        k.id_konseling DESC

    LIMIT 8
';

$result_terbaru = mysqli_query(
  $conn,
  $sql_terbaru
);

/*
 * |--------------------------------------------------------------------------
 * | DATA GRAFIK STATUS
 * |--------------------------------------------------------------------------
 */

$status_data = [
  'menunggu' => 0,
  'terjadwal' => 0,
  'selesai' => 0,
  'batal' => 0
];

$result_status = mysqli_query(
  $conn,
  'SELECT
        status,
        COUNT(*) AS total

     FROM konseling

     GROUP BY status'
);

if ($result_status) {
  while ($row = mysqli_fetch_assoc($result_status)) {
    $status = strtolower(
      trim(
        $row['status'] ?? ''
      )
    );

    if (isset($status_data[$status])) {
      $status_data[$status] =
        (int) $row['total'];
    }
  }
}

/*
 * |--------------------------------------------------------------------------
 * | HEADER
 * |--------------------------------------------------------------------------
 */

require_once __DIR__ . '/layout/header.php';

?>


<style>

/* =========================================================
   DASHBOARD
========================================================= */

.dashboard-page {

    padding-bottom: 30px;

}


/* =========================================================
   HEADER DASHBOARD
========================================================= */

.dashboard-header {

    margin-bottom: 24px;

}


.dashboard-title {

    font-size: 28px;

    font-weight: 700;

    color: var(--text-main);

    margin-bottom: 6px;

}


.dashboard-subtitle {

    font-size: 14px;

    color: var(--text-muted);

    margin-bottom: 0;

}


.dashboard-welcome {

    color: var(--text-main);

    font-size: 16px;

    margin-bottom: 4px;

}


.dashboard-welcome strong {

    font-weight: 700;

}


.dashboard-clock {

    color: var(--text-main);

    font-size: 22px;

    font-weight: 700;

}


.dashboard-date {

    color: var(--text-muted);

    font-size: 13px;

    margin-top: 3px;

}


.system-online {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    margin-top: 8px;

    padding: 6px 11px;

    border-radius: 20px;

    background: rgba(25, 135, 84, .10);

    color: #198754;

    font-size: 12px;

    font-weight: 600;

}


/* =========================================================
   STATISTIK
========================================================= */

.stat-card {

    height: 100%;

    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    box-shadow: 0 4px 18px rgba(0, 0, 0, .04);

    overflow: hidden;

    transition: .2s ease;

}


.stat-card:hover {

    transform: translateY(-2px);

    box-shadow:
        0 7px 22px rgba(0, 0, 0, .07);

}


.stat-card .card-body {

    padding: 22px;

    position: relative;

}


.stat-label {

    color: var(--text-muted);

    font-size: 14px;

    font-weight: 500;

    margin-bottom: 7px;

}


.stat-number {

    color: var(--text-main);

    font-size: 30px;

    font-weight: 700;

    line-height: 1.2;

}


.stat-description {

    color: var(--text-muted);

    font-size: 12px;

    margin-top: 7px;

}


.stat-icon-box {

    position: absolute;

    right: 20px;

    top: 20px;

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    font-size: 22px;

}


/* =========================================================
   WARNA ICON STATISTIK
========================================================= */

.stat-blue .stat-icon-box {

    background: rgba(13, 110, 253, .10);

    color: #0d6efd;

}


.stat-green .stat-icon-box {

    background: rgba(25, 135, 84, .10);

    color: #198754;

}


.stat-orange .stat-icon-box {

    background: rgba(255, 159, 67, .12);

    color: #e68a00;

}


.stat-red .stat-icon-box {

    background: rgba(220, 53, 69, .10);

    color: #dc3545;

}


/* =========================================================
   CARD UMUM
========================================================= */

.dashboard-card {

    width: 100%;

    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    box-shadow:
        0 4px 18px rgba(0, 0, 0, .04);

    overflow: hidden;

}


/* =========================================================
   KONSELING ROW
========================================================= */

.konseling-row {

    display: flex;

    align-items: stretch;

}


.konseling-row > .col-lg-8,
.konseling-row > .col-lg-4 {

    display: flex;

}


/* =========================================================
   CARD HEADER
========================================================= */

.dashboard-card-header {

    padding: 18px 22px;

    background: var(--bg-card);

    border-bottom:
        1px solid var(--border-color);

    display: flex;

    align-items: center;

    justify-content: space-between;

}


.dashboard-card-title {

    color: var(--text-main);

    font-size: 15px;

    font-weight: 700;

    margin: 0;

}


.dashboard-card-title i {

    margin-right: 8px;

    color: #0d6efd;

}


/* =========================================================
   KONSELING TERBARU
========================================================= */

.latest-card {

    min-height: 420px;

    display: flex;

    flex-direction: column;

}


.latest-card-body {

    padding: 0;

    flex: 1;

}


.latest-table-wrapper {

    width: 100%;

    overflow-x: auto;

}


.latest-table {

    width: 100%;

    margin: 0;

    color: var(--text-main);

}


.latest-table thead th {

    background: var(--bg-body);

    color: var(--text-main);

    border-bottom:
        1px solid var(--border-color);

    border-top: none;

    padding: 14px 18px;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;

}


.latest-table tbody td {

    color: var(--text-main);

    border-color: var(--border-color);

    padding: 14px 18px;

    font-size: 13px;

    vertical-align: middle;

}


.latest-table tbody tr {

    transition: .15s ease;

}


.latest-table tbody tr:hover {

    background:
        rgba(13, 110, 253, .03);

}


.latest-table tbody tr:last-child td {

    border-bottom: none;

}


/* =========================================================
   PROFIL SISWA
========================================================= */

.student-profile {

    display: flex;

    align-items: center;

    gap: 10px;

    min-width: 190px;

}


.student-avatar {

    width: 38px;

    height: 38px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background:
        rgba(13, 110, 253, .10);

    color: #0d6efd;

    font-size: 14px;

    font-weight: 700;

    text-transform: uppercase;

}


.student-info {

    min-width: 0;

}


.student-name {

    color: var(--text-main);

    font-size: 13px;

    font-weight: 600;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

}


.student-class {

    color: var(--text-muted);

    font-size: 11px;

    margin-top: 2px;

}


/* =========================================================
   STATUS BADGE
========================================================= */

.badge-status {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;

}


.status-menunggu {

    background:
        rgba(255, 193, 7, .12);

    color: #b58100;

}


.status-terjadwal {

    background:
        rgba(13, 110, 253, .10);

    color: #0d6efd;

}


.status-selesai {

    background:
        rgba(25, 135, 84, .10);

    color: #198754;

}


.status-batal {

    background:
        rgba(220, 53, 69, .10);

    color: #dc3545;

}


.status-default {

    background: var(--bg-body);

    color: var(--text-muted);

}


/* =========================================================
   STATUS CARD
========================================================= */

.status-card {

    min-height: 420px;

    display: flex;

    flex-direction: column;

}


.status-card-body {

    flex: 1;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    padding: 18px 20px 22px;

}


.status-chart-wrapper {

    position: relative;

    width: 100%;

    height: 260px;

    display: flex;

    align-items: center;

    justify-content: center;

}


#grafikStatus {

    width: 100% !important;

    height: 260px !important;

}


/* =========================================================
   KETERANGAN STATUS
========================================================= */

.status-legend {

    width: 100%;

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 10px 14px;

    margin-top: 5px;

    padding: 0 4px;

}


.status-legend-item {

    display: flex;

    align-items: center;

    gap: 8px;

    color: var(--text-main);

    font-size: 12px;

    font-weight: 500;

}


.status-legend-dot {

    width: 10px;

    height: 10px;

    flex-shrink: 0;

    border-radius: 50%;

}


.legend-menunggu {

    background: #ffc107;

}


.legend-terjadwal {

    background: #0d6efd;

}


.legend-selesai {

    background: #198754;

}


.legend-batal {

    background: #dc3545;

}


.status-legend-count {

    margin-left: auto;

    color: var(--text-muted);

    font-size: 11px;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {

    padding: 50px 20px;

    text-align: center;

    color: var(--text-muted);

}


.empty-state i {

    display: block;

    font-size: 38px;

    margin-bottom: 10px;

    opacity: .55;

}


.empty-state span {

    font-size: 13px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 991px) {

    .konseling-row {

        display: block;

    }


    .konseling-row > .col-lg-8,
    .konseling-row > .col-lg-4 {

        display: block;

        margin-bottom: 20px;

    }


    .latest-card,
    .status-card {

        min-height: auto;

    }

}


@media (max-width: 768px) {

    .dashboard-title {

        font-size: 24px;

    }


    .dashboard-clock {

        font-size: 20px;

    }


    .dashboard-time {

        text-align: left !important;

        margin-top: 18px;

    }

}


@media (max-width: 576px) {

    .stat-card .card-body {

        padding: 18px;

    }


    .stat-number {

        font-size: 26px;

    }


    .stat-icon-box {

        width: 42px;

        height: 42px;

        font-size: 19px;

    }


    .latest-table {

        min-width: 700px;

    }


    .status-legend {

        grid-template-columns: 1fr;

    }

}

</style>


<!-- =========================================================
     DASHBOARD
========================================================= -->

<div class="dashboard-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="dashboard-header">

        <div class="row align-items-center">


            <!-- KIRI -->

            <div class="col-lg-8">

                <h1 class="dashboard-title">

                    <i class="bi bi-heart-pulse-fill me-2"></i>

                    Dashboard BK Online

                </h1>


                <p class="dashboard-welcome">

                    Selamat datang,

                    <strong>

                        <?= htmlspecialchars(
                          $_SESSION['admin_nama']
                            ?? 'Administrator'
                        ); ?>

                    </strong>

                </p>


                <p class="dashboard-subtitle">

                    Kelola data siswa, guru BK, dan layanan
                    konseling sekolah dari satu tempat.

                </p>

            </div>


            <!-- KANAN -->

            <div
                class="col-lg-4 text-lg-end dashboard-time mt-3 mt-lg-0"
            >

                <div
                    class="dashboard-clock"
                    id="jamDigital"
                >
                    00:00:00 WIB
                </div>


                <div
                    class="dashboard-date"
                    id="tanggalDigital"
                >
                    Memuat tanggal...
                </div>


                <span class="system-online">

                    <i class="bi bi-circle-fill"></i>

                    Sistem Online

                </span>

            </div>


        </div>

    </div>



    <!-- =====================================================
         STATISTIK
    ====================================================== -->

    <div class="row g-4 mb-4">


        <!-- TOTAL SISWA -->

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card stat-blue">

                <div class="card-body">

                    <div class="stat-label">

                        Total Siswa

                    </div>


                    <div class="stat-number">

                        <?= $total_siswa; ?>

                    </div>


                    <div class="stat-description">

                        Data siswa terdaftar

                    </div>


                    <div class="stat-icon-box">

                        <i class="bi bi-people-fill"></i>

                    </div>

                </div>

            </div>

        </div>



        <!-- GURU BK -->

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card stat-green">

                <div class="card-body">

                    <div class="stat-label">

                        Guru BK

                    </div>


                    <div class="stat-number">

                        <?= $total_guru; ?>

                    </div>


                    <div class="stat-description">

                        Guru BK terdaftar

                    </div>


                    <div class="stat-icon-box">

                        <i class="bi bi-person-workspace"></i>

                    </div>

                </div>

            </div>

        </div>



        <!-- TOTAL KONSELING -->

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card stat-orange">

                <div class="card-body">

                    <div class="stat-label">

                        Total Konseling

                    </div>


                    <div class="stat-number">

                        <?= $total_konseling; ?>

                    </div>


                    <div class="stat-description">

                        Seluruh pengajuan

                    </div>


                    <div class="stat-icon-box">

                        <i class="bi bi-chat-dots-fill"></i>

                    </div>

                </div>

            </div>

        </div>



        <!-- KONSELING SELESAI -->

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card stat-red">

                <div class="card-body">

                    <div class="stat-label">

                        Konseling Selesai

                    </div>


                    <div class="stat-number">

                        <?= $total_selesai; ?>

                    </div>


                    <div class="stat-description">

                        Konseling yang selesai

                    </div>


                    <div class="stat-icon-box">

                        <i class="bi bi-check-circle-fill"></i>

                    </div>

                </div>

            </div>

        </div>


    </div>



    <!-- =====================================================
         KONSELING TERBARU + STATUS
    ====================================================== -->

    <div class="row g-4 konseling-row">


        <!-- =================================================
             KONSELING TERBARU
        ================================================== -->

        <div class="col-lg-8">

            <div class="dashboard-card latest-card">


                <!-- HEADER -->

                <div class="dashboard-card-header">

                    <h5 class="dashboard-card-title">

                        <i class="bi bi-clock-history"></i>

                        Konseling Terbaru

                    </h5>

                </div>


                <!-- BODY -->

                <div class="latest-card-body">

                    <div class="latest-table-wrapper">

                        <table class="table latest-table">


                            <thead>

                                <tr>

                                    <th>No</th>

                                    <th>Siswa</th>

                                    <th>Guru BK</th>

                                    <th>Tanggal</th>

                                    <th>Status</th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php if (
                              $result_terbaru &&
                              mysqli_num_rows(
                                $result_terbaru
                              ) > 0
                            ): ?>


                                <?php $no = 1; ?>


                                <?php while (
                                  $row =
                                    mysqli_fetch_assoc(
                                      $result_terbaru
                                    )
                                ): ?>


                                    <?php

                                    /*
                                     * |--------------------------------------------------
                                     * | STATUS
                                     * |--------------------------------------------------
                                     */

                                    $status = strtolower(
                                      trim(
                                        $row['status'] ?? ''
                                      )
                                    );

                                    switch ($status) {
                                      case 'menunggu':
                                        $status_class =
                                          'status-menunggu';

                                        break;

                                      case 'terjadwal':
                                        $status_class =
                                          'status-terjadwal';

                                        break;

                                      case 'selesai':
                                        $status_class =
                                          'status-selesai';

                                        break;

                                      case 'batal':
                                        $status_class =
                                          'status-batal';

                                        break;

                                      default:
                                        $status_class =
                                          'status-default';

                                        break;
                                    }

                                    /*
                                     * |--------------------------------------------------
                                     * | NAMA SISWA
                                     * |--------------------------------------------------
                                     */

                                    $nama_siswa = trim(
                                      $row['siswa_nama'] ?? ''
                                    );

                                    if (
                                      $nama_siswa === ''
                                    ) {
                                      $nama_siswa =
                                        'Siswa Sudah Dihapus';
                                    }

                                    /*
                                     * |--------------------------------------------------
                                     * | HURUF AVATAR
                                     * |--------------------------------------------------
                                     */

                                    $huruf_awal = strtoupper(
                                      substr(
                                        $nama_siswa,
                                        0,
                                        1
                                      )
                                    );

                                    ?>


                                    <tr>


                                        <!-- NOMOR -->

                                        <td>

                                            <?= $no++; ?>

                                        </td>



                                        <!-- SISWA -->

                                        <td>

                                            <div
                                                class="student-profile"
                                            >


                                                <div
                                                    class="student-avatar"
                                                >

                                                    <?= htmlspecialchars(
                                                      $huruf_awal
                                                    ); ?>

                                                </div>


                                                <div
                                                    class="student-info"
                                                >

                                                    <div
                                                        class="student-name"
                                                    >

                                                        <?= htmlspecialchars(
                                                          $nama_siswa
                                                        ); ?>

                                                    </div>


                                                    <div
                                                        class="student-class"
                                                    >

                                                        <?= htmlspecialchars(
                                                          $row['kelas']
                                                            ?? '-'
                                                        ); ?>

                                                    </div>

                                                </div>


                                            </div>

                                        </td>



                                        <!-- GURU BK -->

                                        <td>

                                            <?= htmlspecialchars(
                                              $row['guru_nama']
                                                ?? 'Belum Ditentukan'
                                            ); ?>

                                        </td>



                                        <!-- TANGGAL -->

                                        <td>

                                            <?= htmlspecialchars(
                                              $row['tanggal']
                                                ?? '-'
                                            ); ?>

                                        </td>



                                        <!-- STATUS -->

                                        <td>

                                            <span
                                                class="badge-status
                                                <?= $status_class; ?>"
                                            >

                                                <?= htmlspecialchars(
                                                  ucfirst(
                                                    $status ?: '-'
                                                  )
                                                ); ?>

                                            </span>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td colspan="5">


                                        <div
                                            class="empty-state"
                                        >

                                            <i
                                                class="bi bi-inbox"
                                            ></i>


                                            <span>

                                                Belum ada data
                                                konseling.

                                            </span>

                                        </div>


                                    </td>

                                </tr>


                            <?php endif; ?>


                            </tbody>


                        </table>

                    </div>

                </div>

            </div>

        </div>



        <!-- =================================================
             STATUS KONSELING
        ================================================== -->

        <div class="col-lg-4">

            <div class="dashboard-card status-card">


                <!-- HEADER -->

                <div class="dashboard-card-header">

                    <h5
                        class="dashboard-card-title"
                    >

                        <i
                            class="bi bi-pie-chart-fill"
                        ></i>

                        Status Konseling

                    </h5>

                </div>


                <!-- BODY -->

                <div
                    class="status-card-body"
                >


                    <!-- GRAFIK -->

                    <div
                        class="status-chart-wrapper"
                    >

                        <canvas
                            id="grafikStatus"
                        ></canvas>

                    </div>


                    <!-- KETERANGAN WARNA -->

                    <div
                        class="status-legend"
                    >


                        <!-- MENUNGGU -->

                        <div
                            class="status-legend-item"
                        >

                            <span
                                class="
                                status-legend-dot
                                legend-menunggu
                                "
                            ></span>


                            <span>
                                Menunggu
                            </span>


                            <span
                                class="status-legend-count"
                            >

                                <?= $status_data['menunggu']; ?>

                            </span>

                        </div>



                        <!-- TERJADWAL -->

                        <div
                            class="status-legend-item"
                        >

                            <span
                                class="
                                status-legend-dot
                                legend-terjadwal
                                "
                            ></span>


                            <span>
                                Terjadwal
                            </span>


                            <span
                                class="status-legend-count"
                            >

                                <?= $status_data['terjadwal']; ?>

                            </span>

                        </div>



                        <!-- SELESAI -->

                        <div
                            class="status-legend-item"
                        >

                            <span
                                class="
                                status-legend-dot
                                legend-selesai
                                "
                            ></span>


                            <span>
                                Selesai
                            </span>


                            <span
                                class="status-legend-count"
                            >

                                <?= $status_data['selesai']; ?>

                            </span>

                        </div>



                        <!-- BATAL -->

                        <div
                            class="status-legend-item"
                        >

                            <span
                                class="
                                status-legend-dot
                                legend-batal
                                "
                            ></span>


                            <span>
                                Batal
                            </span>


                            <span
                                class="status-legend-count"
                            >

                                <?= $status_data['batal']; ?>

                            </span>

                        </div>


                    </div>

                </div>

            </div>

        </div>


    </div>

</div>



<!-- =========================================================
     CHART JS
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/chart.js"
></script>


<script>

/*
|--------------------------------------------------------------------------
| JAM DIGITAL
|--------------------------------------------------------------------------
*/

function updateJam() {

    const sekarang =
        new Date();


    const hari = [

        'Minggu',

        'Senin',

        'Selasa',

        'Rabu',

        'Kamis',

        'Jumat',

        'Sabtu'

    ];


    const bulan = [

        'Januari',

        'Februari',

        'Maret',

        'April',

        'Mei',

        'Juni',

        'Juli',

        'Agustus',

        'September',

        'Oktober',

        'November',

        'Desember'

    ];


    const jam =
        String(
            sekarang.getHours()
        ).padStart(2, '0');


    const menit =
        String(
            sekarang.getMinutes()
        ).padStart(2, '0');


    const detik =
        String(
            sekarang.getSeconds()
        ).padStart(2, '0');


    const jamElement =
        document.getElementById(
            'jamDigital'
        );


    const tanggalElement =
        document.getElementById(
            'tanggalDigital'
        );


    if (jamElement) {

        jamElement.textContent =

            jam +
            ':' +
            menit +
            ':' +
            detik +
            ' WIB';

    }


    if (tanggalElement) {

        tanggalElement.textContent =

            hari[
                sekarang.getDay()
            ] +

            ', ' +

            sekarang.getDate() +

            ' ' +

            bulan[
                sekarang.getMonth()
            ] +

            ' ' +

            sekarang.getFullYear();

    }

}


updateJam();


setInterval(

    updateJam,

    1000

);


/*
|--------------------------------------------------------------------------
| GRAFIK STATUS KONSELING
|--------------------------------------------------------------------------
*/

const canvas =
    document.getElementById(
        'grafikStatus'
    );


if (canvas) {


    new Chart(

        canvas,

        {

            type: 'doughnut',


            data: {

                labels: [

                    'Menunggu',

                    'Terjadwal',

                    'Selesai',

                    'Batal'

                ],


                datasets: [{

                    data: [

                        <?= $status_data['menunggu']; ?>,

                        <?= $status_data['terjadwal']; ?>,

                        <?= $status_data['selesai']; ?>,

                        <?= $status_data['batal']; ?>

                    ],


                    backgroundColor: [

                        '#ffc107',

                        '#0d6efd',

                        '#198754',

                        '#dc3545'

                    ],


                    borderWidth: 0

                }]

            },


            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '62%',


                plugins: {

                    legend: {

                        display: false

                    }

                }

            }

        }

    );

}

</script>


<?php

/*
 * |--------------------------------------------------------------------------
 * | FOOTER
 * |--------------------------------------------------------------------------
 */

require_once __DIR__ . '/layout/footer.php';

?>