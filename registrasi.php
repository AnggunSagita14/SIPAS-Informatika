
<?php
require_once "config/database.php";

$error = "";

// Periksa apakah akun Admin sudah terdaftar
$cek_admin = $conn->prepare("
    SELECT id_user
    FROM users
    WHERE id_role = 1
      AND email IS NOT NULL
      AND password IS NOT NULL
    LIMIT 1
");
$cek_admin->execute();

$admin_terdaftar = (bool) $cek_admin->fetch(PDO::FETCH_ASSOC);

// Halaman tetap ditampilkan meskipun Admin sudah terdaftar
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($admin_terdaftar) {
        $error = "Akun Admin sudah terdaftar. Registrasi hanya digunakan untuk setup awal.";

    } else {
        $nama = trim($_POST["nama_lengkap"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";
        $konfirmasi = $_POST["konfirmasi_password"] ?? "";

        if ($nama === "" || $email === "" || $password === "") {
            $error = "Semua kolom wajib diisi.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid.";
        } elseif (strlen($password) < 8) {
            $error = "Password minimal 8 karakter.";
        } elseif ($password !== $konfirmasi) {
            $error = "Konfirmasi password tidak sesuai.";
        } else {
            try {
                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $conn->prepare("
                    UPDATE users
                    SET nama_lengkap = :nama,
                        email = :email,
                        password = :password,
                        status_akun = 'aktif'
                    WHERE id_user = 1
                      AND id_role = 1
                ");

                $stmt->execute([
                    "nama" => $nama,
                    "email" => $email,
                    "password" => $password_hash
                ]);

                if ($stmt->rowCount() === 1) {
                    header("Location: login.php?registrasi=berhasil");
                    exit;
                }

                $error = "Akun Admin tidak ditemukan atau sudah terdaftar.";

            } catch (PDOException $e) {
                $error = "Email sudah digunakan atau registrasi gagal.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrasi Mahasiswa | SIPAS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --text: #172b4d;
            --muted: #718096;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 18px;
            overflow-x: hidden;
            background:
                linear-gradient(135deg, #f0f7ff 0%, #f8faff 50%, #e8f1ff 100%);
            position: relative;
        }

        /* Background dekorasi animasi */
        .background-shape {
            position: fixed;
            border-radius: 50%;
            filter: blur(2px);
            pointer-events: none;
            z-index: 0;
            animation: float 9s ease-in-out infinite;
        }

        .shape-one {
            width: 320px;
            height: 320px;
            background: rgba(59, 130, 246, 0.12);
            top: -100px;
            left: -80px;
        }

        .shape-two {
            width: 260px;
            height: 260px;
            background: rgba(147, 197, 253, 0.25);
            bottom: -80px;
            right: -60px;
            animation-delay: -3s;
        }

        .shape-three {
            width: 100px;
            height: 100px;
            background: rgba(37, 99, 235, 0.08);
            top: 25%;
            right: 12%;
            animation-delay: -5s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
            }

            50% {
                transform: translateY(-25px) translateX(12px);
            }
        }

        /* Layout */
        .page-wrapper {
            width: 100%;
            max-width: 1050px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(37, 99, 235, 0.12);
            backdrop-filter: blur(18px);
            position: relative;
            z-index: 1;
            animation: cardEnter 0.8s ease both;
        }

        @keyframes cardEnter {
            from {
                opacity: 0;
                transform: translateY(35px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Panel kiri */
        .welcome-panel {
            background: linear-gradient(145deg, #1e40af, #2563eb, #3b82f6);
            color: white;
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .welcome-panel::before {
            content: "";
            position: absolute;
            width: 350px;
            height: 350px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            top: -100px;
            right: -150px;
        }

        .welcome-panel::after {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            bottom: -100px;
            left: -100px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            z-index: 1;
        }

        
.brand-logo {
    width: 58px;
    height: 58px;
    min-width: 58px;
    border-radius: 15px;
    background: white;
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.brand-logo img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

        .brand-name {
            font-size: 23px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .brand-subtitle {
            font-size: 10px;
            letter-spacing: 2px;
            opacity: 0.75;
            margin-top: 4px;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
            margin: 65px 0;
        }

        .welcome-badge {
            display: inline-block;
            padding: 8px 13px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.12);
            border-radius: 30px;
            font-size: 11px;
            margin-bottom: 22px;
            letter-spacing: 0.5px;
        }

        .welcome-content h1 {
            font-size: clamp(28px, 3vw, 38px);
            line-height: 1.25;
            margin-bottom: 18px;
            font-weight: 800;
        }

        .welcome-content p {
            color: rgba(255, 255, 255, 0.78);
            font-size: 14px;
            line-height: 1.8;
            max-width: 360px;
        }

        .welcome-footer {
            position: relative;
            z-index: 1;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.65);
        }

        /* Panel registrasi */
        .register-panel {
            padding: 50px 45px;
        }

        .register-heading {
            margin-bottom: 30px;
            animation: fadeUp 0.7s 0.2s ease both;
        }

        .register-heading .small-title {
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .register-heading h2 {
            color: var(--text);
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .register-heading p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-message {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
            padding: 13px 15px;
            border-radius: 10px;
            font-size: 12px;
            margin-bottom: 20px;
            animation: fadeUp 0.3s ease;
        }

        .form-group {
            margin-bottom: 19px;
            animation: fadeUp 0.6s ease both;
        }

        .form-group:nth-of-type(1) {
            animation-delay: 0.25s;
        }

        .form-group:nth-of-type(2) {
            animation-delay: 0.35s;
        }

        .form-group:nth-of-type(3) {
            animation-delay: 0.45s;
        }

        .form-group:nth-of-type(4) {
            animation-delay: 0.55s;
        }

        .form-group label {
            display: block;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 9px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            height: 49px;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0 15px;
            font-family: inherit;
            font-size: 13px;
            color: var(--text);
            background: #fbfdff;
            transition: 0.25s ease;
        }

        .input-wrapper input::placeholder {
            color: #a0aec0;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #60a5fa;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }

        .password-note {
            color: #94a3b8;
            font-size: 10px;
            margin-top: 7px;
        }

        .register-button {
            width: 100%;
            height: 51px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.22);
            transition: 0.25s ease;
            animation: fadeUp 0.6s 0.6s ease both;
        }

        .register-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(37, 99, 235, 0.32);
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .register-button:active {
            transform: scale(0.98);
        }

        .security-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px;
            background: #f0f7ff;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            margin-top: 22px;
            animation: fadeUp 0.6s 0.7s ease both;
        }

        .security-icon {
            color: var(--primary);
            font-size: 16px;
        }

        .security-note p {
            color: #64748b;
            font-size: 10px;
            line-height: 1.7;
        }

        .security-note strong {
            color: #1e40af;
        }

        .bottom-text {
            text-align: center;
            margin-top: 25px;
            font-size: 11px;
            color: #94a3b8;
        }

        .bottom-text a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .bottom-text a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 850px) {
            .page-wrapper {
                max-width: 520px;
                grid-template-columns: 1fr;
            }

            .welcome-panel {
                padding: 30px;
            }

            .welcome-content {
                margin: 35px 0 25px;
            }

            .welcome-content h1 {
                font-size: 27px;
            }

            .welcome-content p {
                font-size: 12px;
            }

            .welcome-footer {
                display: none;
            }

            .register-panel {
                padding: 35px 30px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .page-wrapper {
                border-radius: 20px;
            }

            .welcome-panel {
                padding: 25px;
            }

            .brand-logo {
                width: 45px;
                height: 45px;
                border-radius: 12px;
            }

            .brand-name {
                font-size: 20px;
            }

            .welcome-content {
                margin: 30px 0 10px;
            }

            .welcome-content h1 {
                font-size: 25px;
            }

            .register-panel {
                padding: 30px 24px;
            }

            .register-heading h2 {
                font-size: 24px;
            }
        }

        /* Mengurangi animasi jika pengguna memilih reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>

<div class="background-shape shape-one"></div>
<div class="background-shape shape-two"></div>
<div class="background-shape shape-three"></div>

<div class="page-wrapper">

    <!-- Panel informasi -->
    <section class="welcome-panel">


<div class="brand">
    <div class="brand-logo">
    <img src="/SIPAS-Informatika/assets/logo-informatika.png"
         alt="Logo Informatika UNTAN">
</div>

    <div>
        <div class="brand-name">SIPAS</div>
        <div class="brand-subtitle">INFORMATIKA UNTAN</div>
    </div>
</div>

        <div class="welcome-content">

            <div class="welcome-badge">
                ✦ Sistem Pelayanan Akademik
            </div>

            <h1>Selamat Datang<br>di SIPAS</h1>

            <p>
                Sistem Informasi Pelayanan Administrasi Surat
                untuk mendukung pengelolaan surat akademik
                secara lebih mudah, terstruktur, dan efisien.
            </p>

        </div>

        <div class="welcome-footer">
            © 2026 SIPAS Informatika UNTAN
        </div>

    </section>

    <!-- Panel registrasi -->
    <section class="register-panel">

        <div class="register-heading">
            <div class="small-title">Administrator Account</div>

            <h2>Buat Akun Admin</h2>

            <p>
                Lengkapi data berikut untuk membuat akun
                Administrator SIPAS.
            </p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>

                <div class="input-wrapper">
                    <input
                        type="text"
                        id="nama_lengkap"
                        name="nama_lengkap"
                        placeholder="Masukkan nama lengkap"
                        autocomplete="name"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>

                <div class="input-wrapper">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@example.com"
                        autocomplete="email"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <div class="input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Buat password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="password-note">
                    Password minimal 8 karakter.
                </div>
            </div>

            <div class="form-group">
                <label for="konfirmasi_password">
                    Konfirmasi Password
                </label>

                <div class="input-wrapper">
                    <input
                        type="password"
                        id="konfirmasi_password"
                        name="konfirmasi_password"
                        placeholder="Ulangi password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="register-button">
                Daftar sebagai Admin &nbsp; →
            </button>

        </form>

        <div class="security-note">
            <div class="security-icon">🔒</div>

            <p>
                <strong>Registrasi terbatas</strong><br>
                SIPAS hanya memiliki satu akun Administrator.
                Setelah akun dibuat, registrasi akan ditutup.
            </p>
        </div>

        <div class="bottom-text">
            Sudah memiliki akun?
            <a href="login.php">Login di sini</a>
        </div>

    </section>

</div>

</body>
</html>
