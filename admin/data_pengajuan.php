
<?php
session_start();

require_once "../config/database.php";

// Memeriksa hak akses Admin
if (
    !isset($_SESSION["id_user"]) ||
    $_SESSION["role"] != "admin"
) {
    header("Location: ../login.php");
    exit;
}

// Mengambil seluruh data pengajuan mahasiswa
$sql = "SELECT
            applications.*,
            users.nama_lengkap,
            users.nim,
            COALESCE(
                letter_types.nama_surat,
                applications.jenis_surat_lainnya
            ) AS nama_surat
        FROM applications
        JOIN users
            ON applications.id_user = users.id_user
        LEFT JOIN letter_types
            ON applications.id_letter_type = letter_types.id_letter_type
        ORDER BY applications.tanggal_pengajuan DESC";

$stmt = $conn->query($sql);
$data_pengajuan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mengubah status menjadi tulisan yang mudah dibaca
function labelStatus($status) {
    $daftar = [
        'draft' => 'Draft',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'perlu_perbaikan' => 'Perlu Perbaikan',
        'menunggu_persetujuan' => 'Menunggu Persetujuan',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
        'selesai' => 'Selesai'
    ];

    return $daftar[$status] ?? $status;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengajuan - SIPAS Informatika</title>

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
            overflow-x: auto;
            border: 1px solid #e8edf5;
            box-shadow: 0 5px 20px rgba(18, 52, 90, 0.04);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
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

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #e0e7ff;
            color: #3730a3;
            font-size: 12px;
            white-space: nowrap;
        }

        .detail-link {
            display: inline-block;
            padding: 7px 12px;
            background: #EAF3FC;
            color: #1D5FAF;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        .detail-link:hover {
            background: #D6E9FA;
        }

        .kosong {
            text-align: center;
            padding: 30px;
            color: #64748b;
        }


        .btn-tambah {
            display: inline-block;
            padding: 11px 17px;
            background: #2563eb;
            color: white;
            border-radius: 7px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-tambah:hover {
            background: #1d4ed8;
        }

        .table-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .table-heading h3 {
            color: #123B70;
            font-size: 16px;
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

        <a href="data_pengajuan.php" class="active">
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
            <h2>Data Pengajuan</h2>
            <p>Daftar pengajuan surat akademik mahasiswa</p>
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

        <a href="tambah_pengajuan.php" class="btn-tambah">
            + Tambah Pengajuan
        </a>
    </div>

    <div class="table-container">


        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Mahasiswa</th>
                    <th>NIM</th>
                    <th>Jenis Surat</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($data_pengajuan) > 0): ?>

                    <?php foreach ($data_pengajuan as $index => $data): ?>

                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <?= htmlspecialchars($data["nama_lengkap"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data["nim"] ?? "-") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data["nama_surat"] ?? "Tidak diketahui") ?>
                            </td>

                            <td>
                                <?= date(
                                    "d-m-Y H:i",
                                    strtotime($data["tanggal_pengajuan"])
                                ) ?>
                            </td>

                            <td>
                                <span class="status">
                                    <?= htmlspecialchars(
                                        labelStatus($data["status_pengajuan"])
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <a
                                    href="detail_pengajuan.php?id=<?= $data['id_application'] ?>"
                                    class="detail-link">
                                    Detail
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="kosong">
                            Belum ada pengajuan surat.
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
