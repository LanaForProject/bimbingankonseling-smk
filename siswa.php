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

$admin_nama = $_SESSION['admin_nama'] ?? 'Administrator';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

/*
 * |--------------------------------------------------------------------------
 * | AMBIL DATA SISWA
 * |--------------------------------------------------------------------------
 */

$query = mysqli_query($conn, '
    SELECT
        id_siswa,
        nis,
        nama,
        kelas,
        jurusan,
        foto
    FROM siswa
    ORDER BY id_siswa DESC
');

if (!$query) {
    die('Query error: ' . mysqli_error($conn));
}

/*
 * |--------------------------------------------------------------------------
 * | HEADER
 * |--------------------------------------------------------------------------
 */

require_once __DIR__ . '/layout/header.php';

?>

<style>

/* =====================================================
   DATA SISWA
   STYLE SAMA DENGAN DATA GURU BK
===================================================== */

.siswa-page {
    padding-bottom: 30px;
}


/* =====================================================
   HEADER HALAMAN
===================================================== */

.siswa-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 15px;
    margin-bottom: 24px;
}

.siswa-title {
    margin: 0;

    font-size: 28px;
    font-weight: 700;

    color: var(--text-main);
}

.siswa-subtitle {
    margin: 5px 0 0;

    color: var(--text-muted);

    font-size: 14px;
}


/* =====================================================
   TOMBOL TAMBAH
===================================================== */

.btn-tambah-siswa {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 8px;

    padding: 10px 17px;

    border-radius: 10px;
    border: none;

    background: #0d6efd;
    color: #fff;

    text-decoration: none;

    font-weight: 600;

    transition: 0.2s ease;
}

.btn-tambah-siswa:hover {
    background: #0b5ed7;
    color: #fff;

    transform: translateY(-1px);
}


/* =====================================================
   ALERT
===================================================== */

.alert {
    border-radius: 12px;
    border: none;

    margin-bottom: 20px;
}


/* =====================================================
   CARD UTAMA
===================================================== */

.siswa-card {
    background: var(--bg-card) !important;

    border: 1px solid var(--border-color) !important;

    border-radius: 16px;

    overflow: hidden;

    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
}


/* =====================================================
   HEADER CARD
===================================================== */

.siswa-card-header {
    padding: 18px 22px;

    background: #0c1839 !important;

    border-bottom: 1px solid #1d315e !important;

    display: flex;

    justify-content: space-between;
    align-items: center;
}

.siswa-card-header h5 {
    margin: 0;

    color: #ffffff !important;

    font-weight: 700;
}

.siswa-card-header h5 i {
    color: #ffffff !important;
}

.siswa-card-header span {
    color: #6f86b5 !important;

    font-size: 13px;
}


/* =====================================================
   TABLE
===================================================== */

.siswa-table {
    width: 100%;

    margin: 0 !important;

    background: #0b1735 !important;

    color: #ffffff !important;

    vertical-align: middle;

    --bs-table-bg: #0b1735 !important;
    --bs-table-color: #ffffff !important;
    --bs-table-border-color: #1d315e !important;
    --bs-table-hover-bg: rgba(13, 110, 253, 0.04) !important;
    --bs-table-hover-color: #ffffff !important;
}


/* =====================================================
   HEADER TABEL
===================================================== */

.siswa-table thead {
    background: #11204a !important;
}

.siswa-table thead tr {
    background: #11204a !important;
}

.siswa-table thead th {
    padding: 15px 18px;

    background: #11204a !important;

    color: #ffffff !important;

    border-bottom: 1px solid #1d315e !important;

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;
}


/* =====================================================
   BODY TABEL
===================================================== */

.siswa-table tbody {
    background: #0b1735 !important;
}

.siswa-table tbody tr {
    background: #0b1735 !important;

    color: #ffffff !important;

    transition: 0.2s ease;
}

.siswa-table tbody td {
    padding: 15px 18px;

    background: #0b1735 !important;

    color: #ffffff !important;

    border-bottom: 1px solid #1d315e !important;

    vertical-align: middle;
}

.siswa-table tbody tr:last-child td {
    border-bottom: none !important;
}

.siswa-table tbody tr:hover td {
    background: rgba(13, 110, 253, 0.04) !important;

    color: #ffffff !important;
}


/* =====================================================
   NOMOR
===================================================== */

.siswa-nomor {
    color: #ffffff !important;
}


/* =====================================================
   SISWA
===================================================== */

.siswa-name-wrapper {
    display: flex;

    align-items: center;

    gap: 12px;
}


/* =====================================================
   FOTO SISWA
===================================================== */

.siswa-avatar {
    width: 40px;
    height: 40px;

    min-width: 40px;
    min-height: 40px;

    border-radius: 50%;

    background: rgba(13, 110, 253, 0.12);

    color: #0d6efd;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    font-weight: 700;

    flex-shrink: 0;

    overflow: hidden;

    border: 1px solid rgba(13, 110, 253, 0.15);
}

.siswa-avatar img {
    width: 100%;
    height: 100%;

    object-fit: cover;

    display: block;
}

.siswa-avatar i {
    font-size: 20px;

    color: #0d6efd;
}


/* =====================================================
   NAMA SISWA
===================================================== */

.siswa-name {
    color: #ffffff !important;

    font-weight: 600;
}

.siswa-nis {
    color: #6f86b5 !important;

    font-size: 12px;

    margin-top: 2px;
}


/* =====================================================
   KELAS
===================================================== */

.siswa-kelas {
    display: inline-flex;

    align-items: center;

    padding: 5px 9px;

    border-radius: 8px;

    background: rgba(13, 110, 253, 0.10);

    color: #0d6efd !important;

    font-size: 12px;

    font-weight: 600;
}


/* =====================================================
   JURUSAN
===================================================== */

.siswa-jurusan {
    color: #ffffff !important;
}


/* =====================================================
   AKSI
===================================================== */

.siswa-actions {
    display: flex;

    justify-content: flex-end;

    gap: 7px;
}

.btn-action {
    width: 36px;
    height: 36px;

    border-radius: 9px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border: none;

    text-decoration: none;

    transition: 0.2s ease;
}


/* =====================================================
   DETAIL
===================================================== */

.btn-detail {
    background: rgba(13, 110, 253, 0.10);

    color: #0d6efd;
}

.btn-detail:hover {
    background: #0d6efd;

    color: #fff;

    transform: translateY(-1px);
}


/* =====================================================
   EDIT
===================================================== */

.btn-edit {
    background: rgba(13, 110, 253, 0.10);

    color: #0d6efd;
}

.btn-edit:hover {
    background: #0d6efd;

    color: #fff;

    transform: translateY(-1px);
}


/* =====================================================
   HAPUS
===================================================== */

.btn-delete {
    background: rgba(220, 53, 69, 0.10);

    color: #dc3545;
}

.btn-delete:hover {
    background: #dc3545;

    color: #fff;
}


/* =====================================================
   DATA KOSONG
===================================================== */

.empty-siswa {
    text-align: center;

    padding: 55px 20px !important;

    background: #0b1735 !important;

    color: #6f86b5 !important;
}

.empty-siswa i {
    font-size: 45px;

    margin-bottom: 12px;

    opacity: 0.5;

    color: #0d6efd;
}

.empty-siswa p {
    margin: 0;

    color: #6f86b5 !important;
}


/* =====================================================
   LIGHT MODE
===================================================== */

[data-theme="light"] .siswa-card {
    background: var(--bg-card) !important;
}

[data-theme="light"] .siswa-card-header {
    background: var(--bg-card) !important;

    border-bottom-color: var(--border-color) !important;
}

[data-theme="light"] .siswa-card-header h5 {
    color: var(--text-main) !important;
}

[data-theme="light"] .siswa-card-header h5 i {
    color: var(--text-main) !important;
}

[data-theme="light"] .siswa-card-header span {
    color: var(--text-muted) !important;
}

[data-theme="light"] .siswa-table {
    background: var(--bg-card) !important;

    color: var(--text-main) !important;

    --bs-table-bg: var(--bg-card) !important;
    --bs-table-color: var(--text-main) !important;
    --bs-table-border-color: var(--border-color) !important;
}

[data-theme="light"] .siswa-table thead,
[data-theme="light"] .siswa-table thead tr,
[data-theme="light"] .siswa-table thead th {
    background: var(--bg-body) !important;

    color: var(--text-main) !important;

    border-color: var(--border-color) !important;
}

[data-theme="light"] .siswa-table tbody,
[data-theme="light"] .siswa-table tbody tr,
[data-theme="light"] .siswa-table tbody td {
    background: var(--bg-card) !important;

    color: var(--text-main) !important;

    border-color: var(--border-color) !important;
}

[data-theme="light"] .siswa-table tbody tr:hover td {
    background: rgba(13, 110, 253, 0.04) !important;

    color: var(--text-main) !important;
}

[data-theme="light"] .siswa-name {
    color: var(--text-main) !important;
}

[data-theme="light"] .siswa-jurusan {
    color: var(--text-main) !important;
}

[data-theme="light"] .siswa-nis {
    color: var(--text-muted) !important;
}

[data-theme="light"] .siswa-nomor {
    color: var(--text-main) !important;
}

[data-theme="light"] .empty-siswa,
[data-theme="light"] .empty-siswa td {
    background: var(--bg-card) !important;

    color: var(--text-muted) !important;
}

[data-theme="light"] .empty-siswa p {
    color: var(--text-muted) !important;
}


/* =====================================================
   FIX WARNA DATA SISWA
   SAMA DENGAN GURU BK
===================================================== */

.siswa-page .siswa-card {
    background: #0c1630 !important;

    border-color: #1d315e !important;
}

.siswa-page .siswa-card-header {
    background: #0c1630 !important;

    border-bottom: 1px solid #1d315e !important;
}

.siswa-page .siswa-card-header h5 {
    color: #ffffff !important;
}

.siswa-page .siswa-card-header h5 i {
    color: #ffffff !important;
}

.siswa-page .siswa-card-header span {
    color: #6f86b5 !important;
}


/* =====================================================
   TABLE FIX
===================================================== */

.siswa-page .table-responsive {
    background: #0c1630 !important;
}

.siswa-page table.siswa-table {
    background: #0c1630 !important;

    --bs-table-bg: #0c1630 !important;
    --bs-table-color: #ffffff !important;
    --bs-table-border-color: #1d315e !important;
    --bs-table-hover-bg: rgba(13, 110, 253, 0.04) !important;
    --bs-table-hover-color: #ffffff !important;
}


/* =====================================================
   HEADER TABLE
===================================================== */

.siswa-page table.siswa-table thead {
    background: #121e42 !important;
}

.siswa-page table.siswa-table thead tr {
    background: #121e42 !important;
}

.siswa-page table.siswa-table thead th {
    background: #121e42 !important;

    color: #ffffff !important;

    border-color: #1d315e !important;
}


/* =====================================================
   ISI TABLE
===================================================== */

.siswa-page table.siswa-table tbody {
    background: #0c1630 !important;
}

.siswa-page table.siswa-table tbody tr {
    background: #0c1630 !important;
}

.siswa-page table.siswa-table tbody td {
    background: #0c1630 !important;

    color: #ffffff !important;

    border-color: #1d315e !important;
}

.siswa-page table.siswa-table tbody tr:hover td {
    background: rgba(13, 110, 253, 0.04) !important;

    color: #ffffff !important;
}


/* =====================================================
   DATA SISWA
===================================================== */

.siswa-page .siswa-name {
    color: #ffffff !important;
}

.siswa-page .siswa-nis {
    color: #6f86b5 !important;
}

.siswa-page .siswa-jurusan {
    color: #ffffff !important;
}

.siswa-page .siswa-nomor {
    color: #ffffff !important;
}


/* =====================================================
   FOTO SISWA
===================================================== */

.siswa-page .siswa-avatar {
    background: rgba(13, 110, 253, 0.12) !important;

    border: 1px solid rgba(13, 110, 253, 0.15);

    overflow: hidden;
}

.siswa-page .siswa-avatar img {
    width: 100%;
    height: 100%;

    object-fit: cover;

    display: block;
}

.siswa-page .siswa-avatar i {
    color: #0d6efd !important;

    font-size: 20px;
}


/* =====================================================
   KELAS
===================================================== */

.siswa-page .siswa-kelas {
    background: rgba(13, 110, 253, 0.10) !important;

    color: #0d6efd !important;
}


/* =====================================================
   EMPTY
===================================================== */

.siswa-page .empty-siswa,
.siswa-page .empty-siswa td {
    background: #0c1630 !important;

    color: #6f86b5 !important;
}


/* =====================================================
   LIGHT MODE FIX
===================================================== */

[data-theme="light"] .siswa-page .siswa-card,
[data-theme="light"] .siswa-page .siswa-card-header {
    background: var(--bg-card) !important;

    border-color: var(--border-color) !important;
}

[data-theme="light"] .siswa-page table.siswa-table {
    background: var(--bg-card) !important;

    --bs-table-bg: var(--bg-card) !important;
    --bs-table-color: var(--text-main) !important;
    --bs-table-border-color: var(--border-color) !important;
}

[data-theme="light"] .siswa-page table.siswa-table thead,
[data-theme="light"] .siswa-page table.siswa-table thead tr,
[data-theme="light"] .siswa-page table.siswa-table thead th {
    background: var(--bg-body) !important;

    color: var(--text-main) !important;

    border-color: var(--border-color) !important;
}

[data-theme="light"] .siswa-page table.siswa-table tbody,
[data-theme="light"] .siswa-page table.siswa-table tbody tr,
[data-theme="light"] .siswa-page table.siswa-table tbody td {
    background: var(--bg-card) !important;

    color: var(--text-main) !important;

    border-color: var(--border-color) !important;
}

[data-theme="light"] .siswa-page .siswa-name,
[data-theme="light"] .siswa-page .siswa-jurusan,
[data-theme="light"] .siswa-page .siswa-nomor {
    color: var(--text-main) !important;
}

[data-theme="light"] .siswa-page .siswa-nis {
    color: var(--text-muted) !important;
}

[data-theme="light"] .siswa-page .empty-siswa,
[data-theme="light"] .siswa-page .empty-siswa td {
    background: var(--bg-card) !important;

    color: var(--text-muted) !important;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 768px) {

    .siswa-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .btn-tambah-siswa {
        width: 100%;

        justify-content: center;
    }

    .siswa-card-header {
        align-items: flex-start;

        flex-direction: column;

        gap: 5px;
    }

    .siswa-table {
        min-width: 850px;
    }

}

</style>


<div class="siswa-page">


    <!-- =================================================
         HEADER
    ================================================= -->

    <div class="siswa-header">

        <div>

            <h1 class="siswa-title">
                Data Siswa
            </h1>

            <p class="siswa-subtitle">
                Kelola data siswa yang terdaftar dalam sistem BK Online
            </p>

        </div>


        <a
            href="siswa_tambah.php"
            class="btn-tambah-siswa"
        >

            <i class="bi bi-person-plus-fill"></i>

            Tambah Siswa

        </a>

    </div>


    <!-- =================================================
         ALERT
    ================================================= -->

    <?php if ($success === 'tambah'): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-check-circle-fill me-2"></i>

            Data siswa berhasil ditambahkan.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>


    <?php elseif ($success === 'edit'): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-check-circle-fill me-2"></i>

            Data siswa berhasil diperbarui.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>


    <?php elseif ($success === 'hapus'): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-check-circle-fill me-2"></i>

            Data siswa berhasil dihapus.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>


    <?php elseif ($error === 'notfound'): ?>

        <div
            class="alert alert-warning alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            Data siswa tidak ditemukan.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>


    <?php elseif ($error === 'hapus'): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-x-circle-fill me-2"></i>

            Data siswa gagal dihapus.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- =================================================
         CARD DATA SISWA
    ================================================= -->

    <div class="siswa-card">


        <!-- =================================================
             CARD HEADER
        ================================================= -->

        <div class="siswa-card-header">

            <div>

                <h5>

                    <i class="bi bi-people-fill me-2"></i>

                    Daftar Siswa

                </h5>

            </div>


            <span>

                <?= mysqli_num_rows($query); ?>

                Siswa Terdaftar

            </span>

        </div>


        <!-- =================================================
             TABLE
        ================================================= -->

        <div class="table-responsive">

            <table class="table siswa-table">


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
                            Jurusan
                        </th>

                        <th width="150" class="text-end">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php

                        $no = 1;

                        while ($row = mysqli_fetch_assoc($query)):
                            $nama = $row['nama'] ?? '-';

                            $foto = trim($row['foto'] ?? '');

                            /*
                             * |--------------------------------------------------------------------------
                             * | CEK FOTO SISWA
                             * |--------------------------------------------------------------------------
                             */

                            $foto_path = __DIR__ . '/uploads/siswa/' . $foto;

                            $foto_ada = (
                                $foto !== '' &&
                                file_exists($foto_path)
                            );

                            ?>

                            <tr>


                                <!-- =================================================
                                     NOMOR
                                ================================================= -->

                                <td class="siswa-nomor">

                                    <?= $no++; ?>

                                </td>


                                <!-- =================================================
                                     SISWA
                                ================================================= -->

                                <td>

                                    <div class="siswa-name-wrapper">


                                        <!-- FOTO SISWA -->

                                        <div class="siswa-avatar">

                                            <?php if ($foto_ada): ?>

                                                <img
                                                    src="uploads/siswa/<?= htmlspecialchars($foto); ?>"
                                                    alt="Foto <?= htmlspecialchars($nama); ?>"
                                                    loading="lazy"
                                                >

                                            <?php else: ?>

                                                <i class="bi bi-person-fill"></i>

                                            <?php endif; ?>

                                        </div>


                                        <!-- NAMA + NIS -->

                                        <div>

                                            <div class="siswa-name">

                                                <?= htmlspecialchars($nama); ?>

                                            </div>


                                            <div class="siswa-nis">

                                                NIS:

                                                <?= htmlspecialchars(
                                                    $row['nis'] ?? '-'
                                                ); ?>

                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- =================================================
                                     KELAS
                                ================================================= -->

                                <td>

                                    <span class="siswa-kelas">

                                        <?= htmlspecialchars(
                                            $row['kelas'] ?? '-'
                                        ); ?>

                                    </span>

                                </td>


                                <!-- =================================================
                                     JURUSAN
                                ================================================= -->

                                <td class="siswa-jurusan">

                                    <?= htmlspecialchars(
                                        $row['jurusan'] ?? '-'
                                    ); ?>

                                </td>


                                <!-- =================================================
                                     AKSI
                                ================================================= -->

                                <td>

                                    <div class="siswa-actions">


                                        <!-- DETAIL -->

                                        <a
                                            href="siswa_detail.php?id=<?= (int) $row['id_siswa']; ?>"
                                            class="btn-action btn-detail"
                                            title="Detail Siswa"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>


                                        <!-- EDIT -->

                                        <a
                                            href="siswa_edit.php?id=<?= (int) $row['id_siswa']; ?>"
                                            class="btn-action btn-edit"
                                            title="Edit Siswa"
                                        >

                                            <i class="bi bi-pencil-square"></i>

                                        </a>


                                        <!-- HAPUS -->

                                        <a
                                            href="siswa_hapus.php?id=<?= (int) $row['id_siswa']; ?>"
                                            class="btn-action btn-delete"
                                            title="Hapus Siswa"
                                            onclick="return confirm('Yakin ingin menghapus data siswa ini?');"
                                        >

                                            <i class="bi bi-trash3"></i>

                                        </a>

                                    </div>

                                </td>

                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="5"
                                class="empty-siswa"
                            >

                                <i class="bi bi-person-x d-block"></i>

                                <p>
                                    Belum ada data siswa.
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