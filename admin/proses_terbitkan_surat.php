
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

$id_application = $_GET["id"] ?? null;

if (!$id_application) {
    die("ID pengajuan tidak ditemukan.");
}

// Mengambil data pengajuan
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
    <title>Form Penerbitan Surat - SIPAS Informatika</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <style>
        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h2 {
            margin-bottom: 8px;
        }

        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            max-width: 850px;
        }

        .info-section {
            background: #f5f7fb;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .info-section h3,
        .form-section h3 {
            margin-top: 0;
            margin-bottom: 18px;
        }

        .info-item {
            margin-bottom: 12px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            max-width: 100%;
            padding: 11px 12px;
            border: 1px solid #d5d9e2;
            border-radius: 7px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .form-group input[type="file"] {
            background: #fff;
        }

        .form-note {
            display: block;
            color: #777;
            font-size: 13px;
            margin-top: 7px;
        }

        .form-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 25px;
        }

        .submit-button {
            border: none;
            padding: 11px 18px;
            border-radius: 7px;
            background: #2563eb;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .submit-button:hover {
            background: #1d4ed8;
        }

        .back-link {
            color: #555;
            text-decoration: none;
            font-size: 14px;
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
            <p>Formulir penerbitan surat akademik mahasiswa</p>
        </div>

        <div class="admin-profile">
            <div class="profile-avatar">A</div>
            <div>
                <strong><?= htmlspecialchars($_SESSION["nama_lengkap"]) ?></strong>
                <span>Administrator</span>
            </div>
        </div>
    </header>

    <div class="page-heading">
        <h2>Formulir Penerbitan Surat</h2>
        <p>Lengkapi informasi surat sebelum diterbitkan.</p>
    </div>

    <div class="form-container">

        <div class="info-section">
            <h3>Informasi Pengajuan</h3>

            <div class="info-item">
                <strong>Nama Mahasiswa:</strong>
                <?= htmlspecialchars($data["nama_lengkap"]) ?>
            </div>

            <div class="info-item">
                <strong>NIM:</strong>
                <?= htmlspecialchars($data["nim"] ?? "-") ?>
            </div>

            <div class="info-item">
                <strong>Jenis Surat:</strong>
                <?= htmlspecialchars($data["jenis_surat"] ?? "-") ?>
            </div>

            <div class="info-item">
                <strong>Status:</strong>
                Disetujui Ketua Prodi
            </div>
        </div>

        <div class="form-section">
            <h3>Data Surat</h3>

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
                        Format file harus PDF dan maksimal 5 MB.
                    </small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-button">
                        Simpan dan Terbitkan Surat
                    </button>

                    <a href="terbitkan_surat.php" class="back-link">
                        Kembali
                    </a>
                </div>

            </form>
        </div>

    </div>

</div>

</body>
</html>
