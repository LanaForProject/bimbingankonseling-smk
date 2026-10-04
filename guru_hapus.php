<?php

session_start();

require_once __DIR__ . '/config/database.php';

// =====================================================
// CEK LOGIN ADMIN
// =====================================================

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// =====================================================
// AMBIL ID GURU
// =====================================================

$id_guru = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id_guru <= 0) {
    header('Location: guru.php?error=id');
    exit;
}

// =====================================================
// CEK DATA GURU
// =====================================================

$stmt = mysqli_prepare(
    $conn,
    'SELECT id_guru, nip, nama
     FROM guru_bk
     WHERE id_guru = ?
     LIMIT 1'
);

mysqli_stmt_bind_param(
    $stmt,
    'i',
    $id_guru
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$guru = mysqli_fetch_assoc($result);

// Kalau tidak ditemukan
if (!$guru) {
    header('Location: guru.php?error=notfound');
    exit;
}

// =====================================================
// HAPUS GURU
// =====================================================

$delete = mysqli_prepare(
    $conn,
    'DELETE FROM guru_bk
     WHERE id_guru = ?'
);

mysqli_stmt_bind_param(
    $delete,
    'i',
    $id_guru
);

// =====================================================
// EKSEKUSI
// =====================================================

if (mysqli_stmt_execute($delete)) {
    header('Location: guru.php?success=hapus');
    exit;
}

// =====================================================
// JIKA GAGAL
// =====================================================

header('Location: guru.php?error=hapus');
exit;

?>