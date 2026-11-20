
<?php
session_start();

require_once "config/database.php";

// Memeriksa apakah mahasiswa sudah melakukan pencarian Jejak Surat
if (!isset($_SESSION["jejak_application_id"])) {
    header("Location: login.php");
    exit;
}

$id_application = $_SESSION["jejak_application_id"];

// Mengambil informasi pengajuan dan surat yang diterbitkan
$sql = "SELECT
            a.kode_surat,
            a.tujuan_pengajuan,
            a.tanggal_pengajuan,
            a.status_pengajuan,
            a.catatan_admin,
            u.nama_lengkap,
            u.nim,
            COALESCE(
                lt.nama_surat,
                a.jenis_surat_lainnya
            ) AS nama_surat,
            il.nomor_surat,
            il.tanggal_terbit,
            il.file_surat
        FROM applications a
        JOIN users u
            ON a.id_user = u.id_user
        LEFT JOIN letter_types lt
            ON a.id_letter_type = lt.id_letter_type
        LEFT JOIN issued_letters il
            ON a.id_application = il.id_application
        WHERE a.id_application = :id_application
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ":id_application" => $id_application
]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika data tidak ditemukan
if (!$data) {
    unset($_SESSION["jejak_application_id"]);
    header("Location: login.php");
    exit;
}

// Menentukan nama status
$status = [
    "draft" => "Draft",
    "menunggu_verifikasi" => "Menunggu Verifikasi",
    "perlu_perbaikan" => "Perlu Perbaikan",
    "menunggu_persetujuan" => "Menunggu Persetujuan",
    "disetujui" => "Disetujui",
    "ditolak" => "Ditolak",
    "selesai" => "Selesai"
];
// Menentukan status verifikasi
if ($data["status_pengajuan"] == "disetujui" ||
    $data["status_pengajuan"] == "selesai") {
    $status_verifikasi = "Diterima";
} elseif ($data["status_pengajuan"] == "ditolak") {
    $status_verifikasi = "Ditolak";
} else {
    $status_verifikasi = "Menunggu Verifikasi";
}
// Menentukan lokasi file PDF
$file_surat = "";

if (!empty($data["file_surat"])) {
    $file_surat = "uploads/surat/" . basename($data["file_surat"]);
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Jejak Surat - SIPAS Informatika</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #334155;
        }

        .header {
            background: linear-gradient(100deg, #2563eb, #1e3a8a);
            color: white;
            padding: 18px 30px;
        }

        .header-content {
            max-width: 1000px;
            margin: auto;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 40px;
            height: 40px;
            background: white;
            color: #2563eb;
            border-radius: 7px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: 17px;
        }

        .logo img {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 50%;
}

        .header h2 {
            font-size: 18px;
        }

        .header p {
            font-size: 11px;
            margin-top: 4px;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-title {
            margin-bottom: 20px;
        }

        .page-title h2 {
            color: #1e3a8a;
            font-size: 22px;
            margin-bottom: 6px;
        }

        .page-title p {
            color: #64748b;
            font-size: 13px;
        }

        .card {
            background: white;
            border-radius: 9px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.07);
            margin-bottom: 22px;
            overflow: hidden;
        }

        .card-header {
            background: #2563eb;
            color: white;
            padding: 13px 18px;
            font-size: 15px;
            font-weight: bold;
        }

        .card-body {
            padding: 20px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 190px 1fr;
            gap: 15px;
            padding: 13px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
            line-height: 1.6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #475569;
        }

        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            background: #dcfce7;
            color: #15803d;
            font-weight: bold;
            font-size: 12px;
        }

        .pdf-container {
            background: #eaf2ff;
            padding: 15px;
            border-radius: 7px;
        }

        .pdf-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .pdf-toolbar h3 {
            font-size: 14px;
            color: #1e3a8a;
        }

        .btn-download {
            display: inline-block;
            background: #15803d;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .btn-download:hover {
            background: #166534;
        }

        .pdf-viewer {
            width: 100%;
            height: 750px;
            border: none;
            background: white;
            border-radius: 5px;
        }

        .no-file {
            padding: 25px;
            text-align: center;
            color: #64748b;
            background: #f8fafc;
            border-radius: 6px;
            font-size: 13px;
        }

        .footer {
            background: #1e3a8a;
            color: white;
            padding: 25px 20px;
            text-align: center;
            font-size: 12px;
            margin-top: 40px;
        }

        @media (max-width: 600px) {
            .info-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .header {
                padding: 15px;
            }

            .pdf-viewer {
                height: 500px;
            }
        }
    </style>
</head>

<body>

<header class="header">
    <div class="header-content">
        <div class="logo">
    <img
        src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTwzn_3SgDGs1covLp67lIAESYtvhPnZn8Sxa6XDueGjEHCb-CEun2GFTs&s=10"
        alt="Logo Universitas Tanjungpura"
    >
</div>

        <div>
            <h2>Sistem Informasi Pengajuan dan Pengelolaan Surat</h2>
            <p>Program Studi Informatika - Universitas Tanjungpura</p>
        </div>
    </div>
</header>

<main class="container">

    <div class="page-title">
        <h2>Detail Jejak Surat</h2>
        <p>Informasi proses pengajuan dan surat akademik Anda.</p>
    </div>

    <section class="card">

        <div class="card-header">
            Detail Pengajuan
        </div>

        <div class="card-body">

                    <div class="info-row">
    <div class="info-label">Pembuat Draf</div>
    <div>PROGRAM STUDI INFORMATIKA | Fakultas Teknik</div>
</div>

<div class="info-row">
    <div class="info-label">Verifikator</div>
    <div>
        EVA FAJA RIPANTI, S.KOM, MMSI, Ph.D
        – Kepala Program Studi Informatika
    </div>
</div>

<div class="info-row">
    <div class="info-label">Penanda Tangan</div>
    <div>
        EVA FAJA RIPANTI, S.KOM, MMSI, Ph.D
        – Kepala Program Studi Informatika
    </div>
</div>

            <div class="info-row">
                <div class="info-label">Nama Mahasiswa</div>
                <div><?= e($data["nama_lengkap"]) ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">NIM</div>
                <div><?= e($data["nim"] ?? "-") ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Jenis Surat</div>
                <div><?= e($data["nama_surat"] ?? "-") ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Tanggal Pengajuan</div>
                <div>
                    <?= date("d-m-Y", strtotime($data["tanggal_pengajuan"])) ?>
                </div>
            </div>

            <?php if (!empty($data["catatan_admin"])): ?>
                <div class="info-row">
                    <div class="info-label">Catatan Admin</div>
                    <div><?= nl2br(e($data["catatan_admin"])) ?></div>
                </div>
            <?php endif; ?>

    <?php if (!empty($data["nomor_surat"])): ?>

                <div class="info-row">
                    <div class="info-label">Tanggal Terbit</div>
                    <div>
                        <?= date("d-m-Y", strtotime($data["tanggal_terbit"])) ?>
                    </div>
                </div>
            <?php endif; ?>
<div class="info-row">
    <div class="info-label">Status Verifikasi</div>
    <div>
        <span class="status">
            <?= e($status_verifikasi) ?>
        </span>
    </div>
</div>

        </div>
    </section>

    <?php if (!empty($file_surat)): ?>

        <section class="card">

            <div class="card-header">
                Dokumen Surat
            </div>

            <div class="card-body">

                <div class="pdf-container">

                    <div class="pdf-toolbar">
                        <h3>Pratinjau Dokumen PDF</h3>

                        <a
                            href="<?= e($file_surat) ?>"
                            target="_blank"
                            class="btn-download"
                            download
                        >
                            Unduh Surat
                        </a>
                    </div>

                    <iframe
                        src="<?= e($file_surat) ?>"
                        class="pdf-viewer"
                        title="Pratinjau Surat PDF"
                    ></iframe>

                </div>

            </div>
        </section>

    <?php endif; ?>

</main>

<footer class="footer">
    &copy; 2026 SIPAS Informatika
    <br>
    Universitas Tanjungpura
</footer>

</body>
</html>
