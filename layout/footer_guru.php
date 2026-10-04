</section>

</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="guru-footer">

    <span>
        &copy; <?= date('Y'); ?> BK Online
    </span>

    <span>
        Sistem Bimbingan & Konseling
    </span>

</footer>


<!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- =========================================================
     THEME SCRIPT
========================================================= -->

<script>

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const toggle =
                document.getElementById(
                    'guruThemeToggle'
                );


            if (!toggle) {
                return;
            }


            const icon =
                toggle.querySelector('i');


            function updateIcon() {

                const theme =
                    document.documentElement.getAttribute(
                        'data-theme'
                    );


                if (theme === 'dark') {

                    icon.className =
                        'bi bi-sun-fill';

                    toggle.title =
                        'Aktifkan mode terang';

                } else {

                    icon.className =
                        'bi bi-moon-fill';

                    toggle.title =
                        'Aktifkan mode gelap';

                }

            }


            updateIcon();


            toggle.addEventListener(
                'click',
                function () {

                    const current =
                        document.documentElement.getAttribute(
                            'data-theme'
                        );


                    const next =
                        current === 'dark'
                            ? 'light'
                            : 'dark';


                    document.documentElement.setAttribute(
                        'data-theme',
                        next
                    );

                    document.documentElement.setAttribute(
                        'data-bs-theme',
                        next
                    );


                    localStorage.setItem(
                        'bk_theme',
                        next
                    );


                    updateIcon();

                }
            );

        }
    );

</script>


</body>

</html>