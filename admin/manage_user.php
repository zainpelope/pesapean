<?php
include '../koneksi.php'; // Memasukkan file koneksi database

$action = isset($_GET['action']) ? $_GET['action'] : 'add'; // Menentukan aksi (tambah/edit/hapus)
$id_user = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Mengambil ID pengguna jika ada

$username = '';
$email = '';
$password = ''; // Variabel ini hanya akan diisi saat penambahan
$message = ''; // Pesan untuk feedback pengguna

// --- Ambil ID Role untuk 'Penjual' secara otomatis ---
$id_role_penjual = null;
$query_get_penjual_role = "SELECT id_role FROM role WHERE nama_role = 'Penjual'";
$result_get_penjual_role = mysqli_query($koneksi, $query_get_penjual_role);

if ($result_get_penjual_role && mysqli_num_rows($result_get_penjual_role) > 0) {
    $row_penjual_role = mysqli_fetch_assoc($result_get_penjual_role);
    $id_role_penjual = $row_penjual_role['id_role'];
} else {
    // Jika role 'Penjual' tidak ditemukan, tampilkan error dan hentikan eksekusi
    die("Error: Role 'Penjual' tidak ditemukan di database Anda. Pastikan ada role dengan nama 'Penjual'.");
}
// -----------------------------------------------------

// Logika untuk aksi "edit" (tidak ada perubahan pada role di sini)
if ($action == 'edit' && $id_user > 0) {
    $query_user = "SELECT username, email FROM users WHERE id_user = ?";
    $stmt = mysqli_prepare($koneksi, $query_user);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user_data = mysqli_fetch_assoc($result);
        $username = $user_data['username'];
        $email = $user_data['email'];
        // Role tidak perlu diambil karena tidak akan diedit melalui form ini
    } else {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4'>Pengguna tidak ditemukan.</div>";
        $action = 'add'; // Kembali ke mode tambah jika pengguna tidak ditemukan
    }
    mysqli_stmt_close($stmt);
}

// Logika untuk menangani pengiriman form (POST request)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Validasi input
    if (empty($username) || empty($email)) {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4'>Username dan Email harus diisi.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4'>Format email tidak valid.</div>";
    } else {
        if ($action == 'add') {
            // Pastikan password diisi saat menambah pengguna baru
            if (empty($_POST['password'])) {
                $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4'>Password harus diisi saat menambahkan pengguna baru.</div>";
            } else {
                // BARIS INI YANG DIUBAH: Mengambil password langsung tanpa hashing
                $password = trim($_POST['password']);
                // BARIS SEBELUMNYA: $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash password

                $createdAt = date('Y-m-d H:i:s');

                // Memeriksa apakah username atau email sudah ada
                $check_query = "SELECT id_user FROM users WHERE username = ? OR email = ?";
                $stmt_check = mysqli_prepare($koneksi, $check_query);
                mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
                mysqli_stmt_execute($stmt_check);
                mysqli_stmt_store_result($stmt_check);

                if (mysqli_stmt_num_rows($stmt_check) > 0) {
                    $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4'>Username atau Email sudah terdaftar.</div>";
                } else {
                    // Gunakan id_role_penjual yang sudah diambil
                    $insert_query = "INSERT INTO users (username, email, password, id_role, createdAt) VALUES (?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($koneksi, $insert_query);
                    mysqli_stmt_bind_param($stmt, "sssis", $username, $email, $password, $id_role_penjual, $createdAt);

                    if (mysqli_stmt_execute($stmt)) {
                        // Redirect ke ../admin/data_user.php setelah berhasil tambah
                        header("Location: ../admin/data_user.php?message=" . urlencode("Pengguna berhasil ditambahkan sebagai Penjual."));
                        exit(); // Penting untuk menghentikan eksekusi script setelah redirect
                    } else {
                        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4'>Gagal menambahkan pengguna: " . mysqli_error($koneksi) . "</div>";
                    }
                }
                mysqli_stmt_close($stmt_check);
            }
        } elseif ($action == 'edit') {
            $updateAt = date('Y-m-d H:i:s');
            // Role tidak diubah melalui form ini
            $update_query = "UPDATE users SET username = ?, email = ?, updateAt = ? WHERE id_user = ?";
            $stmt = mysqli_prepare($koneksi, $update_query);
            mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $updateAt, $id_user);

            if (mysqli_stmt_execute($stmt)) {
                // Redirect ke ../admin/data_user.php setelah berhasil perbarui
                header("Location: ../admin/data_user.php?message=" . urlencode("Pengguna berhasil diperbarui."));
                exit(); // Penting untuk menghentikan eksekusi script setelah redirect
            } else {
                $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4'>Gagal memperbarui pengguna: " . mysqli_error($koneksi) . "</div>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Logika untuk aksi "delete"
if ($action == 'delete' && $id_user > 0) {
    $delete_query = "DELETE FROM users WHERE id_user = ?";
    $stmt = mysqli_prepare($koneksi, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $id_user);

    if (mysqli_stmt_execute($stmt)) {
        // Redirect kembali ke halaman ../admin/data_user setelah berhasil hapus
        header("Location: ../admin/data_user.php?message=" . urlencode("Pengguna berhasil dihapus."));
        exit();
    } else {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4'>Gagal menghapus pengguna: " . mysqli_error($koneksi) . "</div>";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($action == 'add') ? 'Tambah Pengguna' : 'Edit Pengguna'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #4b5563;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease-in-out;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: #ffffff;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .info-box {
            background-color: #e0f2fe;
            border: 1px solid #90cdf4;
            color: #2b6cb0;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">
            <?php echo ($action == 'add') ? 'Tambah Pengguna Baru' : 'Edit Pengguna'; ?>
        </h1>

        <?php echo $message; // Menampilkan pesan feedback
        ?>

        <form action="manage_user.php<?php echo ($action == 'edit') ? '?action=edit&id=' . htmlspecialchars($id_user) : '?action=add'; ?>" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="form-input" required>
            </div>
            <?php if ($action == 'add'): // Password hanya diperlukan saat menambah pengguna baru
            ?>
                <div class="form-group">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>

            <?php endif; ?>

            <div class="flex justify-end space-x-4 mt-6">
                <button type="submit" class="btn btn-primary">
                    <?php echo ($action == 'add') ? 'Tambah Pengguna' : 'Perbarui Pengguna'; ?>
                </button>
                <a href="../admin/data_user.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</body>

</html>

<?php
mysqli_close($koneksi); // Menutup koneksi database
?>