<?php

session_start();

require_once "config/database.php";


/*
|--------------------------------------------------------------------------
| CEK LOGIN GURU
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| DATA GURU
|--------------------------------------------------------------------------
*/

$guru_nama = $_SESSION['guru_nama'] ?? 'Guru BK';


/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$stat_total = 0;
$stat_menunggu = 0;
$stat_terjadwal = 0;
$stat_selesai = 0;


/*
|--------------------------------------------------------------------------
| TOTAL KONSELING
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM konseling
    WHERE id_guru = ?
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $stat_total = (int) $row['total'];
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| MENUNGGU
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM konseling
    WHERE id_guru = ?
    AND status = 'menunggu'
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $stat_menunggu = (int) $row['total'];
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| TERJADWAL
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM konseling
    WHERE id_guru = ?
    AND status = 'terjadwal'
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $stat_terjadwal = (int) $row['total'];
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| SELESAI
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM konseling
    WHERE id_guru = ?
    AND status = 'selesai'
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $stat_selesai = (int) $row['total'];
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| KONSELING TERBARU
|--------------------------------------------------------------------------
*/

$konseling_terbaru = [];

$stmt = $conn->prepare("
    SELECT
        k.id_konseling,
        k.masalah,
        k.tanggal,
        k.status,

        s.id_siswa,
        s.nama AS nama_siswa,
        s.nis,
        s.kelas,
        s.foto AS foto_siswa

    FROM konseling k

    LEFT JOIN siswa s
        ON k.id_siswa = s.id_siswa

    WHERE k.id_guru = ?

    ORDER BY
        k.tanggal DESC,
        k.id_konseling DESC

    LIMIT 6
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $konseling_terbaru[] = $row;
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| JADWAL BERIKUTNYA
|--------------------------------------------------------------------------
*/

$jadwal_berikutnya = [];

$stmt = $conn->prepare("
    SELECT
        k.id_konseling,
        k.masalah,
        k.tanggal,
        k.status,

        s.id_siswa,
        s.nama AS nama_siswa,
        s.nis,
        s.kelas,
        s.foto AS foto_siswa

    FROM konseling k

    LEFT JOIN siswa s
        ON k.id_siswa = s.id_siswa

    WHERE k.id_guru = ?

    AND k.status = 'terjadwal'

    AND k.tanggal >= NOW()

    ORDER BY
        k.tanggal ASC,
        k.id_konseling ASC

    LIMIT 5
");

$stmt->bind_param("i", $guru_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $jadwal_berikutnya[] = $row;
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| STATUS BADGE
|--------------------------------------------------------------------------
*/

function statusGuruBadge($status)
{
    switch ($status) {

        case 'terjadwal':
            return [
                'class' => 'status-terjadwal',
                'icon' => 'bi-calendar-check-fill',
                'text' => 'Terjadwal'
            ];

        case 'selesai':
            return [
                'class' => 'status-selesai',
                'icon' => 'bi-check-circle-fill',
                'text' => 'Selesai'
            ];

        case 'batal':
            return [
                'class' => 'status-batal',
                'icon' => 'bi-x-circle-fill',
                'text' => 'Batal'
            ];

        case 'menunggu':
            return [
                'class' => 'status-menunggu',
                'icon' => 'bi-clock-fill',
                'text' => 'Menunggu'
            ];

        default:
            return [
                'class' => 'status-menunggu',
                'icon' => 'bi-clock-fill',
                'text' => ucfirst($status)
            ];
    }
}


/*
|--------------------------------------------------------------------------
| FORMAT TANGGAL
|--------------------------------------------------------------------------
*/

function formatTanggalGuru($tanggal)
{
    if (!$tanggal) {
        return '-';
    }

    $timestamp = strtotime($tanggal);

    if (!$timestamp) {
        return '-';
    }

    return date('d M Y, H:i', $timestamp) . ' WIB';
}


/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$page_title = "Dashboard Guru BK | BK Online";


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once "layout/header_guru.php";

?>


<style>
    /* =========================================================
   DASHBOARD
========================================================= */

    .guru-dashboard {
        width: 100%;
    }


    /* =========================================================
   WELCOME
========================================================= */

    .guru-dashboard-heading {
        margin-bottom: 24px;
    }

    .guru-dashboard-heading h1 {
        margin: 0;
        font-size: 28px;
        line-height: 1.3;
        font-weight: 700;
        color: var(--text-main);
    }

    .guru-dashboard-heading p {
        margin: 7px 0 0;
        font-size: 13px;
        color: var(--text-muted);
    }


    /* =========================================================
   STATISTIK
========================================================= */

    .guru-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .guru-stat-card {
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

    .guru-stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(77, 145, 255, .35);
    }

    .guru-stat-icon {
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

    .guru-stat-info {
        min-width: 0;
    }

    .guru-stat-number {
        display: block;

        font-size: 27px;
        line-height: 1.1;
        font-weight: 700;

        color: var(--text-main);
    }

    .guru-stat-label {
        display: block;

        margin-top: 6px;

        font-size: 12px;
        color: var(--text-muted);
    }


    /* =========================================================
   CONTENT GRID
========================================================= */

    .guru-dashboard-grid {
        display: grid;

        grid-template-columns:
            minmax(0, 1.65fr) minmax(320px, 1fr);

        gap: 18px;

        align-items: start;
    }


    /* =========================================================
   CARD
========================================================= */

    .guru-dashboard-card {
        background: var(--bg-card);

        border:
            1px solid var(--border-color);

        border-radius: 14px;

        overflow: hidden;

        box-shadow: var(--shadow);
    }

    .guru-dashboard-card-header {
        min-height: 62px;

        padding: 16px 18px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 12px;

        border-bottom:
            1px solid var(--border-color);
    }

    .guru-dashboard-card-title {
        display: flex;
        align-items: center;

        gap: 10px;
    }

    .guru-dashboard-card-title i {
        color: #4d91ff;
        font-size: 17px;
    }

    .guru-dashboard-card-title h2 {
        margin: 0;

        color: var(--text-main);

        font-size: 14px;
        font-weight: 700;
    }

    .guru-dashboard-card-link {
        color: #4d91ff;

        font-size: 11px;
        font-weight: 600;

        text-decoration: none;
    }

    .guru-dashboard-card-link:hover {
        color: #78adff;
    }


    /* =========================================================
   KONSELING TERBARU
========================================================= */

    .guru-counseling-list {
        padding: 4px 18px;
    }

    .guru-counseling-item {
        padding: 16px 0;

        display: flex;
        align-items: center;

        gap: 13px;

        border-bottom:
            1px solid var(--border-color);
    }

    .guru-counseling-item:last-child {
        border-bottom: 0;
    }


    /* =========================================================
   AVATAR
========================================================= */

    .guru-student-avatar {
        width: 44px;
        height: 44px;
        min-width: 44px;

        overflow: hidden;

        border-radius: 50%;

        display: flex;
        align-items: center;
        justify-content: center;

        background: #1769e8;
        color: #fff;

        font-size: 18px;
    }

    .guru-student-avatar img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;
    }


    /* =========================================================
   INFO SISWA
========================================================= */

    .guru-counseling-info {
        flex: 1;
        min-width: 0;
    }

    .guru-student-name {
        display: block;

        color: var(--text-main);

        font-size: 13px;
        font-weight: 700;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .guru-student-class {
        display: block;

        margin-top: 4px;

        color: var(--text-muted);

        font-size: 10px;
    }

    .guru-counseling-date {
        margin-top: 6px;

        color: var(--text-muted);

        font-size: 10px;
    }

    .guru-counseling-date i {
        margin-right: 3px;
    }


    /* =========================================================
   RIGHT COUNSELING
========================================================= */

    .guru-counseling-right {
        display: flex;

        flex-direction: column;
        align-items: flex-end;

        gap: 8px;

        flex-shrink: 0;
    }


    /* =========================================================
   STATUS
========================================================= */

    .guru-status {
        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 5px 9px;

        border-radius: 20px;

        font-size: 9px;
        font-weight: 700;

        white-space: nowrap;
    }

    .guru-status i {
        font-size: 9px;
    }

    .status-terjadwal {
        color: #65a2ff;
        background: rgba(57, 133, 255, .12);
    }

    .status-selesai {
        color: #45d69a;
        background: rgba(25, 135, 84, .12);
    }

    .status-batal {
        color: #ff7180;
        background: rgba(220, 53, 69, .12);
    }

    .status-menunggu {
        color: #ffc65c;
        background: rgba(255, 193, 7, .12);
    }


    /* =========================================================
   DETAIL BUTTON
========================================================= */

    .guru-detail-button {
        height: 30px;

        padding: 0 10px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 5px;

        border:
            1px solid var(--border-color);

        border-radius: 7px;

        color: var(--text-main);
        background: transparent;

        font-size: 9px;
        font-weight: 600;

        text-decoration: none;

        transition: .2s ease;
    }

    .guru-detail-button:hover {
        color: #fff;

        background: #1769e8;

        border-color: #1769e8;
    }


    /* =========================================================
   JADWAL
========================================================= */

    .guru-schedule-list {
        padding: 4px 18px;
    }

    .guru-schedule-item {
        padding: 16px 0;

        display: flex;

        gap: 13px;

        border-bottom:
            1px solid var(--border-color);
    }

    .guru-schedule-item:last-child {
        border-bottom: 0;
    }


    /* =========================================================
   TANGGAL
========================================================= */

    .guru-schedule-date {
        width: 55px;
        height: 60px;
        min-width: 55px;

        border-radius: 11px;

        display: flex;

        flex-direction: column;

        align-items: center;
        justify-content: center;

        background:
            rgba(57, 133, 255, .12);

        color: #4d91ff;
    }

    .guru-schedule-day {
        font-size: 20px;

        line-height: 1;

        font-weight: 700;
    }

    .guru-schedule-month {
        margin-top: 5px;

        font-size: 9px;

        text-transform: uppercase;

        font-weight: 700;
    }


    /* =========================================================
   INFO JADWAL
========================================================= */

    .guru-schedule-info {
        min-width: 0;

        flex: 1;
    }

    .guru-schedule-name {
        display: block;

        color: var(--text-main);

        font-size: 13px;
        font-weight: 700;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .guru-schedule-time {
        display: flex;

        align-items: center;

        gap: 5px;

        margin-top: 7px;

        color: var(--text-muted);

        font-size: 10px;
    }

    .guru-schedule-time i {
        color: #4d91ff;
    }


    /* =========================================================
   EMPTY
========================================================= */

    .guru-empty {
        padding: 45px 20px;

        text-align: center;

        color: var(--text-muted);
    }

    .guru-empty i {
        display: block;

        margin-bottom: 10px;

        font-size: 30px;

        opacity: .55;
    }

    .guru-empty span {
        font-size: 11px;
    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 1100px) {

        .guru-stat-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .guru-dashboard-grid {
            grid-template-columns: 1fr;
        }
    }


    @media (max-width: 700px) {

        .guru-dashboard-heading h1 {
            font-size: 23px;
        }

        .guru-stat-grid {
            grid-template-columns: 1fr;
        }

        .guru-counseling-item {
            align-items: flex-start;
        }

        .guru-counseling-right {
            align-items: flex-end;
        }
    }


    @media (max-width: 500px) {

        .guru-dashboard-heading h1 {
            font-size: 20px;
        }

        .guru-counseling-item {
            flex-wrap: wrap;
        }

        .guru-counseling-right {
            width: 100%;

            flex-direction: row;

            justify-content: space-between;

            align-items: center;
        }
    }
</style>


<div class="guru-dashboard">


    <!-- =====================================================
         WELCOME
    ====================================================== -->

    <div class="guru-dashboard-heading">

        <h1>
            Selamat datang,
            <?= htmlspecialchars($guru_nama); ?> 👋
        </h1>

        <p>
            Kelola kegiatan bimbingan dan konseling siswa dari sini.
        </p>

    </div>


    <!-- =====================================================
         STATISTIK
    ====================================================== -->

    <div class="guru-stat-grid">


        <!-- TOTAL -->

        <div class="guru-stat-card">

            <div class="guru-stat-icon">
                <i class="bi bi-chat-square-text-fill"></i>
            </div>

            <div class="guru-stat-info">

                <span class="guru-stat-number">
                    <?= $stat_total; ?>
                </span>

                <span class="guru-stat-label">
                    Total Konseling
                </span>

            </div>

        </div>


        <!-- MENUNGGU -->

        <div class="guru-stat-card">

            <div class="guru-stat-icon">
                <i class="bi bi-clock-fill"></i>
            </div>

            <div class="guru-stat-info">

                <span class="guru-stat-number">
                    <?= $stat_menunggu; ?>
                </span>

                <span class="guru-stat-label">
                    Menunggu
                </span>

            </div>

        </div>


        <!-- TERJADWAL -->

        <div class="guru-stat-card">

            <div class="guru-stat-icon">
                <i class="bi bi-calendar-check-fill"></i>
            </div>

            <div class="guru-stat-info">

                <span class="guru-stat-number">
                    <?= $stat_terjadwal; ?>
                </span>

                <span class="guru-stat-label">
                    Terjadwal
                </span>

            </div>

        </div>


        <!-- SELESAI -->

        <div class="guru-stat-card">

            <div class="guru-stat-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>

            <div class="guru-stat-info">

                <span class="guru-stat-number">
                    <?= $stat_selesai; ?>
                </span>

                <span class="guru-stat-label">
                    Selesai
                </span>

            </div>

        </div>

    </div>


    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <div class="guru-dashboard-grid">


        <!-- =================================================
             KONSELING TERBARU
        ================================================== -->

        <div class="guru-dashboard-card">

            <div class="guru-dashboard-card-header">

                <div class="guru-dashboard-card-title">

                    <i class="bi bi-chat-square-dots-fill"></i>

                    <h2>
                        Konseling Terbaru
                    </h2>

                </div>

                <a href="konseling_guru.php" class="guru-dashboard-card-link">
                    Lihat Semua
                </a>

            </div>


            <div class="guru-counseling-list">


                <?php if (!empty($konseling_terbaru)): ?>


                    <?php foreach ($konseling_terbaru as $row): ?>

                        <?php

                        $status =
                            statusGuruBadge(
                                $row['status']
                            );


                        $foto_siswa_ada = false;

                        $foto_siswa_url = '';


                        if (!empty($row['foto_siswa'])) {

                            $foto_path =
                                __DIR__ .
                                "/uploads/siswa/" .
                                $row['foto_siswa'];


                            if (file_exists($foto_path)) {

                                $foto_siswa_ada = true;

                                $foto_siswa_url =
                                    "uploads/siswa/" .
                                    rawurlencode(
                                        $row['foto_siswa']
                                    );
                            }
                        }

                        ?>


                        <div class="guru-counseling-item">


                            <!-- FOTO -->

                            <div class="guru-student-avatar">

                                <?php if ($foto_siswa_ada): ?>

                                    <img src="<?= htmlspecialchars($foto_siswa_url); ?>" alt="Foto siswa">

                                <?php else: ?>

                                    <i class="bi bi-person-fill"></i>

                                <?php endif; ?>

                            </div>


                            <!-- INFO -->

                            <div class="guru-counseling-info">

                                <span class="guru-student-name">

                                    <?= htmlspecialchars(
                                        $row['nama_siswa'] ?? 'Siswa'
                                    ); ?>

                                </span>


                                <span class="guru-student-class">

                                    <?= htmlspecialchars(
                                        $row['kelas'] ?? '-'
                                    ); ?>

                                    • NIS
                                    <?= htmlspecialchars(
                                        $row['nis'] ?? '-'
                                    ); ?>

                                </span>


                                <div class="guru-counseling-date">

                                    <i class="bi bi-calendar3"></i>

                                    <?= htmlspecialchars(
                                        formatTanggalGuru(
                                            $row['tanggal']
                                        )
                                    ); ?>

                                </div>

                            </div>


                            <!-- STATUS + DETAIL -->

                            <div class="guru-counseling-right">

                                <span class="guru-status <?= htmlspecialchars($status['class']); ?>">

                                    <i class="bi <?= htmlspecialchars($status['icon']); ?>"></i>

                                    <?= htmlspecialchars(
                                        $status['text']
                                    ); ?>

                                </span>


                                <a href="konseling_detail_guru.php?id=<?= $row['id_konseling']; ?>&from=dashboard"
                                    class="guru-detail-button">
                                    <i class="bi bi-eye"></i>
                                    Detail
                                </a>

                            </div>

                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="guru-empty">

                        <i class="bi bi-chat-square"></i>

                        <span>
                            Belum ada data konseling.
                        </span>

                    </div>


                <?php endif; ?>


            </div>

        </div>


        <!-- =================================================
             JADWAL BERIKUTNYA
        ================================================== -->

        <div class="guru-dashboard-card">

            <div class="guru-dashboard-card-header">

                <div class="guru-dashboard-card-title">

                    <i class="bi bi-calendar-event-fill"></i>

                    <h2>
                        Jadwal Berikutnya
                    </h2>

                </div>


                <a href="konseling_guru.php" class="guru-dashboard-card-link">
                    Semua
                </a>

            </div>


            <div class="guru-schedule-list">


                <?php if (!empty($jadwal_berikutnya)): ?>


                    <?php foreach ($jadwal_berikutnya as $row): ?>

                        <?php

                        $timestamp =
                            strtotime(
                                $row['tanggal']
                            );

                        ?>


                        <div class="guru-schedule-item">


                            <!-- TANGGAL -->

                            <div class="guru-schedule-date">

                                <span class="guru-schedule-day">

                                    <?= date(
                                        'd',
                                        $timestamp
                                    ); ?>

                                </span>


                                <span class="guru-schedule-month">

                                    <?= date(
                                        'M',
                                        $timestamp
                                    ); ?>

                                </span>

                            </div>


                            <!-- INFO -->

                            <div class="guru-schedule-info">

                                <span class="guru-schedule-name">

                                    <?= htmlspecialchars(
                                        $row['nama_siswa'] ?? 'Siswa'
                                    ); ?>

                                </span>


                                <div class="guru-schedule-time">

                                    <i class="bi bi-clock"></i>

                                    <?= date(
                                        'H:i',
                                        $timestamp
                                    ); ?>

                                    WIB

                                </div>


                                <span class="guru-student-class">

                                    <?= htmlspecialchars(
                                        $row['kelas'] ?? '-'
                                    ); ?>

                                </span>

                            </div>

                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="guru-empty">

                        <i class="bi bi-calendar-x"></i>

                        <span>
                            Tidak ada jadwal konseling berikutnya.
                        </span>

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<?php

require_once "layout/footer_guru.php";

?>