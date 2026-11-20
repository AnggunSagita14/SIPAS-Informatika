
<?php
session_start();

require_once "../config/database.php";

// Memeriksa hak akses Admin
if (
    !isset($_SESSION["id_user"]) ||
    ($_SESSION["role"] ?? "") != "admin"
) {
    header("Location: ../login.php");
    exit;
}

// Mengambil data surat yang sudah diterbitkan
$sql = "SELECT
            il.id_issued_letter,
            il.nomor_surat,
            il.tanggal_terbit,
            il.file_surat,
            u.nama_lengkap,
            u.nim,
            COALESCE(lt.nama_surat, a.jenis_surat_lainnya) AS jenis_surat
        FROM issued_letters il
        JOIN applications a ON il.id_application = a.id_application
        JOIN users u ON a.id_user = u.id_user
        LEFT JOIN letter_types lt ON a.id_letter_type = lt.id_letter_type
        ORDER BY il.tanggal_terbit DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$surat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Surat Terbit - SIPAS</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <style>
        .content-wrapper {
            padding: 25px;
        }

        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h2 {
            margin-bottom: 8px;
        }

        .table-container {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            overflow-x: auto;
        }

        .table-heading {
            margin-bottom: 20px;
        }

        .table-heading h3 {
            margin: 0 0 6px;
            color: #263449;
        }

        .table-heading p {
            margin: 0;
            color: #7b8494;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th {
            background: #f5f7fb;
            color: #475467;
            font-size: 13px;
            font-weight: 600;
            text-align: left;
            padding: 14px 12px;
            white-space: nowrap;
        }

        td {
            padding: 14px 12px;
            border-bottom: 1px solid #edf0f5;
            font-size: 13px;
            color: #475467;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .btn-lihat {
            display: inline-block;
            padding: 8px 12px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
        }

        .btn-lihat:hover {
            background: #1d4ed8;
        }

        .empty-data {
            text-align: center;
            padding: 30px;
            color: #7b8494;
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

        <a href="riwayat_surat.php" class="active">
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
            <h2>Riwayat Surat Terbit</h2>
            <p>Daftar surat akademik yang telah diterbitkan</p>
        </div>

        <div class="admin-profile">
            <div class="profile-avatar">A</div>
            <div>
                <strong><?= htmlspecialchars($_SESSION["nama_lengkap"]) ?></strong>
                <span>Administrator</span>
            </div>
        </div>
    </header>

    <div class="content-wrapper">

        <div class="table-container">

            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Mahasiswa</th>
                        <th>NIM</th>
                        <th>Jenis Surat</th>
                        <th>Nomor Surat</th>
                        <th>Tanggal Terbit</th>
                        <th>File Surat</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($surat) > 0): ?>

                        <?php $no = 1; ?>

                        <?php foreach ($surat as $row): ?>

                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <?= htmlspecialchars($row["nama_lengkap"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row["nim"] ?? "-") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row["jenis_surat"] ?? "-") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row["nomor_surat"]) ?>
                                </td>

                                <td>
                                    <?= date("d-m-Y", strtotime($row["tanggal_terbit"])) ?>
                                </td>

                                <td>
                                    <?php if (!empty($row["file_surat"])): ?>

                                        <a
                                            href="../uploads/surat/<?= rawurlencode(basename($row["file_surat"])) ?>"
                                            target="_blank"
                                            class="btn-lihat"
                                        >
                                            Lihat Surat
                                        </a>

                                    <?php else: ?>

                                        <span>-</span>

                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="empty-data">
                                Belum ada surat yang diterbitkan.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>

        </div>

    </div>

</div>

</body>
</html>
