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
| ID GURU
|--------------------------------------------------------------------------
*/
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: guru.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA GURU
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT
        id_guru,
        nip,
        nama,
        foto
    FROM guru_bk
    WHERE id_guru = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

$guru = $result->fetch_assoc();

$stmt->close();


if (!$guru) {
    header("Location: guru.php");
    exit;
}


$error = '';

/*
|--------------------------------------------------------------------------
| PROSES EDIT
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nip === '' || $nama === '') {

        $error = "NIP dan nama wajib diisi.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | CEK DUPLIKAT NIP
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT id_guru
            FROM guru_bk
            WHERE nip = ?
            AND id_guru != ?
            LIMIT 1
        ");

        $stmt->bind_param("si", $nip, $id);
        $stmt->execute();

        $duplicate = $stmt->get_result();

        if ($duplicate->num_rows > 0) {

            $error = "NIP sudah digunakan oleh Guru BK lain.";

            $stmt->close();

        } else {

            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | FOTO LAMA
            |--------------------------------------------------------------------------
            */

            $foto_lama = trim($guru['foto'] ?? '');

            $foto_baru = $foto_lama;

            $foto_diubah = false;


            /*
            |--------------------------------------------------------------------------
            | UPLOAD FOTO BARU
            |--------------------------------------------------------------------------
            */

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


                    if (!in_array($extension, $allowed_extensions, true)) {

                        $error = "Format foto harus JPG, JPEG, PNG, atau WEBP.";

                    } elseif ($file_size > 2 * 1024 * 1024) {

                        $error = "Ukuran foto maksimal 2 MB.";

                    } elseif (@getimagesize($tmp_name) === false) {

                        $error = "File yang diupload bukan gambar yang valid.";

                    } else {

                        $upload_dir = __DIR__ . "/uploads/guru/";

                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }

                        $foto_baru =
                            'guru_' .
                            date('YmdHis') .
                            '_' .
                            bin2hex(random_bytes(4)) .
                            '.' .
                            $extension;

                        $destination =
                            $upload_dir . $foto_baru;


                        if (move_uploaded_file($tmp_name, $destination)) {

                            $foto_diubah = true;

                        } else {

                            $error = "Foto baru gagal disimpan.";

                            $foto_baru = $foto_lama;
                        }
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE DATABASE
            |--------------------------------------------------------------------------
            */

            if ($error === '') {

                if ($password !== '') {

                    $password_hash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $stmt = $conn->prepare("
                        UPDATE guru_bk
                        SET
                            nip = ?,
                            nama = ?,
                            foto = ?,
                            password = ?
                        WHERE id_guru = ?
                    ");

                    $stmt->bind_param(
                        "ssssi",
                        $nip,
                        $nama,
                        $foto_baru,
                        $password_hash,
                        $id
                    );

                } else {

                    $stmt = $conn->prepare("
                        UPDATE guru_bk
                        SET
                            nip = ?,
                            nama = ?,
                            foto = ?
                        WHERE id_guru = ?
                    ");

                    $stmt->bind_param(
                        "sssi",
                        $nip,
                        $nama,
                        $foto_baru,
                        $id
                    );
                }


                if ($stmt->execute()) {

                    $stmt->close();


                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS FOTO LAMA
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $foto_diubah &&
                        $foto_lama !== '' &&
                        $foto_lama !== $foto_baru
                    ) {

                        $foto_lama_path =
                            __DIR__ .
                            "/uploads/guru/" .
                            $foto_lama;

                        if (file_exists($foto_lama_path)) {
                            unlink($foto_lama_path);
                        }
                    }


                    header(
                        "Location: guru.php?success=edit"
                    );

                    exit;

                } else {

                    $stmt->close();


                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS FOTO BARU JIKA UPDATE GAGAL
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $foto_diubah &&
                        $foto_baru !== $foto_lama
                    ) {

                        $foto_baru_path =
                            __DIR__ .
                            "/uploads/guru/" .
                            $foto_baru;

                        if (file_exists($foto_baru_path)) {
                            unlink($foto_baru_path);
                        }
                    }

                    $error = "Data Guru BK gagal diperbarui.";
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| FOTO SAAT INI
|--------------------------------------------------------------------------
*/

$foto_lama = trim($guru['foto'] ?? '');

$foto_ada = false;

if ($foto_lama !== '') {

    $foto_path =
        __DIR__ .
        "/uploads/guru/" .
        $foto_lama;

    if (file_exists($foto_path)) {
        $foto_ada = true;
    }
}


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

.foto-section {
    display: flex;
    align-items: center;

    gap: 18px;

    margin-top: 10px;
}

.foto-current {
    width: 110px;
    height: 110px;

    flex-shrink: 0;

    border-radius: 50%;

    object-fit: cover;

    border: 3px solid rgba(13,110,253,.12);
}

.foto-placeholder {
    width: 110px;
    height: 110px;

    flex-shrink: 0;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    background: rgba(13,110,253,.08);

    color: #0d6efd;

    font-size: 42px;

    border: 3px solid rgba(13,110,253,.12);
}

.foto-new-preview {
    width: 110px;
    height: 110px;

    border-radius: 50%;

    object-fit: cover;

    border: 3px solid rgba(25,135,84,.25);

    display: none;
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

    .foto-section {
        flex-direction: column;

        align-items: flex-start;
    }

    .form-actions {
        flex-direction: column;
    }

}

</style>


<div class="guru-form-page">

    <div class="guru-form-header">

        <h1 class="guru-form-title">
            Edit Guru BK
        </h1>

        <p class="guru-form-subtitle">
            Perbarui data dan foto Guru BK
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
                            value="<?= htmlspecialchars($guru['nip']); ?>"
                            required
                        >

                    </div>


                    <!-- NAMA -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            value="<?= htmlspecialchars($guru['nama']); ?>"
                            required
                        >

                    </div>


                    <!-- FOTO -->

                    <div class="col-12">

                        <label class="form-label">
                            Foto Guru
                        </label>

                        <div class="foto-section">

                            <?php if ($foto_ada): ?>

                                    <img
                                        src="uploads/guru/<?= htmlspecialchars($foto_lama); ?>"
                                        class="foto-current"
                                        id="fotoCurrent"
                                        alt="Foto <?= htmlspecialchars($guru['nama']); ?>"
                                    >

                            <?php else: ?>

                                    <div
                                        class="foto-placeholder"
                                        id="fotoCurrent"
                                    >
                                        <i class="bi bi-person-badge-fill"></i>
                                    </div>

                            <?php endif; ?>


                            <img
                                src=""
                                id="fotoNewPreview"
                                class="foto-new-preview"
                                alt="Preview Foto Baru"
                            />

                        </div>


                        <input
                            type="file"
                            name="foto"
                            id="foto"
                            class="form-control mt-3"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <div class="form-text">
                            Pilih foto baru jika ingin mengganti foto.
                            JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
                        </div>

                    </div>


                    <!-- PASSWORD -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Password Baru
                        </label>

                        <div class="input-group">

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Kosongkan jika tidak ingin mengubah"
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
                            Kosongkan jika password tidak ingin diubah.
                        </div>

                    </div>

                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn-simpan"
                    >
                        <i class="bi bi-save me-1"></i>
                        Simpan Perubahan
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
| PREVIEW FOTO BARU
|--------------------------------------------------------------------------
*/

document.getElementById('foto').addEventListener('change', function () {

    const file = this.files[0];

    const preview =
        document.getElementById('fotoNewPreview');

    if (!file) {

        preview.style.display = 'none';

        preview.src = '';

        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {

        preview.src = event.target.result;

        preview.style.display = 'block';

    };

    reader.readAsDataURL(file);

});


/*
|--------------------------------------------------------------------------
| TOGGLE PASSWORD
|--------------------------------------------------------------------------
*/

document.getElementById('togglePassword').addEventListener('click', function () {

    const password =
        document.getElementById('password');

    const icon =
        this.querySelector('i');

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