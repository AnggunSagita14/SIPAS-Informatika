
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

// Memastikan data dikirim melalui POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: data_pengajuan.php");
    exit;
}

$id_application = $_POST["id_application"] ?? "";
$nomor_surat = trim($_POST["nomor_surat"] ?? "");
$tanggal_terbit = $_POST["tanggal_terbit"] ?? "";

// Memeriksa kelengkapan data
if (
    !ctype_digit((string) $id_application) ||
    $nomor_surat === "" ||
    $tanggal_terbit === ""
) {
    die("Data penerbitan surat tidak valid.");
}

// Memeriksa file yang diunggah
if (
    !isset($_FILES["file_surat"]) ||
    $_FILES["file_surat"]["error"] !== UPLOAD_ERR_OK
) {
    die("File surat wajib diunggah.");
}

$file = $_FILES["file_surat"];

// Memeriksa ukuran file maksimal 5 MB
if ($file["size"] > 5 * 1024 * 1024) {
    die("Ukuran file maksimal 5 MB.");
}

// Memeriksa format PDF
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($file["tmp_name"]);

if ($mime_type !== "application/pdf") {
    die("File harus berformat PDF.");
}

// Menyiapkan folder penyimpanan
$folder = "../uploads/surat/";

if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}

// Membuat nama file unik
$nama_file = "surat_" . $id_application . "_" .
             bin2hex(random_bytes(8)) . ".pdf";

$lokasi_file = $folder . $nama_file;

try {

    $conn->beginTransaction();

    // Memeriksa pengajuan yang sudah disetujui
    $sql = "SELECT id_user
            FROM applications
            WHERE id_application = :id
            AND status_pengajuan = 'disetujui'
            FOR UPDATE";

    $stmt = $conn->prepare($sql);
    $stmt->execute(["id" => $id_application]);

    $pengajuan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pengajuan) {
        throw new Exception(
            "Pengajuan tidak ditemukan atau belum disetujui."
        );
    }

    // Memastikan surat belum pernah diterbitkan
    $sql = "SELECT id_issued_letter
            FROM issued_letters
            WHERE id_application = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute(["id" => $id_application]);

    if ($stmt->fetch()) {
        throw new Exception(
            "Surat untuk pengajuan ini sudah pernah diterbitkan."
        );
    }

    // Memindahkan file PDF
    if (!move_uploaded_file($file["tmp_name"], $lokasi_file)) {
        throw new Exception("Gagal menyimpan file PDF.");
    }

    // Menyimpan data surat
    $sql = "INSERT INTO issued_letters
            (id_application, nomor_surat, tanggal_terbit,
             file_surat, diterbitkan_oleh)
            VALUES
            (:id_application, :nomor_surat, :tanggal_terbit,
             :file_surat, :diterbitkan_oleh)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        "id_application" => $id_application,
        "nomor_surat" => $nomor_surat,
        "tanggal_terbit" => $tanggal_terbit,
        "file_surat" => $nama_file,
        "diterbitkan_oleh" => $_SESSION["id_user"]
    ]);

    // Mengubah status pengajuan menjadi selesai
    $sql = "UPDATE applications
            SET status_pengajuan = 'selesai'
            WHERE id_application = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute(["id" => $id_application]);

    // Menyelesaikan transaksi
    $conn->commit();

    // Kembali ke halaman riwayat surat
    header("Location: riwayat_surat.php");
    exit;

} catch (Exception $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // Menghapus file jika proses database gagal
    if (file_exists($lokasi_file)) {
        unlink($lokasi_file);
    }

    die("Terjadi kesalahan: " .
        htmlspecialchars($e->getMessage()));
}
?>
