<?php

session_start();

require_once __DIR__ . "/config/database.php";


/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: guru.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| DATA GURU
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


/*
|--------------------------------------------------------------------------
| FOTO
|--------------------------------------------------------------------------
*/

$foto = trim($guru['foto'] ?? '');

$foto_path =
    __DIR__ .
    "/uploads/guru/" .
    $foto;

$foto_ada =
    $foto !== '' &&
    file_exists($foto_path);


require_once __DIR__ . "/layout/header.php";

?>

<style>

.guru-detail-page {
    padding-bottom: 30px;
}


/* HEADER */

.guru-detail-header {
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

.guru-detail-title {
    margin: 0;

    font-size: 28px;

    font-weight: 700;

    color: var(--text-main);
}

.guru-detail-subtitle {
    margin: 3px 0 0;

    color: var(--text-muted);

    font-size: 14px;
}


/* CARD */

.guru-detail-card {
    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    box-shadow: 0 4px 18px rgba(0,0,0,.04);

    overflow: hidden;
}


/* PROFILE */

.guru-profile-header {
    padding: 30px;

    display: flex;
    align-items: center;

    gap: 22px;

    border-bottom: 1px solid var(--border-color);
}

.guru-profile-photo {
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

.guru-profile-photo img {
    width: 100%;
    height: 100%;

    object-fit: cover;

    display: block;
}

.guru-profile-info h2 {
    margin: 0 0 6px;

    font-size: 24px;

    font-weight: 700;

    color: var(--text-main);
}

.guru-profile-info p {
    margin: 0;

    color: var(--text-muted);

    font-size: 14px;
}

.guru-role {
    display: inline-flex;
    align-items: center;

    gap: 6px;

    margin-top: 10px;

    padding: 7px 11px;

    border-radius: 8px;

    background: rgba(25,135,84,.12);

    color: #198754;

    font-size: 12px;

    font-weight: 600;
}


/* DETAIL */

.guru-detail-section {
    padding: 28px 30px;
}

.guru-detail-section-title {
    margin: 0 0 18px;

    color: var(--text-main);

    font-size: 17px;

    font-weight: 700;
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

    color: var(--text-muted);

    font-size: 12px;

    font-weight: 600;

    text-transform: uppercase;

    letter-spacing: .4px;
}

.detail-value {
    color: var(--text-main);

    font-size: 15px;

    font-weight: 600;

    word-break: break-word;
}


/* FOOTER */

.guru-detail-footer {
    padding: 20px 30px;

    border-top: 1px solid var(--border-color);

    display: flex;
    justify-content: flex-end;
}

.btn-edit-guru {
    display: inline-flex;
    align-items: center;

    gap: 8px;

    padding: 10px 17px;

    background: #0d6efd;

    color: white;

    border-radius: 10px;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;
}

.btn-edit-guru:hover {
    background: #0b5ed7;

    color: white;
}


/* RESPONSIVE */

@media (max-width: 768px) {

    .guru-profile-header {
        padding: 22px;

        flex-direction: column;

        align-items: flex-start;
    }

    .guru-detail-title {
        font-size: 24px;
    }

    .guru-detail-section {
        padding: 22px;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .guru-detail-footer {
        padding: 18px 22px;
    }

}

</style>


<div class="guru-detail-page">


    <!-- HEADER -->

    <div class="guru-detail-header">

        <a
            href="guru.php"
            class="btn-kembali"
            title="Kembali"
        >

            <i class="bi bi-arrow-left"></i>

        </a>


        <div>

            <h1 class="guru-detail-title">
                Detail Guru BK
            </h1>

            <p class="guru-detail-subtitle">
                Informasi lengkap Guru Bimbingan dan Konseling
            </p>

        </div>

    </div>


    <!-- CARD -->

    <div class="guru-detail-card">


        <!-- PROFILE -->

        <div class="guru-profile-header">


            <div class="guru-profile-photo">

                <?php if ($foto_ada): ?>

                        <img
                            src="uploads/guru/<?= htmlspecialchars($foto); ?>"
                            alt="Foto <?= htmlspecialchars($guru['nama']); ?>"
                        >

                <?php else: ?>

                        <i class="bi bi-person-badge-fill"></i>

                <?php endif; ?>

            </div>


            <div class="guru-profile-info">

                <h2>
                    <?= htmlspecialchars($guru['nama']); ?>
                </h2>

                <p>
                    NIP: <?= htmlspecialchars($guru['nip']); ?>
                </p>

                <span class="guru-role">

                    <i class="bi bi-person-badge-fill"></i>

                    Guru Bimbingan & Konseling

                </span>

            </div>

        </div>


        <!-- DETAIL -->

        <div class="guru-detail-section">

            <h3 class="guru-detail-section-title">

                <i class="bi bi-person-vcard me-2"></i>

                Informasi Guru

            </h3>


            <div class="detail-grid">


                <!-- ID -->

                <div class="detail-item">

                    <span class="detail-label">
                        ID Guru
                    </span>

                    <div class="detail-value">
                        #<?= (int) $guru['id_guru']; ?>
                    </div>

                </div>


                <!-- NIP -->

                <div class="detail-item">

                    <span class="detail-label">
                        NIP
                    </span>

                    <div class="detail-value">
                        <?= htmlspecialchars($guru['nip']); ?>
                    </div>

                </div>


                <!-- NAMA -->

                <div class="detail-item">

                    <span class="detail-label">
                        Nama Lengkap
                    </span>

                    <div class="detail-value">
                        <?= htmlspecialchars($guru['nama']); ?>
                    </div>

                </div>


                <!-- ROLE -->

                <div class="detail-item">

                    <span class="detail-label">
                        Jabatan
                    </span>

                    <div class="detail-value">
                        Guru Bimbingan & Konseling
                    </div>

                </div>


            </div>

        </div>


        <!-- FOOTER -->

        <div class="guru-detail-footer">

            <a
                href="guru_edit.php?id=<?= (int) $guru['id_guru']; ?>"
                class="btn-edit-guru"
            >

                <i class="bi bi-pencil"></i>

                Edit Guru

            </a>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . "/layout/footer.php";

?>