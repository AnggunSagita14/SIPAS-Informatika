
<?php
session_start();

require_once "config/database.php";

$error = "";
$error_jejak = "";
$hasil_jejak = null;
$tampilkan_modal = false;

// Proses Login Admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login_admin"])) {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $sql = "SELECT
                users.*,
                roles.nama_role
            FROM users
            JOIN roles
                ON users.id_role = roles.id_role
            WHERE users.email = :email
            AND users.status_akun = 'aktif'
            AND roles.nama_role = 'admin'";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {

        session_regenerate_id(true);

        $_SESSION["id_user"] = $user["id_user"];
        $_SESSION["nama_lengkap"] = $user["nama_lengkap"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = "admin";

        header("Location: admin/dashboard.php");
        exit;

    } else {
        $error = "Email atau password salah!";
    }
}

// Proses Jejak Surat
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["aksi_jejak"])) {

    $tampilkan_modal = true;

    $kode_surat = strtoupper(trim($_POST["kode_surat"] ?? ""));
    $kode_akses = trim($_POST["kode_akses"] ?? "");

    if ($kode_surat == "" || $kode_akses == "") {

        $error_jejak = "Kode Surat dan Kode Akses wajib diisi.";

    } else {

        $sql = "SELECT
            a.id_application,
            a.kode_surat,
            a.kode_akses_hash,
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
        WHERE a.kode_surat = :kode_surat
        LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':kode_surat' => $kode_surat
        ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
    $data &&
    $data["kode_akses_hash"] !== null &&
    password_verify($kode_akses, $data["kode_akses_hash"])
) {
    $_SESSION["jejak_application_id"] = $data["id_application"];

    header("Location: jejak_surat.php");
    exit;

} else {
    $error_jejak = "Kode Surat atau Kode Akses tidak sesuai.";
}
    }
}

$status = [
    "draft" => "Draft",
    "menunggu_verifikasi" => "Menunggu Verifikasi",
    "perlu_perbaikan" => "Perlu Perbaikan",
    "menunggu_persetujuan" => "Menunggu Persetujuan",
    "disetujui" => "Disetujui",
    "ditolak" => "Ditolak",
    "selesai" => "Selesai"
];
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - SIPAS Informatika</title>

<style>
    .jejak-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .jejak-modal.active {
        display: flex;
    }

    .jejak-modal-content {
        background: white;
        width: 100%;
        max-width: 430px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 35px rgba(0,0,0,0.2);
    }

    .jejak-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 17px 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .jejak-modal-header h3 {
        font-size: 16px;
        color: #1e293b;
    }

    .jejak-close {
        border: none;
        background: none;
        color: #94a3b8;
        font-size: 23px;
        cursor: pointer;
    }

    .jejak-modal-body {
        padding: 20px;
    }

    .jejak-modal-body > p {
        color: #64748b;
        font-size: 12px;
        margin-bottom: 18px;
    }

    .jejak-field {
        margin-bottom: 15px;
    }

    .jejak-field label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #334155;
    }

    .jejak-field input {
        width: 100%;
        padding: 11px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        outline: none;
    }

    .jejak-field input:focus {
        border-color: #2563eb;
    }

    .btn-cari-jejak {
        background: #2563eb;
        color: white;
        border: none;
        padding: 11px 18px;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
        font-weight: 600;
    }

    .btn-cari-jejak:hover {
        background: #1d4ed8;
    }

    .jejak-error {
        background: #fee2e2;
        color: #b91c1c;
        padding: 11px;
        border-radius: 6px;
        font-size: 12px;
        margin-bottom: 15px;
    }

    .jejak-result {
        background: #f1f5f9;
        border-radius: 7px;
        padding: 15px;
        margin-top: 18px;
    }

    .jejak-result h4 {
        color: #1e3a8a;
        margin-bottom: 12px;
    }

    .jejak-result p {
        font-size: 12px;
        line-height: 1.8;
        color: #475569;
        overflow-wrap: anywhere;
    }
</style>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="login-page">

    <div class="login-container">

        <!-- Bagian Kiri -->
        <div class="login-banner">

            <div class="banner-overlay"></div>

            <div class="banner-content">

                <div class="banner-text">
                    <p class="banner-subtitle">FAKULTAS TEKNIK</p>

                    <h1>SIPAS<br>INFORMATIKA</h1>

                    <div class="banner-line"></div>

                    <p class="banner-description">
                        Sistem Informasi Pengajuan dan Pengelolaan
                        Surat Akademik Informatika.
                    </p>
                </div>

                <div class="banner-footer">
                    UNIVERSITAS TANJUNGPURA
                </div>

            </div>

        </div>

        <!-- Bagian Kanan -->
        <div class="login-form-section">

            <div class="form-wrapper">

                <div class="form-heading">
<img
    src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ9YcTImabDnZNSKtDQ6Tj8eE4NVcJenuOz9DLZuuYAXA&amp;s=10"
    alt="Logo Informatika"
    style="
        display: block;
        width: 65px;
        height: 65px;
        object-fit: contain;
        margin: 0 auto 15px;
    "
>
                    <h2 class="welcome-title">Selamat Datang!</h2>

                    <p>
                        Silakan masuk menggunakan akun SIPAS Anda.
                    </p>

                </div>

                <!-- Pesan Error -->
                <?php if ($error != ""): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Form Login -->
                <form method="POST" action="">

                    <div class="form-group">

                        <label for="email">Email</label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Masukkan email Anda"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            autocomplete="username"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="password">Password</label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password Anda"
                            autocomplete="current-password"
                            required
                        >

                    </div>

                    <button type="submit" name="login_admin" class="btn-login">
                        Masuk ke SIPAS
                        <span>→</span>
                    </button>

                                </form>

                <!-- Tombol Jejak Surat -->
                
<button
    type="button"
    onclick="bukaJejak()"
    style="
        display: block;
        width: 100%;
        padding: 13px;
        margin-top: 15px;
        text-align: center;
        color: #2563eb;
        background: white;
        border: 1px solid #2563eb;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
    "
>
    Jejak Surat →
</button>


                <div class="login-divider">
                    <span>PORTAL AKADEMIK INFORMATIKA</span>
                </div>

                <div class="login-footer">
                    &copy; 2026 SIPAS Informatika
                    <br>
                    Universitas Tanjungpura
                </div>

            </div>

        </div>

    </div>

</div>


<!-- Pop-up Jejak Surat -->
<div class="jejak-modal <?= $tampilkan_modal ? 'active' : '' ?>" id="jejakModal">

    <div class="jejak-modal-content">

        <div class="jejak-modal-header">
            <h3>Cari Jejak Surat</h3>

            <button type="button" class="jejak-close" onclick="tutupJejak()">
                &times;
            </button>
        </div>

        <div class="jejak-modal-body">

            <p>Kode akses diberikan oleh Admin pengelola surat.</p>

            <?php if ($error_jejak != ""): ?>
                <div class="jejak-error">
                    <?= htmlspecialchars($error_jejak) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <input type="hidden" name="aksi_jejak" value="1">

                <div class="jejak-field">
                    <label for="kode_surat">Kode Surat</label>
                    <input
                        type="text"
                        id="kode_surat"
                        name="kode_surat"
                        placeholder="Masukkan kode surat..."
                        value="<?= htmlspecialchars($_POST["kode_surat"] ?? "") ?>"
                        required
                    >
                </div>

                <div class="jejak-field">
                    <label for="kode_akses">Kode Akses</label>
                    <input
                        type="password"
                        id="kode_akses"
                        name="kode_akses"
                        placeholder="Masukkan kode akses..."
                        required
                    >
                </div>

                <button type="submit" class="btn-cari-jejak">
                    Cari
                </button>

            </form>

            <?php if ($hasil_jejak): ?>

                <div class="jejak-result">

                    <h4>Informasi Pengajuan</h4>

                    <p><strong>Kode Surat:</strong>
                        <?= htmlspecialchars($hasil_jejak["kode_surat"]) ?>
                    </p>

                    <p><strong>Nama:</strong>
                        <?= htmlspecialchars($hasil_jejak["nama_lengkap"]) ?>
                    </p>

                    <p><strong>NIM:</strong>
                        <?= htmlspecialchars($hasil_jejak["nim"] ?? "-") ?>
                    </p>

                    <p><strong>Jenis Surat:</strong>
                        <?= htmlspecialchars($hasil_jejak["nama_surat"] ?? "-") ?>
                    </p>

                    <p><strong>Tanggal Pengajuan:</strong>
                        <?= date("d-m-Y", strtotime($hasil_jejak["tanggal_pengajuan"])) ?>
                    </p>

                    <p><strong>Status:</strong>
                        <?= htmlspecialchars(
                            $status[$hasil_jejak["status_pengajuan"]]
                            ?? $hasil_jejak["status_pengajuan"]
                        ) ?>
                    </p>

                    <?php if (
                        $hasil_jejak["catatan_admin"] &&
                        $hasil_jejak["status_pengajuan"] == "perlu_perbaikan"
                    ): ?>
                        <p><strong>Catatan Admin:</strong>
                            <?= nl2br(htmlspecialchars($hasil_jejak["catatan_admin"])) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($hasil_jejak["nomor_surat"])): ?>

    <p>
        <strong>Nomor Surat:</strong>
        <?= htmlspecialchars($hasil_jejak["nomor_surat"]) ?>
    </p>

    <p>
        <strong>Tanggal Terbit:</strong>
        <?= date("d-m-Y", strtotime($hasil_jejak["tanggal_terbit"])) ?>
    </p>

    <?php if (!empty($hasil_jejak["file_surat"])): ?>

        <a
            href="uploads/surat/<?= rawurlencode(basename($hasil_jejak["file_surat"])) ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="btn-cari-jejak"
            style="
                display: inline-block;
                text-decoration: none;
                margin-top: 10px;
            "
        >
            Unduh Surat (PDF)
        </a>

    <?php endif; ?>

<?php endif; ?>

                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script>
    function bukaJejak() {
        document.getElementById("jejakModal").classList.add("active");
    }

    function tutupJejak() {
        document.getElementById("jejakModal").classList.remove("active");
    }

    document.getElementById("jejakModal").addEventListener("click", function(e) {
        if (e.target === this) {
            tutupJejak();
        }
    });
</script>

</body>
</html>