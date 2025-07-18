<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

include '../koneksi.php'; // Adjust path to your database connection file

// --- IMPORTANT: Check login status and user role (only admin can access) ---
if (!isset($_SESSION['id_user']) || $_SESSION['nama_role'] !== 'Admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: auth/login.php?error=Akses tidak diizinkan. Anda harus login sebagai Admin.");
    exit();
}

$id_admin_login = $_SESSION['id_user']; // Get the logged-in admin's ID

// Process approval or rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $lelang_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $new_status = 'Aktif'; // Auction becomes active after approval
        $approved_by_admin_value = 1; // Set to 1 (approved)
        $approved_at_value = date('Y-m-d H:i:s'); // Approval timestamp
        $id_admin_approver_value = $id_admin_login; // The admin who approved it

        $stmt = mysqli_prepare($koneksi, "UPDATE lelang SET status = ?, approved_by_admin = ?, approved_at = ?, id_admin_approver = ?, updatedAt = NOW() WHERE id_sapi = ?");
        // Parameter types: s (string for status), i (int for approved_by_admin), s (string for approved_at), i (int for id_admin_approver), i (int for lelang_id)
        mysqli_stmt_bind_param($stmt, "sisii", $new_status, $approved_by_admin_value, $approved_at_value, $id_admin_approver_value, $lelang_id);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Lelang berhasil disetujui!');</script>";
        } else {
            echo "<script>alert('Gagal menyetujui lelang: " . mysqli_error($koneksi) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    } elseif ($action === 'reject') {
        // If rejected, change status to 'Ditolak' and keep approved_by_admin as 0
        $new_status = 'Ditolak';
        $approved_by_admin_value = 0; // Still 0 as it's not approved

        $stmt = mysqli_prepare($koneksi, "UPDATE lelang SET status = ?, approved_by_admin = ?, approved_at = NULL, id_admin_approver = NULL, updatedAt = NOW() WHERE id_sapi = ?");
        // Parameter types: s (string for status), i (int for approved_by_admin), i (int for lelang_id)
        mysqli_stmt_bind_param($stmt, "sii", $new_status, $approved_by_admin_value, $lelang_id);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Lelang berhasil ditolak!');</script>";
        } else {
            echo "<script>alert('Gagal menolak lelang: " . mysqli_error($koneksi) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch auction data that are pending approval (approved_by_admin = 0) or rejected (status = 'Ditolak')
$queryPendingLelang = "
    SELECT
        l.id_sapi,
        ds.foto_sapi,
        ds.nama_pemilik,
        ms.name AS kategori,
        l.harga_awal,
        l.harga_tertinggi,
        l.batas_waktu,
        l.status,
        l.createdAt
    FROM lelang l
    INNER JOIN data_sapi ds ON l.id_sapi = ds.id_sapi
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    WHERE l.approved_by_admin = 0 OR l.status = 'Ditolak'
    ORDER BY l.createdAt DESC
";
$resultPendingLelang = mysqli_query($koneksi, $queryPendingLelang);

if (!$resultPendingLelang) {
    die("Error fetching pending auctions: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Persetujuan Lelang Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .table-responsive {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .btn-approve {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-approve:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-reject {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-reject:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .status-badge {
            padding: .35em .65em;
            border-radius: .25rem;
            font-size: 0.75em;
            font-weight: 600;
        }

        .status-pending {
            background-color: #ffc107;
            color: #664d03;
        }

        /* warning */
        .status-aktif {
            background-color: #28a745;
            color: #fff;
        }

        /* success */
        .status-ditolak {
            background-color: #dc3545;
            color: #fff;
        }

        /* danger */
    </style>
</head>

<body>
    <div class="container">
        <h3 class="text-center mb-4">Daftar Lelang Menunggu Persetujuan Admin</h3>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID Lelang</th>
                        <th>Foto Sapi</th>
                        <th>Pemilik</th>
                        <th>Kategori</th>
                        <th>Harga Awal</th>
                        <th>Harga Tertinggi</th>
                        <th>Batas Waktu</th>
                        <th>Diajukan Pada</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultPendingLelang) > 0) : ?>
                        <?php while ($lelang = mysqli_fetch_assoc($resultPendingLelang)) : ?>
                            <tr>
                                <td><?= htmlspecialchars($lelang['id_sapi']); ?></td>
                                <td>
                                    <img src="../uploads_sapi/<?= htmlspecialchars($lelang['foto_sapi']); ?>" alt="Sapi" class="img-fluid">
                                </td>
                                <td><?= htmlspecialchars($lelang['nama_pemilik']); ?></td>
                                <td><?= htmlspecialchars($lelang['kategori']); ?></td>
                                <td>Rp<?= number_format($lelang['harga_awal']); ?></td>
                                <td>Rp<?= number_format($lelang['harga_tertinggi']); ?></td>
                                <td><?= date('d M Y H:i', strtotime($lelang['batas_waktu'])); ?></td>
                                <td><?= date('d M Y H:i', strtotime($lelang['createdAt'])); ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    if ($lelang['status'] == 'Pending') {
                                        $statusClass = 'status-pending';
                                    } elseif ($lelang['status'] == 'Aktif') {
                                        $statusClass = 'status-aktif';
                                    } elseif ($lelang['status'] == 'Ditolak') {
                                        $statusClass = 'status-ditolak';
                                    }
                                    ?>
                                    <span class="status-badge <?= $statusClass; ?>">
                                        <?= htmlspecialchars($lelang['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($lelang['status'] == 'Pending') : ?>
                                        <a href="admin_approval.php?action=approve&id=<?= htmlspecialchars($lelang['id_sapi']); ?>" class="btn btn-approve btn-sm mb-1" onclick="return confirm('Apakah Anda yakin ingin menyetujui lelang ini?');">Setujui</a>
                                        <a href="admin_approval.php?action=reject&id=<?= htmlspecialchars($lelang['id_sapi']); ?>" class="btn btn-reject btn-sm" onclick="return confirm('Apakah Anda yakin ingin menolak lelang ini?');">Tolak</a>
                                    <?php else : ?>
                                        Sudah diproses
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada lelang yang menunggu persetujuan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>