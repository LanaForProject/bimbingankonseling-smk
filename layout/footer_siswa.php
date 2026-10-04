</section>

</main>


<!-- =====================================================
     FOOTER
====================================================== -->

<footer class="student-footer">

    <span>
        BK Online © 2026 Sistem Bimbingan &amp; Konseling
    </span>

    <span>
        Sistem Informasi BK Sekolah
    </span>

</footer>


<!-- =====================================================
     THEME SCRIPT
====================================================== -->

<script>

    (function () {

        const button =
            document.getElementById('themeToggle');

        const html =
            document.documentElement;


        function updateIcon() {

            if (!button) {
                return;
            }


            const theme =
                html.getAttribute('data-theme');


            button.innerHTML =
                theme === 'dark'

                    ? '<i class="bi bi-sun-fill"></i>'

                    : '<i class="bi bi-moon-fill"></i>';


            button.title =
                theme === 'dark'

                    ? 'Gunakan mode terang'

                    : 'Gunakan mode gelap';
        }


        updateIcon();


        if (button) {

            button.addEventListener(
                'click',
                function () {

                    const current =
                        html.getAttribute('data-theme');


                    const next =
                        current === 'dark'
                            ? 'light'
                            : 'dark';


                    html.setAttribute(
                        'data-theme',
                        next
                    );


                    html.setAttribute(
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

    })();

</script>


</body>

</html>