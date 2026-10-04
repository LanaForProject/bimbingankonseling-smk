<?php

session_start();

require_once __DIR__ . "/config/database.php";

/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_nama = $_SESSION['admin_nama'] ?? 'Administrator';

$akun = [];
$error = '';

/*
|--------------------------------------------------------------------------
| AMBIL SEMUA AKUN
|--------------------------------------------------------------------------
*/

try {

    /* =========================================================
       ADMIN
    ========================================================= */

    $query_admin = "
        SELECT
            id_admin AS id,
            nama,
            username,
            NULL AS foto,
            'Admin' AS role,
            'admin' AS role_key
        FROM admin
    ";

    $result_admin = $conn->query($query_admin);

    if ($result_admin) {

        while ($row = $result_admin->fetch_assoc()) {
            $akun[] = $row;
        }
    }


    /* =========================================================
       GURU BK
    ========================================================= */

    $query_guru = "
        SELECT
            id_guru AS id,
            nama,
            nip AS username,
            foto,
            'Guru BK' AS role,
            'guru' AS role_key
        FROM guru_bk
    ";

    $result_guru = $conn->query($query_guru);

    if ($result_guru) {

        while ($row = $result_guru->fetch_assoc()) {
            $akun[] = $row;
        }
    }


    /* =========================================================
       SISWA
    ========================================================= */

    $query_siswa = "
        SELECT
            id_siswa AS id,
            nama,
            nis AS username,
            foto,
            'Siswa' AS role,
            'siswa' AS role_key
        FROM siswa
    ";

    $result_siswa = $conn->query($query_siswa);

    if ($result_siswa) {

        while ($row = $result_siswa->fetch_assoc()) {
            $akun[] = $row;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | URUTKAN ROLE
    |--------------------------------------------------------------------------
    */

    usort($akun, function ($a, $b) {

        $urutan = [
            'admin' => 1,
            'guru' => 2,
            'siswa' => 3
        ];

        return ($urutan[$a['role_key']] ?? 99)
            <=> ($urutan[$b['role_key']] ?? 99);
    });

} catch (Throwable $e) {

    $error = "Terjadi kesalahan saat mengambil data akun.";

}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/layout/header.php";

?>

<style>
    /* =========================================================
   AKUN PAGE
========================================================= */

    .akun-page {
        padding-bottom: 30px;
    }


    /* =========================================================
   HEADER
========================================================= */

    .akun-header {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 20px;

        margin-bottom: 24px;
    }

    .akun-title {
        margin: 0;

        font-size: 28px;
        font-weight: 700;

        color: var(--text-main);
    }

    .akun-subtitle {
        margin: 4px 0 0;

        font-size: 14px;

        color: var(--text-muted);
    }


    /* =========================================================
   CARD
========================================================= */

    .akun-card {
        background: var(--bg-card);

        border: 1px solid var(--border-color);

        border-radius: 16px;

        box-shadow: 0 4px 18px rgba(0, 0, 0, .04);

        overflow: hidden;
    }


    /* =========================================================
   CARD HEADER
========================================================= */

    .akun-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 15px;

        padding: 18px 22px;

        border-bottom: 1px solid var(--border-color);
    }

    .akun-card-title {
        display: flex;
        align-items: center;

        gap: 10px;

        margin: 0;

        font-size: 16px;
        font-weight: 700;

        color: var(--text-main);
    }

    .akun-card-title i {
        color: #0d6efd;
    }


    /* =========================================================
   TABLE
========================================================= */

    .akun-table-wrapper {
        width: 100%;

        overflow-x: auto;
    }

    .akun-table {
        width: 100%;

        margin: 0;

        border-collapse: collapse;

        color: var(--text-main);
    }

    .akun-table thead th {
        padding: 15px 18px;

        background: var(--bg-body);

        color: var(--text-main);

        border-bottom: 1px solid var(--border-color);

        font-size: 13px;

        font-weight: 700;

        white-space: nowrap;
    }

    .akun-table tbody td {
        padding: 14px 18px;

        color: var(--text-main);

        border-bottom: 1px solid var(--border-color);

        vertical-align: middle;

        font-size: 14px;
    }

    .akun-table tbody tr:last-child td {
        border-bottom: none;
    }

    .akun-table tbody tr {
        transition: background .2s ease;
    }

    .akun-table tbody tr:hover {
        background: var(--bg-body);
    }


    /* =========================================================
   NOMOR
========================================================= */

    .nomor-akun {
        width: 60px;

        color: var(--text-muted);

        font-weight: 600;
    }


    /* =========================================================
   NAMA AKUN
========================================================= */

    .nama-wrapper {
        display: flex;
        align-items: center;

        gap: 12px;

        min-width: 230px;
    }

    .nama-avatar {
        width: 44px;
        height: 44px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;

        border-radius: 50%;

        background: rgba(13, 110, 253, .10);

        color: #0d6efd;

        border: 2px solid rgba(13, 110, 253, .12);

        font-size: 19px;
    }

    .nama-avatar img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;
    }

    .nama-info {
        min-width: 0;
    }

    .nama-user {
        display: block;

        color: var(--text-main);

        font-size: 14px;

        font-weight: 700;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }

    .username-user {
        display: block;

        margin-top: 2px;

        color: var(--text-muted);

        font-size: 12px;
    }


    /* =========================================================
   USERNAME
========================================================= */

    .username-cell {
        font-weight: 600;

        color: var(--text-main);

        white-space: nowrap;
    }


    /* =========================================================
   ROLE BADGE
========================================================= */

    .role-badge {
        display: inline-flex;
        align-items: center;

        gap: 6px;

        padding: 7px 11px;

        border-radius: 8px;

        font-size: 12px;

        font-weight: 600;

        white-space: nowrap;
    }

    .role-admin {
        background: rgba(111, 66, 193, .12);

        color: #6f42c1;
    }

    .role-guru {
        background: rgba(25, 135, 84, .12);

        color: #198754;
    }

    .role-siswa {
        background: rgba(13, 110, 253, .12);

        color: #0d6efd;
    }


    /* =========================================================
   PASSWORD
========================================================= */

    .password-status {
        display: inline-flex;
        align-items: center;

        gap: 7px;

        color: #198754;

        font-size: 13px;

        font-weight: 600;

        white-space: nowrap;
    }

    .password-status i {
        font-size: 15px;
    }


    /* =========================================================
   ACTION
========================================================= */

    .aksi-akun {
        display: flex;
        align-items: center;

        gap: 7px;
    }

    .btn-aksi {
        width: 36px;
        height: 36px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border: none;

        border-radius: 9px;

        text-decoration: none;

        font-size: 15px;

        transition: .2s ease;
    }

    .btn-detail {
        background: rgba(13, 110, 253, .10);

        color: #0d6efd;
    }

    .btn-detail:hover {
        background: #0d6efd;

        color: #fff;

        transform: translateY(-1px);
    }


    /* =========================================================
   EMPTY
========================================================= */

    .akun-empty {
        padding: 55px 20px;

        text-align: center;

        color: var(--text-muted);
    }

    .akun-empty i {
        display: block;

        margin-bottom: 12px;

        font-size: 42px;

        opacity: .5;
    }

    .akun-empty p {
        margin: 0;

        font-size: 14px;
    }


    /* =========================================================
   ALERT
========================================================= */

    .akun-alert {
        margin-bottom: 20px;

        border-radius: 12px;
    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 768px) {

        .akun-header {
            align-items: flex-start;
        }

        .akun-title {
            font-size: 24px;
        }

        .akun-card-header {
            padding: 16px 18px;
        }

        .akun-table thead th,
        .akun-table tbody td {
            padding: 13px 14px;
        }

    }
</style>


<div class="akun-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="akun-header">

        <div>

            <h1 class="akun-title">
                Akun
            </h1>

            <p class="akun-subtitle">
                Kelola dan lihat informasi seluruh akun pengguna BK
            </p>

        </div>

    </div>


    <!-- =====================================================
         ERROR
    ====================================================== -->

    <?php if ($error): ?>

        <div class="alert alert-danger akun-alert">

            <i class="bi bi-exclamation-triangle me-2"></i>

            <?= htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         CARD
    ====================================================== -->

    <div class="akun-card">


        <!-- CARD HEADER -->

        <div class="akun-card-header">

            <h2 class="akun-card-title">

                <i class="bi bi-people-fill"></i>

                Data Akun

            </h2>

        </div>


        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="akun-table-wrapper">

            <?php if (!empty($akun)): ?>

                <table class="akun-table">

                    <thead>

                        <tr>

                            <th class="nomor-akun">
                                No
                            </th>

                            <th>
                                Nama Pengguna
                            </th>

                            <th>
                                Username
                            </th>

                            <th>
                                Role
                            </th>

                            <th>
                                Password
                            </th>

                            <th>
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($akun as $index => $row): ?>

                            <?php

                            /*
                            |--------------------------------------------------------------------------
                            | ROLE
                            |--------------------------------------------------------------------------
                            */

                            $role_key = $row['role_key'];

                            if ($role_key === 'admin') {

                                $icon = 'bi-shield-lock-fill';

                                $role_class = 'role-admin';

                            } elseif ($role_key === 'guru') {

                                $icon = 'bi-person-badge-fill';

                                $role_class = 'role-guru';

                            } else {

                                $icon = 'bi-mortarboard-fill';

                                $role_class = 'role-siswa';

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | FOTO
                            |--------------------------------------------------------------------------
                            */

                            $foto_ada = false;

                            $foto = trim($row['foto'] ?? '');

                            if ($role_key === 'guru' && $foto !== '') {

                                $foto_path =
                                    __DIR__ .
                                    '/uploads/guru/' .
                                    $foto;

                                if (file_exists($foto_path)) {
                                    $foto_ada = true;
                                }

                            } elseif ($role_key === 'siswa' && $foto !== '') {

                                $foto_path =
                                    __DIR__ .
                                    '/uploads/siswa/' .
                                    $foto;

                                if (file_exists($foto_path)) {
                                    $foto_ada = true;
                                }
                            }

                            ?>

                            <tr>


                                <!-- NOMOR -->

                                <td class="nomor-akun">

                                    <?= $index + 1; ?>

                                </td>


                                <!-- NAMA -->

                                <td>

                                    <div class="nama-wrapper">


                                        <div class="nama-avatar">

                                            <?php if ($foto_ada): ?>

                                                <?php if ($role_key === 'guru'): ?>

                                                    <img src="uploads/guru/<?= htmlspecialchars($foto); ?>"
                                                        alt="Foto <?= htmlspecialchars($row['nama']); ?>" loading="lazy">

                                                <?php elseif ($role_key === 'siswa'): ?>

                                                    <img src="uploads/siswa/<?= htmlspecialchars($foto); ?>"
                                                        alt="Foto <?= htmlspecialchars($row['nama']); ?>" loading="lazy">

                                                <?php endif; ?>

                                            <?php else: ?>

                                                <i class="bi <?= $icon; ?>"></i>

                                            <?php endif; ?>

                                        </div>


                                        <div class="nama-info">

                                            <span class="nama-user">

                                                <?= htmlspecialchars($row['nama']); ?>

                                            </span>

                                            <span class="username-user">

                                                <?= htmlspecialchars($row['role']); ?>

                                            </span>

                                        </div>

                                    </div>

                                </td>


                                <!-- USERNAME -->

                                <td class="username-cell">

                                    <?= htmlspecialchars($row['username']); ?>

                                </td>


                                <!-- ROLE -->

                                <td>

                                    <span class="role-badge <?= $role_class; ?>">

                                        <i class="bi <?= $icon; ?>"></i>

                                        <?= htmlspecialchars($row['role']); ?>

                                    </span>

                                </td>


                                <!-- PASSWORD -->

                                <td>

                                    <span class="password-status">

                                        <i class="bi bi-shield-check"></i>

                                        Tersimpan aman

                                    </span>

                                </td>


                                <!-- AKSI -->

                                <td>

                                    <div class="aksi-akun">

                                        <a href="akun_detail.php?id=<?= (int) $row['id']; ?>&role=<?= urlencode($role_key); ?>"
                                            class="btn-aksi btn-detail" title="Detail Akun">

                                            <i class="bi bi-eye"></i>

                                        </a>

                                    </div>

                                </td>


                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php else: ?>


                <div class="akun-empty">

                    <i class="bi bi-person-x"></i>

                    <p>
                        Belum ada data akun.
                    </p>

                </div>


            <?php endif; ?>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . "/layout/footer.php";

?>