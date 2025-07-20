<?php
// Aktifkan pelaporan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../koneksi.php'; // Pastikan jalur ini benar

// Redirect jika tidak login atau bukan penjual
if (!isset($_SESSION['id_user']) || $_SESSION['nama_role'] !== 'Penjual') {
    $_SESSION['message'] = 'Anda harus login sebagai Penjual untuk mengakses halaman ini.';
    $_SESSION['message_type'] = 'danger';
    header('Location: ../auth/login.php');
    exit();
}

// Ambil ID sapi dari URL
$id_sapi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data lelang untuk diisi ke formulir
$stmt = mysqli_prepare($koneksi, "
    SELECT ds.id_sapi, ds.foto_sapi, ds.alamat_pemilik, ds.nama_pemilik, ds.nomor_pemilik, ds.id_macamSapi,
           ms.name AS kategori_name, l.harga_awal, l.batas_waktu, l.id_user AS lelang_id_user
    FROM data_sapi ds
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    INNER JOIN lelang l ON ds.id_sapi = l.id_sapi
    WHERE ds.id_sapi = ? AND l.id_user = ?
");

if (!$stmt) {
    die("Error preparing statement: " . mysqli_error($koneksi));
}
mysqli_stmt_bind_param($stmt, "ii", $id_sapi, $_SESSION['id_user']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$sapi = mysqli_fetch_assoc($result);

// Jika lelang tidak ditemukan atau user tidak memiliki izin
if (!$sapi) {
    $_SESSION['message'] = 'Lelang tidak ditemukan atau Anda tidak memiliki izin untuk mengeditnya.';
    $_SESSION['message_type'] = 'danger';
    header('Location: lelang.php'); // Redirect kembali ke daftar lelang
    exit();
}

// Ambil semua kategori untuk dropdown
$queryKategori = mysqli_query($koneksi, "SELECT id_macamSapi, name FROM macamSapi ORDER BY name ASC");
$kategori_options = [];
if ($queryKategori) {
    while ($row = mysqli_fetch_assoc($queryKategori)) {
        $kategori_options[] = $row;
    }
}

// Tangani pengiriman formulir (jika ada data POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sapi_post = isset($_POST['id_sapi']) ? (int)$_POST['id_sapi'] : 0;

    // Pastikan ID dari POST sama dengan ID dari GET
    if ($id_sapi_post !== $id_sapi) {
        $_SESSION['message'] = 'Terjadi kesalahan ID lelang. Silakan coba lagi.';
        $_SESSION['message_type'] = 'danger';
        header('Location: lelang.php');
        exit();
    }

    $nama_pemilik = htmlspecialchars(trim($_POST['nama_pemilik']));
    $nomor_pemilik = htmlspecialchars(trim($_POST['nomor_pemilik']));
    $alamat_pemilik = htmlspecialchars(trim($_POST['alamat_pemilik']));
    $id_macamSapi = (int)$_POST['kategori'];
    $harga_awal = (int)$_POST['harga_awal'];
    $batas_waktu = htmlspecialchars(trim($_POST['batas_waktu']));

    $foto_sapi = $sapi['foto_sapi']; // Pertahankan foto yang sudah ada secara default
    $upload_error = false;

    // Logika upload file baru
    if (isset($_FILES['foto_sapi']) && $_FILES['foto_sapi']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads_sapi/";
        $new_file_name = uniqid() . '_' . basename($_FILES["foto_sapi"]["name"]);
        $target_file = $target_dir . $new_file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi ekstensi file
        $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $valid_extensions)) {
            $_SESSION['message'] = 'Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.';
            $_SESSION['message_type'] = 'danger';
            $upload_error = true;
        }
        // Validasi ukuran file (contoh: max 5MB)
        if ($_FILES["foto_sapi"]["size"] > 5000000) {
            $_SESSION['message'] = 'Maaf, ukuran file terlalu besar (maks 5MB).';
            $_SESSION['message_type'] = 'danger';
            $upload_error = true;
        }

        if (!$upload_error) {
            if (move_uploaded_file($_FILES["foto_sapi"]["tmp_name"], $target_file)) {
                // Hapus foto lama jika ada dan bukan foto default
                if ($sapi['foto_sapi'] && file_exists($target_dir . $sapi['foto_sapi']) && $sapi['foto_sapi'] !== 'default.jpg') {
                    unlink($target_dir . $sapi['foto_sapi']);
                }
                $foto_sapi = $new_file_name;
            } else {
                $_SESSION['message'] = 'Terjadi error saat mengunggah foto baru.';
                $_SESSION['message_type'] = 'danger';
                $upload_error = true;
            }
        }
    }

    if (!$upload_error) {
        // Mulai transaksi database
        mysqli_begin_transaction($koneksi);

        try {
            // Update tabel data_sapi
            $stmt_update_ds = mysqli_prepare($koneksi, "
                UPDATE data_sapi
                SET nama_pemilik = ?, nomor_pemilik = ?, alamat_pemilik = ?, id_macamSapi = ?, foto_sapi = ?, updatedAt = NOW()
                WHERE id_sapi = ?
            ");
            if (!$stmt_update_ds) throw new Exception("Prepare data_sapi failed: " . mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_update_ds, "sssisi", $nama_pemilik, $nomor_pemilik, $alamat_pemilik, $id_macamSapi, $foto_sapi, $id_sapi);
            $success_ds = mysqli_stmt_execute($stmt_update_ds);

            // Update tabel lelang
            $stmt_update_lelang = mysqli_prepare($koneksi, "
                UPDATE lelang
                SET harga_awal = ?, batas_waktu = ?, updatedAt = NOW()
                WHERE id_sapi = ? AND id_user = ?
            ");
            if (!$stmt_update_lelang) throw new Exception("Prepare lelang failed: " . mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_update_lelang, "isii", $harga_awal, $batas_waktu, $id_sapi, $_SESSION['id_user']);
            $success_lelang = mysqli_stmt_execute($stmt_update_lelang);

            if ($success_ds && $success_lelang) {
                mysqli_commit($koneksi);
                $_SESSION['message'] = 'Lelang berhasil diperbarui!';
                $_SESSION['message_type'] = 'success';
                header('Location: lelang.php'); // Redirect ke halaman lelang
                exit();
            } else {
                throw new Exception("Gagal memperbarui lelang.");
            }
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $_SESSION['message'] = 'Gagal memperbarui lelang: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Edit Lelang Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: rgb(240, 161, 44);
            --secondary-color: rgb(48, 52, 56);
            --white-bg: #ffffff;
            --box-shadow-light: 0 4px 15px rgba(0, 0, 0, 0.08);
            --border-radius-md: 10px;
        }

        .main-header {
            background-color: var(--secondary-color);
            box-shadow: 0 2px 10px var(--box-shadow-light);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .navbar .logo a {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 1.5rem;
        }

        .nav-links li a {
            text-decoration: none;
            color: var(--white-bg);
            font-weight: 600;
        }

        .auth-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .auth-links .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-links .btn-primary {
            background-color: var(--primary-color);
            color: var(--white-bg);
            border: none;
        }

        .form-container {
            background-color: var(--white-bg);
            padding: 30px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-light);
            max-width: 600px;
            margin: 30px auto;
        }

        .form-container h2 {
            text-align: center;
            color: var(--secondary-color);
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: var(--white-bg);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #d09050;
        }
    </style>
</head>

<body class="bg-light">
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="../penjual/beranda.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../penjual/beranda.php">Beranda</a></li>
                <li><a href="../penjual/peta.php">Peta Interaktif</a></li>
                <li><a href="../penjual/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../penjual/lelang.php">Lelang</a></li>
                <li><a href="../penjual/pesan.php">Pesan</a></li>
            </ul>
            <div class="auth-links">
                <?php if (isset($_SESSION['id_user'])): ?>
                    <a href="../auth/profile.php" class="btn btn-primary">Profile</a>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-primary">Login</a>
                    <a href="../auth/register.php" class="btn btn-outline-primary">Daftar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="form-container">
            <h2>Edit Lelang Sapi</h2>
            <?php
            // Periksa pesan sesi dan tampilkan
            if (isset($_SESSION['message'])) {
                $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
                echo '<div class="alert alert-' . $message_type . ' alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($_SESSION['message']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_sapi" value="<?= htmlspecialchars($sapi['id_sapi']); ?>">

                <div class="mb-3">
                    <label for="nama_pemilik" class="form-label">Nama Pemilik:</label>
                    <input type="text" class="form-control" id="nama_pemilik" name="nama_pemilik" value="<?= htmlspecialchars($sapi['nama_pemilik']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nomor_pemilik" class="form-label">Nomor Telepon Pemilik:</label>
                    <input type="text" class="form-control" id="nomor_pemilik" name="nomor_pemilik" value="<?= htmlspecialchars($sapi['nomor_pemilik']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="alamat_pemilik" class="form-label">Alamat Pemilik:</label>
                    <textarea class="form-control" id="alamat_pemilik" name="alamat_pemilik" rows="3" required><?= htmlspecialchars($sapi['alamat_pemilik']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori Sapi:</label>
                    <select class="form-select" id="kategori" name="kategori" required>
                        <?php foreach ($kategori_options as $kategori) : ?>
                            <option value="<?= htmlspecialchars($kategori['id_macamSapi']); ?>" <?= ($sapi['id_macamSapi'] == $kategori['id_macamSapi']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kategori['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="harga_awal" class="form-label">Harga Awal Lelang (Rp):</label>
                    <input type="number" class="form-control" id="harga_awal" name="harga_awal" value="<?= htmlspecialchars($sapi['harga_awal']); ?>" required min="0">
                </div>
                <div class="mb-3">
                    <label for="batas_waktu" class="form-label">Batas Waktu Lelang:</label>
                    <input type="datetime-local" class="form-control" id="batas_waktu" name="batas_waktu" value="<?= date('Y-m-d\TH:i', strtotime($sapi['batas_waktu'])); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="foto_sapi" class="form-label">Foto Sapi (kosongkan jika tidak ingin mengubah):</label>
                    <?php if ($sapi['foto_sapi']): ?>
                        <div class="mb-2">
                            <img src="../uploads_sapi/<?= htmlspecialchars($sapi['foto_sapi']); ?>" alt="Current Sapi Photo" style="max-width: 200px; height: auto; border-radius: 5px;">
                            <p class="small text-muted mt-1">Foto Saat Ini</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="foto_sapi" name="foto_sapi" accept="image/*">
                </div>

                <button type="submit" class="btn btn-submit w-100">Simpan Perubahan</button>
                <a href="lelang.php" class="btn btn-secondary w-100 mt-2">Batal</a>
            </form>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>