<?php

session_start();

require_once __DIR__ . "/config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| PESAN
|--------------------------------------------------------------------------
*/

$success = $_GET['success'] ?? '';

/*
|--------------------------------------------------------------------------
| DATA GURU
|--------------------------------------------------------------------------
*/

$guru_list = [];

$query = "
    SELECT
        id_guru,
        nip,
        nama,
        foto
    FROM guru_bk
    ORDER BY id_guru DESC
";

$result = $conn->query($query);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $guru_list[] = $row;

    }
}


require_once __DIR__ . "/layout/header.php";

?>

<style>

.guru-page {
    padding-bottom: 30px;
}


/* HEADER */

.guru-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;

    margin-bottom: 24px;
}

.guru-title {
    margin: 0;

    font-size: 28px;
    font-weight: 700;

    color: var(--text-main);
}

.guru-subtitle {
    margin: 4px 0 0;

    font-size: 14px;

    color: var(--text-muted);
}

.btn-tambah-guru {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    padding: 10px 17px;

    background: #0d6efd;

    color: white;

    border-radius: 10px;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

    transition: .2s ease;
}

.btn-tambah-guru:hover {
    background: #0b5ed7;

    color: white;

    transform: translateY(-1px);
}


/* CARD */

.guru-card {
    background: var(--bg-card);

    border: 1px solid var(--border-color);

    border-radius: 16px;

    box-shadow: 0 4px 18px rgba(0,0,0,.04);

    overflow: hidden;
}

.guru-card-header {
    padding: 18px 22px;

    border-bottom: 1px solid var(--border-color);
}

.guru-card-title {
    margin: 0;

    color: var(--text-main);

    font-size: 16px;

    font-weight: 700;
}


/* TABLE */

.guru-table-wrapper {
    overflow-x: auto;
}

.guru-table {
    width: 100%;

    border-collapse: collapse;

    margin: 0;
}

.guru-table thead th {
    padding: 15px 18px;

    background: var(--bg-body);

    color: var(--text-main);

    border-bottom: 1px solid var(--border-color);

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;
}

.guru-table tbody td {
    padding: 14px 18px;

    color: var(--text-main);

    border-bottom: 1px solid var(--border-color);

    vertical-align: middle;

    font-size: 14px;
}

.guru-table tbody tr:last-child td {
    border-bottom: none;
}

.guru-table tbody tr:hover {
    background: var(--bg-body);
}


/* GURU PROFILE */

.guru-profile {
    display: flex;
    align-items: center;

    gap: 12px;

    min-width: 220px;
}

.guru-avatar {
    width: 44px;
    height: 44px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;

    border-radius: 50%;

    background: rgba(13,110,253,.10);

    color: #0d6efd;

    border: 2px solid rgba(13,110,253,.12);

    font-size: 19px;
}

.guru-avatar img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;
}

.guru-name {
    color: var(--text-main);

    font-weight: 700;
}

.guru-nip {
    color: var(--text-muted);

    font-size: 12px;

    margin-top: 2px;
}


/* ACTION */

.guru-actions {
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

    transition: .2s ease;
}

.btn-detail {
    background: rgba(13,110,253,.10);

    color: #0d6efd;
}

.btn-detail:hover {
    background: #0d6efd;

    color: white;
}

.btn-edit {
    background: rgba(13,110,253,.10);

    color: #0d6efd;
}

.btn-edit:hover {
    background: #0d6efd;

    color: white;
}

.btn-hapus {
    background: rgba(220,53,69,.10);

    color: #dc3545;
}

.btn-hapus:hover {
    background: #dc3545;

    color: white;
}


/* EMPTY */

.guru-empty {
    padding: 55px 20px;

    text-align: center;

    color: var(--text-muted);
}

.guru-empty i {
    display: block;

    margin-bottom: 12px;

    font-size: 42px;

    opacity: .5;
}


/* ALERT */

.guru-alert {
    border-radius: 12px;

    margin-bottom: 20px;
}


/* RESPONSIVE */

@media (max-width: 768px) {

    .guru-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .guru-title {
        font-size: 24px;
    }

}

</style>


<div class="guru-page">


    <!-- HEADER -->

    <div class="guru-header">

        <div>

            <h1 class="guru-title">
                Guru BK
            </h1>

            <p class="guru-subtitle">
                Kelola data Guru Bimbingan dan Konseling
            </p>

        </div>


        <a
            href="guru_tambah.php"
            class="btn-tambah-guru"
        >

            <i class="bi bi-person-plus-fill"></i>

            Tambah Guru

        </a>

    </div>


    <!-- PESAN -->

    <?php if ($success === 'tambah'): ?>

            <div class="alert alert-success guru-alert">

                <i class="bi bi-check-circle me-2"></i>

                Guru BK berhasil ditambahkan.

            </div>

    <?php elseif ($success === 'edit'): ?>

            <div class="alert alert-success guru-alert">

                <i class="bi bi-check-circle me-2"></i>

                Data Guru BK berhasil diperbarui.

            </div>

    <?php elseif ($success === 'hapus'): ?>

            <div class="alert alert-success guru-alert">

                <i class="bi bi-check-circle me-2"></i>

                Guru BK berhasil dihapus.

            </div>

    <?php endif; ?>


    <!-- CARD -->

    <div class="guru-card">


        <div class="guru-card-header">

            <h2 class="guru-card-title">

                <i class="bi bi-people-fill me-2"></i>

                Data Guru BK

            </h2>

        </div>


        <div class="guru-table-wrapper">

            <?php if (!empty($guru_list)): ?>

                    <table class="guru-table">

                        <thead>

                            <tr>

                                <th>No</th>

                                <th>Guru BK</th>

                                <th>NIP</th>

                                <th>Aksi</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($guru_list as $index => $guru): ?>

                                    <?php

                                    $foto = trim($guru['foto'] ?? '');

                                    $foto_path =
                                        __DIR__ .
                                        '/uploads/guru/' .
                                        $foto;

                                    $foto_ada =
                                        $foto !== '' &&
                                        file_exists($foto_path);

                                    ?>


                                    <tr>


                                        <!-- NO -->

                                        <td>
                                            <?= $index + 1; ?>
                                        </td>


                                        <!-- GURU -->

                                        <td>

                                            <div class="guru-profile">

                                                <div class="guru-avatar">

                                                    <?php if ($foto_ada): ?>

                                                            <img
                                                                src="uploads/guru/<?= htmlspecialchars($foto); ?>"
                                                                alt="Foto <?= htmlspecialchars($guru['nama']); ?>"
                                                                loading="lazy"
                                                            >

                                                    <?php else: ?>

                                                            <i class="bi bi-person-badge-fill"></i>

                                                    <?php endif; ?>

                                                </div>


                                                <div>

                                                    <div class="guru-name">

                                                        <?= htmlspecialchars($guru['nama']); ?>

                                                    </div>

                                                    <div class="guru-nip">

                                                        Guru Bimbingan & Konseling

                                                    </div>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- NIP -->

                                        <td>

                                            <?= htmlspecialchars($guru['nip']); ?>

                                        </td>


                                        <!-- AKSI -->

                                        <td>

                                            <div class="guru-actions">


                                                <a
                                                    href="guru_detail.php?id=<?= (int) $guru['id_guru']; ?>"
                                                    class="btn-aksi btn-detail"
                                                    title="Detail Guru"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>


                                                <a
                                                    href="guru_edit.php?id=<?= (int) $guru['id_guru']; ?>"
                                                    class="btn-aksi btn-edit"
                                                    title="Edit Guru"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>


                                                <a
                                                    href="guru_hapus.php?id=<?= (int) $guru['id_guru']; ?>"
                                                    class="btn-aksi btn-hapus"
                                                    title="Hapus Guru"
                                                    onclick="return confirm('Yakin ingin menghapus Guru BK ini?');"
                                                >

                                                    <i class="bi bi-trash"></i>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

            <?php else: ?>

                    <div class="guru-empty">

                        <i class="bi bi-person-x"></i>

                        <p>
                            Belum ada data Guru BK.
                        </p>

                    </div>

            <?php endif; ?>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . "/layout/footer.php";

?>