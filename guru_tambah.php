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

$error = '';

/*
|--------------------------------------------------------------------------
| PROSES TAMBAH
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    if ($nip === '' || $nama === '' || $password === '') {

        $error = "NIP, nama, dan password wajib diisi.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | CEK NIP
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT id_guru
            FROM guru_bk
            WHERE nip = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $nip);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = "NIP sudah terdaftar.";

            $stmt->close();

        } else {

            $stmt->close();

            /*
            |--------------------------------------------------------------------------
            | FOTO
            |--------------------------------------------------------------------------
            */

            $nama_file_foto = '';

            if (
                isset($_FILES['foto']) &&
                $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {

                    $error = "Foto gagal diupload.";

                } else {

                    $tmp_name = $_FILES['foto']['tmp_name'];
                    $file_name = $_FILES['foto']['name'];
                    $file_size = $_FILES['foto']['size'];

                    $extension = strtolower(
                        pathinfo($file_name, PATHINFO_EXTENSION)
                    );

                    $allowed_extensions = [
                        'jpg',
                        'jpeg',
                        'png',
                        'webp'
                    ];

                    /*
                    |--------------------------------------------------------------------------
                    | CEK EXTENSION
                    |--------------------------------------------------------------------------
                    */

                    if (!in_array($extension, $allowed_extensions, true)) {

                        $error = "Format foto harus JPG, JPEG, PNG, atau WEBP.";

                        /*
                        |--------------------------------------------------------------------------
                        | CEK UKURAN
                        |--------------------------------------------------------------------------
                        */

                    } elseif ($file_size > 2 * 1024 * 1024) {

                        $error = "Ukuran foto maksimal 2 MB.";

                        /*
                        |--------------------------------------------------------------------------
                        | CEK FILE GAMBAR
                        |--------------------------------------------------------------------------
                        */

                    } elseif (@getimagesize($tmp_name) === false) {

                        $error = "File yang diupload bukan gambar yang valid.";

                    } else {

                        $upload_dir = __DIR__ . "/uploads/guru/";

                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }

                        $nama_file_foto =
                            'guru_' .
                            date('YmdHis') .
                            '_' .
                            bin2hex(random_bytes(4)) .
                            '.' .
                            $extension;

                        $destination =
                            $upload_dir . $nama_file_foto;

                        if (!move_uploaded_file($tmp_name, $destination)) {

                            $error = "Foto gagal disimpan.";

                            $nama_file_foto = '';
                        }
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATA
            |--------------------------------------------------------------------------
            */

            if ($error === '') {

                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $conn->prepare("
                    INSERT INTO guru_bk
                    (nip, nama, foto, password)
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "ssss",
                    $nip,
                    $nama,
                    $nama_file_foto,
                    $password_hash
                );

                if ($stmt->execute()) {

                    $stmt->close();

                    header("Location: guru.php?success=tambah");
                    exit;

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS FOTO JIKA DATABASE GAGAL
                    |--------------------------------------------------------------------------
                    */

                    if ($nama_file_foto !== '') {

                        $file_path =
                            __DIR__ .
                            "/uploads/guru/" .
                            $nama_file_foto;

                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }

                    $error = "Data Guru BK gagal disimpan.";

                    $stmt->close();
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/layout/header.php";

?>

<style>

.guru-form-page {
    padding-bottom: 30px;
}

.guru-form-header {
    margin-bottom: 24px;
}

.guru-form-title {
    margin: 0;

    font-size: 28px;
    font-weight: 700;

    color: var(--text-main);
}

.guru-form-subtitle {
    margin: 4px 0 0;

    font-size: 14px;

    color: var(--text-muted);
}

.guru-form-card {
    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    box-shadow: 0 4px 18px rgba(0,0,0,.04);

    overflow: hidden;
}

.guru-form-body {
    padding: 28px;
}

.form-label {
    color: var(--text-main);

    font-size: 14px;

    font-weight: 600;

    margin-bottom: 7px;
}

.form-control {
    background: var(--bg-card);

    color: var(--text-main);

    border: 1px solid var(--border-color);

    border-radius: 10px;

    padding: 11px 13px;
}

.form-control:focus {
    background: var(--bg-card);

    color: var(--text-main);

    border-color: #0d6efd;

    box-shadow: 0 0 0 .2rem rgba(13,110,253,.12);
}

.form-text {
    color: var(--text-muted);
}

.foto-preview-wrapper {
    margin-top: 12px;
}

.foto-preview {
    width: 110px;
    height: 110px;

    border-radius: 50%;

    object-fit: cover;

    border: 3px solid rgba(13,110,253,.12);

    display: none;
}

.foto-placeholder {
    width: 110px;
    height: 110px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    background: rgba(13,110,253,.08);

    color: #0d6efd;

    font-size: 42px;

    border: 3px solid rgba(13,110,253,.12);
}

.form-actions {
    display: flex;

    gap: 10px;

    margin-top: 25px;

    padding-top: 22px;

    border-top: 1px solid var(--border-color);
}

.btn-simpan {
    border: none;

    background: #0d6efd;

    color: white;

    padding: 10px 18px;

    border-radius: 10px;

    font-weight: 600;
}

.btn-simpan:hover {
    background: #0b5ed7;
    color: white;
}

.btn-batal {
    display: inline-flex;
    align-items: center;

    padding: 10px 18px;

    border-radius: 10px;

    background: var(--bg-body);

    color: var(--text-main);

    border: 1px solid var(--border-color);

    text-decoration: none;

    font-weight: 600;
}

.btn-batal:hover {
    color: var(--text-main);
}

@media (max-width: 768px) {

    .guru-form-body {
        padding: 20px;
    }

    .guru-form-title {
        font-size: 24px;
    }

    .form-actions {
        flex-direction: column;
    }

}

</style>


<div class="guru-form-page">

    <div class="guru-form-header">

        <h1 class="guru-form-title">
            Tambah Guru BK
        </h1>

        <p class="guru-form-subtitle">
            Tambahkan data Guru BK baru ke dalam sistem
        </p>

    </div>


    <?php if ($error): ?>

            <div class="alert alert-danger rounded-3">

                <i class="bi bi-exclamation-triangle me-2"></i>

                <?= htmlspecialchars($error); ?>

            </div>

    <?php endif; ?>


    <div class="guru-form-card">

        <div class="guru-form-body">

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="row g-4">

                    <!-- NIP -->

                    <div class="col-md-6">

                        <label class="form-label">
                            NIP
                        </label>

                        <input
                            type="text"
                            name="nip"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['nip'] ?? ''); ?>"
                            placeholder="Masukkan NIP"
                            required
                        >

                    </div>


                    <!-- Nama -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>"
                            placeholder="Masukkan nama lengkap"
                            required
                        >

                    </div>


                    <!-- FOTO -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Foto Guru
                        </label>

                        <input
                            type="file"
                            name="foto"
                            id="foto"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <div class="form-text">
                            JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
                        </div>

                        <div class="foto-preview-wrapper">

                            <div
                                class="foto-placeholder"
                                id="fotoPlaceholder"
                            >
                                <i class="bi bi-person-badge-fill"></i>
                            </div>

                            <img
                                src=""
                                id="fotoPreview"
                                class="foto-preview"
                                alt="Preview Foto"
                            >

                        </div>

                    </div>


                    <!-- PASSWORD -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Password
                        </label>

                        <div class="input-group">

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Masukkan password"
                                required
                            >

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                id="togglePassword"
                            >
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>

                        <div class="form-text">
                            Password digunakan untuk login Guru BK.
                        </div>

                    </div>

                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn-simpan"
                    >
                        <i class="bi bi-save me-1"></i>
                        Simpan Guru
                    </button>

                    <a
                        href="guru.php"
                        class="btn-batal"
                    >
                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali
                    </a>

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

document.getElementById('foto').addEventListener('change', function () {

    const file = this.files[0];

    const preview = document.getElementById('fotoPreview');

    const placeholder = document.getElementById('fotoPlaceholder');

    if (!file) {

        preview.style.display = 'none';

        placeholder.style.display = 'flex';

        preview.src = '';

        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {

        preview.src = event.target.result;

        preview.style.display = 'block';

        placeholder.style.display = 'none';

    };

    reader.readAsDataURL(file);

});


/*
|--------------------------------------------------------------------------
| TOGGLE PASSWORD
|--------------------------------------------------------------------------
*/

document.getElementById('togglePassword').addEventListener('click', function () {

    const password = document.getElementById('password');

    const icon = this.querySelector('i');

    if (password.type === 'password') {

        password.type = 'text';

        icon.classList.remove('bi-eye');

        icon.classList.add('bi-eye-slash');

    } else {

        password.type = 'password';

        icon.classList.remove('bi-eye-slash');

        icon.classList.add('bi-eye');

    }

});

</script>


<?php

require_once __DIR__ . "/layout/footer.php";

?>