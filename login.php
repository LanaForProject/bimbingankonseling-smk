<?php
session_start();
require_once __DIR__ . "/config/database.php";
$error = '';
$role = $_POST['role'] ?? 'siswa';
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($role === 'admin') {
        $stmt = $conn->prepare("
            SELECT id_admin, nama, username, password
            FROM admin
            WHERE username = ?
            LIMIT 1
        ");
        if (!$stmt) {
            $error = "Terjadi kesalahan pada sistem login.";
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION = [];
                $_SESSION['role'] = 'admin';
                $_SESSION['admin_id'] = (int) $user['id_admin'];
                $_SESSION['admin_nama'] = $user['nama'];
                $_SESSION['admin_username'] = $user['username'];
                header("Location: dashboard.php");
                exit;
            }
            $error = "Username atau password admin salah.";
        }
    } elseif ($role === 'guru') {
        $stmt = $conn->prepare("
            SELECT id_guru, nip, nama, foto, password
            FROM guru_bk
            WHERE nip = ?
            LIMIT 1
        ");
        if (!$stmt) {
            $error = "Terjadi kesalahan pada sistem login.";
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION = [];
                $_SESSION['role'] = 'guru';
                $_SESSION['guru_id'] = (int) $user['id_guru'];
                $_SESSION['guru_nip'] = $user['nip'];
                $_SESSION['guru_nama'] = $user['nama'];
                $_SESSION['guru_foto'] = $user['foto'] ?? '';
                header("Location: dashboard_guru.php");
                exit;
            }
            $error = "NIP atau password Guru BK salah.";
        }
    } elseif ($role === 'siswa') {
        $stmt = $conn->prepare("
            SELECT id_siswa, nis, nama, kelas, jurusan, foto, password
            FROM siswa
            WHERE nis = ?
            LIMIT 1
        ");
        if (!$stmt) {
            $error = "Terjadi kesalahan pada sistem login.";
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION = [];
                $_SESSION['role'] = 'siswa';
                $_SESSION['siswa_id'] = (int) $user['id_siswa'];
                $_SESSION['siswa_nis'] = $user['nis'];
                $_SESSION['siswa_nama'] = $user['nama'];
                $_SESSION['siswa_kelas'] = $user['kelas'];
                $_SESSION['siswa_jurusan'] = $user['jurusan'];
                $_SESSION['siswa_foto'] = $user['foto'] ?? '';
                header("Location: dashboard_siswa.php");
                exit;
            }
            $error = "NIS atau password siswa salah.";
        }
    } else {
        $error = "Role login tidak valid.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BK Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        (function () {
            const STORAGE_KEY = 'bk_theme';
            const savedTheme = localStorage.getItem(STORAGE_KEY);
            const theme = savedTheme || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <style>
        :root {
            --bg-body: #f5f7fb;
            --bg-card: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --primary: #0d6efd;
            --primary-hover: #0b5ed7;
            --danger: #dc3545;
        }

        [data-theme="dark"] {
            --bg-body: #111827;
            --bg-card: #1f2937;
            --text-main: #f9fafb;
            --text-muted: #9ca3af;
            --border-color: #374151;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            transition: background-color .2s ease, color .2s ease;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, .08);
        }

        .login-logo {
            width: 68px;
            height: 68px;
            margin: 0 auto 18px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(13, 110, 253, .12);
            color: #0d6efd;
            font-size: 31px;
        }

        .login-title {
            text-align: center;
            margin: 0;
            font-size: 27px;
            font-weight: 700;
            color: var(--text-main);
        }

        .login-subtitle {
            text-align: center;
            margin: 7px 0 28px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .role-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
        }

        .role-wrapper {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 21px;
        }

        .role-option {
            position: relative;
        }

        .role-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .role-option label {
            min-height: 75px;
            padding: 10px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-align: center;
            cursor: pointer;
            border: 1px solid var(--border-color);
            border-radius: 11px;
            color: var(--text-muted);
            transition: background .2s ease, border-color .2s ease, color .2s ease, transform .2s ease;
        }

        .role-option label:hover {
            border-color: #0d6efd;
            color: #0d6efd;
            transform: translateY(-1px);
        }

        .role-option label i {
            font-size: 21px;
        }

        .role-option label span {
            font-size: 12px;
            font-weight: 600;
        }

        .role-option input:checked+label {
            background: rgba(13, 110, 253, .1);
            border-color: #0d6efd;
            color: #0d6efd;
        }

        .form-label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
        }

        .form-control {
            min-height: 46px;
            border-radius: 10px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 10px 13px;
        }

        .form-control:focus {
            background: var(--bg-card);
            color: var(--text-main);
            border-color: #0d6efd;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: 8px;
        }

        .toggle-password:hover {
            color: #0d6efd;
            background: rgba(13, 110, 253, .08);
        }

        .btn-login {
            width: 100%;
            min-height: 46px;
            margin-top: 23px;
            border: none;
            border-radius: 10px;
            background: #0d6efd;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s ease, transform .2s ease;
        }

        .btn-login:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
        }

        .login-alert {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            padding: 12px 14px;
            margin-bottom: 20px;
            border-radius: 11px;
            background: rgba(220, 53, 69, .1);
            border: 1px solid rgba(220, 53, 69, .2);
            color: #dc3545;
            font-size: 13px;
            line-height: 1.5;
        }

        .login-info {
            margin-top: 21px;
            padding-top: 18px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .back-home {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: 100%;
            min-height: 43px;
            margin-top: 15px;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            background: transparent;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: background .2s ease, border-color .2s ease, color .2s ease, transform .2s ease;
        }

        .back-home:hover {
            background: rgba(13, 110, 253, .08);
            border-color: #0d6efd;
            color: #0d6efd;
            transform: translateY(-1px);
        }

        .back-home i {
            font-size: 15px;
        }

        @media (max-width: 480px) {
            .login-wrapper {
                padding: 15px;
            }

            .login-card {
                padding: 25px 20px;
                border-radius: 15px;
            }

            .login-title {
                font-size: 24px;
            }

            .role-wrapper {
                gap: 6px;
            }

            .role-option label {
                min-height: 70px;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <h1 class="login-title">BK Online</h1>
            <p class="login-subtitle">Sistem Bimbingan & Konseling</p>
            <?php if ($error !== ''): ?>
                <div class="login-alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div><?= htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>
            <form method="POST" autocomplete="off">
                <span class="role-label">Login sebagai</span>
                <div class="role-wrapper">
                    <div class="role-option">
                        <input type="radio" id="roleAdmin" name="role" value="admin" <?= $role === 'admin' ? 'checked' : ''; ?>>
                        <label for="roleAdmin">
                            <i class="bi bi-person-gear"></i>
                            <span>Admin</span>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="roleSiswa" name="role" value="siswa" <?= $role === 'siswa' ? 'checked' : ''; ?>>
                        <label for="roleSiswa">
                            <i class="bi bi-mortarboard-fill"></i>
                            <span>Siswa</span>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="roleGuru" name="role" value="guru" <?= $role === 'guru' ? 'checked' : ''; ?>>
                        <label for="roleGuru">
                            <i class="bi bi-person-workspace"></i>
                            <span>Guru BK</span>
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label" id="usernameLabel">NIS</label>
                    <input type="text" class="form-control" id="username" name="username"
                        value="<?= htmlspecialchars($username); ?>" placeholder="Masukkan NIS" required>
                </div>
                <div>
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Masukkan password" required>
                        <button type="button" class="toggle-password" id="togglePassword" title="Tampilkan password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    Login
                </button>
            </form>
            <div class="login-info">
                <div id="loginInfoText">Siswa login menggunakan NIS dan password.</div>
            </div>
            <a href="index.php" class="back-home">
                <i class="bi bi-house-door"></i>
                Kembali ke Halaman Utama
            </a>
        </div>
    </div>
    <script>
        const roleInputs = document.querySelectorAll('input[name="role"]');
        const usernameLabel = document.getElementById('usernameLabel');
        const usernameInput = document.getElementById('username');
        const loginInfoText = document.getElementById('loginInfoText');

        function updateLoginForm() {
            const selected = document.querySelector('input[name="role"]:checked');
            if (!selected) {
                return;
            }
            if (selected.value === 'admin') {
                usernameLabel.textContent = 'Username';
                usernameInput.placeholder = 'Masukkan username';
                loginInfoText.textContent = 'Admin login menggunakan username dan password.';
            } else if (selected.value === 'siswa') {
                usernameLabel.textContent = 'NIS';
                usernameInput.placeholder = 'Masukkan NIS';
                loginInfoText.textContent = 'Siswa login menggunakan NIS dan password.';
            } else if (selected.value === 'guru') {
                usernameLabel.textContent = 'NIP';
                usernameInput.placeholder = 'Masukkan NIP';
                loginInfoText.textContent = 'Guru BK login menggunakan NIP dan password.';
            }
        }

        roleInputs.forEach(function (input) {
            input.addEventListener('change', updateLoginForm);
        });

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                this.title = 'Sembunyikan password';
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                this.title = 'Tampilkan password';
            }
        });

        updateLoginForm();
    </script>
</body>

</html>