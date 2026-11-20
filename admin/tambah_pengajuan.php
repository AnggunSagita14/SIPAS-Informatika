
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

$error = "";

// Mengambil jenis surat yang aktif
$query = $conn->query("
    SELECT id_letter_type, nama_surat
    FROM letter_types
    WHERE status_aktif = 'aktif'
    ORDER BY nama_surat ASC
");

$jenis_surat = $query->fetchAll(PDO::FETCH_ASSOC);

// Memproses formulir
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nim = trim($_POST["nim"] ?? "");
    $nama = trim($_POST["nama_lengkap"] ?? "");
    $no_hp = trim($_POST["no_hp"] ?? "");   
    $id_letter_type = $_POST["id_letter_type"] ?? "";
    $jenis_surat_lainnya = trim($_POST["jenis_surat_lainnya"] ?? "");
    $tujuan = trim($_POST["tujuan_pengajuan"] ?? "");

if ($id_letter_type === "lainnya") {
    $id_letter_type = null;
}

    if (
    $nim == "" ||
    $nama == "" ||
    (
        $id_letter_type === null &&
        $jenis_surat_lainnya == ""
    ) ||
    $tujuan == ""
) {
        $error = "Mohon lengkapi semua data wajib.";
    } else {
        try {
            $conn->beginTransaction();

            // Memeriksa apakah NIM sudah terdaftar
            $cek = $conn->prepare("
                SELECT id_user
                FROM users
                WHERE nim = :nim AND id_role = 1
            ");

            $cek->execute(["nim" => $nim]);
            $mahasiswa = $cek->fetch(PDO::FETCH_ASSOC);

            // Jika belum ada, simpan identitas mahasiswa
            if ($mahasiswa) {

                $id_user = $mahasiswa["id_user"];

                $update = $conn->prepare("
                    UPDATE users
                    SET nama_lengkap = :nama,
                        no_hp = :no_hp
                    WHERE id_user = :id_user
                ");

                $update->execute([
                    "nama" => $nama,
                    "no_hp" => $no_hp ?: null,
                    "id_user" => $id_user
                ]);

            } else {

                $insert_user = $conn->prepare("
                    INSERT INTO users
                    (id_role, nim, nama_lengkap, no_hp, email, password)
                    VALUES (1, :nim, :nama, :no_hp, NULL, NULL)
                ");

                $insert_user->execute([
                    "nim" => $nim,
                    "nama" => $nama,
                    "no_hp" => $no_hp ?: null
                ]);

                $id_user = $conn->lastInsertId();
            }

            // Membuat kode surat dan kode akses
$kode_surat = "SIPAS-" . date("Ymd") . "-" . strtoupper(bin2hex(random_bytes(3)));
$kode_akses = strtoupper(bin2hex(random_bytes(4)));

$kode_akses_hash = password_hash($kode_akses, PASSWORD_DEFAULT);

$kode_akses_enkripsi = openssl_encrypt(
    $kode_akses,
    "AES-256-CBC",
    hash("sha256", "KUNCI_RAHASIA_SIPAS", true),
    0,
    substr(hash("sha256", "IV_SIPAS_INFORMATIKA", true), 0, 16)
);
// Menyimpan pengajuan
$insert_pengajuan = $conn->prepare("
    INSERT INTO applications
    (
        kode_surat,
        kode_akses_hash,
        kode_akses_enkripsi,
        id_user,
        id_letter_type,
        jenis_surat_lainnya,
        tujuan_pengajuan,
        status_pengajuan
    )
    VALUES
    (
        :kode_surat,
        :kode_akses_hash,
        :kode_akses_enkripsi,
        :id_user,
        :id_letter_type,
        :jenis_surat_lainnya,
        :tujuan,
        'menunggu_verifikasi'
    )
");

$insert_pengajuan->execute([
    "kode_surat" => $kode_surat,
    "kode_akses_hash" => $kode_akses_hash,
    "kode_akses_enkripsi" => $kode_akses_enkripsi,
    "id_user" => $id_user,
    "id_letter_type" => $id_letter_type,
    "jenis_surat_lainnya" => $jenis_surat_lainnya ?: null,
    "tujuan" => $tujuan
]);

            $conn->commit();

            // Menampilkan kode untuk diberikan kepada mahasiswa
            $_SESSION["hasil_pengajuan"] = [
                "kode_surat" => $kode_surat,
                "kode_akses" => $kode_akses,
                "nama" => $nama
            ];

            header("Location: tambah_pengajuan.php?berhasil=1");
            exit;

        } catch (PDOException $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            $error = "Pengajuan gagal disimpan. Periksa kembali data mahasiswa.";
        }
    }
}

// Mengambil hasil pengajuan yang baru dibuat
$hasil = $_SESSION["hasil_pengajuan"] ?? null;

if (isset($_GET["berhasil"]) && $hasil) {
    unset($_SESSION["hasil_pengajuan"]);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengajuan - SIPAS Informatika</title>
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

        .form-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e8edf5;
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #334B68;
            font-size: 13px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #d8e0eb;
            border-radius: 7px;
            font-size: 13px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn-simpan {
            padding: 11px 18px;
            border: none;
            border-radius: 7px;
            background: #2563eb;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-kembali {
            padding: 11px 18px;
            border-radius: 7px;
            background: #eef2f7;
            color: #475569;
            text-decoration: none;
            font-size: 13px;
        }

        .pesan-error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .hasil {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 22px;
        }

        .hasil p {
            margin: 9px 0;
            font-size: 14px;
        }

        .hasil strong {
            color: #064e3b;
        }

        .catatan {
            font-size: 12px;
            margin-top: 12px;
            color: #047857;
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
            <h2>Tambah Pengajuan</h2>
            <p>Membuat pengajuan surat untuk mahasiswa</p>
        </div>

        <div class="admin-profile">
            <div class="profile-avatar">A</div>
            <div>
                <strong><?= htmlspecialchars($_SESSION["nama_lengkap"]) ?></strong>
                <span>Administrator</span>
            </div>
        </div>
    </header>

    <?php if ($hasil): ?>
        <div class="hasil">
            <strong>Pengajuan berhasil dibuat!</strong>
            <p>Nama Mahasiswa: <strong><?= htmlspecialchars($hasil["nama"]) ?></strong></p>
            <p>Kode Surat: <strong><?= htmlspecialchars($hasil["kode_surat"]) ?></strong></p>
            <p>Kode Akses: <strong><?= htmlspecialchars($hasil["kode_akses"]) ?></strong></p>
            <p class="catatan">
                Simpan dan berikan kedua kode tersebut kepada mahasiswa untuk memantau pengajuan melalui menu Jejak Surat.
            </p>
        </div>
    <?php endif; ?>

    <div class="form-container">

        <?php if ($error): ?>
            <div class="pesan-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>NIM Mahasiswa *</label>
                <input type="text" name="nim" required>
            </div>

            <div class="form-group">
                <label>Nama Lengkap Mahasiswa *</label>
                <input type="text" name="nama_lengkap" required>
            </div>

            <div class="form-group">
                <label>Nomor HP</label>
                <input type="text" name="no_hp">
            </div>

            <div class="form-group">
                <label>Jenis Surat *</label>
                <select name="id_letter_type" id="id_letter_type" required>
                    <option value="">-- Pilih Jenis Surat --</option>

                    <?php foreach ($jenis_surat as $jenis): ?>
                        <option value="<?= $jenis["id_letter_type"] ?>">
                            <?= htmlspecialchars($jenis["nama_surat"]) ?>
                        </option>
                    <?php endforeach; ?>

                    <option value="lainnya">Lainnya</option>

                </select>
<div id="form-jenis-lainnya" style="display: none; margin-top: 12px;">
    <input
        type="text"
        name="jenis_surat_lainnya"
        id="jenis_surat_lainnya"
        placeholder="Tuliskan nama jenis surat..."
    >
</div>
            </div>

            <div class="form-group">
                <label>Tujuan Pengajuan *</label>
                <textarea
                    name="tujuan_pengajuan"
                    placeholder="Tuliskan tujuan pengajuan surat..."
                    required></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">
                    Simpan Pengajuan
                </button>

                <a href="data_pengajuan.php" class="btn-kembali">
                    Kembali
                </a>
            </div>

        </form>
    </div>

    <footer class="dashboard-footer">
        SIPAS Informatika UNTAN &copy; 2026
    </footer>

</main>

<script>
const pilihanSurat = document.getElementById("id_letter_type");
const formLainnya = document.getElementById("form-jenis-lainnya");
const inputLainnya = document.getElementById("jenis_surat_lainnya");

pilihanSurat.addEventListener("change", function () {
    if (this.value === "lainnya") {
        formLainnya.style.display = "block";
        inputLainnya.required = true;
    } else {
        formLainnya.style.display = "none";
        inputLainnya.required = false;
        inputLainnya.value = "";
    }
});
</script>

</body>
</html>
