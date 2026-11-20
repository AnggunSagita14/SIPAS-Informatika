
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
    SELECT
        u.nim,
        u.nama_lengkap,
        u.no_hp,
        COUNT(a.id_application) AS jumlah_pengajuan
    FROM users u
    LEFT JOIN applications a
        ON u.id_user = a.id_user
    WHERE u.id_role = 1
    GROUP BY
        u.id_user,
        u.nim,
        u.nama_lengkap,
        u.no_hp
    ORDER BY u.nama_lengkap ASC
");

$mahasiswa = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - SIPAS Informatika</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <style>
        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h2 {
            margin-bottom: 8px;
        }

        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        .data-table th,
        .data-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background: #f5f7fb;
            color: #444;
        }

        .data-table tr:last-child td {
            border-bottom: none;
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
        <a href="data_mahasiswa.php" class="active">
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

<div class="main-content">

    <header class="topbar">
        <div>
            <h2>Data Mahasiswa</h2>
            <p>Daftar identitas mahasiswa Informatika</p>
        </div>

        <div class="admin-profile">
            <div class="profile-avatar">A</div>
            <div>
                <strong><?= htmlspecialchars($_SESSION["nama_lengkap"]) ?></strong>
                <span>Administrator</span>
            </div>
        </div>
    </header>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIM</th>
                    <th>Nama Lengkap</th>
                    <th>No. HP</th>
                    <th>Jumlah Pengajuan</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($mahasiswa) > 0): ?>
                    <?php $no = 1; ?>

                    <?php foreach ($mahasiswa as $mhs): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($mhs["nim"] ?? "-"); ?></td>
                            <td><?= htmlspecialchars($mhs["nama_lengkap"]); ?></td>
                            <td><?= htmlspecialchars($mhs["no_hp"] ?? "-"); ?></td>
                            <td><?= (int) $mhs["jumlah_pengajuan"]; ?> pengajuan</td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">
                            Belum ada data mahasiswa.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
