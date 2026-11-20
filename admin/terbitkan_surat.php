
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


$id_application = $_GET["id"] ?? null;

// Jika belum ada ID, tampilkan daftar pengajuan yang siap diterbitkan
if ($id_application === null) {

    $query = $conn->query("
        SELECT
            a.id_application,
            u.nama_lengkap,
            u.nim,
            COALESCE(lt.nama_surat, a.jenis_surat_lainnya) AS jenis_surat,
            a.tanggal_pengajuan
        FROM applications a
        JOIN users u ON a.id_user = u.id_user
        LEFT JOIN letter_types lt
            ON a.id_letter_type = lt.id_letter_type
        WHERE a.status_pengajuan = 'disetujui'
        AND NOT EXISTS (
            SELECT 1
            FROM issued_letters il
            WHERE il.id_application = a.id_application
        )
        ORDER BY a.tanggal_pengajuan ASC
    ");

    $daftar = $query->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Penerbitan Surat - SIPAS Informatika</title>
        <link rel="stylesheet" href="../assets/css/dashboard.css">
        <style>
            .table-container {
                background: white;
                padding: 20px;
                border-radius: 12px;
                overflow-x: auto;
            }

            .data-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 750px;
            }

            .data-table th,
            .data-table td {
                padding: 14px;
                text-align: left;
                border-bottom: 1px solid #eee;
            }

            .data-table th {
                background: #f5f7fb;
            }

            .btn-terbitkan {
                display: inline-block;
                padding: 8px 13px;
                background: #2563eb;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 13px;
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
        <a href="terbitkan_surat.php" class="active">
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
                <h2>Penerbitan Surat</h2>
                <p>Daftar pengajuan yang siap diterbitkan</p>
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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftar) > 0): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($daftar as $data): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($data["nama_lengkap"]) ?></td>
                                <td><?= htmlspecialchars($data["nim"] ?? "-") ?></td>
                                <td><?= htmlspecialchars($data["jenis_surat"] ?? "-") ?></td>
                                <td>
                                    <a class="btn-terbitkan"
                                       href="terbitkan_surat.php?id=<?= (int) $data["id_application"] ?>">
                                        Terbitkan
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">
                                Belum ada pengajuan yang siap diterbitkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    </body>
    </html>

    <?php
    exit;
}

// Memeriksa ID pengajuan
if (!ctype_digit((string) $id_application)) {
    die("ID pengajuan tidak valid.");
}

$sql = "SELECT
            a.id_application,
            a.status_pengajuan,
            u.nama_lengkap,
            u.nim,
            COALESCE(lt.nama_surat, a.jenis_surat_lainnya) AS jenis_surat
        FROM applications a
        JOIN users u ON a.id_user = u.id_user
        LEFT JOIN letter_types lt
            ON a.id_letter_type = lt.id_letter_type
        WHERE a.id_application = :id
        AND a.status_pengajuan = 'disetujui'
        AND NOT EXISTS (
            SELECT 1
            FROM issued_letters il
            WHERE il.id_application = a.id_application
        )";

$stmt = $conn->prepare($sql);
$stmt->execute(["id" => $id_application]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Pengajuan tidak ditemukan, belum disetujui, atau sudah diterbitkan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penerbitan Surat - SIPAS Informatika</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <style>
        .content-wrapper {
            max-width: 900px;
        }

        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h2 {
            margin-bottom: 8px;
        }

        .form-card {
            background: #fff;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .section-title {
            font-size: 17px;
            margin: 0 0 18px;
            color: #263449;
        }

        .student-info {
            background: #f6f8fc;
            border: 1px solid #e8edf5;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 28px;
        }

        .info-row {
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
            font-size: 14px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            width: 145px;
            flex-shrink: 0;
            color: #64748b;
        }

        .info-value {
            color: #263449;
            font-weight: 600;
        }

        .approved-label {
            display: inline-block;
            padding: 5px 11px;
            border-radius: 20px;
            background: #e8f5e9;
            color: #287d3c;
            font-size: 12px;
            font-weight: 600;
        }

        .form-divider {
            border: none;
            border-top: 1px solid #edf0f5;
            margin: 25px 0;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: #344054;
            font-size: 14px;
            font-weight: 600;
        }

        .form-group input {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 12px 14px;
            border: 1px solid #d8dee8;
            border-radius: 8px;
            background: #fff;
            font-size: 14px;
            color: #263449;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .form-note {
            display: block;
            margin-top: 8px;
            color: #7b8494;
            font-size: 12px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 28px;
        }

        .submit-button {
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .submit-button:hover {
            background: #1d4ed8;
        }

        .back-button {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 8px;
            background: #f1f3f6;
            color: #475467;
            text-decoration: none;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .form-card {
                padding: 18px;
            }

            .info-row {
                flex-direction: column;
                gap: 5px;
            }

            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }
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
    <a href="terbitkan_surat.php" class="active">
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
            <h2>Penerbitan Surat</h2>
            <p>Pengelolaan penerbitan surat akademik</p>
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

        <div class="page-heading">
            <h2>Formulir Penerbitan Surat</h2>
            <p>Lengkapi informasi berikut untuk menerbitkan surat mahasiswa.</p>
        </div>

        <div class="form-card">

            <h3 class="section-title">Informasi Pengajuan</h3>

            <div class="student-info">
                <div class="info-row">
                    <div class="info-label">Nama Mahasiswa</div>
                    <div class="info-value">
                        <?= htmlspecialchars($data["nama_lengkap"]) ?>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">NIM</div>
                    <div class="info-value">
                        <?= htmlspecialchars($data["nim"] ?? "-") ?>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Jenis Surat</div>
                    <div class="info-value">
                        <?= htmlspecialchars($data["jenis_surat"] ?? "-") ?>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Status Pengajuan</div>
                    <div class="info-value">
                        <span class="approved-label">Disetujui</span>
                    </div>
                </div>
            </div>

            <hr class="form-divider">

            <h3 class="section-title">Data Surat</h3>

            <form action="simpan_surat.php"
                  method="POST"
                  enctype="multipart/form-data">

                <input type="hidden"
                       name="id_application"
                       value="<?= htmlspecialchars($data["id_application"]) ?>">

                <div class="form-group">
                    <label for="nomor_surat">Nomor Surat</label>
                    <input
                        type="text"
                        id="nomor_surat"
                        name="nomor_surat"
                        placeholder="Masukkan nomor surat resmi"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="tanggal_terbit">Tanggal Terbit</label>
                    <input
                        type="date"
                        id="tanggal_terbit"
                        name="tanggal_terbit"
                        value="<?= date('Y-m-d') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="file_surat">Upload File Surat (PDF)</label>
                    <input
                        type="file"
                        id="file_surat"
                        name="file_surat"
                        accept=".pdf,application/pdf"
                        required
                    >
                    <small class="form-note">
                        Gunakan format PDF dengan ukuran maksimal 5 MB.
                    </small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-button">
                        Simpan dan Terbitkan Surat
                    </button>

                    <a href="data_pengajuan.php" class="back-button">
                        Kembali
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
