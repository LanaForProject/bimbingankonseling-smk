<?php

session_start();

require_once __DIR__ . "/config/database.php";


/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'siswa'
) {

    header("Location: login.php");

    exit;
}


$siswa_id =
    (int) ($_SESSION['siswa_id'] ?? 0);


if ($siswa_id <= 0) {

    header("Location: login.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL RIWAYAT KONSELING SISWA
|--------------------------------------------------------------------------
*/

$riwayat = [];


$stmt = $conn->prepare("
    SELECT
        k.id_konseling,
        k.masalah,
        k.solusi,
        k.tanggal,
        k.status,
        g.nama AS nama_guru

    FROM konseling k

    LEFT JOIN guru_bk g
        ON k.id_guru = g.id_guru

    WHERE k.id_siswa = ?

    ORDER BY
        k.tanggal DESC,
        k.id_konseling DESC
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

    $riwayat[] = $row;
}


$stmt->close();


/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$total =
    count($riwayat);

$terjadwal = 0;

$selesai = 0;

$batal = 0;


foreach ($riwayat as $row) {

    switch (
        strtolower(
            $row['status'] ?? ''
        )
    ) {

        case 'terjadwal':

            $terjadwal++;

            break;


        case 'selesai':

            $selesai++;

            break;


        case 'batal':

            $batal++;

            break;
    }
}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

function status_riwayat($status)
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
| FORMAT TANGGAL
|--------------------------------------------------------------------------
*/

function tanggal_riwayat($tanggal)
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
| HEADER SISWA
|--------------------------------------------------------------------------
*/

$page_title =
    'Riwayat Konseling | BK Online';


require_once
    __DIR__ .
    "/layout/header_siswa.php";

?>


<style>
    /* =====================================================
   HEADER
===================================================== */

    .page-head {

        margin-bottom: 22px;
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


    /* =====================================================
   STATISTIK
===================================================== */

    .stats {

        display: grid;

        grid-template-columns:
            repeat(4,
                minmax(0, 1fr));

        gap: 14px;

        margin-bottom: 18px;
    }


    .stat {

        background: var(--bg-card);

        border:
            1px solid var(--border-color);

        border-radius: 14px;

        padding: 16px;

        box-shadow: var(--shadow);
    }


    .stat-label {

        color: var(--text-muted);

        font-size: 10px;
    }


    .stat-number {

        margin-top: 6px;

        color: var(--text-main);

        font-size: 23px;

        font-weight: 700;
    }


    .stat-top {

        display: flex;

        justify-content:
            space-between;

        gap: 10px;
    }


    .stat-icon {

        width: 36px;

        height: 36px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 9px;

        background:
            rgba(13, 110, 253, .12);

        color: #0d6efd;
    }


    /* =====================================================
   CARD
===================================================== */

    .history-card {

        background: var(--bg-card);

        border:
            1px solid var(--border-color);

        border-radius: 16px;

        box-shadow: var(--shadow);

        overflow: hidden;
    }


    .history-head {

        min-height: 55px;

        padding: 0 18px;

        display: flex;

        align-items: center;

        justify-content:
            space-between;

        border-bottom:
            1px solid var(--border-color);
    }


    .history-title {

        display: flex;

        align-items: center;

        gap: 8px;

        color: var(--text-main);

        font-size: 13px;

        font-weight: 700;
    }


    .history-title i {

        color: #0d6efd;
    }


    .history-count {

        color: var(--text-muted);

        font-size: 10px;
    }


    /* =====================================================
   TABLE
===================================================== */

    .table-wrap {

        overflow-x: auto;
    }


    .history-table {

        width: 100%;

        min-width: 760px;

        border-collapse:
            collapse;
    }


    .history-table th {

        padding: 13px 15px;

        background: var(--bg-body);

        border-bottom:
            1px solid var(--border-color);

        color: var(--text-muted);

        font-size: 9px;

        text-align: left;

        text-transform: uppercase;
    }


    .history-table td {

        padding: 14px 15px;

        border-bottom:
            1px solid var(--border-color);

        color: var(--text-main);

        font-size: 10px;

        vertical-align: middle;
    }


    .history-table tr:last-child td {

        border-bottom: 0;
    }


    /* =====================================================
   GURU
===================================================== */

    .guru-name {

        font-weight: 700;
    }


    .muted {

        margin-top: 3px;

        color: var(--text-muted);

        font-size: 9px;
    }


    /* =====================================================
   MASALAH
===================================================== */

    .problem {

        max-width: 340px;

        color: var(--text-muted);

        line-height: 1.5;
    }


    /* =====================================================
   STATUS
===================================================== */

    .status {

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
   DETAIL BUTTON
===================================================== */

    .btn-detail {

        width: 31px;

        height: 31px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        border-radius: 7px;

        background:
            rgba(13, 110, 253, .1);

        color: #0d6efd;
    }


    .btn-detail:hover {

        background: #0d6efd;

        color: #fff;
    }


    /* =====================================================
   EMPTY
===================================================== */

    .empty {

        padding: 55px 20px;

        text-align: center;
    }


    .empty-icon {

        width: 55px;

        height: 55px;

        margin:
            0 auto 12px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 50%;

        background:
            rgba(13, 110, 253, .1);

        color: #0d6efd;

        font-size: 23px;
    }


    .empty-title {

        color: var(--text-main);

        font-size: 14px;

        font-weight: 700;
    }


    .empty-text {

        max-width: 400px;

        margin:
            6px auto 0;

        color: var(--text-muted);

        font-size: 10px;

        line-height: 1.6;
    }


    .btn-ajukan {

        margin-top: 15px;

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 9px 13px;

        border-radius: 8px;

        background: #0d6efd;

        color: #fff;

        font-size: 10px;

        font-weight: 700;
    }


    .btn-ajukan:hover {

        background: #0b5ed7;

        color: #fff;
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
    }


    @media (max-width: 550px) {

        .stats {

            grid-template-columns: 1fr;
        }
    }
</style>


<!-- =====================================================
     HEADER HALAMAN
===================================================== -->

<div class="page-head">

    <h1 class="page-title">

        Riwayat Konseling

    </h1>


    <p class="page-subtitle">

        Lihat seluruh riwayat konseling yang pernah kamu ajukan.

    </p>

</div>


<!-- =====================================================
     STATISTIK
===================================================== -->

<div class="stats">


    <!-- TOTAL -->

    <div class="stat">

        <div class="stat-top">

            <div>

                <div class="stat-label">
                    Total Konseling
                </div>

                <div class="stat-number">
                    <?= $total; ?>
                </div>

            </div>


            <div class="stat-icon">

                <i class="bi bi-chat-square-text-fill"></i>

            </div>

        </div>

    </div>


    <!-- TERJADWAL -->

    <div class="stat">

        <div class="stat-top">

            <div>

                <div class="stat-label">
                    Terjadwal
                </div>

                <div class="stat-number">
                    <?= $terjadwal; ?>
                </div>

            </div>


            <div class="stat-icon">

                <i class="bi bi-calendar-check-fill"></i>

            </div>

        </div>

    </div>


    <!-- SELESAI -->

    <div class="stat">

        <div class="stat-top">

            <div>

                <div class="stat-label">
                    Selesai
                </div>

                <div class="stat-number">
                    <?= $selesai; ?>
                </div>

            </div>


            <div class="stat-icon">

                <i class="bi bi-check-circle-fill"></i>

            </div>

        </div>

    </div>


    <!-- BATAL -->

    <div class="stat">

        <div class="stat-top">

            <div>

                <div class="stat-label">
                    Batal
                </div>

                <div class="stat-number">
                    <?= $batal; ?>
                </div>

            </div>


            <div class="stat-icon">

                <i class="bi bi-x-circle-fill"></i>

            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     RIWAYAT
===================================================== -->

<div class="history-card">


    <div class="history-head">

        <div class="history-title">

            <i class="bi bi-clock-history"></i>

            Riwayat Konseling Saya

        </div>


        <div class="history-count">

            <?= $total; ?> Data

        </div>

    </div>


    <?php if (!$riwayat): ?>


        <div class="empty">


            <div class="empty-icon">

                <i class="bi bi-chat-square-dots"></i>

            </div>


            <div class="empty-title">

                Belum Ada Riwayat Konseling

            </div>


            <div class="empty-text">

                Kamu belum memiliki data konseling.
                Silakan ajukan konseling jika membutuhkan
                bantuan dari Guru BK.

            </div>


            <a href="konseling_tambah.php" class="btn-ajukan">

                <i class="bi bi-plus-lg"></i>

                Ajukan Konseling

            </a>


        </div>


    <?php else: ?>


        <div class="table-wrap">

            <table class="history-table">


                <thead>

                    <tr>

                        <th>
                            No
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Guru BK
                        </th>

                        <th>
                            Permasalahan
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


                    <?php

                    $no = 1;

                    ?>


                    <?php foreach ($riwayat as $row): ?>


                        <?php

                        [
                            $cls,
                            $icon,
                            $label
                        ] =
                            status_riwayat(
                                $row['status']
                            );

                        ?>


                        <tr>


                            <td>

                                <?= $no++; ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    tanggal_riwayat(
                                        $row['tanggal']
                                    )
                                ); ?>

                            </td>


                            <td>

                                <div class="guru-name">

                                    <?= htmlspecialchars(
                                        $row['nama_guru']
                                        ?? 'Guru BK'
                                    ); ?>

                                </div>


                                <div class="muted">

                                    Guru BK

                                </div>

                            </td>


                            <td>

                                <div class="problem">

                                    <?= htmlspecialchars(
                                        $row['masalah']
                                    ); ?>

                                </div>

                            </td>


                            <td>

                                <span class="status <?= $cls; ?>">

                                    <i class="bi <?= $icon; ?>"></i>

                                    <?= $label; ?>

                                </span>

                            </td>


                            <td>

                                <a href="konseling_detail_siswa.php?id=<?= $row['id_konseling']; ?>&from=riwayat"
                                    class="btn-detail" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>

            </table>

        </div>


    <?php endif; ?>


</div>


<?php

require_once
    __DIR__ .
    "/layout/footer_siswa.php";

?>