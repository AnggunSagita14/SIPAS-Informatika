
<?php
session_start();

if (
    !isset($_SESSION["id_user"]) ||
    $_SESSION["role"] != "admin"
) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

$query = $conn->query("
    SELECT id_letter_type, nama_surat
    FROM letter_types
    ORDER BY id_letter_type ASC
");

$jenis_surat = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Surat - SIPAS Informatika</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <style>
        .page-heading {
            margin-top: 25px;
        }

        .page-heading h1 {
            color: #123B70;
            font-size: 23px;
            margin-bottom: 7px;
        }

        .page-heading p {
            color: #8190A3;
            font-size: 13px;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-top: 25px;
            border: 1px solid #e8edf5;
            box-shadow: 0 5px 20px rgba(18, 52, 90, 0.04);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        th {
            background: #f1f5f9;
            color: #334B68;
            font-weight: 600;
        }

        td {
            color: #475569;
        }

        .empty {
            text-align: center;
            padding: 25px;
            color: #64748b;
        }
    </style>
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
        <a href="dashboard.php">
            <span class="menu-icon">⌂</span>
            Dashboard
        </a>

        <a href="data_pengajuan.php">
            <span class="menu-icon">▤</span>
            Data Pengajuan
        </a>

        <a href="jenis_surat.php" class="active">
            <span class="menu-icon">▧</span>
            Jenis Surat
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
            <h2>Jenis Surat</h2>
            <p>Daftar jenis surat akademik yang tersedia</p>
        </div>

        <div class="admin-profile">
            <div class="profile-avatar">A</div>
            <div>
                <strong>
                    <?= htmlspecialchars($_SESSION["nama_lengkap"]) ?>
                </strong>
                <span>Administrator</span>
            </div>
        </div>
    </header>

    <div class="table-container">

        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">No.</th>
                    <th>Nama Jenis Surat</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($jenis_surat) > 0): ?>

                    <?php $no = 1; ?>

                    <?php foreach ($jenis_surat as $surat): ?>

                        <tr>
                            <td><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars($surat["nama_surat"]); ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="2" class="empty">
                            Belum ada jenis surat.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>
        </table>

    </div>

    <footer class="dashboard-footer">
        SIPAS Informatika UNTAN &copy; 2026
    </footer>

</main>

</body>
</html>
