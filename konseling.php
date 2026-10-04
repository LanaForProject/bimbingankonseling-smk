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

$title = 'Data Konseling';

/*
 * |--------------------------------------------------------------------------
 * | AMBIL DATA KONSELING
 * |--------------------------------------------------------------------------
 * | LEFT JOIN digunakan supaya riwayat tetap tampil walaupun
 * | data siswa atau guru BK sudah dihapus.
 * |--------------------------------------------------------------------------
 */

$query = mysqli_query($conn, '
    SELECT
        k.id_konseling,
        k.id_siswa,
        k.id_guru,
        k.tanggal,
        k.status,
        k.masalah,
        k.solusi,

        s.nis,
        s.nama AS nama_siswa,
        s.kelas,
        s.jurusan,

        g.nip,
        g.nama AS nama_guru

    FROM konseling k

    LEFT JOIN siswa s
        ON k.id_siswa = s.id_siswa

    LEFT JOIN guru_bk g
        ON k.id_guru = g.id_guru

    ORDER BY k.tanggal DESC, k.id_konseling DESC
');

if (!$query) {
    die('Query Data Konseling gagal: ' . mysqli_error($conn));
}

/*
 * |--------------------------------------------------------------------------
 * | HITUNG TOTAL DATA
 * |--------------------------------------------------------------------------
 */

$total_konseling = mysqli_num_rows($query);

/*
 * |--------------------------------------------------------------------------
 * | HEADER
 * |--------------------------------------------------------------------------
 */

require_once __DIR__ . '/layout/header.php';
?>

<style>

/* =========================================================
   DATA KONSELING
========================================================= */

.konseling-page {
    width: 100%;
    padding-bottom: 30px;
}

/* =========================================================
   HEADER
========================================================= */

.page-header-konseling {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 24px;
}

.page-header-left h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--text-main);
}

.page-header-left h1 i {
    color: #0d6efd;
    margin-right: 8px;
}

.page-header-left p {
    margin: 6px 0 0;
    color: var(--text-muted);
    font-size: 14px;
}

/* =========================================================
   INFO ADMIN
========================================================= */

.info-konseling {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    background: rgba(13, 110, 253, .10);
    color: #0d6efd;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
}

/* =========================================================
   CARD
========================================================= */

.konseling-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
}

/* =========================================================
   CARD HEADER
========================================================= */

.konseling-card-header {
    padding: 18px 22px;
    border-bottom: 1px solid var(--border-color);

    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.konseling-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.konseling-title-icon {
    width: 40px;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: rgba(13, 110, 253, .12);
    color: #0d6efd;

    border-radius: 10px;
    font-size: 18px;
}

.konseling-title h5 {
    margin: 0;
    color: var(--text-main);
    font-size: 16px;
    font-weight: 700;
}

.konseling-title p {
    margin: 3px 0 0;
    color: var(--text-muted);
    font-size: 12px;
}

.total-badge {
    background: rgba(13, 110, 253, .12);
    color: #0d6efd;
    padding: 7px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
}

/* =========================================================
   TABLE
========================================================= */

.konseling-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.konseling-table {
    width: 100%;
    margin: 0;
    color: var(--text-main);
    border-collapse: collapse;
}

.konseling-table thead th {
    background: var(--bg-body);
    color: var(--text-main);

    border-bottom: 1px solid var(--border-color);

    padding: 15px 18px;

    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.konseling-table tbody td {
    color: var(--text-main);

    border-bottom: 1px solid var(--border-color);

    padding: 15px 18px;

    font-size: 13px;
    vertical-align: middle;
}

.konseling-table tbody tr {
    transition: .2s ease;
}

.konseling-table tbody tr:hover {
    background: rgba(13, 110, 253, .04);
}

.konseling-table tbody tr:last-child td {
    border-bottom: none;
}

/* =========================================================
   NOMOR
========================================================= */

.nomor-konseling {
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
    font-weight: 600;
    font-size: 13px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.siswa-nis {
    color: var(--text-muted);
    font-size: 11px;
    margin-top: 2px;
}

/* =========================================================
   KELAS
========================================================= */

.badge-kelas {
    display: inline-block;

    background: rgba(13, 110, 253, .10);
    color: #0d6efd;

    padding: 6px 9px;

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
    color: var(--text-muted);
    font-size: 11px;
    margin-top: 2px;
}

/* =========================================================
   TANGGAL
========================================================= */

.tanggal-konseling {
    color: var(--text-main);
    font-size: 13px;
    white-space: nowrap;
}

.tanggal-konseling i {
    color: #0d6efd;
    margin-right: 5px;
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

.status-menunggu {
    background: rgba(255, 193, 7, .12);
    color: #b58100;
}

.status-terjadwal {
    background: rgba(13, 110, 253, .10);
    color: #0d6efd;
}

.status-selesai {
    background: rgba(25, 135, 84, .10);
    color: #198754;
}

.status-batal {
    background: rgba(220, 53, 69, .10);
    color: #dc3545;
}

.status-lain {
    background: rgba(108, 117, 125, .10);
    color: #6c757d;
}

/* =========================================================
   MASALAH
========================================================= */

.masalah-konseling {
    max-width: 220px;
    color: var(--text-main);
    font-size: 12px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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

    border-radius: 8px;

    border: none;
    text-decoration: none;

    transition: .2s ease;

    font-size: 14px;
}

.btn-detail {
    background: rgba(13, 110, 253, .10);
    color: #0d6efd;
}

.btn-detail:hover {
    background: #0d6efd;
    color: #fff;
}

/* =========================================================
   EMPTY DATA
========================================================= */

.data-kosong {
    text-align: center;
    padding: 60px 20px !important;
    color: var(--text-muted);
}

.data-kosong-icon {
    width: 58px;
    height: 58px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin: 0 auto 15px;

    background: rgba(13, 110, 253, .12);
    color: #0d6efd;

    border-radius: 14px;

    font-size: 24px;
}

.data-kosong h5 {
    color: var(--text-main);
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 5px;
}

.data-kosong p {
    margin: 0;
    color: var(--text-muted);
    font-size: 13px;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .page-header-konseling {
        align-items: flex-start;
        flex-direction: column;
    }

    .info-konseling {
        width: 100%;
        justify-content: center;
    }

    .konseling-card-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .total-badge {
        align-self: flex-start;
    }

    .konseling-table {
        min-width: 900px;
    }
}

</style>

<div class="konseling-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="page-header-konseling">

        <div class="page-header-left">

            <h1>
                <i class="bi bi-chat-left-text"></i>
                Data Konseling
            </h1>

            <p>
                Lihat data pengajuan bimbingan dan konseling siswa
            </p>

        </div>

        <div class="info-konseling">
            <i class="bi bi-info-circle"></i>
            Admin hanya dapat melihat data
        </div>

    </div>


    <!-- =====================================================
         CARD
    ====================================================== -->

    <div class="konseling-card">

        <div class="konseling-card-header">

            <div class="konseling-title">

                <div class="konseling-title-icon">
                    <i class="bi bi-chat-square-text"></i>
                </div>

                <div>
                    <h5>Daftar Konseling</h5>

                    <p>
                        Pengajuan konseling dari siswa
                    </p>
                </div>

            </div>

            <span class="total-badge">
                <?= $total_konseling; ?> Data
            </span>

        </div>


        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="konseling-table-wrapper">

            <table class="konseling-table">

                <thead>

                    <tr>

                        <th width="60">No</th>

                        <th>Siswa</th>

                        <th>Kelas</th>

                        <th>Guru BK</th>

                        <th>Tanggal</th>

                        <th>Status</th>

                        <th>Masalah</th>

                        <th width="80">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                <?php if ($total_konseling > 0): ?>

                    <?php $no = 1; ?>

                    <?php while ($row = mysqli_fetch_assoc($query)): ?>

                        <?php

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
                            : 'Belum Ditentukan';

                        $nip = !empty($row['nip'])
                            ? $row['nip']
                            : '-';

                        /*
                         * |--------------------------------------------------------------------------
                         * | STATUS
                         * |--------------------------------------------------------------------------
                         */

                        $status = strtolower(trim($row['status'] ?? ''));

                        switch ($status) {
                            case 'menunggu':
                                $status_class = 'status-menunggu';
                                $status_icon = 'bi-hourglass-split';
                                $status_text = 'Menunggu';
                                break;

                            case 'terjadwal':
                                $status_class = 'status-terjadwal';
                                $status_icon = 'bi-calendar-check';
                                $status_text = 'Terjadwal';
                                break;

                            case 'selesai':
                                $status_class = 'status-selesai';
                                $status_icon = 'bi-check-circle';
                                $status_text = 'Selesai';
                                break;

                            case 'batal':
                                $status_class = 'status-batal';
                                $status_icon = 'bi-x-circle';
                                $status_text = 'Batal';
                                break;

                            default:
                                $status_class = 'status-lain';
                                $status_icon = 'bi-question-circle';
                                $status_text = !empty($row['status'])
                                    ? ucfirst($row['status'])
                                    : 'Belum Ada';
                                break;
                        }

                        /*
                         * |--------------------------------------------------------------------------
                         * | FORMAT TANGGAL
                         * |--------------------------------------------------------------------------
                         */

                        $tanggal = !empty($row['tanggal'])
                            ? date('d/m/Y', strtotime($row['tanggal']))
                            : '-';

                        /*
                         * |--------------------------------------------------------------------------
                         * | AVATAR
                         * |--------------------------------------------------------------------------
                         */

                        $initial = !empty($row['nama_siswa'])
                            ? strtoupper(substr($row['nama_siswa'], 0, 1))
                            : '?';
                        ?>

                        <tr>

                            <!-- NO -->

                            <td>
                                <span class="nomor-konseling">
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
                                            <?= htmlspecialchars($nama_siswa); ?>
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
                                        <?= htmlspecialchars($row['kelas']); ?>
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
                                        <?= htmlspecialchars($nama_guru); ?>
                                    </div>

                                    <div class="guru-nip">
                                        NIP:
                                        <?= htmlspecialchars($nip); ?>
                                    </div>

                                </div>

                            </td>


                            <!-- TANGGAL -->

                            <td>

                                <div class="tanggal-konseling">

                                    <i class="bi bi-calendar3"></i>

                                    <?= htmlspecialchars($tanggal); ?>

                                </div>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span class="status-badge <?= $status_class; ?>">

                                    <i class="bi <?= $status_icon; ?>"></i>

                                    <?= htmlspecialchars($status_text); ?>

                                </span>

                            </td>


                            <!-- MASALAH -->

                            <td>

                                <div
                                    class="masalah-konseling"
                                    title="<?= htmlspecialchars($row['masalah'] ?? '-'); ?>"
                                >

                                    <?=
                                    !empty($row['masalah'])
                                        ? htmlspecialchars($row['masalah'])
                                        : '-';
                                    ?>

                                </div>

                            </td>


                            <!-- AKSI -->

                            <td>

                                <a
                                    href="konseling_detail.php?id=<?= (int) $row['id_konseling']; ?>"
                                    class="btn-aksi btn-detail"
                                    title="Detail Konseling"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="8" class="data-kosong">

                            <div class="data-kosong-icon">
                                <i class="bi bi-chat-square-text"></i>
                            </div>

                            <h5>
                                Belum Ada Data Konseling
                            </h5>

                            <p>
                                Belum ada siswa yang mengajukan konseling.
                            </p>

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php
require_once __DIR__ . '/layout/footer.php';
?>