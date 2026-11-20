
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
$aksi = $_POST["aksi"] ?? "";
$catatan_admin = trim($_POST["catatan_admin"] ?? "");

// Memeriksa data
if (
    !ctype_digit((string) $id_application) ||
    !in_array($aksi, ["perlu_perbaikan", "disetujui", "ditolak"], true)
) {
    die("Data verifikasi tidak valid.");
}

// Catatan wajib untuk perbaikan dan penolakan
if (
    in_array($aksi, ["perlu_perbaikan", "ditolak"], true) &&
    $catatan_admin === ""
) {
    die("Catatan wajib diisi.");
}

try {
    $conn->beginTransaction();

    // Memeriksa pengajuan yang akan diproses
    $stmt = $conn->prepare("
        SELECT id_user
        FROM applications
        WHERE id_application = :id
        AND status_pengajuan = 'menunggu_verifikasi'
        FOR UPDATE
    ");

    $stmt->execute(["id" => $id_application]);
    $pengajuan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pengajuan) {
        throw new Exception("Pengajuan tidak ditemukan atau sudah diproses.");
    }

    // Menentukan status baru
    $status_baru = $aksi;

    // Memperbarui status pengajuan
    $stmt = $conn->prepare("
        UPDATE applications
        SET status_pengajuan = :status,
            catatan_admin = :catatan
        WHERE id_application = :id
    ");

    $stmt->execute([
        "status" => $status_baru,
        "catatan" => $catatan_admin !== "" ? $catatan_admin : null,
        "id" => $id_application
    ]);

    // Mencatat aktivitas Admin
    $stmt = $conn->prepare("
        INSERT INTO approval_logs
        (id_application, id_user, tindakan, catatan)
        VALUES (:id_application, :id_user, :tindakan, :catatan)
    ");

    $stmt->execute([
        "id_application" => $id_application,
        "id_user" => $_SESSION["id_user"],
        "tindakan" => $aksi,
        "catatan" => $catatan_admin ?: null
    ]);

    $conn->commit();

    header("Location: data_pengajuan.php");
    exit;

} catch (Exception $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die("Proses verifikasi gagal: " .
        htmlspecialchars($e->getMessage()));
}
?>
