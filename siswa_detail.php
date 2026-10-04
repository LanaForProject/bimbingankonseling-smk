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

$title = 'Detail Siswa';

/*
 * |--------------------------------------------------------------------------
 * | AMBIL ID SISWA
 * |--------------------------------------------------------------------------
 */

$id_siswa = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id_siswa <= 0) {
    header('Location: siswa.php');
    exit;
}

/*
 * |--------------------------------------------------------------------------
 * | AMBIL DATA SISWA
 * |--------------------------------------------------------------------------
 * |
 * | FOTO SEKARANG IKUT DIAMBIL DARI DATABASE
 * |
 * |--------------------------------------------------------------------------
 */

$stmt = mysqli_prepare(
    $conn,
    'SELECT
        id_siswa,
        nis,
        nama,
        kelas,
        jurusan,
        foto
     FROM siswa
     WHERE id_siswa = ?
     LIMIT 1'
);

mysqli_stmt_bind_param(
    $stmt,
    'i',
    $id_siswa
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$siswa = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$siswa) {
    header('Location: siswa.php?error=notfound');
    exit;
}

/*
 * |--------------------------------------------------------------------------
 * | CEK FOTO SISWA
 * |--------------------------------------------------------------------------
 */

$foto = trim($siswa['foto'] ?? '');

$foto_path = __DIR__ . '/uploads/siswa/' . $foto;

$foto_ada = (
    $foto !== '' &&
    file_exists($foto_path)
);

/*
 * |--------------------------------------------------------------------------
 * | AMBIL RIWAYAT KONSELING
 * |--------------------------------------------------------------------------
 * |
 * | LEFT JOIN digunakan agar data tetap aman apabila guru BK
 * | sudah tidak ada di tabel guru_bk.
 * |
 * |--------------------------------------------------------------------------
 */

$konseling_query = mysqli_prepare(
    $conn,
    'SELECT
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
     ORDER BY k.tanggal DESC, k.id_konseling DESC'
);

mysqli_stmt_bind_param(
    $konseling_query,
    'i',
    $id_siswa
);

mysqli_stmt_execute($konseling_query);

$riwayat = mysqli_stmt_get_result($konseling_query);

$total_konseling = mysqli_num_rows($riwayat);

/*
 * |--------------------------------------------------------------------------
 * | HEADER
 * |--------------------------------------------------------------------------
 */

require_once __DIR__ . '/layout/header.php';

?>

<style>

/* =========================================================
   PAGE
========================================================= */

.detail-siswa-page {
    width: 100%;
    padding-bottom: 30px;
}


/* =========================================================
   HEADER
========================================================= */

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 15px;
    margin-bottom: 24px;
}

.detail-header-left {
    min-width: 0;
}

.detail-header-title {
    display: flex;
    align-items: center;
    gap: 10px;

    margin: 0;

    color: var(--text-main);

    font-size: 28px;
    font-weight: 700;
    line-height: 1.2;
}

.detail-header-title i {
    color: #0d6efd;
    font-size: 27px;
}

.detail-header-subtitle {
    margin: 5px 0 0;

    color: var(--text-muted);

    font-size: 14px;
}


/* =========================================================
   BUTTON KEMBALI
========================================================= */

.btn-kembali-detail {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 8px;

    padding: 10px 17px;

    border: none;
    border-radius: 10px;

    background: #6c757d;
    color: #fff;

    text-decoration: none;

    font-size: 13px;
    font-weight: 600;

    transition: .2s ease;
}

.btn-kembali-detail:hover {
    background: #5c636a;
    color: #fff;

    transform: translateY(-1px);
}


/* =========================================================
   PROFILE CARD
========================================================= */

.profile-card {
    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    overflow: hidden;

    box-shadow: 0 4px 18px rgba(0, 0, 0, .04);

    margin-bottom: 22px;
}


/* =========================================================
   PROFILE HEADER
========================================================= */

.profile-card-header {
    padding: 22px;

    display: flex;

    align-items: center;

    gap: 16px;

    border-bottom: 1px solid var(--border-color);
}


/* =========================================================
   FOTO PROFILE
========================================================= */

.profile-avatar {
    width: 76px;
    height: 76px;

    min-width: 76px;
    min-height: 76px;

    flex-shrink: 0;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: rgba(13, 110, 253, .12);

    color: #0d6efd;

    font-size: 30px;

    overflow: hidden;

    border: 2px solid rgba(13, 110, 253, .15);
}

.profile-avatar img {
    width: 100%;
    height: 100%;

    object-fit: cover;

    display: block;
}

.profile-avatar i {
    color: #0d6efd;

    font-size: 30px;
}


/* =========================================================
   PROFILE MAIN
========================================================= */

.profile-main {
    min-width: 0;
}

.profile-name {
    margin: 0;

    color: var(--text-main);

    font-size: 21px;
    font-weight: 700;
}

.profile-nis {
    margin-top: 4px;

    color: var(--text-muted);

    font-size: 13px;
}


/* =========================================================
   INFORMASI SISWA
========================================================= */

.profile-info {
    padding: 22px;

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 16px;
}

.info-item {
    padding: 15px 16px;

    border: 1px solid var(--border-color);

    border-radius: 12px;

    background: var(--bg-body);
}

.info-label {
    display: block;

    margin-bottom: 5px;

    color: var(--text-muted);

    font-size: 12px;

    font-weight: 500;
}

.info-value {
    color: var(--text-main);

    font-size: 14px;

    font-weight: 600;
}


/* =========================================================
   RIWAYAT CARD
========================================================= */

.riwayat-siswa-card {
    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    overflow: hidden;

    box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
}

.riwayat-siswa-header {
    padding: 18px 22px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    border-bottom: 1px solid var(--border-color);
}

.riwayat-siswa-title {
    display: flex;

    align-items: center;

    gap: 10px;
}

.riwayat-siswa-title > div {
    width: 36px;
    height: 36px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 9px;

    background: rgba(13, 110, 253, .12);

    color: #0d6efd;
}

.riwayat-siswa-title h5 {
    margin: 0;

    color: var(--text-main);

    font-size: 15px;

    font-weight: 700;
}

.total-konseling {
    padding: 6px 11px;

    border-radius: 8px;

    background: rgba(13, 110, 253, .12);

    color: #0d6efd;

    font-size: 12px;

    font-weight: 600;
}


/* =========================================================
   TABLE
========================================================= */

.detail-table-wrapper {
    width: 100%;

    overflow-x: auto;
}

.detail-table {
    width: 100%;

    margin: 0;

    border-collapse: collapse;

    color: var(--text-main);
}

.detail-table thead th {
    padding: 14px 18px;

    background: var(--bg-body);

    color: var(--text-main);

    border-bottom: 1px solid var(--border-color);

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;

    text-align: left;
}

.detail-table tbody td {
    padding: 15px 18px;

    color: var(--text-main);

    border-bottom: 1px solid var(--border-color);

    font-size: 13px;

    vertical-align: middle;
}

.detail-table tbody tr:last-child td {
    border-bottom: none;
}

.detail-table tbody tr {
    transition: .2s ease;
}

.detail-table tbody tr:hover {
    background: rgba(13, 110, 253, .04);
}


/* =========================================================
   MASALAH
========================================================= */

.masalah-detail {
    max-width: 280px;

    color: var(--text-main);

    line-height: 1.5;
}


/* =========================================================
   SOLUSI
========================================================= */

.solusi-detail {
    max-width: 280px;

    color: var(--text-muted);

    line-height: 1.5;
}


/* =========================================================
   GURU
========================================================= */

.guru-detail {
    color: var(--text-main);

    font-weight: 600;
}


/* =========================================================
   STATUS
========================================================= */

.status-detail {
    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 10px;

    border-radius: 8px;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;
}

.status-selesai {
    background: rgba(25, 135, 84, .1);

    color: #198754;
}

.status-batal {
    background: rgba(220, 53, 69, .1);

    color: #dc3545;
}

.status-terjadwal {
    background: rgba(13, 110, 253, .1);

    color: #0d6efd;
}

.status-lain {
    background: rgba(108, 117, 125, .1);

    color: #6c757d;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.detail-empty {
    padding: 55px 20px;

    text-align: center;

    color: var(--text-muted);
}

.detail-empty-icon {
    width: 60px;
    height: 60px;

    margin: 0 auto 15px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: rgba(13, 110, 253, .12);

    color: #0d6efd;

    font-size: 25px;
}

.detail-empty h5 {
    margin-bottom: 6px;

    color: var(--text-main);

    font-size: 15px;

    font-weight: 700;
}

.detail-empty p {
    margin: 0;

    font-size: 13px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .detail-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .btn-kembali-detail {
        width: 100%;
    }

    .profile-info {
        grid-template-columns: 1fr;

        padding: 16px;
    }

    .profile-card-header {
        padding: 18px;
    }

    .riwayat-siswa-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .riwayat-siswa-header .total-konseling {
        align-self: flex-start;
    }

}


@media (max-width: 480px) {

    .detail-header-title {
        font-size: 24px;
    }

    .detail-header-subtitle {
        font-size: 13px;
    }

    .profile-card-header {
        align-items: flex-start;
    }

    .profile-avatar {
        width: 64px;
        height: 64px;

        min-width: 64px;
        min-height: 64px;
    }

    .profile-name {
        font-size: 18px;
    }

}

</style>


<div class="detail-siswa-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="detail-header">

        <div class="detail-header-left">

            <h1 class="detail-header-title">

                <i class="bi bi-person-vcard-fill"></i>

                Detail Siswa

            </h1>

            <p class="detail-header-subtitle">

                Informasi lengkap siswa dan riwayat bimbingan konseling.

            </p>

        </div>


        <a
            href="siswa.php"
            class="btn-kembali-detail"
        >

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

    </div>


    <!-- =====================================================
         PROFILE SISWA
    ====================================================== -->

    <div class="profile-card">

        <div class="profile-card-header">


            <!-- =================================================
                 FOTO SISWA
            ================================================= -->

            <div class="profile-avatar">

                <?php if ($foto_ada): ?>

                    <img
                        src="uploads/siswa/<?= htmlspecialchars($foto); ?>"
                        alt="Foto <?= htmlspecialchars($siswa['nama']); ?>"
                    >

                <?php else: ?>

                    <i class="bi bi-person-fill"></i>

                <?php endif; ?>

            </div>


            <!-- =================================================
                 NAMA + NIS
            ================================================= -->

            <div class="profile-main">

                <h2 class="profile-name">

                    <?= htmlspecialchars(
                        $siswa['nama'] ?? '-'
                    ); ?>

                </h2>

                <div class="profile-nis">

                    NIS:

                    <?= htmlspecialchars(
                        $siswa['nis'] ?? '-'
                    ); ?>

                </div>

            </div>

        </div>


        <!-- =================================================
             INFORMASI SISWA
        ================================================= -->

        <div class="profile-info">


            <!-- NIS -->

            <div class="info-item">

                <span class="info-label">
                    NIS
                </span>

                <div class="info-value">

                    <?= htmlspecialchars(
                        $siswa['nis'] ?? '-'
                    ); ?>

                </div>

            </div>


            <!-- NAMA -->

            <div class="info-item">

                <span class="info-label">
                    Nama Siswa
                </span>

                <div class="info-value">

                    <?= htmlspecialchars(
                        $siswa['nama'] ?? '-'
                    ); ?>

                </div>

            </div>


            <!-- KELAS -->

            <div class="info-item">

                <span class="info-label">
                    Kelas
                </span>

                <div class="info-value">

                    <?= htmlspecialchars(
                        $siswa['kelas'] ?? '-'
                    ); ?>

                </div>

            </div>


            <!-- JURUSAN -->

            <div class="info-item">

                <span class="info-label">
                    Jurusan
                </span>

                <div class="info-value">

                    <?= htmlspecialchars(
                        $siswa['jurusan'] ?? '-'
                    ); ?>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         RIWAYAT KONSELING
    ====================================================== -->

    <div class="riwayat-siswa-card">

        <div class="riwayat-siswa-header">

            <div class="riwayat-siswa-title">

                <div>

                    <i class="bi bi-clock-history"></i>

                </div>

                <h5>
                    Riwayat Konseling
                </h5>

            </div>


            <div class="total-konseling">

                <?= $total_konseling; ?> Data

            </div>

        </div>


        <?php if ($total_konseling > 0): ?>

            <div class="detail-table-wrapper">

                <table class="detail-table">

                    <thead>

                        <tr>

                            <th style="width: 60px;">
                                No
                            </th>

                            <th style="width: 120px;">
                                Tanggal
                            </th>

                            <th>
                                Masalah
                            </th>

                            <th>
                                Solusi
                            </th>

                            <th>
                                Guru BK
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $no = 1;

                        while ($row = mysqli_fetch_assoc($riwayat)):
                            $status = strtolower(
                                trim($row['status'] ?? '')
                            );

                            switch ($status) {
                                case 'selesai':
                                    $status_class =
                                        'status-selesai';

                                    $status_icon =
                                        'bi-check-circle-fill';

                                    break;

                                case 'batal':
                                    $status_class =
                                        'status-batal';

                                    $status_icon =
                                        'bi-x-circle-fill';

                                    break;

                                case 'terjadwal':
                                    $status_class =
                                        'status-terjadwal';

                                    $status_icon =
                                        'bi-calendar-check-fill';

                                    break;

                                default:
                                    $status_class =
                                        'status-lain';

                                    $status_icon =
                                        'bi-info-circle-fill';

                                    break;
                            }

                            ?>

                            <tr>


                                <!-- NO -->

                                <td>

                                    <?= $no++; ?>

                                </td>


                                <!-- TANGGAL -->

                                <td>

                                    <?php

                                    if (!empty($row['tanggal'])) {
                                        echo date(
                                            'd-m-Y',
                                            strtotime(
                                                $row['tanggal']
                                            )
                                        );
                                    } else {
                                        echo '-';
                                    }

                                    ?>

                                </td>


                                <!-- MASALAH -->

                                <td>

                                    <div class="masalah-detail">

                                        <?= nl2br(
                                            htmlspecialchars(
                                                $row['masalah'] ?? '-'
                                            )
                                        ); ?>

                                    </div>

                                </td>


                                <!-- SOLUSI -->

                                <td>

                                    <div class="solusi-detail">

                                        <?=
                                        !empty($row['solusi'])
                                            ? nl2br(
                                                htmlspecialchars(
                                                    $row['solusi']
                                                )
                                            )
                                            : '-';
                                        ?>

                                    </div>

                                </td>


                                <!-- GURU -->

                                <td>

                                    <?php if (!empty($row['nama_guru'])): ?>

                                        <span class="guru-detail">

                                            <?= htmlspecialchars(
                                                $row['nama_guru']
                                            ); ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Guru Sudah Dihapus

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="status-detail <?= $status_class; ?>"
                                    >

                                        <i
                                            class="bi <?= $status_icon; ?>"
                                        ></i>

                                        <?= htmlspecialchars(
                                            ucfirst(
                                                $status
                                                    ?: 'Tidak diketahui'
                                            )
                                        ); ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="detail-empty">

                <div class="detail-empty-icon">

                    <i class="bi bi-clock-history"></i>

                </div>

                <h5>
                    Belum Ada Riwayat Konseling
                </h5>

                <p>
                    Siswa ini belum memiliki data bimbingan dan konseling.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>


<?php

require_once __DIR__ . '/layout/footer.php';

?>