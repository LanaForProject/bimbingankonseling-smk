<?php

require_once __DIR__ . '/config/database.php';

/*
|--------------------------------------------------------------------------
| DATA GURU BK
|--------------------------------------------------------------------------
*/

$guru_bk = [];

$query_guru = "
    SELECT
        id_guru,
        nip,
        nama,
        foto
    FROM guru_bk
    ORDER BY nama ASC
";

$result_guru = $conn->query($query_guru);

if ($result_guru) {

  while ($row = $result_guru->fetch_assoc()) {
    $guru_bk[] = $row;
  }

}


/*
|--------------------------------------------------------------------------
| CEK STATUS KONSELING
|--------------------------------------------------------------------------
*/

$hasil_status = [];
$nis_cari = '';

if (isset($_GET['nis'])) {

  $nis_cari = trim($_GET['nis']);

  if ($nis_cari !== '') {

    $stmt = $conn->prepare("
            SELECT
                s.id_siswa,
                s.nis,
                s.nama,
                s.kelas,
                s.jurusan,

                k.id_konseling,
                k.masalah,
                k.solusi,
                k.tanggal,
                k.status,

                g.nama AS nama_guru,
                g.nip AS nip_guru,
                g.foto AS foto_guru

            FROM siswa s

            LEFT JOIN konseling k
                ON s.id_siswa = k.id_siswa

            LEFT JOIN guru_bk g
                ON k.id_guru = g.id_guru

            WHERE s.nis = ?

            ORDER BY
                k.tanggal DESC,
                k.id_konseling DESC
        ");

    if ($stmt) {

      $stmt->bind_param('s', $nis_cari);
      $stmt->execute();

      $result_status = $stmt->get_result();

      while ($row = $result_status->fetch_assoc()) {
        $hasil_status[] = $row;
      }

      $stmt->close();
    }
  }
}


/*
|--------------------------------------------------------------------------
| HELPER STATUS
|--------------------------------------------------------------------------
*/

function statusBadgeHome($status)
{
  switch ($status) {

    case 'terjadwal':
      return [
        'class' => 'badge-status-terjadwal',
        'icon' => 'fa-calendar-check',
        'text' => 'Terjadwal'
      ];

    case 'selesai':
      return [
        'class' => 'badge-status-selesai',
        'icon' => 'fa-circle-check',
        'text' => 'Selesai'
      ];

    case 'batal':
      return [
        'class' => 'badge-status-batal',
        'icon' => 'fa-circle-xmark',
        'text' => 'Dibatalkan'
      ];

    case 'menunggu':
      return [
        'class' => 'badge-status-menunggu',
        'icon' => 'fa-clock',
        'text' => 'Menunggu'
      ];

    default:
      return [
        'class' => 'badge-status-menunggu',
        'icon' => 'fa-clock',
        'text' => 'Menunggu'
      ];
  }
}


/*
|--------------------------------------------------------------------------
| TAHUN
|--------------------------------------------------------------------------
*/

$tahun_sekarang = date('Y');

?>

<!DOCTYPE html>
<html lang="id" data-theme="dark">

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>BK Online SMKN 7 Kota Serang - Bimbingan & Konseling</title>


  <!-- =========================================================
         GOOGLE FONT
    ========================================================== -->

  <link rel="preconnect" href="https://fonts.googleapis.com">

  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">


  <!-- =========================================================
         BOOTSTRAP
    ========================================================== -->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


  <!-- =========================================================
         FONT AWESOME
    ========================================================== -->

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


  <!-- =========================================================
         CSS UTAMA
    ========================================================== -->

  <link rel="stylesheet" href="assets/css/style.css">


  <style>
    /* =========================================================
           NAVBAR
        ========================================================= */

    .navbar-custom {
      min-height: 72px;
      padding: 10px 0;

      background: var(--bg-card);

      border-bottom:
        1px solid var(--border-card);

      transition:
        background .25s ease,
        border-color .25s ease,
        box-shadow .25s ease;

      z-index: 1050;
    }


    .navbar-custom .container {
      display: flex;
      align-items: center;
    }


    /* =========================================================
           BRAND
        ========================================================= */

    .navbar-custom .navbar-brand {

      display: flex;
      align-items: center;

      gap: 10px;

      margin-right: 25px;
      padding: 0;

      color: var(--text-main);

      text-decoration: none;

      flex-shrink: 0;
    }


    .navbar-custom .brand-icon-box {

      width: 42px;
      height: 42px;

      display: flex;
      align-items: center;
      justify-content: center;

      flex-shrink: 0;

      border-radius: 11px;

      background: var(--accent-cyan);

      color: #ffffff;

      font-size: 17px;

      box-shadow:
        0 5px 15px rgba(13, 110, 253, .20);
    }


    .navbar-custom .brand-title {

      display: block;

      color: var(--text-main);

      font-size: 15px;
      font-weight: 800;

      line-height: 1.2;
    }


    .navbar-custom .brand-subtitle {

      display: block;

      margin-top: 3px;

      color: var(--text-secondary);

      font-size: 9px;
      font-weight: 500;

      line-height: 1.2;
    }


    /* =========================================================
           NAV MENU
        ========================================================== */

    .navbar-custom .navbar-nav {

      display: flex;
      align-items: center;

      gap: 3px;
    }


    .navbar-custom .nav-item {

      display: flex;
      align-items: center;
    }


    .navbar-custom .nav-link {

      position: relative;

      display: inline-flex;
      align-items: center;
      justify-content: center;

      min-height: 40px;

      padding: 8px 12px !important;

      border-radius: 9px;

      color: var(--text-secondary) !important;

      font-size: 12px;
      font-weight: 600;

      text-decoration: none;

      white-space: nowrap;

      transition:
        color .2s ease,
        background .2s ease,
        transform .2s ease;
    }


    /* HOVER */

    .navbar-custom .nav-link:hover {

      color: var(--accent-cyan) !important;

      background:
        rgba(13, 110, 253, .08);

    }


    /* =========================================================
           ACTIVE MENU
        ========================================================== */

    .navbar-custom .nav-link.active {

      color: #ffffff !important;

      background:
        var(--accent-cyan);

      font-weight: 700;

      box-shadow:
        0 5px 14px rgba(13, 110, 253, .18);

    }


    .navbar-custom .nav-link.active:hover {

      color: #ffffff !important;

      background:
        var(--accent-cyan);

    }


    .navbar-custom .nav-link.active i {

      color: #ffffff;

    }


    /* =========================================================
           BUTTON TEMA
        ========================================================== */

    .navbar-custom .btn-theme-toggle {

      width: 40px;
      height: 40px;

      display: inline-flex;
      align-items: center;
      justify-content: center;

      padding: 0;

      border:
        1px solid var(--border-input);

      border-radius: 9px;

      background:
        var(--bg-card-secondary);

      color:
        var(--text-main);

      cursor: pointer;

      transition:
        color .2s ease,
        background .2s ease,
        border-color .2s ease;
    }


    .navbar-custom .btn-theme-toggle:hover {

      color:
        var(--accent-cyan);

      border-color:
        var(--accent-cyan);

      background:
        rgba(13, 110, 253, .08);
    }


    /* =========================================================
           MOBILE TOGGLE
        ========================================================== */

    .navbar-custom .navbar-toggler {

      width: 42px;
      height: 42px;

      display: flex;
      align-items: center;
      justify-content: center;

      padding: 0;

      border-radius: 9px !important;

      background:
        rgba(13, 110, 253, .10);

      color:
        var(--text-main) !important;
    }


    .navbar-custom .navbar-toggler:focus {

      box-shadow: none;

    }


    /* =========================================================
           TOP BAR
        ========================================================== */

    .top-bar {

      min-height: 35px;

      padding: 7px 0;

      background:
        var(--bg-card-secondary);

      border-bottom:
        1px solid var(--border-card);

      color:
        var(--text-secondary);

      font-size: 11px;
    }


    .top-bar a {

      color:
        var(--text-secondary);

      text-decoration: none;

      transition:
        color .2s ease;
    }


    .top-bar a:hover {

      color:
        var(--accent-cyan);
    }


    /* =========================================================
           GURU BK
        ========================================================== */

    .counselor-img-wrapper {

      height: 240px;

      display: flex;
      align-items: center;
      justify-content: center;

      overflow: hidden;

      background:
        var(--bg-card-secondary);
    }


    .counselor-photo {

      width: 100%;
      height: 100%;

      object-fit: cover;

      display: block;
    }


    .counselor-fallback {

      width: 100%;
      height: 100%;

      display: flex;
      align-items: center;
      justify-content: center;
    }


    /* =========================================================
           STATUS BADGE
        ========================================================== */

    .badge-status-terjadwal {

      color: #65a2ff !important;

      background:
        rgba(57, 133, 255, .12) !important;

      border:
        1px solid rgba(57, 133, 255, .18);

      padding: 8px 12px;

      border-radius: 999px;

      font-size: 12px;
    }


    .badge-status-selesai {

      color: #45d69a !important;

      background:
        rgba(25, 135, 84, .12) !important;

      border:
        1px solid rgba(25, 135, 84, .18);

      padding: 8px 12px;

      border-radius: 999px;

      font-size: 12px;
    }


    .badge-status-batal {

      color: #ff7180 !important;

      background:
        rgba(220, 53, 69, .12) !important;

      border:
        1px solid rgba(220, 53, 69, .18);

      padding: 8px 12px;

      border-radius: 999px;

      font-size: 12px;
    }


    .badge-status-menunggu {

      color: #ffc65c !important;

      background:
        rgba(255, 193, 7, .12) !important;

      border:
        1px solid rgba(255, 193, 7, .18);

      padding: 8px 12px;

      border-radius: 999px;

      font-size: 12px;
    }


    /* =========================================================
           STATUS RESULT
        ========================================================== */

    .status-result-card {

      transition:
        transform .2s ease,
        box-shadow .2s ease;
    }


    .status-result-card:hover {

      transform:
        translateY(-2px);
    }


    .status-info-box {

      background:
        var(--bg-card-secondary);

      border:
        1px solid var(--border-card);

      border-radius: 12px;

      padding: 15px;
    }


    .status-problem-box {

      background:
        var(--bg-card-secondary);

      border:
        1px solid var(--border-card);

      border-radius: 12px;

      padding: 15px;

      color:
        var(--text-main);

      line-height: 1.7;

      text-align: left;

      white-space: pre-wrap;

      overflow-wrap: anywhere;
    }


    .status-solution-box {

      background:
        rgba(34, 197, 94, .10);

      border:
        1px solid rgba(34, 197, 94, .30);

      border-radius: 12px;

      padding: 15px;
    }


    /* =========================================================
           SEARCH STATUS
        ========================================================== */

    .status-search-card {

      background:
        var(--bg-card);

      border:
        1px solid var(--border-card);

      border-radius: 18px;

      box-shadow:
        0 8px 30px rgba(0, 0, 0, .05);
    }


    .status-search-input {

      background:
        var(--bg-card) !important;

      color:
        var(--text-main) !important;

      border-color:
        var(--border-input) !important;
    }


    .status-search-input::placeholder {

      color:
        var(--text-secondary);

      opacity: .75;
    }


    .status-search-input:focus {

      box-shadow:
        none !important;

      border-color:
        var(--accent-cyan) !important;
    }


    /* =========================================================
           EMPTY STATE
        ========================================================== */

    .status-empty {

      padding: 50px 25px;

      border:
        1px solid var(--border-card);

      border-radius: 18px;

      background:
        var(--bg-card);
    }


    /* =========================================================
           GURU CARD
        ========================================================== */

    .counselor-card {

      height: 100%;

      overflow: hidden;

      background:
        var(--bg-card);

      border:
        1px solid var(--border-card);

      border-radius: 18px;

      box-shadow:
        0 8px 30px rgba(0, 0, 0, .05);

      transition:
        transform .2s ease,
        box-shadow .2s ease,
        border-color .2s ease;
    }


    .counselor-card:hover {

      transform:
        translateY(-4px);

      border-color:
        rgba(13, 110, 253, .30);

      box-shadow:
        0 14px 35px rgba(0, 0, 0, .08);
    }


    /* =========================================================
           MOBILE NAVBAR
        ========================================================== */

    @media (max-width: 991.98px) {

      .navbar-custom {

        padding:
          10px 0;
      }


      .navbar-custom .navbar-collapse {

        margin-top:
          12px;

        padding:
          12px;

        border:
          1px solid var(--border-card);

        border-radius:
          14px;

        background:
          var(--bg-card);

        box-shadow:
          0 10px 30px rgba(0, 0, 0, .08);
      }


      .navbar-custom .navbar-nav {

        width:
          100%;

        align-items:
          stretch !important;

        gap:
          4px !important;
      }


      .navbar-custom .nav-item {

        width:
          100%;
      }


      .navbar-custom .nav-link {

        width:
          100%;

        min-height:
          43px;

        justify-content:
          flex-start;

        padding:
          9px 13px !important;
      }


      .navbar-custom .btn-theme-toggle {

        margin-left:
          0;

        width:
          100%;
      }


      .navbar-custom .btn-primary-pill,
      .navbar-custom .btn-outline-pill {

        width:
          100%;

        justify-content:
          center;

        margin-left:
          0 !important;
      }

    }


    /* =========================================================
           MOBILE KECIL
        ========================================================== */

    @media (max-width: 767.98px) {

      .counselor-img-wrapper {

        height:
          220px;
      }


      #formCekStatus {

        flex-direction:
          column;
      }


      #formCekStatus .btn-primary-pill {

        width:
          100%;

        justify-content:
          center;
      }


      .status-result-card {

        padding:
          20px !important;
      }

    }


    @media (max-width: 575.98px) {

      .navbar-custom .brand-subtitle {

        display:
          none;
      }


      .navbar-custom .brand-title {

        font-size:
          14px;
      }


      .navbar-custom .brand-icon-box {

        width:
          38px;

        height:
          38px;
      }

    }
  </style>

</head>


<body>


  <!-- =========================================================
     TOP BAR
========================================================= -->

  <div class="top-bar d-none d-lg-block">

    <div class="container d-flex justify-content-between align-items-center">

      <div class="d-flex align-items-center gap-4">

        <span>

          <i class="fa-regular fa-envelope me-2"></i>

          bk@smkn7kotaserang.sch.id

        </span>


        <span>

          <i class="fa-solid fa-phone me-2"></i>

          (0254) 824-777

        </span>


        <span>

          <i class="fa-solid fa-location-dot me-2"></i>

          Ruang Konseling Gedung A Lt. 1

        </span>

      </div>


      <div class="d-flex align-items-center gap-3">

        <a href="#" aria-label="X">
          <i class="fa-brands fa-x-twitter"></i>
        </a>

        <a href="#" aria-label="Facebook">
          <i class="fa-brands fa-facebook-f"></i>
        </a>

        <a href="#" aria-label="Instagram">
          <i class="fa-brands fa-instagram"></i>
        </a>

        <a href="#" aria-label="YouTube">
          <i class="fa-brands fa-youtube"></i>
        </a>

      </div>

    </div>

  </div>



  <!-- =========================================================
     NAVBAR
========================================================= -->

  <nav class="navbar navbar-expand-lg navbar-custom sticky-top" id="mainNavbar">

    <div class="container">


      <!-- BRAND -->

      <a class="navbar-brand" href="#beranda">

        <div class="brand-icon-box">

          <i class="fa-solid fa-heart-pulse"></i>

        </div>


        <div>

          <span class="brand-title">
            BK Online
          </span>

          <span class="brand-subtitle">
            SMK Negeri 7 Kota Serang
          </span>

        </div>

      </a>


      <!-- MOBILE BUTTON -->

      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
        aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">

        <i class="fa-solid fa-bars fs-4"></i>

      </button>


      <!-- NAVIGATION -->

      <div class="collapse navbar-collapse" id="mainNav">

        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">


          <!-- BERANDA -->

          <li class="nav-item">

            <a class="nav-link active" href="#beranda" data-section="beranda">

              Beranda

            </a>

          </li>


          <!-- TENTANG -->

          <li class="nav-item">

            <a class="nav-link" href="#tentang" data-section="tentang">

              Tentang

            </a>

          </li>


          <!-- LAYANAN -->

          <li class="nav-item">

            <a class="nav-link" href="#layanan" data-section="layanan">

              Layanan

            </a>

          </li>


          <!-- ALUR -->

          <li class="nav-item">

            <a class="nav-link" href="#alur" data-section="alur">

              Alur

            </a>

          </li>


          <!-- GURU -->

          <li class="nav-item">

            <a class="nav-link" href="#guru" data-section="guru">

              Guru BK

            </a>

          </li>


          <!-- CEK STATUS -->

          <li class="nav-item">

            <a class="nav-link" href="#cek-status" data-section="cek-status">

              <i class="fa-solid fa-magnifying-glass me-1"></i>

              Cek Status

            </a>

          </li>


          <!-- THEME -->

          <li class="nav-item ms-lg-2 my-2 my-lg-0">

            <button class="btn-theme-toggle" type="button" title="Ganti Mode Gelap / Terang" aria-label="Ganti Tema">

              <i class="fa-solid fa-sun"></i>

            </button>

          </li>


          <!-- AJUKAN -->

          <li class="nav-item ms-lg-2 mt-2 mt-lg-0">

            <a class="btn-primary-pill" href="login.php">

              <i class="fa-solid fa-calendar-plus me-1"></i>

              Ajukan Konseling

            </a>

          </li>


          <!-- LOGIN -->

          <li class="nav-item ms-lg-2 mt-2 mt-lg-0">

            <a class="btn-outline-pill" href="login.php">

              <i class="fa-solid fa-right-to-bracket me-1"></i>

              Masuk

            </a>

          </li>


        </ul>

      </div>

    </div>

  </nav>



  <!-- =========================================================
     HERO
========================================================= -->

  <section id="beranda" class="hero-section">

    <div class="container">

      <div class="row align-items-center g-5">


        <div class="col-lg-7">

          <div class="hero-tag">

            <i class="fa-solid fa-shield-halved"></i>

            Layanan Resmi Bimbingan & Konseling

          </div>


          <h1 class="hero-title mb-4">

            Ruang Aman untuk<br>

            <span class="hero-title-gradient">
              Bercerita &
            </span><br>

            <span class="hero-title-gradient">
              Berkonsultasi
            </span>

          </h1>


          <p class="hero-subtitle mb-4">

            Sampaikan cerita, kendala belajar, atau rencana
            masa depanmu bersama Guru BK secara aman,
            privat, dan terpercaya.

          </p>


          <div class="d-flex flex-wrap gap-3">

            <a href="login.php" class="btn-primary-pill">

              Ajukan Konseling

              <i class="fa-solid fa-arrow-right ms-1"></i>

            </a>


            <a href="#alur" class="btn-outline-pill">

              <i class="fa-regular fa-circle-play me-1"></i>

              Pelajari Alur

            </a>

          </div>

        </div>


        <div class="col-lg-5">

          <div class="hero-glass-card">

            <div class="d-flex align-items-center gap-3 mb-4">

              <div class="hero-card-icon">

                <i class="fa-solid fa-comment-dots"></i>

              </div>


              <div>

                <span class="status-pill-green">

                  Layanan Aktif • Jam Sekolah

                </span>

                <h4 class="fw-bold mt-1 mb-0">

                  Pusat Konsultasi Siswa

                </h4>

              </div>

            </div>


            <ul class="hero-checklist">

              <li>

                <i class="fa-solid fa-shield-halved"></i>

                <span>
                  Privasi & Kerahasiaan Terjaga
                </span>

              </li>


              <li>

                <i class="fa-solid fa-user-tie"></i>

                <span>
                  Didampingi Langsung oleh Guru BK
                </span>

              </li>


              <li>

                <i class="fa-regular fa-calendar-check"></i>

                <span>
                  Jadwal Konseling Rapi & Terstruktur
                </span>

              </li>

            </ul>


            <div class="pt-3 border-top d-flex align-items-center gap-2 text-secondary small">

              <i class="fa-regular fa-clock"></i>

              <span>
                Waktu Layanan: 08.00 – 15.00 WIB
              </span>

            </div>

          </div>

        </div>

      </div>


      <!-- FEATURE -->

      <div class="row g-4 mt-5">


        <div class="col-md-4">

          <div class="feature-box">

            <div class="feature-icon-circle">

              <i class="fa-solid fa-shield-halved"></i>

            </div>

            <h5 class="fw-bold mb-2">
              Kerahasiaan Terjamin
            </h5>

            <p class="text-secondary small mb-0">

              Setiap cerita, keluhan, dan privasi
              permasalahan siswa dijaga dalam proses
              bimbingan dan konseling.

            </p>

          </div>

        </div>


        <div class="col-md-4">

          <div class="feature-box">

            <div class="feature-icon-circle">

              <i class="fa-solid fa-briefcase"></i>

            </div>

            <h5 class="fw-bold mb-2">
              Konseling Karir & BKK
            </h5>

            <p class="text-secondary small mb-0">

              Bimbingan karir masa depan, persiapan
              PKL, informasi dunia kerja, dan
              perguruan tinggi.

            </p>

          </div>

        </div>


        <div class="col-md-4">

          <div class="feature-box">

            <div class="feature-icon-circle">

              <i class="fa-solid fa-graduation-cap"></i>

            </div>

            <h5 class="fw-bold mb-2">
              Konseling Belajar
            </h5>

            <p class="text-secondary small mb-0">

              Dukungan untuk mengatasi hambatan belajar,
              manajemen waktu, dan pengembangan
              minat bakat.

            </p>

          </div>

        </div>


      </div>

    </div>

  </section>



  <!-- =========================================================
     TENTANG
========================================================= -->

  <section id="tentang" class="section-padding">

    <div class="container">

      <div class="row align-items-center g-5">


        <div class="col-lg-6">

          <div class="card p-4 p-md-5 rounded-4 shadow-sm border" style="background: var(--bg-card);">

            <span class="section-tag mb-2">
              Tentang BK SMKN 7 Serang
            </span>


            <h3 class="fw-bold mb-3">

              Sahabat Siswa, Ruang Diskusi
              Tanpa Rasa Takut.

            </h3>


            <p class="text-secondary mb-3">

              Layanan Bimbingan dan Konseling di
              SMKN 7 Kota Serang menjadi ruang
              pendampingan bagi siswa dalam menghadapi
              berbagai kebutuhan pribadi, sosial,
              belajar, dan karir.

            </p>


            <ul class="list-unstyled d-flex flex-column gap-2 fw-semibold mb-4">

              <li>

                <i class="fa-solid fa-circle-check me-2" style="color: var(--accent-cyan);"></i>

                Pendampingan siswa secara terarah

              </li>


              <li>

                <i class="fa-solid fa-circle-check me-2" style="color: var(--accent-cyan);"></i>

                Pendekatan humanis dan empatik

              </li>


              <li>

                <i class="fa-solid fa-circle-check me-2" style="color: var(--accent-cyan);"></i>

                Kolaborasi dengan pihak sekolah

              </li>

            </ul>


            <div>

              <a href="login.php" class="btn-primary-pill">

                <i class="fa-solid fa-envelope-open-text me-1"></i>

                Konsultasikan Sekarang

              </a>

            </div>

          </div>

        </div>


        <div class="col-lg-6">

          <div class="p-2">

            <span class="section-tag">
              Komitmen Kami
            </span>


            <h2 class="section-heading mb-4">

              Mendampingi Setiap Langkah
              Siswa Menuju Masa Depan.

            </h2>


            <p class="text-secondary mb-4">

              Setiap siswa memiliki kebutuhan dan
              tantangan yang berbeda. Melalui layanan
              BK, siswa dapat memperoleh ruang untuk
              berdiskusi dan mencari solusi bersama
              Guru BK.

            </p>


            <div class="row g-3">


              <div class="col-sm-6">

                <div class="p-3 border rounded-3" style="
                                    background: var(--bg-card);
                                    border-color: var(--border-card) !important;
                                ">

                  <i class="fa-solid fa-scale-balanced fs-3 mb-2" style="color: var(--accent-cyan);"></i>

                  <h6 class="fw-bold mb-1">
                    Adil & Terbuka
                  </h6>

                  <small class="text-secondary">

                    Siap mendengarkan siswa
                    dengan pendekatan yang
                    terbuka dan positif.

                  </small>

                </div>

              </div>


              <div class="col-sm-6">

                <div class="p-3 border rounded-3" style="
                                    background: var(--bg-card);
                                    border-color: var(--border-card) !important;
                                ">

                  <i class="fa-solid fa-handshake-angle fs-3 mb-2" style="color: var(--accent-cyan);"></i>

                  <h6 class="fw-bold mb-1">
                    Solutif & Suportif
                  </h6>

                  <small class="text-secondary">

                    Membantu siswa merumuskan
                    langkah penyelesaian bersama
                    Guru BK.

                  </small>

                </div>

              </div>


            </div>

          </div>

        </div>


      </div>

    </div>

  </section>



  <!-- =========================================================
     LAYANAN
========================================================= -->

  <section id="layanan" class="section-padding" style="background: var(--bg-card-secondary);">

    <div class="container">


      <div class="text-center max-w-700 mx-auto mb-5">

        <span class="section-tag">
          Bidang Pendampingan
        </span>

        <h2 class="section-heading">
          4 Layanan Utama Konseling
        </h2>

        <div class="section-divider"></div>

        <p class="text-secondary mt-3">

          Fasilitas pendampingan yang dirancang untuk
          mendukung perkembangan siswa secara utuh.

        </p>

      </div>


      <div class="row g-4">


        <div class="col-lg-3 col-md-6">

          <div class="service-card text-center">

            <div class="service-icon mx-auto">

              <i class="fa-solid fa-user-shield"></i>

            </div>

            <h5 class="fw-bold mb-3">
              Bimbingan Pribadi
            </h5>

            <p class="text-secondary small mb-0">

              Membantu pemahaman diri,
              pengelolaan emosi, pembentukan
              karakter, dan pengembangan kepercayaan diri.

            </p>

          </div>

        </div>


        <div class="col-lg-3 col-md-6">

          <div class="service-card text-center">

            <div class="service-icon mx-auto">

              <i class="fa-solid fa-people-group"></i>

            </div>

            <h5 class="fw-bold mb-3">
              Bimbingan Sosial
            </h5>

            <p class="text-secondary small mb-0">

              Mendukung hubungan positif dengan
              teman, komunikasi, adaptasi sekolah,
              dan penyelesaian konflik.

            </p>

          </div>

        </div>


        <div class="col-lg-3 col-md-6">

          <div class="service-card text-center">

            <div class="service-icon mx-auto">

              <i class="fa-solid fa-book-open-reader"></i>

            </div>

            <h5 class="fw-bold mb-3">
              Bimbingan Belajar
            </h5>

            <p class="text-secondary small mb-0">

              Membantu menemukan metode belajar,
              mengatur waktu, dan menghadapi
              berbagai tantangan akademik.

            </p>

          </div>

        </div>


        <div class="col-lg-3 col-md-6">

          <div class="service-card text-center">

            <div class="service-icon mx-auto">

              <i class="fa-solid fa-briefcase"></i>

            </div>

            <h5 class="fw-bold mb-3">
              Bimbingan Karir & PKL
            </h5>

            <p class="text-secondary small mb-0">

              Membantu perencanaan karir,
              persiapan PKL, portofolio,
              dunia kerja, dan kuliah.

            </p>

          </div>

        </div>


      </div>

    </div>

  </section>



  <!-- =========================================================
     ALUR
========================================================= -->

  <section id="alur" class="section-padding">

    <div class="container">


      <div class="text-center max-w-700 mx-auto mb-5">

        <span class="section-tag">
          Prosedur Konseling
        </span>

        <h2 class="section-heading">
          Alur Layanan Siswa
        </h2>

        <div class="section-divider"></div>

        <p class="text-secondary mt-3">

          Langkah mudah bagi siswa untuk
          mendapatkan layanan bimbingan konseling.

        </p>

      </div>


      <div class="row g-4">


        <div class="col-lg-3 col-md-6">

          <div class="step-card">

            <span class="step-number">
              1
            </span>

            <h5 class="fw-bold mb-2">
              Login Portal
            </h5>

            <p class="text-secondary small mb-0">

              Masuk menggunakan akun siswa
              yang telah terdaftar pada sistem BK.

            </p>

          </div>

        </div>


        <div class="col-lg-3 col-md-6">

          <div class="step-card">

            <span class="step-number">
              2
            </span>

            <h5 class="fw-bold mb-2">
              Ajukan Konseling
            </h5>

            <p class="text-secondary small mb-0">

              Isi masalah atau kebutuhan
              konseling serta waktu yang diinginkan.

            </p>

          </div>

        </div>


        <div class="col-lg-3 col-md-6">

          <div class="step-card">

            <span class="step-number">
              3
            </span>

            <h5 class="fw-bold mb-2">
              Sesi Konseling
            </h5>

            <p class="text-secondary small mb-0">

              Guru BK menangani permohonan dan
              melaksanakan sesi konseling sesuai jadwal.

            </p>

          </div>

        </div>


        <div class="col-lg-3 col-md-6">

          <div class="step-card">

            <span class="step-number">
              4
            </span>

            <h5 class="fw-bold mb-2">
              Solusi & Tindak Lanjut
            </h5>

            <p class="text-secondary small mb-0">

              Guru BK memberikan catatan,
              solusi, dan tindak lanjut konseling.

            </p>

          </div>

        </div>


      </div>

    </div>

  </section>



  <!-- =========================================================
     GURU BK
========================================================= -->

  <section id="guru" class="section-padding" style="background: var(--bg-card-secondary);">

    <div class="container">


      <div class="text-center max-w-700 mx-auto mb-5">

        <span class="section-tag">
          Tenaga Pendidik
        </span>

        <h2 class="section-heading">
          Guru Bimbingan & Konseling
        </h2>

        <div class="section-divider"></div>

        <p class="text-secondary mt-3">

          Tim Guru BK yang siap mendengarkan
          dan mendampingi kebutuhan siswa.

        </p>

      </div>


      <div class="row g-4 justify-content-center">


        <?php if (!empty($guru_bk)): ?>


          <?php foreach ($guru_bk as $guru): ?>

            <?php

            $nama_guru =
              $guru['nama'] ?? 'Guru BK';

            $nip_guru =
              $guru['nip'] ?? '-';

            $foto_guru =
              $guru['foto'] ?? '';

            $foto_nama = '';

            if (!empty($foto_guru)) {

              $foto_nama = basename(
                str_replace(
                  '\\',
                  '/',
                  $foto_guru
                )
              );

            }

            $foto_path =
              __DIR__ .
              '/uploads/guru/' .
              $foto_nama;

            $foto_ada =
              $foto_nama !== '' &&
              file_exists($foto_path);

            ?>


            <div class="col-lg-4 col-md-6">

              <div class="counselor-card">


                <div class="counselor-img-wrapper">


                  <?php if ($foto_ada): ?>

                    <img src="uploads/guru/<?= rawurlencode($foto_nama); ?>" alt="<?= htmlspecialchars($nama_guru); ?>"
                      class="counselor-photo">

                  <?php else: ?>

                    <div class="counselor-fallback">

                      <div class="fs-1" style="color: var(--accent-cyan);">

                        <i class="fa-solid fa-user-tie"></i>

                      </div>

                    </div>

                  <?php endif; ?>


                </div>


                <div class="p-4 text-center">


                  <span class="hero-tag mb-2">

                    Guru BK

                  </span>


                  <h5 class="fw-bold mb-1">

                    <?= htmlspecialchars($nama_guru); ?>

                  </h5>


                  <p class="text-secondary small mb-3">

                    NIP:
                    <?= htmlspecialchars($nip_guru); ?>

                  </p>


                  <a href="login.php" class="btn-outline-pill btn-sm px-4">

                    <i class="fa-solid fa-calendar-plus me-1"></i>

                    Ajukan Konseling

                  </a>


                </div>


              </div>

            </div>


          <?php endforeach; ?>


        <?php else: ?>


          <div class="col-12">

            <div class="text-center p-5">

              <i class="fa-solid fa-user-tie fs-1 text-secondary mb-3"></i>

              <h5 class="fw-bold">

                Data Guru BK Belum Tersedia

              </h5>

              <p class="text-secondary mb-0">

                Data Guru BK akan ditampilkan
                setelah ditambahkan oleh admin.

              </p>

            </div>

          </div>


        <?php endif; ?>


      </div>

    </div>

  </section>



  <!-- =========================================================
     CEK STATUS
========================================================= -->

  <section id="cek-status" class="section-padding" style="
        background: var(--bg-card-secondary);
        border-top: 1px solid var(--border-card);
    ">

    <div class="container">


      <div class="text-center max-w-700 mx-auto mb-5">

        <span class="section-tag">
          Layanan Transparan
        </span>

        <h2 class="section-heading">
          Cek Status Konseling
        </h2>

        <div class="section-divider"></div>

        <p class="text-secondary mt-3">

          Masukkan NIS untuk melihat jadwal,
          status, Guru BK, serta catatan konseling.

        </p>

      </div>


      <!-- SEARCH -->

      <div class="row justify-content-center mb-5">

        <div class="col-lg-7 col-md-9">


          <div class="status-search-card p-3">


            <form id="formCekStatus" method="GET" action="index.php#cek-status" class="d-flex gap-2">


              <div class="input-group">


                <span class="input-group-text" style="
                                    background: var(--bg-card-secondary);
                                    border-color: var(--border-input);
                                    color: var(--accent-cyan);
                                ">

                  <i class="fa-solid fa-id-card"></i>

                </span>


                <input type="text" id="inputNisCari" name="nis" class="form-control status-search-input"
                  placeholder="Masukkan NIS Anda" value="<?= htmlspecialchars($nis_cari); ?>" required>


              </div>


              <button type="submit" class="btn-primary-pill text-nowrap px-4">

                <i class="fa-solid fa-magnifying-glass"></i>

                Periksa

              </button>


            </form>


          </div>


          <div class="text-center mt-2">

            <small class="text-secondary">

              Masukkan NIS sesuai akun siswa yang
              terdaftar di sistem BK.

            </small>

          </div>


        </div>

      </div>


      <!-- HASIL -->

      <?php if ($nis_cari !== ''): ?>


        <div class="row justify-content-center">

          <div class="col-lg-10">


            <?php if (!empty($hasil_status)): ?>


              <?php
              $siswa = $hasil_status[0];
              ?>


              <!-- DATA SISWA -->

              <div class="card border shadow-sm mb-4 rounded-4 overflow-hidden" style="
                                background: var(--bg-card);
                                border-color: var(--border-card) !important;
                            ">

                <div class="card-header p-3" style="
                                    background: var(--bg-card-secondary);
                                    border-bottom: 1px solid var(--border-card);
                                ">

                  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div class="d-flex align-items-center gap-2">

                      <i class="fa-solid fa-user-graduate fs-5" style="color: var(--accent-cyan);"></i>

                      <span class="fw-bold" style="color: var(--text-main);">

                        <?= htmlspecialchars(
                          $siswa['nama']
                        ); ?>

                      </span>

                    </div>


                    <span class="hero-tag mb-0">

                      NIS:
                      <?= htmlspecialchars(
                        $siswa['nis']
                      ); ?>

                    </span>

                  </div>

                </div>


                <div class="card-body">

                  <div class="row g-3">


                    <div class="col-md-4">

                      <small class="text-secondary d-block">
                        NIS
                      </small>

                      <strong>

                        <?= htmlspecialchars(
                          $siswa['nis']
                        ); ?>

                      </strong>

                    </div>


                    <div class="col-md-4">

                      <small class="text-secondary d-block">
                        Kelas
                      </small>

                      <strong>

                        <?= htmlspecialchars(
                          $siswa['kelas']
                        ); ?>

                      </strong>

                    </div>


                    <div class="col-md-4">

                      <small class="text-secondary d-block">
                        Jurusan
                      </small>

                      <strong>

                        <?= htmlspecialchars(
                          $siswa['jurusan']
                        ); ?>

                      </strong>

                    </div>


                  </div>

                </div>

              </div>


              <!-- RIWAYAT -->

              <h5 class="fw-bold mb-3" style="color: var(--text-main);">

                <i class="fa-solid fa-calendar-days me-2" style="color: var(--accent-cyan);"></i>

                Riwayat Konseling

              </h5>


              <!-- DATA KONSELING -->

              <?php foreach ($hasil_status as $item): ?>


                <?php

                $badge =
                  statusBadgeHome(
                    $item['status']
                  );

                $tanggal = '-';

                if (!empty($item['tanggal'])) {

                  $tanggal =
                    date(
                      'd M Y, H:i',
                      strtotime(
                        $item['tanggal']
                      )
                    ) .
                    ' WIB';
                }

                ?>


                <div class="card border rounded-4 shadow-sm p-4 mb-3 status-result-card" style="
                                    background: var(--bg-card);
                                    border-color: var(--border-card) !important;
                                ">


                  <!-- HEADER -->

                  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">

                    <div>

                      <span class="text-secondary small d-block">

                        <i class="fa-regular fa-clock me-1"></i>

                        Jadwal Konseling

                      </span>


                      <h6 class="fw-bold mb-0 mt-1" style="color: var(--text-main);">

                        <?= htmlspecialchars(
                          $tanggal
                        ); ?>

                      </h6>

                    </div>


                    <span class="badge <?= $badge['class']; ?> fs-6">

                      <i class="fa-solid <?= $badge['icon']; ?> me-1"></i>

                      <?= htmlspecialchars(
                        $badge['text']
                      ); ?>

                    </span>

                  </div>


                  <!-- GURU -->

                  <div class="status-info-box mb-3">

                    <small class="text-secondary d-block fw-semibold mb-1">

                      Guru Pembimbing

                    </small>


                    <p class="mb-0 fw-bold" style="color: var(--text-main);">

                      <i class="fa-solid fa-user-tie me-2" style="color: var(--accent-cyan);"></i>


                      <?= htmlspecialchars(
                        $item['nama_guru']
                        ?? 'Belum ditentukan'
                      ); ?>

                    </p>

                  </div>


                  <!-- MASALAH -->

                  <div class="mb-3">

                    <small class="text-secondary d-block fw-semibold mb-1">

                      Masalah / Keluhan

                    </small>


                    <div class="status-problem-box">

                      <?php

                      $masalah =
                        $item['masalah'] ?? '';

                      if (
                        trim($masalah) !== ''
                      ) {

                        echo nl2br(
                          htmlspecialchars(
                            $masalah
                          )
                        );

                      } else {

                        echo 'Tidak ada keterangan masalah.';

                      }

                      ?>

                    </div>

                  </div>


                  <!-- SOLUSI -->

                  <?php if (
                    !empty(
                    trim(
                      $item['solusi'] ?? ''
                    )
                  )
                  ): ?>


                    <div class="status-solution-box">

                      <small class="text-success d-block fw-bold mb-1">

                        <i class="fa-solid fa-comment-medical me-1"></i>

                        Catatan & Solusi Guru BK

                      </small>


                      <div class="small" style="color: var(--text-main);">

                        <?= nl2br(
                          htmlspecialchars(
                            $item['solusi']
                          )
                        ); ?>

                      </div>

                    </div>


                  <?php endif; ?>


                </div>


              <?php endforeach; ?>


            <?php else: ?>


              <!-- TIDAK ADA DATA -->

              <div class="status-empty text-center">

                <i class="fa-regular fa-folder-open fs-1 text-secondary mb-3 d-block"></i>


                <h5 class="fw-bold" style="color: var(--text-main);">

                  Data Tidak Ditemukan

                </h5>


                <p class="text-secondary small mb-4">

                  NIS

                  <strong>

                    <?= htmlspecialchars(
                      $nis_cari
                    ); ?>

                  </strong>

                  belum memiliki data konseling
                  pada sistem.

                </p>


                <a href="login.php" class="btn-primary-pill btn-sm px-4">

                  <i class="fa-solid fa-right-to-bracket me-1"></i>

                  Masuk ke Portal BK

                </a>

              </div>


            <?php endif; ?>


          </div>

        </div>


      <?php endif; ?>


    </div>

  </section>



  <!-- =========================================================
     FOOTER
========================================================= -->

  <footer class="footer-bw">

    <div class="container">


      <div class="row g-4">


        <!-- ABOUT -->

        <div class="col-lg-4 col-md-6">

          <div class="d-flex align-items-center gap-2 mb-3">

            <i class="fa-solid fa-graduation-cap fs-2 text-white"></i>

            <span class="fs-5 fw-bold text-white">

              BK SMKN 7 KOTA SERANG

            </span>

          </div>


          <p class="text-secondary small mb-3">

            Unit Bimbingan dan Konseling SMKN 7
            Kota Serang berkomitmen mendampingi
            siswa dalam pengembangan pribadi,
            sosial, belajar, dan karir.

          </p>


          <div class="d-flex gap-2">

            <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="
                            width:36px;
                            height:36px;
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                        ">

              <i class="fa-brands fa-facebook-f"></i>

            </a>


            <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="
                            width:36px;
                            height:36px;
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                        ">

              <i class="fa-brands fa-instagram"></i>

            </a>


            <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="
                            width:36px;
                            height:36px;
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                        ">

              <i class="fa-brands fa-youtube"></i>

            </a>

          </div>

        </div>


        <!-- LINK -->

        <div class="col-lg-3 col-md-6">

          <h5>
            Tautan Cepat
          </h5>


          <ul class="list-unstyled small d-flex flex-column gap-2">

            <li>

              <a href="#beranda">

                <i class="fa-solid fa-angle-right me-2 text-white"></i>

                Beranda

              </a>

            </li>


            <li>

              <a href="#tentang">

                <i class="fa-solid fa-angle-right me-2 text-white"></i>

                Tentang BK

              </a>

            </li>


            <li>

              <a href="#layanan">

                <i class="fa-solid fa-angle-right me-2 text-white"></i>

                4 Layanan Konseling

              </a>

            </li>


            <li>

              <a href="#alur">

                <i class="fa-solid fa-angle-right me-2 text-white"></i>

                Alur Layanan Siswa

              </a>

            </li>


            <li>

              <a href="#guru">

                <i class="fa-solid fa-angle-right me-2 text-white"></i>

                Guru Pembimbing BK

              </a>

            </li>


            <li>

              <a href="#cek-status">

                <i class="fa-solid fa-angle-right me-2 text-white"></i>

                Cek Status Konseling

              </a>

            </li>

          </ul>

        </div>


        <!-- JAM -->

        <div class="col-lg-2 col-md-6">

          <h5>
            Jam Pelayanan
          </h5>


          <ul class="list-unstyled small text-secondary d-flex flex-column gap-2">

            <li>

              <strong class="text-white">
                Senin - Kamis:
              </strong>

              <br>

              07.00 - 15.30 WIB

            </li>


            <li>

              <strong class="text-white">
                Jumat:
              </strong>

              <br>

              07.00 - 15.00 WIB

            </li>


            <li>

              <strong class="text-white">
                Sabtu - Minggu:
              </strong>

              <br>

              Libur

            </li>

          </ul>

        </div>


        <!-- KONTAK -->

        <div class="col-lg-3 col-md-6">

          <h5>
            Kontak Sekolah
          </h5>


          <ul class="list-unstyled small text-secondary d-flex flex-column gap-2">

            <li>

              <i class="fa-solid fa-map-pin me-2 text-white"></i>

              Jl. Bangdes Jl. Kp. Baru,
              Panancangan, Kec. Cipocok Jaya,
              Kota Serang, Banten 42124

            </li>


            <li>

              <i class="fa-solid fa-phone me-2 text-white"></i>

              (0254) 123-456

            </li>


            <li>

              <i class="fa-solid fa-envelope me-2 text-white"></i>

              bimbingankonseling@smkn7serang.sch.id

            </li>


            <li>

              <i class="fa-solid fa-globe me-2 text-white"></i>

              smkn7serang.sch.id

            </li>

          </ul>

        </div>


      </div>


      <!-- FOOTER BOTTOM -->

      <div
        class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small">

        <p class="mb-2 mb-md-0">

          &copy;

          <span id="copyrightYear">
            <?= $tahun_sekarang; ?>
          </span>

          <strong>
            BK SMKN 7 Kota Serang
          </strong>.

          All Rights Reserved.

        </p>


        <p class="mb-0">

          <a href="login.php" class="text-secondary text-decoration-none">

            <i class="fa-solid fa-lock me-1"></i>

            Area Masuk Petugas & Siswa

          </a>

        </p>

      </div>

    </div>

  </footer>



  <!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



  <!-- =========================================================
     NAVBAR ACTIVE SYSTEM
========================================================= -->

  <script>

    document.addEventListener('DOMContentLoaded', function () {

      const navLinks =
        document.querySelectorAll(
          '.navbar-custom .nav-link[data-section]'
        );

      const sections = [];


      /*
      |--------------------------------------------------------------------------
      | AMBIL SECTION
      |--------------------------------------------------------------------------
      */

      navLinks.forEach(function (link) {

        const sectionId =
          link.getAttribute('data-section');

        const section =
          document.getElementById(sectionId);

        if (section) {

          sections.push({
            id: sectionId,
            element: section,
            link: link
          });

        }

      });


      /*
      |--------------------------------------------------------------------------
      | FUNGSI ACTIVE
      |--------------------------------------------------------------------------
      */

      function setActive(id) {

        navLinks.forEach(function (link) {

          const linkSection =
            link.getAttribute('data-section');

          if (linkSection === id) {

            link.classList.add('active');

          } else {

            link.classList.remove('active');

          }

        });

      }


      /*
      |--------------------------------------------------------------------------
      | KLIK MENU
      |--------------------------------------------------------------------------
      */

      navLinks.forEach(function (link) {

        link.addEventListener('click', function () {

          const sectionId =
            this.getAttribute('data-section');

          setActive(sectionId);


          /*
          | Tutup menu mobile
          */

          const navbar =
            document.getElementById('mainNav');

          if (
            navbar &&
            navbar.classList.contains('show')
          ) {

            const collapse =
              bootstrap.Collapse.getInstance(
                navbar
              );

            if (collapse) {

              collapse.hide();

            }

          }

        });

      });


      /*
      |--------------------------------------------------------------------------
      | ACTIVE BERDASARKAN SCROLL
      |--------------------------------------------------------------------------
      */

      function updateActiveMenu() {

        const scrollPosition =
          window.scrollY + 180;

        let currentSection = 'beranda';


        sections.forEach(function (item) {

          const sectionTop =
            item.element.offsetTop;

          if (
            scrollPosition >= sectionTop
          ) {

            currentSection =
              item.id;

          }

        });


        setActive(currentSection);

      }


      /*
      |--------------------------------------------------------------------------
      | SCROLL
      |--------------------------------------------------------------------------
      */

      window.addEventListener(
        'scroll',
        updateActiveMenu,
        {
          passive: true
        }
      );


      /*
      |--------------------------------------------------------------------------
      | LOAD
      |--------------------------------------------------------------------------
      */

      updateActiveMenu();

    });

  </script>



  <!-- =========================================================
     THEME
========================================================= -->

  <script>

    (function () {

      const key = 'bk_theme';

      const html =
        document.documentElement;

      const themeButton =
        document.querySelector(
          '.btn-theme-toggle'
        );


      /*
      |--------------------------------------------------------------------------
      | TEMA AWAL
      |--------------------------------------------------------------------------
      */

      const savedTheme =
        localStorage.getItem(key) || 'dark';

      html.setAttribute(
        'data-theme',
        savedTheme
      );

      html.setAttribute(
        'data-bs-theme',
        savedTheme
      );


      /*
      |--------------------------------------------------------------------------
      | ICON
      |--------------------------------------------------------------------------
      */

      function updateThemeIcon() {

        if (!themeButton) {
          return;
        }

        const icon =
          themeButton.querySelector('i');

        if (!icon) {
          return;
        }

        const currentTheme =
          html.getAttribute('data-theme');

        if (currentTheme === 'dark') {

          icon.className =
            'fa-solid fa-sun';

          themeButton.title =
            'Aktifkan Mode Terang';

        } else {

          icon.className =
            'fa-solid fa-moon';

          themeButton.title =
            'Aktifkan Mode Gelap';

        }

      }


      updateThemeIcon();


      /*
      |--------------------------------------------------------------------------
      | TOGGLE
      |--------------------------------------------------------------------------
      */

      if (themeButton) {

        themeButton.addEventListener(
          'click',
          function () {

            const currentTheme =
              html.getAttribute(
                'data-theme'
              );

            const newTheme =
              currentTheme === 'dark'
                ? 'light'
                : 'dark';


            html.setAttribute(
              'data-theme',
              newTheme
            );

            html.setAttribute(
              'data-bs-theme',
              newTheme
            );


            localStorage.setItem(
              key,
              newTheme
            );


            updateThemeIcon();

          }
        );

      }

    })();

  </script>



  <!-- =========================================================
     AUTO SCROLL CEK STATUS
========================================================= -->

  <?php if ($nis_cari !== ''): ?>

    <script>

      document.addEventListener(
        'DOMContentLoaded',
        function () {

          const section =
            document.getElementById(
              'cek-status'
            );

          if (section) {

            setTimeout(
              function () {

                section.scrollIntoView({
                  behavior: 'smooth',
                  block: 'start'
                });

              },
              150
            );

          }

        }
      );

    </script>

  <?php endif; ?>


</body>

</html>