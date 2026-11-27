
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPAS Informatika</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #eef4ff, #f8faff, #e4edff);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
            overflow: hidden;
        }

        .circle {
            position: fixed;
            border-radius: 50%;
            background: rgba(80, 130, 255, 0.10);
            animation: float 7s ease-in-out infinite;
        }

        .circle-one {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -80px;
        }

        .circle-two {
            width: 220px;
            height: 220px;
            bottom: -70px;
            right: -50px;
            animation-delay: 2s;
        }

        .container {
            position: relative;
            width: 100%;
            max-width: 950px;
            background: white;
            border-radius: 28px;
            box-shadow: 0 25px 70px rgba(45, 75, 140, 0.13);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            animation: appear 0.8s ease;
        }

        .left {
            background: linear-gradient(145deg, #2858c5, #4779e8);
            color: white;
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

.logo {
    width: 76px;
    height: 76px;
    border-radius: 18px;
    background: white;
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    animation: logoFloat 3s ease-in-out infinite;
}

.logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.brand-name {
    display: flex;
    flex-direction: column;
    margin-bottom: 25px;
    color: white;
}

.brand-name span {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 2px;
}

.brand-name small {
    font-size: 9px;
    letter-spacing: 1.5px;
    color: #dce7ff;
    margin-top: 4px;
}

.typing {
    display: inline-block;
    min-height: 20px;
}

.typing::after {
    content: "|";
    margin-left: 4px;
    animation: blink 0.7s infinite;
}

@keyframes blink {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0;
    }
}

@keyframes logoFloat {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

        .left h1 {
            font-size: 34px;
            line-height: 1.3;
            margin-bottom: 18px;
        }

        .left p {
            font-size: 14px;
            line-height: 1.9;
            color: #e4ecff;
        }

        .illustration {
            margin-top: 35px;
            font-size: 65px;
            opacity: 0.9;
        }

        .right {
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right .tag {
            color: #4779e8;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }

        .right h2 {
            color: #1d2b4f;
            font-size: 27px;
            margin-bottom: 12px;
        }

        .description {
            color: #7b8498;
            font-size: 13px;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 15px;
            border-radius: 13px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
            margin-bottom: 15px;
        }

        .button-login {
            background: #3568d4;
            color: white;
            box-shadow: 0 8px 20px rgba(53, 104, 212, 0.22);
        }

        .button-login:hover {
            background: #244fae;
            transform: translateY(-3px);
            color: white;
        }

        .button-register {
            background: white;
            color: #3568d4;
            border: 1.5px solid #dce5f8;
        }

        .button-register:hover {
            background: #f2f6ff;
            border-color: #3568d4;
            transform: translateY(-3px);
            color: #244fae;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            color: #a0a8b8;
        }

        @keyframes appear {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        @media (max-width: 700px) {
            body {
                overflow: auto;
            }

            .container {
                grid-template-columns: 1fr;
                max-width: 450px;
            }

            .left {
                padding: 35px 30px;
            }

            .left h1 {
                font-size: 27px;
            }

            .logo {
                width: 55px;
                height: 55px;
                margin-bottom: 20px;
            }

            .illustration {
                display: none;
            }

            .right {
                padding: 35px 30px;
            }

            .right h2 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

    <div class="circle circle-one"></div>
    <div class="circle circle-two"></div>

    <main class="container">

        <section class="left">

<div class="logo">
    <img
        src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTwzn_3SgDGs1covLp67lIAESYtvhPnZn8Sxa6XDueGjEHCb-CEun2GFTs&s=10"
        alt="Logo Informatika Universitas Tanjungpura"
    >
</div>

<div class="brand-name">
    <span>INFORMATIKA</span>
    <small>UNIVERSITAS TANJUNGPURA</small>
</div>

            <h1>SIPAS<br>Informatika</h1>

            <p>
                Sistem Informasi Pelayanan Administrasi Surat
                untuk mempermudah pengelolaan dan pemantauan
                layanan surat akademik secara digital.
            </p>

            <div class="illustration">📄</div>
        </section>

        <section class="right">
            <span class="tag typing" id="welcomeText"></span>

            <h2>Mulai Akses SIPAS</h2>

<p class="description">
    Admin dapat masuk untuk mengelola layanan surat akademik.
    Mahasiswa dapat melacak pengajuan menggunakan kode surat
    dan kode akses tanpa perlu login.
</p>

            <a href="login.php" class="button button-login">
                <span>→</span> Login Admin
            </a>

            <a href="jejak_surat.php" class="button button-register">
    <span>⌕</span> Jejak Surat
</a>

            <div class="footer">
                © 2026 SIPAS Informatika
            </div>
        </section>

    </main>
<script>
    const welcomeText = document.getElementById("welcomeText");
    const greeting = "SELAMAT DATANG";

    let index = 0;

    function typeGreeting() {
        if (index < greeting.length) {
            welcomeText.textContent += greeting.charAt(index);
            index++;

            setTimeout(typeGreeting, 100);
        }
    }

    window.addEventListener("load", typeGreeting);
</script>
</body>
</html>
