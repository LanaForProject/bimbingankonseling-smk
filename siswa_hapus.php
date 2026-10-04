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
 * | AMBIL ID SISWA
 * |--------------------------------------------------------------------------
 */
$id_siswa = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_siswa <= 0) {
    header('Location: siswa.php?error=id');
    exit;
}

/*
 * |--------------------------------------------------------------------------
 * | CEK SISWA
 * |--------------------------------------------------------------------------
 */
$stmt = mysqli_prepare(
    $conn,
    'SELECT id_siswa, nis, nama
     FROM siswa
     WHERE id_siswa = ?
     LIMIT 1'
);

mysqli_stmt_bind_param($stmt, 'i', $id_siswa);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$siswa) {
    header('Location: siswa.php?error=notfound');
    exit;
}

/*
 * |--------------------------------------------------------------------------
 * | HAPUS SISWA
 * |--------------------------------------------------------------------------
 * |
 * | JANGAN hapus data dari tabel konseling.
 * |
 * | Karena FK:
 * |
 * | fk_konseling_siswa
 * | ON DELETE SET NULL
 * |
 * | maka ketika siswa dihapus:
 * | id_siswa pada konseling otomatis menjadi NULL.
 * |
 * |--------------------------------------------------------------------------
 */

$delete = mysqli_prepare(
    $conn,
    'DELETE FROM siswa WHERE id_siswa = ?'
);

mysqli_stmt_bind_param($delete, 'i', $id_siswa);

if (mysqli_stmt_execute($delete)) {
    mysqli_stmt_close($delete);

    header('Location: siswa.php?success=hapus');
    exit;
}

/*
 * |--------------------------------------------------------------------------
 * | GAGAL HAPUS
 * |--------------------------------------------------------------------------
 */

mysqli_stmt_close($delete);

header('Location: siswa.php?error=hapus');
exit;
?>