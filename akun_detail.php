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


/*
|--------------------------------------------------------------------------
| PARAMETER
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$role = $_GET['role'] ?? '';


$role_valid = [
    'admin',
    'guru',
    'siswa'
];


if (
    $id <= 0 ||
    !in_array($role, $role_valid, true)
) {

    header("Location: akun.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| DATA AKUN
|--------------------------------------------------------------------------
*/

$akun = null;

$error = '';


try {


    /* =========================================================
       ADMIN
    ========================================================= */

    if ($role === 'admin') {

        $stmt = $conn->prepare("
            SELECT
                id_admin AS id,
                nama,
                username,
                NULL AS nip,
                NULL AS nis,
                NULL AS kelas,
                NULL AS jurusan,
                NULL AS foto,
                'Admin' AS role
            FROM admin
            WHERE id_admin = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $result = $stmt->get_result();

        $akun = $result->fetch_assoc();

        $stmt->close();


        /* =========================================================
           GURU BK
        ========================================================= */

    } elseif ($role === 'guru') {

        $stmt = $conn->prepare("
            SELECT
                id_guru AS id,
                nama,
                nip AS username,
                nip,
                NULL AS nis,
                NULL AS kelas,
                NULL AS jurusan,
                foto,
                'Guru BK' AS role
            FROM guru_bk
            WHERE id_guru = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $result = $stmt->get_result();

        $akun = $result->fetch_assoc();

        $stmt->close();


        /* =========================================================
           SISWA
        ========================================================= */

    } elseif ($role === 'siswa') {

        $stmt = $conn->prepare("
            SELECT
                id_siswa AS id,
                nama,
                nis AS username,
                NULL AS nip,
                nis,
                kelas,
                jurusan,
                foto,
                'Siswa' AS role
            FROM siswa
            WHERE id_siswa = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $result = $stmt->get_result();

        $akun = $result->fetch_assoc();

        $stmt->close();
    }


} catch (Throwable $e) {

    $error = "Terjadi kesalahan saat mengambil data akun.";

}


/*
|--------------------------------------------------------------------------
| CEK DATA
|--------------------------------------------------------------------------
*/

if (!$akun && $error === '') {

    header("Location: akun.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| FOTO
|--------------------------------------------------------------------------
*/

$foto = '';

$foto_ada = false;

$foto_url = '';

if ($akun) {

    $foto = trim($akun['foto'] ?? '');


    if ($role === 'guru' && $foto !== '') {

        $foto_path =
            __DIR__ .
            '/uploads/guru/' .
            $foto;

        if (file_exists($foto_path)) {

            $foto_ada = true;

            $foto_url =
                'uploads/guru/' .
                rawurlencode($foto);
        }


    } elseif ($role === 'siswa' && $foto !== '') {

        $foto_path =
            __DIR__ .
            '/uploads/siswa/' .
            $foto;

        if (file_exists($foto_path)) {

            $foto_ada = true;

            $foto_url =
                'uploads/siswa/' .
                rawurlencode($foto);
        }
    }
}


/*
|--------------------------------------------------------------------------
| ICON ROLE
|--------------------------------------------------------------------------
*/

if ($role === 'admin') {

    $icon_role = 'bi-shield-lock-fill';

    $role_class = 'role-admin';

} elseif ($role === 'guru') {

    $icon_role = 'bi-person-badge-fill';

    $role_class = 'role-guru';

} else {

    $icon_role = 'bi-mortarboard-fill';

    $role_class = 'role-siswa';
}


require_once __DIR__ . "/layout/header.php";

?>

<style>

/* =========================================================
   AKUN DETAIL
========================================================= */

.akun-detail-page {
    padding-bottom: 30px;
}


/* =========================================================
   HEADER
========================================================= */

.akun-detail-header {
    display: flex;
    align-items: center;

    gap: 15px;

    margin-bottom: 24px;
}

.btn-kembali {
    width: 42px;
    height: 42px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    background: var(--bg-card);

    color: var(--text-main);

    border: 1px solid var(--border-color);

    border-radius: 10px;

    text-decoration: none;

    transition: .2s ease;
}

.btn-kembali:hover {
    background: var(--bg-body);

    color: #0d6efd;

    transform: translateX(-2px);
}

.akun-detail-title {
    margin: 0;

    font-size: 28px;

    font-weight: 700;

    color: var(--text-main);
}

.akun-detail-subtitle {
    margin: 3px 0 0;

    font-size: 14px;

    color: var(--text-muted);
}


/* =========================================================
   CARD
========================================================= */

.akun-detail-card {
    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    box-shadow: 0 4px 18px rgba(0,0,0,.04);

    overflow: hidden;
}


/* =========================================================
   PROFILE
========================================================= */

.akun-profile {
    padding: 30px;

    display: flex;
    align-items: center;

    gap: 22px;

    border-bottom: 1px solid var(--border-color);
}

.akun-profile-avatar {
    width: 100px;
    height: 100px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;

    border-radius: 50%;

    background: rgba(13,110,253,.10);

    color: #0d6efd;

    border: 3px solid rgba(13,110,253,.12);

    font-size: 42px;
}

.akun-profile-avatar img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;
}

.akun-profile-info h2 {
    margin: 0 0 7px;

    color: var(--text-main);

    font-size: 24px;

    font-weight: 700;
}

.akun-profile-info p {
    margin: 0 0 10px;

    color: var(--text-muted);

    font-size: 14px;
}


/* =========================================================
   ROLE
========================================================= */

.role-badge {
    display: inline-flex;
    align-items: center;

    gap: 6px;

    padding: 7px 11px;

    border-radius: 8px;

    font-size: 12px;

    font-weight: 600;
}

.role-admin {
    background: rgba(111,66,193,.12);

    color: #6f42c1;
}

.role-guru {
    background: rgba(25,135,84,.12);

    color: #198754;
}

.role-siswa {
    background: rgba(13,110,253,.12);

    color: #0d6efd;
}


/* =========================================================
   DETAIL
========================================================= */

.detail-section {
    padding: 25px 30px;
}

.detail-section-title {
    margin: 0 0 18px;

    font-size: 17px;

    font-weight: 700;

    color: var(--text-main);
}

.detail-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 15px;
}

.detail-item {
    padding: 17px;

    background: var(--bg-body);

    border: 1px solid var(--border-color);

    border-radius: 12px;
}

.detail-label {
    display: block;

    margin-bottom: 6px;

    font-size: 12px;

    font-weight: 600;

    color: var(--text-muted);

    text-transform: uppercase;

    letter-spacing: .4px;
}

.detail-value {
    color: var(--text-main);

    font-size: 15px;

    font-weight: 600;

    word-break: break-word;
}


/* =========================================================
   PASSWORD
========================================================= */

.password-status {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    color: #198754;

    font-weight: 600;
}


/* =========================================================
   FOOTER
========================================================= */

.akun-detail-footer {
    padding: 20px 30px;

    border-top: 1px solid var(--border-color);

    display: flex;

    justify-content: flex-end;

    gap: 10px;
}

.btn-kembali-bawah {
    display: inline-flex;
    align-items: center;

    gap: 8px;

    padding: 10px 17px;

    background: var(--bg-body);

    color: var(--text-main);

    border: 1px solid var(--border-color);

    border-radius: 10px;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;
}

.btn-kembali-bawah:hover {
    color: #0d6efd;

    border-color: #0d6efd;
}

.btn-edit-akun {
    display: inline-flex;
    align-items: center;

    gap: 8px;

    padding: 10px 17px;

    background: #0d6efd;

    color: #fff;

    border-radius: 10px;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;
}

.btn-edit-akun:hover {
    background: #0b5ed7;

    color: #fff;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .akun-detail-title {
        font-size: 23px;
    }

    .akun-profile {
        padding: 22px;

        flex-direction: column;

        align-items: flex-start;
    }

    .detail-section {
        padding: 22px;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .akun-detail-footer {
        padding: 18px 22px;

        flex-direction: column;
    }

}

</style>


<div class="akun-detail-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="akun-detail-header">

        <a
            href="akun.php"
            class="btn-kembali"
            title="Kembali"
        >

            <i class="bi bi-arrow-left"></i>

        </a>


        <div>

            <h1 class="akun-detail-title">
                Detail Akun
            </h1>

            <p class="akun-detail-subtitle">
                Informasi lengkap akun pengguna sistem BK
            </p>

        </div>

    </div>


    <?php if ($error): ?>


            <div class="alert alert-danger">

                <i class="bi bi-exclamation-triangle me-2"></i>

                <?= htmlspecialchars($error); ?>

            </div>


    <?php elseif ($akun): ?>


            <div class="akun-detail-card">


                <!-- =================================================
                 PROFILE
            ================================================== -->

                <div class="akun-profile">


                    <div class="akun-profile-avatar">

                        <?php if ($foto_ada): ?>

                                <img
                                    src="<?= htmlspecialchars($foto_url); ?>"
                                    alt="Foto <?= htmlspecialchars($akun['nama']); ?>"
                                >

                        <?php else: ?>

                                <i class="bi <?= $icon_role; ?>"></i>

                        <?php endif; ?>

                    </div>


                    <div class="akun-profile-info">

                        <h2>
                            <?= htmlspecialchars($akun['nama']); ?>
                        </h2>


                        <p>

                            <?php if ($role === 'admin'): ?>

                                    <?= htmlspecialchars($akun['username']); ?>

                            <?php elseif ($role === 'guru'): ?>

                                    NIP:
                                    <?= htmlspecialchars($akun['nip']); ?>

                            <?php else: ?>

                                    NIS:
                                    <?= htmlspecialchars($akun['nis']); ?>

                            <?php endif; ?>

                        </p>


                        <span class="role-badge <?= $role_class; ?>">

                            <i class="bi <?= $icon_role; ?>"></i>

                            <?= htmlspecialchars($akun['role']); ?>

                        </span>

                    </div>

                </div>


                <!-- =================================================
                 DETAIL
            ================================================== -->

                <div class="detail-section">


                    <h3 class="detail-section-title">

                        <i class="bi bi-person-vcard me-2"></i>

                        Informasi Akun

                    </h3>


                    <div class="detail-grid">


                        <!-- NAMA -->

                        <div class="detail-item">

                            <span class="detail-label">
                                Nama Lengkap
                            </span>

                            <div class="detail-value">

                                <?= htmlspecialchars($akun['nama']); ?>

                            </div>

                        </div>


                        <!-- USERNAME -->

                        <div class="detail-item">

                            <span class="detail-label">

                                <?=
                                    $role === 'admin'
                                    ? 'Username'
                                    : ($role === 'guru' ? 'NIP' : 'NIS');
                                ?>

                            </span>

                            <div class="detail-value">

                                <?= htmlspecialchars($akun['username']); ?>

                            </div>

                        </div>


                        <!-- ROLE -->

                        <div class="detail-item">

                            <span class="detail-label">
                                Role
                            </span>

                            <div class="detail-value">

                                <span class="role-badge <?= $role_class; ?>">

                                    <i class="bi <?= $icon_role; ?>"></i>

                                    <?= htmlspecialchars($akun['role']); ?>

                                </span>

                            </div>

                        </div>


                        <!-- PASSWORD -->

                        <div class="detail-item">

                            <span class="detail-label">
                                Password
                            </span>

                            <div class="detail-value">

                                <span class="password-status">

                                    <i class="bi bi-shield-check"></i>

                                    Tersimpan aman

                                </span>

                            </div>

                        </div>


                        <?php if ($role === 'siswa'): ?>


                                <!-- KELAS -->

                                <div class="detail-item">

                                    <span class="detail-label">
                                        Kelas
                                    </span>

                                    <div class="detail-value">

                                        <?= htmlspecialchars(
                                            $akun['kelas'] ?? '-'
                                        ); ?>

                                    </div>

                                </div>


                                <!-- JURUSAN -->

                                <div class="detail-item">

                                    <span class="detail-label">
                                        Jurusan
                                    </span>

                                    <div class="detail-value">

                                        <?= htmlspecialchars(
                                            $akun['jurusan'] ?? '-'
                                        ); ?>

                                    </div>

                                </div>


                        <?php endif; ?>


                        <?php if ($role === 'guru'): ?>


                                <!-- NIP -->

                                <div class="detail-item">

                                    <span class="detail-label">
                                        NIP
                                    </span>

                                    <div class="detail-value">

                                        <?= htmlspecialchars(
                                            $akun['nip'] ?? '-'
                                        ); ?>

                                    </div>

                                </div>


                                <!-- JABATAN -->

                                <div class="detail-item">

                                    <span class="detail-label">
                                        Jabatan
                                    </span>

                                    <div class="detail-value">

                                        Guru Bimbingan & Konseling

                                    </div>

                                </div>


                        <?php endif; ?>


                        <!-- ID -->

                        <div class="detail-item">

                            <span class="detail-label">
                                ID Akun
                            </span>

                            <div class="detail-value">

                                #<?= (int) $akun['id']; ?>

                            </div>

                        </div>


                    </div>

                </div>


                <!-- =================================================
                 FOOTER
            ================================================== -->

                <div class="akun-detail-footer">


                    <a
                        href="akun.php"
                        class="btn-kembali-bawah"
                    >

                        <i class="bi bi-arrow-left"></i>

                        Kembali

                    </a>


                    <?php if ($role === 'guru'): ?>

                            <a
                                href="guru_edit.php?id=<?= (int) $akun['id']; ?>"
                                class="btn-edit-akun"
                            >

                                <i class="bi bi-pencil"></i>

                                Edit Guru

                            </a>

                    <?php elseif ($role === 'siswa'): ?>

                            <a
                                href="siswa_edit.php?id=<?= (int) $akun['id']; ?>"
                                class="btn-edit-akun"
                            >

                                <i class="bi bi-pencil"></i>

                                Edit Siswa

                            </a>

                    <?php endif; ?>


                </div>


            </div>


    <?php endif; ?>


</div>


<?php

require_once __DIR__ . "/layout/footer.php";

?>