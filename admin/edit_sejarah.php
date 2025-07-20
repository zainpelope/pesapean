<?php
include '../koneksi.php'; // Adjust path to your koneksi.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_home = '';
$sejarah = '';
$gambar_lama = '';
$message = '';
$redirect = false; // Flag to indicate if redirection should occur

// --- Handle GET request (to display existing data for editing) ---
if (isset($_GET['id'])) {
    // Use prepared statement for fetching data
    $sql_fetch = "SELECT sejarah, gambar FROM home WHERE id_home = ?";
    $stmt_fetch = mysqli_prepare($koneksi, $sql_fetch);
    mysqli_stmt_bind_param($stmt_fetch, "i", $_GET['id']);
    mysqli_stmt_execute($stmt_fetch);
    $result_fetch = mysqli_stmt_get_result($stmt_fetch);

    if (mysqli_num_rows($result_fetch) > 0) {
        $row = mysqli_fetch_assoc($result_fetch);
        $id_home = htmlspecialchars($_GET['id']); // Store ID safely
        $sejarah = $row['sejarah'];
        $gambar_lama = $row['gambar'];
    } else {
        $message = "❌ Data tidak ditemukan.";
    }
    mysqli_stmt_close($stmt_fetch);
}

// --- Handle POST request (to update data) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_home = $_POST['id_home'];
    $sejarah = $_POST['sejarah'];
    $gambar_lama_post = $_POST['gambar_lama']; // This is the filename from the hidden input
    $gambar_baru = $_FILES['gambar']['name'];

    $gambar_to_save = $gambar_lama_post; // Default to old image
    $uploadOk = 1;

    // If a new image is uploaded
    if (!empty($gambar_baru)) {
        $tmp = $_FILES['gambar']['tmp_name'];
        $target_dir = "../uploads/"; // Make sure this directory exists and is writable
        $target_file = $target_dir . basename($gambar_baru);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Basic image validation
        $check = getimagesize($tmp);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $message = "File yang diupload bukan gambar.";
            $uploadOk = 0;
        }

        if (file_exists($target_file)) {
            $message = "Maaf, nama file gambar sudah ada.";
            $uploadOk = 0;
        }

        if ($_FILES["gambar"]["size"] > 5000000) { // Max 5MB
            $message = "Maaf, ukuran gambar terlalu besar (maks 5MB).";
            $uploadOk = 0;
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $message = "Maaf, hanya format JPG, JPEG, PNG, & GIF yang diperbolehkan.";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            $message = "❌ Gagal mengupload gambar baru: " . $message;
            $gambar_to_save = $gambar_lama_post; // Keep old image if new upload fails
        } else {
            if (move_uploaded_file($tmp, $target_file)) {
                // Delete old image if it exists and is different from the new one
                if (!empty($gambar_lama_post) && file_exists($target_dir . $gambar_lama_post) && $gambar_lama_post != basename($gambar_baru)) {
                    unlink($target_dir . $gambar_lama_post);
                }
                $gambar_to_save = basename($gambar_baru);
            } else {
                $message = "❌ Gagal mengupload gambar baru. Periksa izin direktori.";
                $gambar_to_save = $gambar_lama_post; // Fallback to old image
            }
        }
    }

    // Update data in database using prepared statements
    $sql_update = "UPDATE home SET sejarah = ?, gambar = ? WHERE id_home = ?";
    $stmt_update = mysqli_prepare($koneksi, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ssi", $sejarah, $gambar_to_save, $id_home); // 's' for string, 'i' for integer

    if (mysqli_stmt_execute($stmt_update)) {
        $message = "✅ Data berhasil diperbarui.";
        $gambar_lama = $gambar_to_save; // Update for display
        $redirect = true; // Set flag for redirection
    } else {
        $message = "❌ Gagal memperbarui data: " . mysqli_error($koneksi);
    }
    mysqli_stmt_close($stmt_update);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 700px;
            margin-top: 50px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: bold;
        }

        .img-thumbnail-preview {
            max-width: 200px;
            height: auto;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mb-4 text-center text-primary">Form Edit Data Home</h2>
        <hr>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_home" value="<?php echo htmlspecialchars($id_home); ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($gambar_lama); ?>">

            <div class="mb-3">
                <label for="sejarah" class="form-label">Sejarah:</label>
                <textarea class="form-control" id="sejarah" name="sejarah" rows="7" required><?php echo htmlspecialchars($sejarah); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Gambar Saat Ini:</label><br>
                <?php if (!empty($gambar_lama) && file_exists("../uploads/" . $gambar_lama)): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($gambar_lama); ?>" class="img-thumbnail-preview" alt="Gambar Saat Ini">
                <?php else: ?>
                    <p class="text-muted">Tidak ada gambar yang tersedia.</p>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="gambar" class="form-label">Upload Gambar Baru (Opsional):</label>
                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                <div class="form-text">Biarkan kosong jika tidak ingin mengubah gambar.</div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary me-md-2">Simpan Perubahan</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='../admin/admin.php'">Kembali ke Admin</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <?php if ($redirect): ?>
        <script>
            setTimeout(function() {
                window.location.href = "../admin/admin.php";
            }, 1500); // Redirect after 1.5 seconds
        </script>
    <?php endif; ?>
</body>

</html>