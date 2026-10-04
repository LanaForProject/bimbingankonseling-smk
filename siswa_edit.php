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

$title = 'Edit Siswa';

$errors = [];

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
    header('Location: siswa.php');
    exit;
}

/*
 * |--------------------------------------------------------------------------
 * | PROSES UPDATE
 * |--------------------------------------------------------------------------
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis = trim($_POST['nis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
     * |--------------------------------------------------------------------------
     * | FOTO LAMA
     * |--------------------------------------------------------------------------
     */

    $foto_lama = $siswa['foto'] ?? null;

    $foto_baru = $foto_lama;

    /*
     * |--------------------------------------------------------------------------
     * | VALIDASI DATA
     * |--------------------------------------------------------------------------
     */

    if ($nis === '') {
        $errors[] = 'NIS wajib diisi.';
    }

    if ($nama === '') {
        $errors[] = 'Nama siswa wajib diisi.';
    }

    if ($kelas === '') {
        $errors[] = 'Kelas wajib diisi.';
    }

    if ($jurusan === '') {
        $errors[] = 'Jurusan wajib diisi.';
    }

    /*
     * |--------------------------------------------------------------------------
     * | VALIDASI PASSWORD
     * |--------------------------------------------------------------------------
     */

    if (
        $password !== '' &&
        strlen($password) < 6
    ) {
        $errors[] =
            'Password minimal 6 karakter.';
    }

    /*
     * |--------------------------------------------------------------------------
     * | CEK NIS DUPLIKAT
     * |--------------------------------------------------------------------------
     */

    if (empty($errors)) {
        $cek = mysqli_prepare(
            $conn,
            'SELECT id_siswa
             FROM siswa
             WHERE nis = ?
             AND id_siswa != ?
             LIMIT 1'
        );

        mysqli_stmt_bind_param(
            $cek,
            'si',
            $nis,
            $id_siswa
        );

        mysqli_stmt_execute($cek);

        $hasil_cek =
            mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_cek) > 0) {
            $errors[] =
                'NIS tersebut sudah digunakan oleh siswa lain.';
        }

        mysqli_stmt_close($cek);
    }

    /*
     * |--------------------------------------------------------------------------
     * | PROSES UPLOAD FOTO
     * |--------------------------------------------------------------------------
     */

    if (
        empty($errors) &&
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        /*
         * |--------------------------------------------------------------------------
         * | CEK ERROR UPLOAD
         * |--------------------------------------------------------------------------
         */

        if (
            $_FILES['foto']['error'] !==
            UPLOAD_ERR_OK
        ) {
            $errors[] =
                'Foto gagal diupload.';
        } else {
            $file_tmp =
                $_FILES['foto']['tmp_name'];

            $file_name =
                $_FILES['foto']['name'];

            $file_size =
                $_FILES['foto']['size'];

            /*
             * |--------------------------------------------------------------------------
             * | AMBIL EXTENSION
             * |--------------------------------------------------------------------------
             */

            $extension =
                strtolower(
                    pathinfo(
                        $file_name,
                        PATHINFO_EXTENSION
                    )
                );

            /*
             * |--------------------------------------------------------------------------
             * | FORMAT YANG DIPERBOLEHKAN
             * |--------------------------------------------------------------------------
             */

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            if (
                !in_array(
                    $extension,
                    $allowed_extensions,
                    true
                )
            ) {
                $errors[] =
                    'Format foto harus JPG, JPEG, PNG, atau WEBP.';
            }
            /*
             * |--------------------------------------------------------------------------
             * | CEK UKURAN
             * |--------------------------------------------------------------------------
             */ elseif (
                $file_size >
                2 * 1024 * 1024
            ) {
                $errors[] =
                    'Ukuran foto maksimal 2 MB.';
            }
            /*
             * |--------------------------------------------------------------------------
             * | CEK FILE GAMBAR
             * |--------------------------------------------------------------------------
             */ elseif (
                !getimagesize($file_tmp)
            ) {
                $errors[] =
                    'File yang diupload bukan gambar yang valid.';
            }
            /*
             * |--------------------------------------------------------------------------
             * | SIMPAN FOTO
             * |--------------------------------------------------------------------------
             */ else {
                $folder_upload =
                    __DIR__ . '/uploads/siswa/';

                /*
                 * | Buat folder jika belum ada
                 */

                if (
                    !is_dir($folder_upload)
                ) {
                    if (
                        !mkdir(
                            $folder_upload,
                            0755,
                            true
                        )
                    ) {
                        $errors[] =
                            'Folder upload foto gagal dibuat.';
                    }
                }

                /*
                 * | Buat nama file unik
                 */

                if (empty($errors)) {
                    $nama_file =
                        'siswa_'
                        . time()
                        . '_'
                        . bin2hex(
                            random_bytes(5)
                        )
                        . '.'
                        . $extension;

                    $tujuan =
                        $folder_upload
                        . $nama_file;

                    /*
                     * |--------------------------------------------------------------------------
                     * | PINDAHKAN FILE
                     * |--------------------------------------------------------------------------
                     */

                    if (
                        move_uploaded_file(
                            $file_tmp,
                            $tujuan
                        )
                    ) {
                        $foto_baru =
                            $nama_file;
                    } else {
                        $errors[] =
                            'Foto gagal disimpan ke server.';
                    }
                }
            }
        }
    }

    /*
     * |--------------------------------------------------------------------------
     * | SIMPAN PERUBAHAN
     * |--------------------------------------------------------------------------
     */

    if (empty($errors)) {
        /*
         * |--------------------------------------------------------------------------
         * | UPDATE DENGAN PASSWORD
         * |--------------------------------------------------------------------------
         */

        if ($password !== '') {
            $password_hash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $update = mysqli_prepare(
                $conn,
                'UPDATE siswa
                 SET
                    nis = ?,
                    nama = ?,
                    kelas = ?,
                    jurusan = ?,
                    foto = ?,
                    password = ?
                 WHERE id_siswa = ?'
            );

            mysqli_stmt_bind_param(
                $update,
                'ssssssi',
                $nis,
                $nama,
                $kelas,
                $jurusan,
                $foto_baru,
                $password_hash,
                $id_siswa
            );
        }
        /*
         * |--------------------------------------------------------------------------
         * | UPDATE TANPA PASSWORD
         * |--------------------------------------------------------------------------
         */ else {
            $update = mysqli_prepare(
                $conn,
                'UPDATE siswa
                 SET
                    nis = ?,
                    nama = ?,
                    kelas = ?,
                    jurusan = ?,
                    foto = ?
                 WHERE id_siswa = ?'
            );

            mysqli_stmt_bind_param(
                $update,
                'sssssi',
                $nis,
                $nama,
                $kelas,
                $jurusan,
                $foto_baru,
                $id_siswa
            );
        }

        /*
         * |--------------------------------------------------------------------------
         * | EKSEKUSI UPDATE
         * |--------------------------------------------------------------------------
         */

        if (
            mysqli_stmt_execute($update)
        ) {
            mysqli_stmt_close($update);

            /*
             * |--------------------------------------------------------------------------
             * | HAPUS FOTO LAMA
             * |--------------------------------------------------------------------------
             * |
             * | Hanya dilakukan jika:
             * | - ada foto lama
             * | - foto baru berbeda
             * |--------------------------------------------------------------------------
             */

            if (
                !empty($foto_lama) &&
                !empty($foto_baru) &&
                $foto_lama !== $foto_baru
            ) {
                $file_lama =
                    __DIR__
                    . '/uploads/siswa/'
                    . $foto_lama;

                if (
                    file_exists($file_lama)
                ) {
                    unlink($file_lama);
                }
            }

            /*
             * |--------------------------------------------------------------------------
             * | REDIRECT
             * |--------------------------------------------------------------------------
             */

            header(
                'Location: siswa.php?success=edit'
            );

            exit;
        } else {
            /*
             * |--------------------------------------------------------------------------
             * | JIKA DATABASE GAGAL
             * |--------------------------------------------------------------------------
             * |
             * | Hapus foto baru yang sudah terupload
             * |--------------------------------------------------------------------------
             */

            if (
                !empty($foto_baru) &&
                $foto_baru !== $foto_lama
            ) {
                $file_baru =
                    __DIR__
                    . '/uploads/siswa/'
                    . $foto_baru;

                if (
                    file_exists($file_baru)
                ) {
                    unlink($file_baru);
                }
            }

            $errors[] =
                'Data siswa gagal diperbarui: '
                . mysqli_error($conn);

            mysqli_stmt_close($update);
        }
    }

    /*
     * |--------------------------------------------------------------------------
     * | TAMPILKAN KEMBALI DATA INPUT JIKA ERROR
     * |--------------------------------------------------------------------------
     */

    $siswa['nis'] =
        $nis;

    $siswa['nama'] =
        $nama;

    $siswa['kelas'] =
        $kelas;

    $siswa['jurusan'] =
        $jurusan;

    $siswa['foto'] =
        $foto_baru;
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
   PAGE
========================================================= */

.edit-siswa-page {

    width: 100%;

    padding-bottom: 30px;

}


/* =========================================================
   HEADER
========================================================= */

.edit-header {

    width: 100%;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 24px;

}


.edit-header-left {

    min-width: 0;

}


.edit-header-title {

    display: flex;

    align-items: center;

    gap: 10px;

    margin: 0;

    color: var(--text-main);

    font-size: 28px;

    font-weight: 700;

    line-height: 1.2;

}


.edit-header-title i {

    font-size: 27px;

}


.edit-header-subtitle {

    margin: 6px 0 0;

    color: var(--text-muted);

    font-size: 14px;

}


/* =========================================================
   BUTTON KEMBALI
========================================================= */

.btn-kembali-edit {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 10px 17px;

    border: 1px solid var(--border-color);

    border-radius: 10px;

    background: var(--bg-card);

    color: var(--text-main);

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

    transition: all .2s ease;

}


.btn-kembali-edit:hover {

    background: var(--bg-body);

    border-color: #0d6efd;

    color: #0d6efd;

}


/* =========================================================
   CARD
========================================================= */

.edit-card {

    width: 100%;

    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 4px 18px rgba(0,0,0,.04);

}


/* =========================================================
   CARD HEADER
========================================================= */

.edit-card-header {

    min-height: 60px;

    padding: 0 22px;

    display: flex;

    align-items: center;

    gap: 9px;

    border-bottom:
        1px solid var(--border-color);

    color: var(--text-main);

    font-size: 16px;

    font-weight: 700;

}


.edit-card-header i {

    font-size: 18px;

}


/* =========================================================
   CARD BODY
========================================================= */

.edit-card-body {

    padding: 24px 22px;

}


/* =========================================================
   FORM GRID
========================================================= */

.edit-form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    column-gap: 22px;

    row-gap: 20px;

}


/* =========================================================
   FORM GROUP
========================================================= */

.edit-form-group {

    width: 100%;

}


.edit-form-group.full {

    grid-column: 1 / -1;

}


.edit-label {

    display: block;

    margin: 0 0 7px;

    color: var(--text-main);

    font-size: 14px;

    font-weight: 600;

}


.edit-label .required {

    color: #dc3545;

}


/* =========================================================
   INPUT
========================================================= */

.edit-input {

    width: 100%;

    height: 46px;

    padding: 0 13px;

    border:
        1px solid var(--border-color);

    border-radius: 10px;

    outline: none;

    background: var(--bg-card);

    color: var(--text-main);

    font-family: inherit;

    font-size: 14px;

    transition:
        border-color .2s ease,
        box-shadow .2s ease;

}


.edit-input::placeholder {

    color: var(--text-muted);

    opacity: .8;

}


.edit-input:focus {

    border-color: #0d6efd;

    box-shadow:
        0 0 0 3px
        rgba(13, 110, 253, .10);

}


/* =========================================================
   FILE INPUT
========================================================= */

.edit-file-input {

    width: 100%;

    min-height: 46px;

    padding: 9px 12px;

    border:
        1px solid var(--border-color);

    border-radius: 10px;

    background: var(--bg-card);

    color: var(--text-main);

    font-size: 13px;

    cursor: pointer;

}


.edit-file-input:focus {

    border-color: #0d6efd;

    outline: none;

    box-shadow:
        0 0 0 3px
        rgba(13, 110, 253, .10);

}


/* =========================================================
   FOTO AREA
========================================================= */

.foto-edit-area {

    display: flex;

    align-items: center;

    gap: 20px;

}


.foto-edit-preview {

    position: relative;

    width: 115px;

    height: 115px;

    flex-shrink: 0;

    overflow: hidden;

    border:
        1px solid var(--border-color);

    border-radius: 14px;

    background: var(--bg-body);

    display: flex;

    align-items: center;

    justify-content: center;

}


.foto-edit-preview i {

    font-size: 44px;

    color: var(--text-muted);

}


.foto-edit-preview img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;

}


.foto-edit-info {

    flex: 1;

}


.foto-edit-name {

    margin-bottom: 6px;

    color: var(--text-main);

    font-size: 14px;

    font-weight: 600;

    word-break: break-word;

}


.foto-edit-help {

    margin-top: 7px;

    color: var(--text-muted);

    font-size: 12px;

    line-height: 1.5;

}


/* =========================================================
   PASSWORD
========================================================= */

.edit-password-box {

    position: relative;

    width: 100%;

}


.edit-password-box .edit-input {

    padding-right: 48px;

}


.edit-password-toggle {

    position: absolute;

    top: 50%;

    right: 10px;

    transform: translateY(-50%);

    width: 34px;

    height: 34px;

    padding: 0;

    border: 0;

    border-radius: 8px;

    background: transparent;

    color: var(--text-muted);

    display: flex;

    align-items: center;

    justify-content: center;

    cursor: pointer;

}


.edit-password-toggle:hover {

    background: var(--bg-body);

    color: #0d6efd;

}


/* =========================================================
   HELP
========================================================= */

.edit-help {

    margin-top: 6px;

    color: var(--text-muted);

    font-size: 12px;

    line-height: 1.5;

}


/* =========================================================
   ERROR
========================================================= */

.edit-error {

    display: flex;

    align-items: flex-start;

    gap: 9px;

    margin-bottom: 22px;

    padding: 13px 15px;

    border: none;

    border-radius: 12px;

    background: rgba(220, 53, 69, .10);

    color: #dc3545;

    font-size: 14px;

}


.edit-error i {

    margin-top: 2px;

}


/* =========================================================
   INFO
========================================================= */

.edit-info {

    display: flex;

    align-items: flex-start;

    gap: 10px;

    margin-top: 22px;

    padding: 13px 15px;

    border-radius: 12px;

    background: rgba(13,110,253,.07);

    border:
        1px solid rgba(13,110,253,.12);

    color: var(--text-muted);

    font-size: 13px;

    line-height: 1.6;

}


.edit-info i {

    margin-top: 2px;

    color: #0d6efd;

    font-size: 17px;

}


.edit-info strong {

    color: var(--text-main);

}


/* =========================================================
   FOOTER FORM
========================================================= */

.edit-card-footer {

    min-height: 68px;

    padding: 13px 22px;

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 10px;

    border-top:
        1px solid var(--border-color);

}


/* =========================================================
   BUTTON BATAL
========================================================= */

.btn-batal-edit {

    height: 40px;

    padding: 0 17px;

    border:
        1px solid var(--border-color);

    border-radius: 10px;

    background: var(--bg-card);

    color: var(--text-main);

    text-decoration: none;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    font-size: 14px;

    font-weight: 600;

    transition: all .2s ease;

}


.btn-batal-edit:hover {

    background: var(--bg-body);

    color: var(--text-main);

}


/* =========================================================
   BUTTON UPDATE
========================================================= */

.btn-update-edit {

    height: 40px;

    padding: 0 18px;

    border: 0;

    border-radius: 10px;

    background: #0d6efd;

    color: #fff;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

    transition: .2s ease;

}


.btn-update-edit:hover {

    background: #0b5ed7;

    color: #fff;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .edit-form-grid {

        grid-template-columns: 1fr;

    }


    .edit-form-group.full {

        grid-column: auto;

    }

}


@media (max-width: 700px) {

    .edit-header {

        align-items: flex-start;

        flex-direction: column;

    }


    .btn-kembali-edit {

        width: 100%;

    }


    .edit-card-body {

        padding: 20px 16px;

    }


    .edit-card-footer {

        padding: 13px 16px;

    }


    .foto-edit-area {

        align-items: flex-start;

        flex-direction: column;

    }


    .foto-edit-info {

        width: 100%;

    }

}


@media (max-width: 480px) {

    .edit-header-title {

        font-size: 24px;

    }


    .edit-header-subtitle {

        font-size: 13px;

    }


    .edit-card-footer {

        flex-direction: column-reverse;

        align-items: stretch;

    }


    .btn-batal-edit,
    .btn-update-edit {

        width: 100%;

    }

}

</style>


<!-- =========================================================
     HALAMAN EDIT
========================================================= -->

<div class="edit-siswa-page">


    <!-- =======================================================
         HEADER
    ======================================================== -->

    <div class="edit-header">

        <div class="edit-header-left">

            <h1 class="edit-header-title">

                <i class="bi bi-pencil-square"></i>

                Edit Siswa

            </h1>


            <p class="edit-header-subtitle">

                Perbarui data siswa yang terdaftar dalam sistem BK Online.

            </p>

        </div>


        <a
            href="siswa.php"
            class="btn-kembali-edit"
        >

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

    </div>


    <!-- =======================================================
         CARD
    ======================================================== -->

    <div class="edit-card">


        <!-- HEADER CARD -->

        <div class="edit-card-header">

            <i class="bi bi-person-vcard-fill"></i>

            <span>Form Edit Data Siswa</span>

        </div>


        <!-- BODY -->

        <div class="edit-card-body">


            <!-- ERROR -->

            <?php if (!empty($errors)): ?>

                <div class="edit-error">

                    <i class="bi bi-exclamation-circle-fill"></i>

                    <div>

                        <?php foreach ($errors as $error): ?>

                            <div>
                                <?= htmlspecialchars($error); ?>
                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                action=""
                enctype="multipart/form-data"
                autocomplete="off"
            >


                <div class="edit-form-grid">


                    <!-- =================================================
                         NIS
                    ================================================== -->

                    <div class="edit-form-group">

                        <label
                            for="nis"
                            class="edit-label"
                        >

                            NIS

                            <span class="required">*</span>

                        </label>


                        <input
                            type="text"
                            id="nis"
                            name="nis"
                            class="edit-input"
                            value="<?= htmlspecialchars($siswa['nis'] ?? ''); ?>"
                            placeholder="Masukkan NIS"
                            maxlength="20"
                            required
                        >

                    </div>


                    <!-- =================================================
                         NAMA
                    ================================================== -->

                    <div class="edit-form-group">

                        <label
                            for="nama"
                            class="edit-label"
                        >

                            Nama Siswa

                            <span class="required">*</span>

                        </label>


                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            class="edit-input"
                            value="<?= htmlspecialchars($siswa['nama'] ?? ''); ?>"
                            placeholder="Masukkan nama lengkap"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- =================================================
                         KELAS
                    ================================================== -->

                    <div class="edit-form-group">

                        <label
                            for="kelas"
                            class="edit-label"
                        >

                            Kelas

                            <span class="required">*</span>

                        </label>


                        <input
                            type="text"
                            id="kelas"
                            name="kelas"
                            class="edit-input"
                            value="<?= htmlspecialchars($siswa['kelas'] ?? ''); ?>"
                            placeholder="Contoh: X RPL 1"
                            maxlength="20"
                            required
                        >


                        <div class="edit-help">

                            Contoh:
                            X RPL 1, XI RPL 2, XII RPL 1

                        </div>

                    </div>


                    <!-- =================================================
                         JURUSAN
                    ================================================== -->

                    <div class="edit-form-group">

                        <label
                            for="jurusan"
                            class="edit-label"
                        >

                            Jurusan

                            <span class="required">*</span>

                        </label>


                        <input
                            type="text"
                            id="jurusan"
                            name="jurusan"
                            class="edit-input"
                            value="<?= htmlspecialchars($siswa['jurusan'] ?? ''); ?>"
                            placeholder="Contoh: Rekayasa Perangkat Lunak"
                            maxlength="50"
                            required
                        >

                    </div>


                    <!-- =================================================
                         FOTO
                    ================================================== -->

                    <div class="edit-form-group full">

                        <label
                            for="foto"
                            class="edit-label"
                        >

                            Foto Siswa

                        </label>


                        <div class="foto-edit-area">


                            <!-- PREVIEW FOTO -->

                            <div class="foto-edit-preview">


                                <?php if (!empty($siswa['foto'])): ?>

                                    <img
                                        src="uploads/siswa/<?= htmlspecialchars($siswa['foto']); ?>"
                                        id="previewFoto"
                                        alt="Foto <?= htmlspecialchars($siswa['nama']); ?>"
                                    >

                                    <i
                                        class="bi bi-person"
                                        id="previewIcon"
                                        style="display:none;"
                                    ></i>

                                <?php else: ?>

                                    <img
                                        src=""
                                        id="previewFoto"
                                        alt="Preview Foto"
                                        style="display:none;"
                                    >

                                    <i
                                        class="bi bi-person"
                                        id="previewIcon"
                                    ></i>

                                <?php endif; ?>


                            </div>


                            <!-- INPUT FOTO -->

                            <div class="foto-edit-info">


                                <?php if (!empty($siswa['foto'])): ?>

                                    <div
                                        class="foto-edit-name"
                                        id="namaFoto"
                                    >

                                        <i class="bi bi-image me-1"></i>

                                        Foto saat ini:
                                        <?= htmlspecialchars($siswa['foto']); ?>

                                    </div>

                                <?php else: ?>

                                    <div
                                        class="foto-edit-name"
                                        id="namaFoto"
                                    >

                                        <i class="bi bi-image me-1"></i>

                                        Belum ada foto

                                    </div>

                                <?php endif; ?>


                                <input
                                    type="file"
                                    id="foto"
                                    name="foto"
                                    class="edit-file-input"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                >


                                <div class="foto-edit-help">

                                    <i class="bi bi-info-circle me-1"></i>

                                    Pilih foto baru jika ingin mengganti
                                    foto siswa. Format JPG, JPEG, PNG, atau
                                    WEBP. Maksimal 2 MB.

                                </div>

                            </div>


                        </div>

                    </div>


                    <!-- =================================================
                         PASSWORD
                    ================================================== -->

                    <div class="edit-form-group full">

                        <label
                            for="password"
                            class="edit-label"
                        >

                            Password Baru

                        </label>


                        <div class="edit-password-box">


                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="edit-input"
                                placeholder="Kosongkan jika password tidak ingin diubah"
                                minlength="6"
                            >


                            <button
                                type="button"
                                class="edit-password-toggle"
                                id="showPassword"
                                title="Tampilkan password"
                            >

                                <i class="bi bi-eye"></i>

                            </button>


                        </div>


                        <div class="edit-help">

                            Kosongkan jika password siswa
                            tidak ingin diubah.

                        </div>

                    </div>


                </div>


                <!-- =================================================
                     INFO
                ================================================== -->

                <div class="edit-info">

                    <i class="bi bi-info-circle-fill"></i>

                    <div>

                        <strong>Informasi:</strong>

                        Jika memilih foto baru, foto lama akan
                        otomatis diganti setelah perubahan berhasil
                        disimpan.

                    </div>

                </div>


                <!-- =================================================
                     FOOTER FORM
                ================================================== -->

                <div class="edit-card-footer">


                    <a
                        href="siswa.php"
                        class="btn-batal-edit"
                    >

                        <i class="bi bi-x-lg"></i>

                        Batal

                    </a>


                    <button
                        type="submit"
                        class="btn-update-edit"
                    >

                        <i class="bi bi-check-lg"></i>

                        Simpan Perubahan

                    </button>


                </div>


            </form>

        </div>

    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| PREVIEW FOTO
|--------------------------------------------------------------------------
*/

const fotoInput =
    document.getElementById('foto');

const previewFoto =
    document.getElementById('previewFoto');

const previewIcon =
    document.getElementById('previewIcon');

const namaFoto =
    document.getElementById('namaFoto');


if (fotoInput) {

    fotoInput.addEventListener(
        'change',
        function () {

            const file =
                this.files[0];


            /*
            | Tidak ada file
            */

            if (!file) {

                return;

            }


            /*
            | Cek ukuran
            */

            if (
                file.size >
                2 * 1024 * 1024
            ) {

                alert(
                    'Ukuran foto maksimal 2 MB.'
                );

                this.value = '';

                return;

            }


            /*
            | Cek tipe
            */

            const allowedTypes = [

                'image/jpeg',

                'image/png',

                'image/webp'

            ];


            if (
                !allowedTypes.includes(
                    file.type
                )
            ) {

                alert(
                    'Format foto harus JPG, JPEG, PNG, atau WEBP.'
                );

                this.value = '';

                return;

            }


            /*
            | Preview
            */

            const reader =
                new FileReader();


            reader.onload =
                function (e) {

                    previewFoto.src =
                        e.target.result;

                    previewFoto.style.display =
                        'block';

                    previewIcon.style.display =
                        'none';


                    if (namaFoto) {

                        namaFoto.innerHTML =
                            '<i class="bi bi-image me-1"></i>' +
                            'Foto baru: ' +
                            file.name;

                    }

                };


            reader.readAsDataURL(file);

        }
    );

}


/*
|--------------------------------------------------------------------------
| SHOW / HIDE PASSWORD
|--------------------------------------------------------------------------
*/

const password =
    document.getElementById(
        'password'
    );

const button =
    document.getElementById(
        'showPassword'
    );


if (
    password &&
    button
) {

    button.addEventListener(
        'click',
        function () {

            if (
                password.type ===
                'password'
            ) {

                password.type =
                    'text';

                button.innerHTML =
                    '<i class="bi bi-eye-slash"></i>';

                button.title =
                    'Sembunyikan password';

            } else {

                password.type =
                    'password';

                button.innerHTML =
                    '<i class="bi bi-eye"></i>';

                button.title =
                    'Tampilkan password';

            }

        }
    );

}

</script>


<?php

require_once __DIR__ . '/layout/footer.php';

?>