<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

include '../koneksi.php'; // Adjust path to your database connection file



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
            echo "<script>alert('Lelang berhasil disetujui!'); window.location.href='../admin/lelang.php';</script>"; // Redirect to refresh the list
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
            echo "<script>alert('Lelang berhasil ditolak!'); window.location.href='admin.php';</script>"; // Redirect to refresh the list
        } else {
            echo "<script>alert('Gagal menolak lelang: " . mysqli_error($koneksi) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch all auction data regardless of status
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesapean - Data Lelang</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-pFQhV+Cq+BfS2Z2v2E2L2R2/2N2P2g2B2D2G2H2I2J2K2L2M2N2O2P2Q2R2S2T2U2V2W2X2Y2Z2==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

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
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="../admin/admin.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../admin/admin.php">Beranda</a></li>
                <li><a href="../admin/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../admin/lelang.php">Lelang</a></li>
                <li><a href="../admin/peta.php">Data User</a></li>
                <li><a href="../admin/peta.php">Pesan</a></li>
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
        <h3 class="text-center mb-4">Lelang Sapi</h3>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>No.</th>
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
                        <?php
                        $counter = 1; // Initialize a counter
                        while ($lelang = mysqli_fetch_assoc($resultPendingLelang)) :
                        ?>
                            <tr>
                                <td><?= $counter++; ?></td>
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
                                        <a href="../admin/lelang.php?action=approve&id=<?= htmlspecialchars($lelang['id_sapi']); ?>" class="btn btn-approve btn-sm mb-1" onclick="return confirm('Apakah Anda yakin ingin menyetujui lelang ini?');">Setujui</a>
                                        <a href="../admin/lelang.php?action=reject&id=<?= htmlspecialchars($lelang['id_sapi']); ?>" class="btn btn-reject btn-sm" onclick="return confirm('Apakah Anda yakin ingin menolak lelang ini?');">Tolak</a>
                                    <?php else : ?>
                                        Sudah diproses
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada data lelang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <?php include '../footer.php'; ?>
</body>

</html>