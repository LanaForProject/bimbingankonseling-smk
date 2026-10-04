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

/*
 * |--------------------------------------------------------------------------
 * | VARIABEL
 * |--------------------------------------------------------------------------
 */
$error = '';
$success = '';

$nis = '';
$nama = '';
$kelas = '';
$jurusan = '';

/*
 * |--------------------------------------------------------------------------
 * | PROSES TAMBAH SISWA
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
     * | VALIDASI
     * |--------------------------------------------------------------------------
     */
    if ($nis === '' || $nama === '' || $kelas === '' || $jurusan === '' || $password === '') {
        $error = 'Semua data wajib diisi, termasuk password akun siswa.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        /*
         * |--------------------------------------------------------------------------
         * | CEK NIS SUDAH ADA ATAU BELUM
         * |--------------------------------------------------------------------------
         */
        $cek = mysqli_prepare(
            $conn,
            'SELECT id_siswa FROM siswa WHERE nis = ? LIMIT 1'
        );

        mysqli_stmt_bind_param($cek, 's', $nis);
        mysqli_stmt_execute($cek);

        $hasil_cek = mysqli_stmt_get_result($cek);
        $sudah_ada = mysqli_fetch_assoc($hasil_cek);

        mysqli_stmt_close($cek);

        if ($sudah_ada) {
            $error = 'NIS tersebut sudah terdaftar. Gunakan NIS lain.';
        } else {
            /*
             * |--------------------------------------------------------------------------
             * | UPLOAD FOTO
             * |--------------------------------------------------------------------------
             */
            $foto_db = null;
            $foto_baru = null;

            if (
                isset($_FILES['foto']) &&
                $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Terjadi kesalahan saat mengupload foto.';
                } else {
                    $nama_file = $_FILES['foto']['name'];
                    $tmp_file = $_FILES['foto']['tmp_name'];
                    $ukuran = $_FILES['foto']['size'];

                    $ext = strtolower(
                        pathinfo($nama_file, PATHINFO_EXTENSION)
                    );

                    $ext_diizinkan = [
                        'jpg',
                        'jpeg',
                        'png',
                        'webp'
                    ];

                    /*
                     * |--------------------------------------------------------------------------
                     * | CEK EKSTENSI
                     * |--------------------------------------------------------------------------
                     */
                    if (!in_array($ext, $ext_diizinkan, true)) {
                        $error = 'Format foto harus JPG, JPEG, PNG, atau WEBP.';

                        /*
                         * |--------------------------------------------------------------------------
                         * | CEK UKURAN
                         * |--------------------------------------------------------------------------
                         */
                    } elseif ($ukuran > 2 * 1024 * 1024) {
                        $error = 'Ukuran foto maksimal 2 MB.';

                        /*
                         * |--------------------------------------------------------------------------
                         * | CEK FILE BENAR-BENAR GAMBAR
                         * |--------------------------------------------------------------------------
                         */
                    } elseif (@getimagesize($tmp_file) === false) {
                        $error = 'File yang diupload bukan gambar yang valid.';
                    } else {
                        /*
                         * |--------------------------------------------------------------------------
                         * | FOLDER UPLOAD
                         * |--------------------------------------------------------------------------
                         */
                        $folder_upload = __DIR__ . '/uploads/siswa/';

                        if (!is_dir($folder_upload)) {
                            mkdir($folder_upload, 0777, true);
                        }

                        /*
                         * |--------------------------------------------------------------------------
                         * | BUAT NAMA FILE UNIK
                         * |--------------------------------------------------------------------------
                         */
                        $foto_baru =
                            'siswa_'
                            . date('YmdHis')
                            . '_'
                            . uniqid()
                            . '.'
                            . $ext;

                        $tujuan = $folder_upload . $foto_baru;

                        /*
                         * |--------------------------------------------------------------------------
                         * | PINDAHKAN FILE
                         * |--------------------------------------------------------------------------
                         */
                        if (move_uploaded_file($tmp_file, $tujuan)) {
                            $foto_db = $foto_baru;
                        } else {
                            $error = 'Foto gagal diupload.';
                        }
                    }
                }
            }

            /*
             * |--------------------------------------------------------------------------
             * | JIKA TIDAK ADA ERROR, SIMPAN SISWA
             * |--------------------------------------------------------------------------
             */
            if ($error === '') {
                /*
                 * |--------------------------------------------------------------------------
                 * | HASH PASSWORD
                 * |--------------------------------------------------------------------------
                 * |
                 * | Password asli tidak disimpan langsung ke database.
                 * |
                 */
                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                /*
                 * |--------------------------------------------------------------------------
                 * | INSERT DATA SISWA
                 * |--------------------------------------------------------------------------
                 */
                $sql = '
                    INSERT INTO siswa
                    (
                        nis,
                        nama,
                        kelas,
                        jurusan,
                        foto,
                        password
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ';

                $stmt = mysqli_prepare($conn, $sql);

                if ($stmt) {
                    mysqli_stmt_bind_param(
                        $stmt,
                        'ssssss',
                        $nis,
                        $nama,
                        $kelas,
                        $jurusan,
                        $foto_db,
                        $password_hash
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);

                        /*
                         * |--------------------------------------------------------------------------
                         * | REDIRECT SETELAH BERHASIL
                         * |--------------------------------------------------------------------------
                         */
                        header('Location: siswa.php?success=tambah');
                        exit;
                    } else {
                        /*
                         * |--------------------------------------------------------------------------
                         * | JIKA INSERT GAGAL, HAPUS FOTO YANG SUDAH DIUPLOAD
                         * |--------------------------------------------------------------------------
                         */
                        if (
                            $foto_baru !== null &&
                            file_exists($folder_upload . $foto_baru)
                        ) {
                            unlink($folder_upload . $foto_baru);
                        }

                        $error = 'Data siswa gagal disimpan: ' . mysqli_error($conn);

                        mysqli_stmt_close($stmt);
                    }
                } else {
                    /*
                     * |--------------------------------------------------------------------------
                     * | JIKA PREPARE GAGAL
                     * |--------------------------------------------------------------------------
                     */
                    if (
                        $foto_baru !== null &&
                        file_exists($folder_upload . $foto_baru)
                    ) {
                        unlink($folder_upload . $foto_baru);
                    }

                    $error = 'Query database gagal diproses.';
                }
            }
        }
    }
}

require_once __DIR__ . '/layout/header.php';
?>

<style>

/* =========================================================
   PAGE
========================================================= */

.siswa-tambah-page {
    padding-bottom: 30px;
}

/* =========================================================
   HEADER
========================================================= */

.siswa-tambah-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}

.siswa-tambah-title {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--text-main);
}

.siswa-tambah-subtitle {
    margin: 5px 0 0;
    font-size: 14px;
    color: var(--text-muted);
}

/* =========================================================
   BUTTON KEMBALI
========================================================= */

.btn-kembali {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 17px;

    background: var(--bg-card);
    color: var(--text-main);

    border: 1px solid var(--border-color);
    border-radius: 10px;

    text-decoration: none;
    font-size: 14px;
    font-weight: 600;

    transition: .2s ease;
}

.btn-kembali:hover {
    background: var(--bg-body);
    color: var(--text-main);
}

/* =========================================================
   CARD
========================================================= */

.siswa-form-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(0,0,0,.04);
    overflow: hidden;
}

.siswa-form-header {
    padding: 18px 22px;
    border-bottom: 1px solid var(--border-color);
}

.siswa-form-header h5 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: var(--text-main);
}

.siswa-form-header p {
    margin: 5px 0 0;
    font-size: 13px;
    color: var(--text-muted);
}

.siswa-form-body {
    padding: 24px 22px;
}

/* =========================================================
   FORM
========================================================= */

.form-label {
    color: var(--text-main);
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 7px;
}

.form-control,
.form-select {
    min-height: 44px;
    background: var(--bg-card);
    color: var(--text-main);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 10px 13px;
}

.form-control:focus,
.form-select:focus {
    background: var(--bg-card);
    color: var(--text-main);
    border-color: #0d6efd;
    box-shadow: 0 0 0 .2rem rgba(13,110,253,.12);
}

.form-control::placeholder {
    color: var(--text-muted);
}

.form-text {
    color: var(--text-muted);
    font-size: 12px;
}

/* =========================================================
   PASSWORD
========================================================= */

.password-wrapper {
    position: relative;
}

.password-wrapper .form-control {
    padding-right: 45px;
}

.btn-show-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);

    border: none;
    background: transparent;

    color: var(--text-muted);

    width: 32px;
    height: 32px;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;
}

.btn-show-password:hover {
    color: #0d6efd;
}

/* =========================================================
   FOTO
========================================================= */

.photo-preview-wrapper {
    margin-top: 12px;
    display: none;
}

.photo-preview {
    width: 110px;
    height: 110px;

    border-radius: 50%;
    object-fit: cover;

    border: 3px solid rgba(13,110,253,.15);
}

.photo-placeholder {
    width: 110px;
    height: 110px;

    border-radius: 50%;

    background: rgba(13,110,253,.1);
    color: #0d6efd;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 40px;

    border: 3px solid rgba(13,110,253,.15);
}

/* =========================================================
   INFO AKUN
========================================================= */

.akun-info {
    margin-top: 22px;

    padding: 15px 17px;

    border-radius: 12px;

    background: rgba(13,110,253,.07);
    border: 1px solid rgba(13,110,253,.12);
}

.akun-info-title {
    display: flex;
    align-items: center;
    gap: 8px;

    margin-bottom: 7px;

    color: #0d6efd;

    font-size: 14px;
    font-weight: 700;
}

.akun-info-text {
    margin: 0;

    color: var(--text-muted);

    font-size: 13px;
    line-height: 1.6;
}

/* =========================================================
   BUTTON
========================================================= */

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;

    margin-top: 25px;
    padding-top: 20px;

    border-top: 1px solid var(--border-color);
}

.btn-batal {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    padding: 10px 18px;

    border-radius: 10px;

    background: var(--bg-body);
    color: var(--text-main);

    border: 1px solid var(--border-color);

    text-decoration: none;

    font-size: 14px;
    font-weight: 600;
}

.btn-batal:hover {
    background: var(--bg-card);
    color: var(--text-main);
}

.btn-simpan {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    padding: 10px 18px;

    border: none;
    border-radius: 10px;

    background: #0d6efd;
    color: white;

    font-size: 14px;
    font-weight: 600;

    cursor: pointer;
    transition: .2s ease;
}

.btn-simpan:hover {
    background: #0b5ed7;
}

/* =========================================================
   ALERT
========================================================= */

.alert {
    border-radius: 12px;
    font-size: 14px;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .siswa-tambah-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .siswa-tambah-title {
        font-size: 24px;
    }

    .siswa-form-body {
        padding: 20px 16px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-batal,
    .btn-simpan {
        width: 100%;
    }
}

</style>


<div class="siswa-tambah-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="siswa-tambah-header">

        <div>
            <h1 class="siswa-tambah-title">
                Tambah Siswa
            </h1>

            <p class="siswa-tambah-subtitle">
                Tambahkan data siswa sekaligus membuat akun login siswa.
            </p>
        </div>

        <a href="siswa.php" class="btn-kembali">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>

    </div>


    <!-- =====================================================
         ALERT ERROR
    ====================================================== -->

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill"></i>

            <div>
                <?= htmlspecialchars($error); ?>
            </div>
        </div>

    <?php endif; ?>


    <!-- =====================================================
         FORM CARD
    ====================================================== -->

    <div class="siswa-form-card">

        <div class="siswa-form-header">

            <h5>
                <i class="bi bi-person-plus me-2"></i>
                Data Siswa
            </h5>

            <p>
                Isi data siswa dengan lengkap.
            </p>

        </div>


        <div class="siswa-form-body">

            <form
                method="POST"
                enctype="multipart/form-data"
                autocomplete="off"
            >

                <div class="row g-4">

                    <!-- =================================================
                         NIS
                    ================================================== -->

                    <div class="col-md-6">

                        <label for="nis" class="form-label">
                            NIS
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="nis"
                            name="nis"
                            value="<?= htmlspecialchars($nis); ?>"
                            placeholder="Masukkan NIS"
                            maxlength="20"
                            required
                        >

                        <div class="form-text">
                            NIS digunakan sebagai username saat siswa login.
                        </div>

                    </div>


                    <!-- =================================================
                         NAMA
                    ================================================== -->

                    <div class="col-md-6">

                        <label for="nama" class="form-label">
                            Nama Lengkap
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="nama"
                            name="nama"
                            value="<?= htmlspecialchars($nama); ?>"
                            placeholder="Masukkan nama lengkap"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- =================================================
                         KELAS
                    ================================================== -->

                    <div class="col-md-6">

                        <label for="kelas" class="form-label">
                            Kelas
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="kelas"
                            name="kelas"
                            value="<?= htmlspecialchars($kelas); ?>"
                            placeholder="Contoh: X RPL 1"
                            maxlength="20"
                            required
                        >

                    </div>


                    <!-- =================================================
                         JURUSAN
                    ================================================== -->

                    <div class="col-md-6">

                        <label for="jurusan" class="form-label">
                            Jurusan
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="jurusan"
                            name="jurusan"
                            value="<?= htmlspecialchars($jurusan); ?>"
                            placeholder="Contoh: Rekayasa Perangkat Lunak"
                            maxlength="50"
                            required
                        >

                    </div>


                    <!-- =================================================
                         FOTO
                    ================================================== -->

                    <div class="col-md-6">

                        <label for="foto" class="form-label">
                            Foto Siswa
                        </label>

                        <input
                            type="file"
                            class="form-control"
                            id="foto"
                            name="foto"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >

                        <div class="form-text">
                            Format JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
                        </div>

                        <div id="photoPreviewWrapper" class="photo-preview-wrapper">

                            <img
                                id="photoPreview"
                                class="photo-preview"
                                src=""
                                alt="Preview Foto"
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         PASSWORD
                    ================================================== -->

                    <div class="col-md-6">

                        <label for="password" class="form-label">
                            Password Akun
                            <span class="text-danger">*</span>
                        </label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Minimal 6 karakter"
                                minlength="6"
                                required
                            >

                            <button
                                type="button"
                                class="btn-show-password"
                                id="togglePassword"
                                title="Tampilkan password"
                            >
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>

                        <div class="form-text">
                            Password ini digunakan siswa untuk login.
                        </div>

                    </div>

                </div>


                <!-- =====================================================
                     INFO AKUN
                ====================================================== -->

                <div class="akun-info">

                    <div class="akun-info-title">

                        <i class="bi bi-shield-lock-fill"></i>

                        Akun Login Siswa

                    </div>

                    <p class="akun-info-text">

                        Setelah data disimpan, siswa otomatis memiliki akun.
                        <strong>NIS</strong> digunakan sebagai username dan
                        <strong>password</strong> yang kamu masukkan digunakan
                        untuk login.

                    </p>

                </div>


                <!-- =====================================================
                     ACTION
                ====================================================== -->

                <div class="form-actions">

                    <a
                        href="siswa.php"
                        class="btn-batal"
                    >
                        <i class="bi bi-x-lg"></i>
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn-simpan"
                    >
                        <i class="bi bi-person-plus-fill"></i>
                        Simpan Siswa
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| TOGGLE PASSWORD
|--------------------------------------------------------------------------
*/

const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

if (togglePassword && passwordInput) {

    togglePassword.addEventListener('click', function () {

        const icon = this.querySelector('i');

        if (passwordInput.type === 'password') {

            passwordInput.type = 'text';

            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');

            this.title = 'Sembunyikan password';

        } else {

            passwordInput.type = 'password';

            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');

            this.title = 'Tampilkan password';
        }

    });

}


/*
|--------------------------------------------------------------------------
| PREVIEW FOTO
|--------------------------------------------------------------------------
*/

const fotoInput = document.getElementById('foto');
const photoPreviewWrapper =
    document.getElementById('photoPreviewWrapper');
const photoPreview =
    document.getElementById('photoPreview');

if (fotoInput) {

    fotoInput.addEventListener('change', function () {

        const file = this.files[0];

        if (!file) {

            photoPreviewWrapper.style.display = 'none';
            photoPreview.src = '';

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | CEK UKURAN
        |--------------------------------------------------------------------------
        */

        if (file.size > 2 * 1024 * 1024) {

            alert('Ukuran foto maksimal 2 MB.');

            this.value = '';

            photoPreviewWrapper.style.display = 'none';
            photoPreview.src = '';

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | PREVIEW
        |--------------------------------------------------------------------------
        */

        const reader = new FileReader();

        reader.onload = function (e) {

            photoPreview.src = e.target.result;

            photoPreviewWrapper.style.display = 'block';

        };

        reader.readAsDataURL(file);

    });

}

</script>

<?php
require_once __DIR__ . '/layout/footer.php';
?>