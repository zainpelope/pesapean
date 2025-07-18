<?php
// Start the session
session_start();

include '../koneksi.php'; // Memasukkan file koneksi database

// Query untuk mengambil semua data pengguna dengan role 'Penjual'
$query_users = "SELECT u.id_user, u.username, u.email, r.nama_role 
                 FROM users u
                 JOIN role r ON u.id_role = r.id_role
                 WHERE r.nama_role = 'Penjual'
                 ORDER BY u.id_user DESC";
$result_users = mysqli_query($koneksi, $query_users);

// Memeriksa apakah query berhasil dieksekusi
if (!$result_users) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesapean - Preferensi Sapi dan Penjualan</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #333;
        }

        .container {
            max-width: 960px;
            margin: 2rem auto;
            padding: 1.5rem;
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 0.75rem 1rem;
            text-align: left;
        }

        th {
            background-color: #e5e7eb;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 0.875rem;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
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

        .btn-edit {
            background-color: #f59e0b;
            color: #ffffff;
        }

        .btn-edit:hover {
            background-color: #d97706;
        }

        .btn-delete {
            background-color: #ef4444;
            color: #ffffff;
        }

        .btn-delete:hover {
            background-color: #dc2626;
        }
    </style>
</head>

<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="admin/admin.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../admin/admin.php">Beranda</a></li>
                <li><a href="../admin/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../admin/lelang.php">Lelang</a></li>
                <li><a href="../admin/data_user.php">Data User</a></li>
                <li><a href="../admin/pesan.php">Pesan</a></li>
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
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">Manajemen Data Penjual</h1>

        <div class="mb-6">
            <a href="manage_user.php?action=add" class="btn btn-primary">Tambah Pengguna Baru</a>
        </div>

        <div class="overflow-x-auto rounded-lg shadow-md">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-4">Nomor</th>
                        <th class="py-3 px-4">Username</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result_users) > 0):
                        $nomor_urut = 1; // Inisialisasi nomor urut
                    ?>
                        <?php while ($row = mysqli_fetch_assoc($result_users)): ?>
                            <tr class="border-b border-gray-200">
                                <td class="py-3 px-4"><?php echo $nomor_urut++; ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['username']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama_role']); ?></td>
                                <td class="py-3 px-4 flex space-x-2">
                                    <a href="../admin/manage_user.php?action=edit&id=<?php echo htmlspecialchars($row['id_user']); ?>" class="btn btn-edit text-sm">Edit</a>
                                    <a href="../admin/manage_user.php?action=delete&id=<?php echo htmlspecialchars($row['id_user']); ?>"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');"
                                        class="btn btn-delete text-sm">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-4 px-4 text-center text-gray-500">Tidak ada data pengguna dengan role Penjual.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>

<?php
mysqli_close($koneksi); // Menutup koneksi database
?>