
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
        a.id_application,
        u.nama_lengkap,
        u.nim,
        COALESCE(lt.nama_surat, a.jenis_surat_lainnya) AS jenis_surat,
        a.tanggal_pengajuan,
        a.status_pengajuan
    FROM applications a
    JOIN users u ON a.id_user = u.id_user
    LEFT JOIN letter_types lt
        ON a.id_letter_type = lt.id_letter_type
    ORDER BY a.tanggal_pengajuan DESC
");

$riwayat = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengajuan - SIPAS Informatika</title>
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
            min-width: 850px;
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

        .detail-link {
            display: inline-block;
            padding: 7px 14px;
            background: #eaf1ff;
            color: #2563eb;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .detail-link:hover {
            background: #dce8ff;
        }

        .status-label {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: #f1f3f5;
    color: #555;
}

.status-menunggu_verifikasi {
    background: #fff4d6;
    color: #946200;
}

.status-perlu_perbaikan {
    background: #fff0e6;
    color: #c45b16;
}

.status-disetujui {
    background: #e5f7ed;
    color: #21834b;
}

.status-ditolak {
    background: #feecec;
    color: #c62828;
}

.status-selesai {
    background: #e8efff;
    color: #2856b6;
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
        <a href="riwayat_pengajuan.php" class="active">
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
            <h2>Riwayat Pengajuan</h2>
            <p>Seluruh riwayat pengajuan surat mahasiswa</p>
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
                    <th>Nama Mahasiswa</th>
                    <th>NIM</th>
                    <th>Jenis Surat</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Status</th>
                    <th>Detail</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($riwayat) > 0): ?>
                    <?php $no = 1; ?>

                    <?php foreach ($riwayat as $data): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($data["nama_lengkap"]); ?></td>
                            <td><?= htmlspecialchars($data["nim"] ?? "-"); ?></td>
                            <td><?= htmlspecialchars($data["jenis_surat"] ?? "-"); ?></td>
                            <td><?= htmlspecialchars($data["tanggal_pengajuan"]); ?></td>
                            <td>
                                <span class="status-label status-<?= htmlspecialchars($data["status_pengajuan"]); ?>">
    <?= htmlspecialchars(ucwords(str_replace("_", " ", $data["status_pengajuan"]))); ?>
</span>
                            </td>
                            <td>
                                <a
                                    href="detail_pengajuan.php?id=<?= (int) $data["id_application"]; ?>"
                                    class="detail-link"
                                >
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">
                            Belum ada riwayat pengajuan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
