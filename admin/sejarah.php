<?php
include '../koneksi.php'; // Adjust path to your koneksi.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = ''; // Initialize a message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sejarah = $_POST['sejarah'];

    // Upload gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $target_dir = "../uploads/"; // Make sure this directory exists and is writable
    $target_file = $target_dir . basename($gambar);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($tmp);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $message = "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        $message = "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (e.g., limit to 5MB)
    if ($_FILES["gambar"]["size"] > 5000000) {
        $message = "Sorry, your file is too large (max 5MB).";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (
        $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif"
    ) {
        $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $message = "❌ " . $message . " Image upload failed.";
    } else {
        if (move_uploaded_file($tmp, $target_file)) {
            // Save data to database
            // Using prepared statements to prevent SQL injection
            $sql = "INSERT INTO home (sejarah, gambar) VALUES (?, ?)";
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $sejarah, $gambar);
            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                $message = "✅ Data berhasil ditambahkan.";
                // Redirect after a short delay to show the success message
                echo '<script>
                        setTimeout(function() {
                            window.location.href = "../admin/admin.php";
                        }, 2000); // Redirect after 2 seconds
                      </script>';
            } else {
                $message = "❌ Gagal menambahkan data: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "❌ Gagal mengupload gambar. Please check directory permissions.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin-top: 50px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mb-4 text-center">Form Sejarah</h2>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="sejarah" class="form-label">Sejarah:</label>
                <textarea class="form-control" id="sejarah" name="sejarah" rows="5" required></textarea>
            </div>

            <div class="mb-3">
                <label for="gambar" class="form-label">Upload Gambar:</label>
                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" required>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>