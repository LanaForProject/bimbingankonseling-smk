<?php
/**
 * ============================================
 * DATABASE CONNECTION - BK ONLINE
 * ============================================
 * XAMPP
 * Host     : localhost
 * Database : konseling
 * Username : root
 * Password : kosong
 * ============================================
 */

$host = "localhost";
$username = "root";
$password = "";
$database = "konseling";

/*
|--------------------------------------------------------------------------
| Koneksi Database
|--------------------------------------------------------------------------
*/

$conn = mysqli_connect($host, $username, $password, $database);

/*
|--------------------------------------------------------------------------
| Cek Koneksi
|--------------------------------------------------------------------------
*/

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

/*
|--------------------------------------------------------------------------
| Set Charset
|--------------------------------------------------------------------------
*/

mysqli_set_charset($conn, "utf8mb4");


/*
|--------------------------------------------------------------------------
| Fungsi SELECT
|--------------------------------------------------------------------------
| Contoh:
| $data = select("SELECT * FROM siswa");
|--------------------------------------------------------------------------
*/

function select($query)
{
    global $conn;

    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query database gagal: " . mysqli_error($conn));
    }

    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}


/*
|--------------------------------------------------------------------------
| Fungsi INSERT
|--------------------------------------------------------------------------
*/

function insert($query)
{
    global $conn;

    if (mysqli_query($conn, $query)) {
        return mysqli_affected_rows($conn);
    }

    die("Query INSERT gagal: " . mysqli_error($conn));
}


/*
|--------------------------------------------------------------------------
| Fungsi UPDATE
|--------------------------------------------------------------------------
*/

function update($query)
{
    global $conn;

    if (mysqli_query($conn, $query)) {
        return mysqli_affected_rows($conn);
    }

    die("Query UPDATE gagal: " . mysqli_error($conn));
}


/*
|--------------------------------------------------------------------------
| Fungsi DELETE
|--------------------------------------------------------------------------
*/

function delete($query)
{
    global $conn;

    if (mysqli_query($conn, $query)) {
        return mysqli_affected_rows($conn);
    }

    die("Query DELETE gagal: " . mysqli_error($conn));
}

?>