<?php

session_start();

require_once "config/database.php";


/*
|--------------------------------------------------------------------------
| CEK LOGIN SISWA
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'siswa'
) {
    header("Location: login.php");
    exit;
}


$siswa_id = (int) ($_SESSION['siswa_id'] ?? 0);


if ($siswa_id <= 0) {

    $_SESSION = [];

    session_destroy();

    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| DATA SISWA
|--------------------------------------------------------------------------
*/

$stmt_siswa = $conn->prepare("
    SELECT
        id_siswa,
        nis,
        nama,
        kelas,
        jurusan,
        foto
    FROM siswa
    WHERE id_siswa = ?
    LIMIT 1
");

$stmt_siswa->bind_param(
    "i",
    $siswa_id
);

$stmt_siswa->execute();

$result_siswa = $stmt_siswa->get_result();

$siswa = $result_siswa->fetch_assoc();

$stmt_siswa->close();


if (!$siswa) {

    $_SESSION = [];

    session_destroy();

    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| DATA GURU BK
|--------------------------------------------------------------------------
*/

$guru_list = [];


$result_guru = $conn->query("
    SELECT
        id_guru,
        nip,
        nama,
        foto
    FROM guru_bk
    ORDER BY nama ASC
");


if ($result_guru) {

    while ($row = $result_guru->fetch_assoc()) {

        $guru_list[] = $row;

    }

}


/*
|--------------------------------------------------------------------------
| DEFAULT FORM
|--------------------------------------------------------------------------
*/

$id_guru = '';

$tanggal_input = '';

$masalah = '';

$error = '';


/*
|--------------------------------------------------------------------------
| PROSES FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_guru = (int) ($_POST['id_guru'] ?? 0);

    $tanggal_input = trim(
        $_POST['tanggal'] ?? ''
    );

    $masalah = trim(
        $_POST['masalah'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDASI GURU
    |--------------------------------------------------------------------------
    */

    if ($id_guru <= 0) {

        $error =
            "Silakan pilih Guru BK terlebih dahulu.";

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
    |--------------------------------------------------------------------------
    */ elseif ($tanggal_input === '') {

        $error =
            "Silakan pilih tanggal dan jam konseling.";

    } else {

        $tanggal_obj = DateTime::createFromFormat(
            'Y-m-d\TH:i',
            $tanggal_input
        );

        $tanggal_errors = DateTime::getLastErrors();


        if (
            !$tanggal_obj ||
            (
                $tanggal_errors !== false &&
                (
                    $tanggal_errors['warning_count'] > 0 ||
                    $tanggal_errors['error_count'] > 0
                )
            ) ||
            $tanggal_obj->format('Y-m-d\TH:i') !== $tanggal_input
        ) {

            $error =
                "Format tanggal dan jam konseling tidak valid.";

        } elseif (
            $tanggal_obj < new DateTime('now')
        ) {

            $error =
                "Tanggal dan jam konseling tidak boleh sebelum waktu sekarang.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI MASALAH
    |--------------------------------------------------------------------------
    */

    if (
        $error === '' &&
        $masalah === ''
    ) {

        $error =
            "Permasalahan wajib diisi.";

    } elseif (
        $error === '' &&
        strlen($masalah) < 10
    ) {

        $error =
            "Permasalahan minimal 10 karakter.";

    } elseif (
        $error === '' &&
        strlen($masalah) > 2000
    ) {

        $error =
            "Permasalahan maksimal 2000 karakter.";

    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $tanggal =
            $tanggal_obj->format(
                'Y-m-d H:i:s'
            );


        /*
        |----------------------------------------------------------------------
        | CEK GURU
        |----------------------------------------------------------------------
        */

        $stmt_guru = $conn->prepare("
            SELECT id_guru
            FROM guru_bk
            WHERE id_guru = ?
            LIMIT 1
        ");

        $stmt_guru->bind_param(
            "i",
            $id_guru
        );

        $stmt_guru->execute();

        $result_check =
            $stmt_guru->get_result();

        $guru_exists =
            $result_check->num_rows > 0;

        $stmt_guru->close();


        if (!$guru_exists) {

            $error =
                "Guru BK yang dipilih tidak ditemukan.";

        } else {

            /*
            |------------------------------------------------------------------
            | INSERT
            |------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                INSERT INTO konseling
                (
                    id_siswa,
                    id_guru,
                    masalah,
                    solusi,
                    tanggal,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    NULL,
                    ?,
                    'terjadwal'
                )
            ");

            $stmt->bind_param(
                "iiss",
                $siswa_id,
                $id_guru,
                $masalah,
                $tanggal
            );


            if ($stmt->execute()) {

                $stmt->close();

                header(
                    "Location: dashboard_siswa.php?success=konseling"
                );

                exit;

            }


            $error =
                "Pengajuan konseling gagal disimpan.";


            $stmt->close();

        }

    }

}


/*
|--------------------------------------------------------------------------
| JUDUL
|--------------------------------------------------------------------------
*/

$page_title = "Ajukan Konseling | BK Online";


/*
|--------------------------------------------------------------------------
| HEADER SISWA
|--------------------------------------------------------------------------
*/

require_once "layout/header_siswa.php";

?>


<style>

/* =========================================================
   KHUSUS HALAMAN KONSELING
   TIDAK MENGUBAH LAYOUT HEADER
========================================================= */

.konseling-wrapper {
    width: 100%;
    max-width: 920px;
    margin: 0 auto;
}


/* =========================================================
   JUDUL
========================================================= */

.konseling-heading {
    margin-bottom: 20px;
}

.konseling-heading h1 {
    margin: 0;
    padding: 0;
    font-size: 23px;
    line-height: 1.3;
    font-weight: 700;
    color: var(--text-main);
}

.konseling-heading p {
    margin: 6px 0 0;
    padding: 0;
    font-size: 11px;
    line-height: 1.5;
    color: var(--text-muted);
}


/* =========================================================
   CARD
========================================================= */

.konseling-box {
    width: 100%;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: var(--shadow);
}


/* =========================================================
   CARD HEADER
========================================================= */

.konseling-box-header {
    min-height: 66px;
    padding: 14px 18px;

    display: flex;
    align-items: center;
    gap: 11px;

    border-bottom: 1px solid var(--border-color);
}

.konseling-box-icon {
    width: 37px;
    height: 37px;
    min-width: 37px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 9px;

    background: rgba(13, 110, 253, .12);

    color: #4d91ff;

    font-size: 16px;
}

.konseling-box-title {
    margin: 0;

    color: var(--text-main);

    font-size: 13px;
    font-weight: 700;
}

.konseling-box-desc {
    margin: 3px 0 0;

    color: var(--text-muted);

    font-size: 9px;
}


/* =========================================================
   BODY
========================================================= */

.konseling-box-body {
    padding: 20px 18px;
}


/* =========================================================
   ALERT
========================================================= */

.konseling-error {
    display: flex;
    align-items: flex-start;
    gap: 8px;

    margin-bottom: 18px;
    padding: 10px 12px;

    border-radius: 8px;

    background: rgba(220, 53, 69, .10);

    border: 1px solid rgba(220, 53, 69, .25);

    color: #ff7b87;

    font-size: 9px;
    line-height: 1.5;
}

.konseling-error i {
    font-size: 11px;
    margin-top: 1px;
}


/* =========================================================
   FORM GROUP
========================================================= */

.konseling-group {
    margin-bottom: 18px;
}

.konseling-group:last-child {
    margin-bottom: 0;
}


/* =========================================================
   LABEL
========================================================= */

.konseling-label {
    display: block;

    margin-bottom: 7px;

    color: var(--text-main);

    font-size: 10px;

    font-weight: 700;
}

.konseling-required {
    color: #ff6675;
}


/* =========================================================
   INPUT
========================================================= */

.konseling-input,
.konseling-select,
.konseling-textarea {

    width: 100%;

    box-sizing: border-box;

    border: 1px solid var(--border-color);

    border-radius: 8px;

    outline: none;

    background: var(--bg-body);

    color: var(--text-main);

    font-family: Arial, Helvetica, sans-serif;

    font-size: 10px;

    transition: .2s ease;
}


.konseling-input,
.konseling-select {

    height: 41px;

    padding: 0 11px;
}


.konseling-textarea {

    min-height: 145px;

    padding: 11px;

    resize: vertical;

    line-height: 1.65;
}


.konseling-input::placeholder,
.konseling-textarea::placeholder {

    color: var(--text-muted);

}


.konseling-input:focus,
.konseling-select:focus,
.konseling-textarea:focus {

    border-color: #3985ff;

    box-shadow:
        0 0 0 3px rgba(57, 133, 255, .10);
}


.konseling-select {

    cursor: pointer;
}


html[data-theme="dark"]
.konseling-select option {

    background: #0c1837;

    color: #ffffff;
}


html[data-theme="light"]
.konseling-select option {

    background: #ffffff;

    color: #1f2937;
}


html[data-theme="dark"]
.konseling-input[type="datetime-local"] {

    color-scheme: dark;
}


html[data-theme="light"]
.konseling-input[type="datetime-local"] {

    color-scheme: light;
}


/* =========================================================
   HELP
========================================================= */

.konseling-help {

    display: block;

    margin-top: 5px;

    color: var(--text-muted);

    font-size: 8px;

    line-height: 1.5;
}


/* =========================================================
   COUNTER
========================================================= */

.konseling-counter {

    margin-top: 5px;

    text-align: right;

    color: var(--text-muted);

    font-size: 8px;
}


/* =========================================================
   ACTION
========================================================= */

.konseling-actions {

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 8px;

    margin-top: 20px;

    padding-top: 16px;

    border-top: 1px solid var(--border-color);
}


.konseling-button {

    height: 36px;

    padding: 0 14px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    border-radius: 8px;

    font-family: Arial, Helvetica, sans-serif;

    font-size: 9px;

    font-weight: 600;

    text-decoration: none;

    cursor: pointer;

    transition: .2s ease;
}


/* BATAL */

.konseling-cancel {

    color: var(--text-main);

    background: transparent;

    border: 1px solid var(--border-color);
}


.konseling-cancel:hover {

    background: rgba(127, 127, 127, .10);

    color: var(--text-main);
}


/* SUBMIT */

.konseling-submit {

    min-width: 135px;

    color: #ffffff;

    background: #1769e8;

    border: 1px solid #1769e8;
}


.konseling-submit:hover {

    color: #ffffff;

    background: #2879f5;

    border-color: #2879f5;

    transform: translateY(-1px);
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 800px) {

    .konseling-wrapper {

        max-width: 100%;

    }

}


@media (max-width: 550px) {

    .konseling-box-body {

        padding: 16px 14px;

    }

    .konseling-box-header {

        padding: 13px 14px;

    }

    .konseling-heading h1 {

        font-size: 20px;

    }

    .konseling-actions {

        flex-direction: column-reverse;

        align-items: stretch;

    }

    .konseling-button {

        width: 100%;

    }

}

</style>


<!-- =========================================================
     KONTEN HALAMAN
     
     PENTING:
     header_siswa.php SUDAH MEMBUKA:
     
     <main class="student-main">
     <section class="student-content">
     
     Jadi di sini JANGAN membuat main/student-content lagi.
========================================================= -->


<div class="konseling-wrapper">


    <!-- =====================================================
         JUDUL
    ====================================================== -->

    <div class="konseling-heading">

        <h1>
            Ajukan Konseling
        </h1>

        <p>
            Sampaikan masalah atau cerita yang ingin kamu konsultasikan kepada Guru BK.
        </p>

    </div>


    <!-- =====================================================
         CARD
    ====================================================== -->

    <div class="konseling-box">


        <!-- HEADER CARD -->

        <div class="konseling-box-header">

            <div class="konseling-box-icon">

                <i class="bi bi-chat-square-text-fill"></i>

            </div>


            <div>

                <h3 class="konseling-box-title">
                    Form Pengajuan Konseling
                </h3>

                <p class="konseling-box-desc">
                    Isi data dengan lengkap dan jelas.
                </p>

            </div>

        </div>


        <!-- BODY -->

        <div class="konseling-box-body">


            <?php if ($error !== ''): ?>

                    <div class="konseling-error">

                        <i class="bi bi-exclamation-circle-fill"></i>

                        <span>
                            <?= htmlspecialchars($error); ?>
                        </span>

                    </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                action=""
                autocomplete="off"
            >


                <!-- =================================================
                     GURU BK
                ================================================== -->

                <div class="konseling-group">


                    <label
                        for="id_guru"
                        class="konseling-label"
                    >

                        Guru BK

                        <span class="konseling-required">
                            *
                        </span>

                    </label>


                    <select
                        name="id_guru"
                        id="id_guru"
                        class="konseling-select"
                        required
                    >

                        <option value="">
                            Pilih Guru BK
                        </option>


                        <?php foreach ($guru_list as $guru): ?>

                                <option
                                    value="<?= (int) $guru['id_guru']; ?>"
                                    <?= (
                                        (int) $id_guru ===
                                        (int) $guru['id_guru']
                                    ) ? 'selected' : ''; ?>
                                >

                                    <?= htmlspecialchars(
                                        $guru['nama']
                                    ); ?>


                                    <?php if (!empty($guru['nip'])): ?>

                                            -
                                            NIP
                                            <?= htmlspecialchars(
                                                $guru['nip']
                                            ); ?>

                                    <?php endif; ?>

                                </option>

                        <?php endforeach; ?>

                    </select>


                    <span class="konseling-help">

                        Pilih Guru BK yang ingin kamu hubungi.

                    </span>


                </div>


                <!-- =================================================
                     TANGGAL DAN JAM
                ================================================== -->

                <div class="konseling-group">


                    <label
                        for="tanggal"
                        class="konseling-label"
                    >

                        Tanggal & Jam Konseling

                        <span class="konseling-required">
                            *
                        </span>

                    </label>


                    <input
                        type="datetime-local"
                        name="tanggal"
                        id="tanggal"
                        class="konseling-input"
                        value="<?= htmlspecialchars(
                            $tanggal_input
                        ); ?>"
                        min="<?= date(
                            'Y-m-d\TH:i'
                        ); ?>"
                        required
                    >


                    <span class="konseling-help">

                        Pilih tanggal dan jam yang kamu inginkan untuk melakukan konseling.

                    </span>


                </div>


                <!-- =================================================
                     MASALAH
                ================================================== -->

                <div class="konseling-group">


                    <label
                        for="masalah"
                        class="konseling-label"
                    >

                        Masalah / Cerita

                        <span class="konseling-required">
                            *
                        </span>

                    </label>


                    <textarea
                        name="masalah"
                        id="masalah"
                        class="konseling-textarea"
                        maxlength="2000"
                        minlength="10"
                        placeholder="Ceritakan masalah atau hal yang ingin kamu konsultasikan..."
                        required
                    ><?= htmlspecialchars(
                        $masalah
                    ); ?></textarea>


                    <div class="konseling-counter">

                        <span id="jumlahKarakter">
                            <?= strlen($masalah); ?>
                        </span>

                        / 2000

                    </div>


                </div>


                <!-- =================================================
                     BUTTON
                ================================================== -->

                <div class="konseling-actions">


                    <a
                        href="dashboard_siswa.php"
                        class="konseling-button konseling-cancel"
                    >

                        <i class="bi bi-x-lg"></i>

                        Batal

                    </a>


                    <button
                        type="submit"
                        class="konseling-button konseling-submit"
                    >

                        <i class="bi bi-send-fill"></i>

                        Ajukan Konseling

                    </button>


                </div>


            </form>


        </div>


    </div>


</div>


<script>

/*
|--------------------------------------------------------------------------
| COUNTER KARAKTER
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const textarea =
            document.getElementById(
                'masalah'
            );


        const counter =
            document.getElementById(
                'jumlahKarakter'
            );


        if (
            textarea &&
            counter
        ) {

            function updateCounter() {

                counter.textContent =
                    textarea.value.length;

            }


            updateCounter();


            textarea.addEventListener(
                'input',
                updateCounter
            );

        }

    }
);

</script>


<?php

require_once "layout/footer_siswa.php";

?>