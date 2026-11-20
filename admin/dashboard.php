
<?php
session_start();

// Memeriksa hak akses Admin
if (
    !isset($_SESSION["id_user"]) ||
    $_SESSION["role"] != "admin"
) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

// Menghitung statistik pengajuan
$sql_total = "SELECT COUNT(*) FROM applications";
$total_pengajuan = $conn->query($sql_total)->fetchColumn();

$sql_verifikasi = "SELECT COUNT(*) FROM applications
                   WHERE status_pengajuan = 'menunggu_verifikasi'";
$menunggu_verifikasi = $conn->query($sql_verifikasi)->fetchColumn();

$sql_perbaikan = "SELECT COUNT(*) FROM applications
                  WHERE status_pengajuan = 'perlu_perbaikan'";
$perlu_perbaikan = $conn->query($sql_perbaikan)->fetchColumn();

$sql_selesai = "SELECT COUNT(*) FROM applications
                WHERE status_pengajuan = 'selesai'";
$surat_selesai = $conn->query($sql_selesai)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIPAS Informatika</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
<img
    src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ9YcTImabDnZNSKtDQ6Tj8eE4NVcJenuOz9DLZuuYAXA&amp;s=10"
    alt="Logo Informatika"
    style="
        width: 45px;
        height: 45px;
        object-fit: contain;
        border-radius: 50%;
        background: white;
        padding: 3px;
        box-sizing: border-box;
    "
>
        <div>
            <h2>SIPAS</h2>
            <p>INFORMATIKA UNTAN</p>
        </div>
    </div>

    <div class="sidebar-label">MENU UTAMA</div>

    <nav>
        <a href="dashboard.php" class="active">
            <span class="menu-icon">⌂</span>
            Dashboard
        </a>
        <style>
    .card-link {
        display: block;
        color: inherit;
        text-decoration: none;
    }

    .card-link .card {
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card-link:hover .card {
        transform: translateY(-4px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.10);
    }
</style>
        <a href="data_pengajuan.php">
            <span class="menu-icon">▤</span>
            Data Pengajuan
        </a>
        <a href="data_mahasiswa.php">
            <span class="menu-icon">♙</span>
            Data Mahasiswa
        </a>
        <a href="riwayat_pengajuan.php">
            <span class="menu-icon">◷</span>
            Riwayat Pengajuan
        </a>
        <a href="terbitkan_surat.php">
            <span class="menu-icon">▣</span>
            Penerbitan Surat
        </a>
        <a href="riwayat_surat.php">
            <span class="menu-icon">▥</span>
            Riwayat Surat Terbit
        </a>
    </nav>

    <div class="sidebar-bottom">
        <a href="../logout.php">
            <span class="menu-icon">↪</span>
            Keluar
        </a>
    </div>
</div>

<main class="main-content">

    <header class="topbar">
        <div>
            <h2>Dashboard</h2>
            <p>Ringkasan aktivitas administrasi akademik</p>
        </div>

        <div class="admin-profile">
            <div class="profile-avatar">A</div>
            <div>
                <strong><?= htmlspecialchars($_SESSION["nama_lengkap"]) ?></strong>
                <span>Administrator</span>
            </div>
        </div>
    </header>

    <section class="welcome">
        <div class="welcome-text">
            <span class="welcome-label">ADMINISTRATOR PANEL</span>
            <h1>Selamat Datang, Admin!</h1>
            <p>
                Kelola pengajuan surat akademik mahasiswa
                Program Studi Informatika dengan mudah melalui
                sistem informasi SIPAS.
            </p>
        </div>
        <div class="welcome-decoration">SI</div>
    </section>

    <section class="section-heading">
        <div>
            <h2>Ringkasan Pengajuan</h2>
            <p>Informasi terbaru mengenai aktivitas pengajuan surat.</p>
        </div>
    </section>


<section class="dashboard-cards">

    <!-- Total Pengajuan -->
    <a href="data_pengajuan.php" class="card-link">
        <div class="card">
            <div class="card-top">
                <div class="card-icon icon-blue">▤</div>
                <span class="card-tag">Keseluruhan</span>
            </div>
            <h3>Total Pengajuan</h3>
            <p><?= $total_pengajuan ?></p>
            <span class="card-description">Seluruh pengajuan mahasiswa</span>
        </div>
    </a>

    <!-- Menunggu Verifikasi -->
    <a href="data_pengajuan.php" class="card-link">
        <div class="card">
            <div class="card-top">
                <div class="card-icon icon-orange">◷</div>
                <span class="card-tag">Diproses</span>
            </div>
            <h3>Menunggu Verifikasi</h3>
            <p><?= $menunggu_verifikasi ?></p>
            <span class="card-description">Menunggu pemeriksaan admin</span>
        </div>
    </a>

    <!-- Perlu Perbaikan -->
    <a href="data_pengajuan.php" class="card-link">
        <div class="card">
            <div class="card-top">
                <div class="card-icon icon-red">!</div>
                <span class="card-tag">Perhatian</span>
            </div>
            <h3>Perlu Perbaikan</h3>
            <p><?= $perlu_perbaikan ?></p>
            <span class="card-description">Pengajuan membutuhkan perbaikan</span>
        </div>
    </a>

    <!-- Surat Selesai -->
    <a href="riwayat_surat.php" class="card-link">
        <div class="card">
            <div class="card-top">
                <div class="card-icon icon-green">✓</div>
                <span class="card-tag">Selesai</span>
            </div>
            <h3>Surat Selesai</h3>
            <p><?= $surat_selesai ?></p>
            <span class="card-description">Surat berhasil diterbitkan</span>
        </div>
    </a>

</section>

    <section class="dashboard-note">
        <div class="note-icon">i</div>
        <div>
            <strong>Informasi Sistem</strong>
            <p>
    Pastikan setiap pengajuan diperiksa dengan teliti.
    Pengajuan yang telah disetujui dapat dilanjutkan ke proses
    penerbitan surat oleh Admin.
</p>
        </div>
    </section>

    <footer class="dashboard-footer">
        SIPAS Informatika UNTAN &copy; 2026
    </footer>

</main>

</body>
</html>
