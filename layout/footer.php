        </main>


        <!-- =================================================
             FOOTER
        ================================================== -->

        <footer class="main-footer">

            <div>

                <strong>BK Online</strong>

                <span>
                    © <?= date('Y'); ?> Sistem Bimbingan & Konseling
                </span>

            </div>


            <div>

                Sistem Informasi BK Sekolah

            </div>

        </footer>


    </div>

</div>


<!-- BOOTSTRAP JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<!-- SIDEBAR -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const sidebar =
        document.getElementById("sidebar");

    const toggle =
        document.getElementById("sidebarToggle");


    if (toggle && sidebar) {

        toggle.addEventListener("click", function () {

            sidebar.classList.toggle("show");

        });

    }

});

</script>


<!-- SIDEBAR SEARCH -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const input =
        document.getElementById("sidebarSearch");

    const items =
        document.querySelectorAll("#sidebarMenu a");


    if (!input) {
        return;
    }


    input.addEventListener("keyup", function () {

        const keyword =
            this.value.toLowerCase().trim();


        items.forEach(function (item) {

            const text =
                item.textContent.toLowerCase();


            item.style.display =
                text.includes(keyword)
                    ? "flex"
                    : "none";

        });

    });

});

</script>


<!-- FULLSCREEN -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const button =
        document.getElementById("fullscreenButton");


    if (!button) {
        return;
    }


    button.addEventListener("click", function (e) {

        e.preventDefault();


        if (!document.fullscreenElement) {

            document.documentElement.requestFullscreen();

        } else {

            document.exitFullscreen();

        }

    });

});

</script>


<!-- THEME -->

<script src="assets/js/theme.js"></script>


</body>

</html>