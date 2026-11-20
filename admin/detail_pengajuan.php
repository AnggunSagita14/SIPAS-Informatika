
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

// Mengambil ID pengajuan
$id = $_GET["id"] ?? "";

if (!ctype_digit((string) $id)) {
    die("ID pengajuan tidak valid.");
}

// Mengambil detail pengajuan
$sql = "SELECT
            a.*,
            u.nim,
            u.nama_lengkap,
            u.no_hp,
            l.nama_surat,
            il.nomor_surat,
            il.tanggal_terbit,
            il.file_surat
        FROM applications a
        JOIN users u
            ON a.id_user = u.id_user
        LEFT JOIN letter_types l
            ON a.id_letter_type = l.id_letter_type
        LEFT JOIN issued_letters il
            ON a.id_application = il.id_application
        WHERE a.id_application = :id";

$stmt = $conn->prepare($sql);
$stmt->execute(["id" => $id]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data pengajuan tidak ditemukan.");
}

// Membuka Kode Akses yang tersimpan secara terenkripsi
$kode_akses = "";

if (!empty($data["kode_akses_enkripsi"])) {
    $kode_akses = openssl_decrypt(
        $data["kode_akses_enkripsi"],
        "AES-256-CBC",
        hash("sha256", "KUNCI_RAHASIA_SIPAS", true),
        0,
        substr(hash("sha256", "IV_SIPAS_INFORMATIKA", true), 0, 16)
    );
}

// Label status
$daftar_status = [
    "draft" => "Draft",
    "menunggu_verifikasi" => "Menunggu Verifikasi",
    "perlu_perbaikan" => "Perlu Perbaikan",
    "menunggu_persetujuan" => "Menunggu Persetujuan",
    "disetujui" => "Disetujui",
    "ditolak" => "Ditolak",
    "selesai" => "Selesai"
];

$status = $daftar_status[$data["status_pengajuan"]]
    ?? $data["status_pengajuan"];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan - SIPAS Informatika</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <style>
        .page-heading {
            margin: 25px 0;
        }

        .page-heading h2 {
            color: #123B70;
            margin-bottom: 7px;
        }

        .page-heading p {
            color: #8190A3;
            font-size: 13px;
        }

        .detail-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e8edf5;
            max-width: 850px;
        }

        .detail-container h3 {
            color: #123B70;
            font-size: 16px;
            margin-bottom: 18px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 15px;
            padding: 13px 0;
            border-bottom: 1px solid #edf0f5;
            font-size: 13px;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #8190A3;
        }

        .detail-value {
            color: #334B68;
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 20px;
            font-size: 12px;
        }

        .catatan-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            color: #475569;
            font-size: 13px;
        }

        .form-verifikasi {
            margin-top: 25px;
            padding-top: 22px;
            border-top: 1px solid #e5e7eb;
        }

        .form-verifikasi label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334B68;
            margin-bottom: 8px;
        }

        .form-verifikasi textarea {
            width: 100%;
            min-height: 90px;
            padding: 12px;
            border: 1px solid #d8e0eb;
            border-radius: 7px;
            resize: vertical;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 13px;
        }

        .form-verifikasi textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        .tombol-aksi {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-aksi {
            padding: 10px 15px;
            border: none;
            border-radius: 7px;
            color: white;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-perbaikan {
            background: #d97706;
        }

        .btn-setuju {
            background: #16a34a;
        }

        .btn-tolak {
            background: #dc2626;
        }

        .btn-kembali {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #eef2f7;
            color: #475569;
            border-radius: 7px;
            text-decoration: none;
            font-size: 13px;
        }

        .info-selesai {
            background: #ecfdf5;
            color: #065f46;
            padding: 14px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .detail-row {
                grid-template-columns: 1fr;
                gap: 5px;
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
            <h2>Detail Pengajuan</h2>
            <p>Informasi lengkap pengajuan surat mahasiswa</p>
        </div>

        <div class="admin-profile">
            <div class="profile-avatar">A</div>
            <div>
                <strong><?= htmlspecialchars($_SESSION["nama_lengkap"]) ?></strong>
                <span>Administrator</span>
            </div>
        </div>
    </header>

    <div class="detail-container">

        <h3>Informasi Mahasiswa</h3>

        <div class="detail-row">
            <div class="detail-label">Nama Lengkap</div>
            <div class="detail-value">
                <?= htmlspecialchars($data["nama_lengkap"]) ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">NIM</div>
            <div class="detail-value">
                <?= htmlspecialchars($data["nim"] ?? "-") ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Nomor HP</div>
            <div class="detail-value">
                <?= htmlspecialchars($data["no_hp"] ?? "-") ?>
            </div>
        </div>

        <h3 style="margin-top: 30px;">Informasi Pengajuan</h3>

       <div class="detail-row">
    <div class="detail-label">Kode Surat</div>
    <div class="detail-value">
        <?= htmlspecialchars($data["kode_surat"] ?? "-") ?>
    </div>
</div>

<div class="detail-row">
    <div class="detail-label">Kode Akses</div>
    <div class="detail-value">
        <?= htmlspecialchars($kode_akses ?: "Belum tersedia") ?>
    </div>
</div>

<div class="detail-row">
    <div class="detail-label">Jenis Surat</div>
    <div class="detail-value">
        <?= htmlspecialchars($data["nama_surat"] ?? "Tidak diketahui") ?>
    </div>
</div>

        <div class="detail-row">
            <div class="detail-label">Tujuan Pengajuan</div>
            <div class="detail-value">
                <?= nl2br(htmlspecialchars($data["tujuan_pengajuan"])) ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Tanggal Pengajuan</div>
            <div class="detail-value">
                <?= date("d-m-Y H:i", strtotime($data["tanggal_pengajuan"])) ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Status Pengajuan</div>
            <div class="detail-value">
                <span class="status">
                    <?= htmlspecialchars($status) ?>
                </span>
            </div>
        </div>

        <?php if (!empty($data["catatan_admin"])): ?>
            <div class="catatan-box">
                <strong>Catatan Admin:</strong><br><br>
                <?= nl2br(htmlspecialchars($data["catatan_admin"])) ?>
            </div>
        <?php endif; ?>

        <?php if ($data["status_pengajuan"] === "menunggu_verifikasi"): ?>

            <form
                action="proses_verifikasi.php"
                method="POST"
                class="form-verifikasi"
                onsubmit="return validasiCatatan(event)"
            >

                <input
                    type="hidden"
                    name="id_application"
                    value="<?= (int) $data["id_application"] ?>"
                >

                <label for="catatan_admin">
                    Catatan Admin
                </label>

                <textarea
                    name="catatan_admin"
                    id="catatan_admin"
                    placeholder="Isi catatan jika pengajuan perlu diperbaiki atau ditolak..."
                ></textarea>

                <div class="tombol-aksi">

                    <button
                        type="submit"
                        name="aksi"
                        value="perlu_perbaikan"
                        class="btn-aksi btn-perbaikan"
                    >
                        Perlu Perbaikan
                    </button>

                    <button
                        type="submit"
                        name="aksi"
                        value="disetujui"
                        class="btn-aksi btn-setuju"
                    >
                        Setujui Pengajuan
                    </button>

                    <button
                        type="submit"
                        name="aksi"
                        value="ditolak"
                        class="btn-aksi btn-tolak"
                    >
                        Tolak Pengajuan
                    </button>

                </div>

            </form>

        <?php elseif ($data["status_pengajuan"] === "disetujui"): ?>

            <div class="info-selesai">
                Pengajuan telah disetujui. Admin dapat melanjutkan ke proses penerbitan surat.
            </div>

        <?php elseif ($data["status_pengajuan"] === "selesai"): ?>

            <div class="info-selesai">
                Surat sudah diterbitkan.

                <?php if (!empty($data["nomor_surat"])): ?>
                    <br><br>
                    Nomor Surat:
                    <strong><?= htmlspecialchars($data["nomor_surat"]) ?></strong>
                <?php endif; ?>
            </div>

        <?php endif; ?>

        <a href="data_pengajuan.php" class="btn-kembali">
            ← Kembali ke Data Pengajuan
        </a>

    </div>

    <footer class="dashboard-footer">
        SIPAS Informatika UNTAN &copy; 2026
    </footer>

</main>

<script>
function validasiCatatan(event) {
    const aksi = event.submitter.value;
    const catatan = document.getElementById("catatan_admin").value.trim();

    if (
        (aksi === "perlu_perbaikan" || aksi === "ditolak") &&
        catatan === ""
    ) {
        alert("Catatan wajib diisi untuk perbaikan atau penolakan.");
        event.preventDefault();
        return false;
    }

    return confirm("Apakah kamu yakin ingin memproses pengajuan ini?");
}
</script>

</body>
</html>
