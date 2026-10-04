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


if (
    $_SESSION['role'] !== 'siswa'
) {


    if (
        $_SESSION['role'] === 'admin' ||
        $_SESSION['role'] === 'guru'
    ) {

        header("Location: dashboard.php");

        exit;
    }


    $_SESSION = [];

    session_destroy();

    header("Location: login.php");

    exit;
}


$siswa_id =
    (int) ($_SESSION['siswa_id'] ?? 0);


if ($siswa_id <= 0) {

    $_SESSION = [];

    session_destroy();

    header("Location: login.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$total_konseling = 0;

$total_terjadwal = 0;

$total_selesai = 0;

$total_batal = 0;


/*
|--------------------------------------------------------------------------
| TOTAL
|--------------------------------------------------------------------------
*/

$stmt =
    $conn->prepare("
        SELECT COUNT(*) AS total

        FROM konseling

        WHERE id_siswa = ?
    ");


$stmt->bind_param(
    "i",
    $siswa_id
);


$stmt->execute();


$total_konseling =
    (int) (
        $stmt
            ->get_result()
            ->fetch_assoc()['total']
        ?? 0
    );


$stmt->close();


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

foreach (
    [
        'terjadwal' =>
            'total_terjadwal',

        'selesai' =>
            'total_selesai',

        'batal' =>
            'total_batal'
    ]
    as $status => $target
) {


    $stmt =
        $conn->prepare("
            SELECT COUNT(*) AS total

            FROM konseling

            WHERE id_siswa = ?

            AND status = ?
        ");


    $stmt->bind_param(
        "is",
        $siswa_id,
        $status
    );


    $stmt->execute();


    $$target =
        (int) (
            $stmt
                ->get_result()
                ->fetch_assoc()['total']
            ?? 0
        );


    $stmt->close();

}


/*
|--------------------------------------------------------------------------
| KONSELING TERBARU
|--------------------------------------------------------------------------
*/

$konseling_terbaru = [];


$stmt =
    $conn->prepare("
        SELECT

            k.id_konseling,

            k.masalah,

            k.tanggal,

            k.status,

            k.solusi,

            g.nama AS nama_guru

        FROM konseling k

        LEFT JOIN guru_bk g
            ON k.id_guru = g.id_guru

        WHERE k.id_siswa = ?

        ORDER BY
            k.tanggal DESC,
            k.id_konseling DESC

        LIMIT 5
    ");


$stmt->bind_param(
    "i",
    $siswa_id
);


$stmt->execute();


$result =
    $stmt->get_result();


while (
    $row =
    $result->fetch_assoc()
) {

    $konseling_terbaru[] =
        $row;
}


$stmt->close();


/*
|--------------------------------------------------------------------------
| JADWAL BERIKUTNYA
|--------------------------------------------------------------------------
*/

$jadwal_berikutnya = null;


$stmt =
    $conn->prepare("
        SELECT

            k.id_konseling,

            k.masalah,

            k.tanggal,

            k.status,

            g.nama AS nama_guru

        FROM konseling k

        LEFT JOIN guru_bk g
            ON k.id_guru = g.id_guru

        WHERE k.id_siswa = ?

        AND k.status = 'terjadwal'

        AND k.tanggal >= CURDATE()

        ORDER BY
            k.tanggal ASC,
            k.id_konseling ASC

        LIMIT 1
    ");


$stmt->bind_param(
    "i",
    $siswa_id
);


$stmt->execute();


$jadwal_berikutnya =
    $stmt
        ->get_result()
        ->fetch_assoc();


$stmt->close();


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

function status_siswa($status)
{

    return match (
    strtolower(
        $status ?? ''
    )
    ) {

        'terjadwal' => [
            'status-terjadwal',
            'bi-calendar-check',
            'Terjadwal'
        ],

        'selesai' => [
            'status-selesai',
            'bi-check-circle',
            'Selesai'
        ],

        'batal' => [
            'status-batal',
            'bi-x-circle',
            'Batal'
        ],

        default => [
            'status-lain',
            'bi-clock',
            'Menunggu'
        ]

    };
}


/*
|--------------------------------------------------------------------------
| TANGGAL
|--------------------------------------------------------------------------
*/

function tanggal_siswa($tanggal)
{

    if (!$tanggal) {

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


    $t =
        strtotime($tanggal);


    return

        date('d', $t)
        . ' '
        . $bulan[
            (int) date('n', $t)
        ]
        . ' '
        . date('Y', $t);
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

$page_title =
    'Dashboard Siswa | BK Online';


require_once
    __DIR__ .
    "/layout/header_siswa.php";

?>


<style>
    /* =====================================================
   HEADER
===================================================== */

    .page-head {

        display: flex;

        align-items: center;

        justify-content:
            space-between;

        gap: 18px;

        margin-bottom: 24px;
    }


    .page-title {

        margin: 0;

        color: var(--text-main);

        font-size: 27px;

        font-weight: 700;
    }


    .page-subtitle {

        margin: 6px 0 0;

        color: var(--text-muted);

        font-size: 13px;
    }


    .btn-main {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        padding: 11px 17px;

        background: #0d6efd;

        color: #fff;

        border-radius: 10px;

        font-size: 12px;

        font-weight: 700;
    }


    .btn-main:hover {

        background: #0b5ed7;

        color: #fff;
    }


    /* =====================================================
   STATS
===================================================== */

    .stats {

        display: grid;

        grid-template-columns:
            repeat(4,
                minmax(0, 1fr));

        gap: 18px;

        margin-bottom: 20px;
    }


    .stat {

        background: var(--bg-card);

        border:
            1px solid var(--border-color);

        border-radius: 16px;

        padding: 18px;

        box-shadow: var(--shadow);
    }


    .stat-top {

        display: flex;

        justify-content:
            space-between;

        gap: 12px;
    }


    .stat-label {

        color: var(--text-muted);

        font-size: 11px;
    }


    .stat-number {

        margin-top: 6px;

        color: var(--text-main);

        font-size: 27px;

        font-weight: 700;
    }


    .stat-icon {

        width: 42px;

        height: 42px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 11px;

        background:
            rgba(13, 110, 253, .12);

        color: #0d6efd;
    }


    /* =====================================================
   GRID
===================================================== */

    .dashboard-grid {

        display: grid;

        grid-template-columns:
            minmax(0, 1.5fr) minmax(280px, .8fr);

        gap: 18px;
    }


    /* =====================================================
   CARD
===================================================== */

    .card-box {

        background: var(--bg-card);

        border:
            1px solid var(--border-color);

        border-radius: 16px;

        box-shadow: var(--shadow);

        overflow: hidden;
    }


    .card-head {

        min-height: 55px;

        padding: 0 18px;

        display: flex;

        align-items: center;

        justify-content:
            space-between;

        border-bottom:
            1px solid var(--border-color);
    }


    .card-title {

        display: flex;

        align-items: center;

        gap: 8px;

        color: var(--text-main);

        font-size: 13px;

        font-weight: 700;
    }


    .card-title i {

        color: #0d6efd;
    }


    .card-body {

        padding: 18px;
    }


    /* =====================================================
   HISTORY
===================================================== */

    .history-item {

        padding: 14px 0;

        border-bottom:
            1px solid var(--border-color);
    }


    .history-item:first-child {

        padding-top: 0;
    }


    .history-item:last-child {

        border-bottom: 0;

        padding-bottom: 0;
    }


    .history-top {

        display: flex;

        justify-content:
            space-between;

        gap: 15px;
    }


    .history-date {

        color: var(--text-main);

        font-size: 11px;

        font-weight: 700;
    }


    .history-guru {

        margin-top: 4px;

        color: var(--text-muted);

        font-size: 10px;
    }


    .history-problem {

        margin-top: 8px;

        color: var(--text-muted);

        font-size: 11px;

        line-height: 1.5;
    }


    /* =====================================================
   STATUS
===================================================== */

    .badge-status {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 5px 8px;

        border-radius: 7px;

        font-size: 9px;

        font-weight: 700;

        white-space: nowrap;
    }


    .status-terjadwal {

        background:
            rgba(13, 110, 253, .12);

        color: #4d9aff;
    }


    .status-selesai {

        background:
            rgba(25, 135, 84, .12);

        color: #35b879;
    }


    .status-batal {

        background:
            rgba(220, 53, 69, .12);

        color: #ff6574;
    }


    .status-lain {

        background:
            rgba(108, 117, 125, .12);

        color: var(--text-muted);
    }


    /* =====================================================
   SCHEDULE
===================================================== */

    .schedule-box {

        padding: 20px;
    }


    .schedule-date {

        color: var(--text-main);

        font-size: 20px;

        font-weight: 700;
    }


    .schedule-label {

        color: var(--text-muted);

        font-size: 10px;

        margin-bottom: 5px;
    }


    .schedule-guru {

        margin-top: 12px;

        color: var(--text-main);

        font-size: 12px;

        font-weight: 700;
    }


    .schedule-problem {

        margin-top: 5px;

        color: var(--text-muted);

        font-size: 10px;

        line-height: 1.5;
    }


    .schedule-button {

        margin-top: 18px;

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 9px 13px;

        border-radius: 8px;

        background:
            rgba(13, 110, 253, .1);

        color: #0d6efd;

        font-size: 10px;

        font-weight: 700;
    }


    /* =====================================================
   EMPTY
===================================================== */

    .empty {

        padding: 35px 10px;

        text-align: center;

        color: var(--text-muted);

        font-size: 11px;
    }


    .empty i {

        display: block;

        margin-bottom: 8px;

        color: #0d6efd;

        font-size: 25px;
    }


    /* =====================================================
   RESPONSIVE
===================================================== */

    @media (max-width: 950px) {

        .stats {

            grid-template-columns:
                repeat(2,
                    minmax(0, 1fr));
        }


        .dashboard-grid {

            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 600px) {

        .page-head {

            align-items:
                flex-start;

            flex-direction:
                column;
        }


        .stats {

            grid-template-columns: 1fr;
        }

    }
</style>


<!-- =====================================================
     HEADER HALAMAN
===================================================== -->

<div class="page-head">


    <div>

        <h1 class="page-title">

            Dashboard Siswa

        </h1>


        <p class="page-subtitle">

            Selamat datang,
            <?= htmlspecialchars(
                $siswa_nama
            ); ?>.

            Kelola layanan bimbingan
            &amp; konseling kamu di sini.

        </p>

    </div>


    <a href="konseling_tambah.php" class="btn-main">

        <i class="bi bi-plus-lg"></i>

        Ajukan Konseling

    </a>


</div>


<!-- =====================================================
     STATISTIK
===================================================== -->

<div class="stats">


    <div class="stat">

        <div class="stat-top">

            <div>

                <div class="stat-label">

                    Total Konseling

                </div>


                <div class="stat-number">

                    <?= $total_konseling; ?>

                </div>

            </div>


            <div class="stat-icon">

                <i class="bi bi-chat-square-text-fill"></i>

            </div>

        </div>

    </div>


    <div class="stat">

        <div class="stat-top">

            <div>

                <div class="stat-label">

                    Terjadwal

                </div>


                <div class="stat-number">

                    <?= $total_terjadwal; ?>

                </div>

            </div>


            <div class="stat-icon">

                <i class="bi bi-calendar-check-fill"></i>

            </div>

        </div>

    </div>


    <div class="stat">

        <div class="stat-top">

            <div>

                <div class="stat-label">

                    Selesai

                </div>


                <div class="stat-number">

                    <?= $total_selesai; ?>

                </div>

            </div>


            <div class="stat-icon">

                <i class="bi bi-check-circle-fill"></i>

            </div>

        </div>

    </div>


    <div class="stat">

        <div class="stat-top">

            <div>

                <div class="stat-label">

                    Batal

                </div>


                <div class="stat-number">

                    <?= $total_batal; ?>

                </div>

            </div>


            <div class="stat-icon">

                <i class="bi bi-x-circle-fill"></i>

            </div>

        </div>

    </div>


</div>


<!-- =====================================================
     CONTENT
===================================================== -->

<div class="dashboard-grid">


    <!-- =================================================
         TERBARU
    ================================================== -->

    <div class="card-box">


        <div class="card-head">


            <div class="card-title">

                <i class="bi bi-clock-history"></i>

                Konseling Terbaru

            </div>


            <a href="riwayat_konseling_siswa.php" class="schedule-button" style="margin:0;">

                Lihat Semua

            </a>


        </div>


        <div class="card-body">


            <?php if (
                !$konseling_terbaru
            ): ?>


                <div class="empty">

                    <i class="bi bi-chat-square-dots"></i>

                    Belum ada data konseling.

                </div>


            <?php else: ?>


                <?php foreach (
                    $konseling_terbaru
                    as $row
                ): ?>


                    <?php

                    [
                        $cls,
                        $icon,
                        $label
                    ] =
                        status_siswa(
                            $row['status']
                        );

                    ?>


                    <div class="history-item">


                        <div class="history-top">


                            <div>


                                <div class="history-date">

                                    <?= htmlspecialchars(
                                        tanggal_siswa(
                                            $row['tanggal']
                                        )
                                    ); ?>

                                </div>


                                <div class="history-guru">

                                    Guru BK:

                                    <?= htmlspecialchars(
                                        $row['nama_guru']
                                        ??
                                        'Belum ditentukan'
                                    ); ?>

                                </div>


                            </div>


                            <span class="
                                    badge-status
                                    <?= $cls; ?>
                                ">

                                <i class="
                                        bi
                                        <?= $icon; ?>
                                    "></i>

                                <?= $label; ?>

                            </span>


                        </div>


                        <div class="history-problem">

                            <?= htmlspecialchars(
                                $row['masalah']
                            ); ?>

                        </div>


                    </div>


                <?php endforeach; ?>


            <?php endif; ?>


        </div>

    </div>


    <!-- =================================================
         JADWAL
    ================================================== -->

    <div class="card-box">


        <div class="card-head">


            <div class="card-title">

                <i class="bi bi-calendar-check-fill"></i>

                Jadwal Konseling

            </div>


        </div>


        <div class="schedule-box">


            <?php if (
                $jadwal_berikutnya
            ): ?>


                <div class="schedule-label">

                    Jadwal berikutnya

                </div>


                <div class="schedule-date">

                    <?= htmlspecialchars(
                        tanggal_siswa(
                            $jadwal_berikutnya['tanggal']
                        )
                    ); ?>

                </div>


                <div class="schedule-guru">

                    <?= htmlspecialchars(
                        $jadwal_berikutnya['nama_guru']
                        ??
                        'Guru BK'
                    ); ?>

                </div>


                <div class="schedule-problem">

                    <?= htmlspecialchars(
                        $jadwal_berikutnya['masalah']
                    ); ?>

                </div>


                <a href="
        konseling_detail_siswa.php
        ?id=<?= (int) $jadwal_berikutnya['id_konseling']; ?>
        &from=dashboard
    " class="schedule-button">
                    Lihat Detail
                    <i class="bi bi-arrow-right"></i>
                </a>


            <?php else: ?>


                <div class="empty">

                    <i class="bi bi-calendar-x"></i>

                    Belum ada jadwal konseling.

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>


<?php

require_once
    __DIR__ .
    "/layout/footer_siswa.php";

?>