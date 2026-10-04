/**
 * Theme Switcher: Dark (Navy) & Light (White)
 * BK Online
 *
 * Mendukung:
 * 1. .btn-theme-toggle
 * 2. #themeToggle
 */

(function () {

  const STORAGE_KEY = 'bk_theme';


  /* =====================================================
     AMBIL TEMA
  ===================================================== */

  function getPreferredTheme() {

    const saved =
      localStorage.getItem(STORAGE_KEY);

    if (saved === 'dark' || saved === 'light') {
      return saved;
    }

    // Default tetap DARK seperti sistem sebelumnya
    return 'dark';

  }


  /* =====================================================
     UPDATE ICON TOMBOL LAMA
     .btn-theme-toggle
  ===================================================== */

  function updateOldToggleIcons(theme) {

    const toggles =
      document.querySelectorAll(
        '.btn-theme-toggle i'
      );


    toggles.forEach(function (icon) {

      if (theme === 'dark') {

        icon.className =
          'fa-solid fa-sun';

        icon.title =
          'Beralih ke Mode Terang (Light/White)';

      } else {

        icon.className =
          'fa-solid fa-moon';

        icon.title =
          'Beralih ke Mode Gelap (Dark/Navy)';

      }

    });

  }


  /* =====================================================
     UPDATE ICON TOMBOL BARU
     #themeToggle
  ===================================================== */

  function updateNewToggleIcon(theme) {

    const icon =
      document.getElementById('themeIcon');

    const button =
      document.getElementById('themeToggle');


    if (!icon || !button) {
      return;
    }


    if (theme === 'dark') {

      // Saat dark, tampilkan matahari
      icon.className =
        'bi bi-sun-fill';

      button.title =
        'Beralih ke Light Mode';

    } else {

      // Saat light, tampilkan bulan
      icon.className =
        'bi bi-moon-fill';

      button.title =
        'Beralih ke Dark Mode';

    }

  }


  /* =====================================================
     UPDATE SEMUA ICON
  ===================================================== */

  function updateToggleIcons(theme) {

    updateOldToggleIcons(theme);

    updateNewToggleIcon(theme);

  }


  /* =====================================================
     TERAPKAN TEMA
  ===================================================== */

  function applyTheme(theme) {

    document.documentElement.setAttribute(
      'data-theme',
      theme
    );

    document.documentElement.setAttribute(
      'data-bs-theme',
      theme
    );

    localStorage.setItem(
      STORAGE_KEY,
      theme
    );


    // Update semua tombol
    updateToggleIcons(theme);

  }


  /* =====================================================
     TEMA AWAL
  ===================================================== */

  const initialTheme =
    getPreferredTheme();


  document.documentElement.setAttribute(
    'data-theme',
    initialTheme
  );

  document.documentElement.setAttribute(
    'data-bs-theme',
    initialTheme
  );


  /* =====================================================
     DOM READY
  ===================================================== */

  document.addEventListener(
    'DOMContentLoaded',
    function () {


      // Update icon sesuai tema
      updateToggleIcons(initialTheme);


      /* =================================================
         TOMBOL LAMA
         .btn-theme-toggle
      ================================================= */

      document
        .querySelectorAll('.btn-theme-toggle')
        .forEach(function (button) {

          button.addEventListener(
            'click',
            function (e) {

              e.preventDefault();


              const currentTheme =
                document.documentElement
                  .getAttribute('data-theme')
                || 'dark';


              const nextTheme =
                currentTheme === 'dark'
                  ? 'light'
                  : 'dark';


              applyTheme(nextTheme);

            }
          );

        });


      /* =================================================
         TOMBOL BARU
         #themeToggle
      ================================================= */

      const themeToggle =
        document.getElementById('themeToggle');


      if (themeToggle) {

        themeToggle.addEventListener(
          'click',
          function (e) {

            e.preventDefault();


            const currentTheme =
              document.documentElement
                .getAttribute('data-theme')
              || 'dark';


            const nextTheme =
              currentTheme === 'dark'
                ? 'light'
                : 'dark';


            applyTheme(nextTheme);

          }
        );

      }

    }
  );


})();