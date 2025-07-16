<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session (important for checking login status)
session_start();

// Include your database connection file
include '../koneksi.php'; // Ensure this path is correct for your database connection

// Automatically update auction status to 'Lewat' (Expired) if the end time has passed
mysqli_query($koneksi, "
    UPDATE lelang
    SET status = 'Lewat', updatedAt = NOW()
    WHERE batas_waktu < NOW() AND status = 'Aktif'
");

// Fetch all categories from the 'macamSapi' table
$queryKategori = mysqli_query($koneksi, "SELECT id_macamSapi, name FROM macamSapi ORDER BY name ASC");
$kategori_options = [];
if ($queryKategori) {
    while ($row = mysqli_fetch_assoc($queryKategori)) {
        $kategori_options[] = $row;
    }
}

// Get the selected category from the URL (GET parameter), default to 'semua' (all)
$selectedKategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';

// Determine the 'My Auctions' filter status
$showMyAuctions = false; // Default to false
if (isset($_GET['my_auctions'])) {
    $showMyAuctions = ($_GET['my_auctions'] == 'true');
} else if (isset($_SESSION['id_user']) && $_SESSION['nama_role'] === 'Penjual') {
    // If 'my_auctions' is not explicitly set in URL, but user is a seller,
    // default to showing only their auctions.
    $showMyAuctions = true;
}

// Initialize the WHERE clause parts
$where_clauses = ["1"]; // Default to '1' (true) so the query is always valid

// If the user is logged in as a seller and 'My Auctions' filter is selected (either by default or explicitly)
$param_types = ""; // Initialize parameter types string for prepared statement
$param_values = []; // Initialize parameter values array for prepared statement

if ($showMyAuctions && isset($_SESSION['id_user']) && $_SESSION['nama_role'] === 'Penjual') {
    $id_user_penjual_login = $_SESSION['id_user'];
    // Filter by the 'id_user' column in the 'lelang' table (which identifies the seller who created the auction)
    $where_clauses[] = "l.id_user = ?";
    $param_types .= "i"; // 'i' for integer
    $param_values[] = $id_user_penjual_login;
}

// Add category filter if a specific category is selected (and it's a valid numeric ID)
if ($selectedKategori != 'semua' && is_numeric($selectedKategori)) {
    $where_clauses[] = "ms.id_macamSapi = ?";
    $param_types .= "i"; // 'i' for integer
    $param_values[] = (int)$selectedKategori;
}

// Combine all WHERE clauses with 'AND'
$final_where_clause = implode(" AND ", $where_clauses);

// Query to fetch auction data
$queryDataSapi = "
    SELECT
        ds.id_sapi,
        ds.foto_sapi,
        ds.alamat_pemilik,
        ds.nama_pemilik,
        ds.nomor_pemilik,
        ms.name AS kategori,
        l.harga_awal,
        l.harga_tertinggi,
        l.batas_waktu,
        l.status,
        l.id_user AS lelang_id_user -- Alias for id_user from the 'lelang' table (the auction creator)
    FROM data_sapi ds
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    INNER JOIN lelang l ON ds.id_sapi = l.id_sapi
    WHERE " . $final_where_clause . "
    ORDER BY l.createdAt DESC
";

// Prepare the SQL statement
$stmt_data_sapi = mysqli_prepare($koneksi, $queryDataSapi);

// Check if the statement was prepared successfully
if (!$stmt_data_sapi) {
    die("Error preparing statement for auction data: " . mysqli_error($koneksi));
}

// Bind parameters if there are any
if (!empty($param_types)) {
    // Use call_user_func_array for binding parameters dynamically
    // The '...' operator unpacks the array into separate arguments for mysqli_stmt_bind_param
    mysqli_stmt_bind_param($stmt_data_sapi, $param_types, ...$param_values);
}

// Execute the prepared statement
mysqli_stmt_execute($stmt_data_sapi);
$resultDataSapi = mysqli_stmt_get_result($stmt_data_sapi);

// Ensure the query result is not false
if (!$resultDataSapi) {
    die("Error executing query for auction data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Daftar Lelang Sapi</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" xintegrity="sha512-pFQhV+Cq+BfS2Z2v2E2L2R2/2N2P2g2B2D2G2H2I2J2K2L2M2N2O2P2Q2R2S2T2U2V2W2X2Y2Z2==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Global CSS variables */
        /* Styling for the "Tambah Lelang" button */
        .btn-add-lelang {
            background-color: var(--primary-color);
            /* Uses your primary orange-brown color */
            color: var(--white-bg);
            /* White text */
            padding: 12px 25px;
            /* Larger padding for a more prominent button */
            border-radius: var(--border-radius-md);
            /* Rounded corners */
            text-decoration: none;
            /* Removes the default underline */
            font-weight: 600;
            /* Bolder text */
            display: inline-flex;
            /* Allows icon and text to sit side-by-side */
            align-items: center;
            /* Vertically centers icon and text */
            gap: 8px;
            /* Space between icon and text */
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            /* Smooth hover effects */
            box-shadow: var(--box-shadow-light);
            /* Subtle shadow for depth */
        }

        .btn-add-lelang:hover {
            background-color: #d09050;
            /* Darker primary color on hover */
            transform: translateY(-2px);
            /* Slight lift effect on hover */
            box-shadow: var(--box-shadow-medium);
            /* Enhanced shadow on hover */
        }

        :root {
            --primary-color: rgb(240, 161, 44);
            /* Orange-brown */
            --secondary-color: rgb(48, 52, 56);
            /* Dark Grey */
            --tertiary-color: #6c757d;
            /* Grey */
            --dark-color: #333;
            /* Dark text */
            --dark-text: #212529;
            --light-bg: #f8f9fa;
            --white-bg: #ffffff;
            --border-color: #dee2e6;
            --box-shadow-light: 0 4px 15px rgba(0, 0, 0, 0.08);
            --box-shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.15);
            --border-radius-sm: 8px;
            --border-radius-md: 10px;
            --border-radius-lg: 12px;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        /* Navbar Styling */
        .main-header {
            background-color: var(--secondary-color);
            /* Dark grey for header */
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
            /* Logo color */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .navbar .logo a:hover {
            color: #f0c080;
            /* Lighter primary color on hover */
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
            /* Menu text color */
            font-weight: 600;
            padding: 0.5rem 0;
            transition: color 0.3s ease, border-bottom 0.3s ease;
        }

        .nav-links li a:hover {
            color: #cccccc;
            /* Lighter white for hover */
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
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .auth-links .btn-primary {
            background-color: var(--primary-color);
            color: var(--white-bg);
            border: none;
        }

        .auth-links .btn-primary:hover {
            background-color: #d09050;
            /* Darker primary for hover */
        }

        .auth-links .btn-outline-primary {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .auth-links .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: var(--white-bg);
        }

        .auth-links .btn-danger {
            background-color: var(--danger-color);
            color: var(--white-bg);
            border: none;
        }

        .auth-links .btn-danger:hover {
            background-color: #b02a37;
        }

        /* Filter Box and Buttons */
        .filter-box {
            background-color: var(--white-bg);
            padding: 20px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-light);
            margin-bottom: 20px;
            /* Added margin for spacing */
        }

        .filter-box h5 {
            color: var(--dark-color);
            margin-bottom: 15px;
            font-weight: 700;
        }

        .form-check {
            margin-bottom: 10px;
        }

        .form-check-label {
            color: var(--dark-text);
            font-weight: 500;
        }

        /* Card Styles */
        .card-sapi {
            width: 100%;
            /* Make cards responsive */
            max-width: 18rem;
            /* Max width for larger screens */
            margin: 10px;
            border: none;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--box-shadow-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-sapi:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-medium);
        }

        .card-header.status {
            font-size: 1em;
            font-weight: bold;
            text-align: center;
            padding: 10px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .status.sedang {
            background-color: #fff3cd;
            /* Light yellow */
            color: #856404;
            /* Dark yellow */
        }

        .status.lewat {
            background-color: #f8d7da;
            /* Light red */
            color: #721c24;
            /* Dark red */
        }

        .status.aktif {
            background-color: #d4edda;
            /* Light green */
            color: #155724;
            /* Dark green */
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .card-body {
            padding: 15px;
        }

        .card-text {
            margin-bottom: 5px;
            color: var(--dark-text);
        }

        .card-text strong {
            color: var(--primary-color);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        /* Responsive Adjustments */
        @media (max-width: 991.98px) {
            .nav-links {
                display: none;
            }

            .navbar {
                padding: 0 1rem;
            }

            .auth-links {
                margin-left: auto;
            }

            .filter-box {
                width: 100%;
                margin-bottom: 20px;
            }

            .col-md-9 {
                justify-content: center;
                /* Center cards on smaller screens */
            }

            .card-sapi {
                max-width: none;
                /* Remove max-width for full flexibility */
                width: calc(50% - 20px);
                /* Two cards per row with margin */
            }
        }

        @media (max-width: 767.98px) {
            .card-sapi {
                width: 100%;
                /* One card per row on very small screens */
                margin: 10px auto;
            }
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
            </ul>
            <div class="auth-links">
                <?php
                // Check if the user is logged in
                if (isset($_SESSION['id_user'])) {
                    // User is logged in, display Profile and Logout button
                    echo '<a href="../auth/profile.php" class="btn btn-primary">Profile</a>';
                } else {
                    // User is not logged in, display Login and Daftar buttons
                    echo '<a href="../auth/login.php" class="btn btn-primary">Login</a>';
                    echo '<a href="../auth/register.php" class="btn btn-outline-primary">Daftar</a>';
                }
                ?>
            </div>
        </nav>
    </header>
    <div class="container text-center my-4">
        <a href="../form_lelang.php" class="btn-add-lelang">
            <i class="fas fa-plus-circle"></i> Tambah Lelang Baru
        </a>
    </div>

    <div class="container mt-5">
        <div class="row">

            <div class="col-md-3 filter-box">
                <h5>Filter Lelang:</h5>
                <form method="GET" action="">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="kategori" id="semua_kategori" value="semua" onchange="this.form.submit()" <?= ($selectedKategori == 'semua') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="semua_kategori">Semua Kategori</label>
                    </div>

                    <?php foreach ($kategori_options as $kategori) : ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kategori" value="<?= htmlspecialchars($kategori['id_macamSapi']); ?>" id="kategori<?= htmlspecialchars($kategori['id_macamSapi']); ?>" onchange="this.form.submit()" <?= ($selectedKategori == $kategori['id_macamSapi']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="kategori<?= htmlspecialchars($kategori['id_macamSapi']); ?>">
                                <?= htmlspecialchars($kategori['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>


                </form>
            </div>

            <div class="col-md-9 d-flex flex-wrap justify-content-center">
                <?php if (mysqli_num_rows($resultDataSapi) == 0): ?>
                    <div class="alert alert-info w-100 text-center">Tidak ada lelang yang tersedia untuk filter ini.</div>
                <?php endif; ?>

                <?php while ($sapi = mysqli_fetch_assoc($resultDataSapi)) : ?>
                    <div class="card card-sapi">
                        <div class="card-header status <?= strtolower($sapi['status']); ?>">
                            <?= htmlspecialchars($sapi['status']); ?>
                        </div>
                        <img src="../uploads_sapi/<?= htmlspecialchars($sapi['foto_sapi']); ?>" class="card-img-top" alt="gambar sapi" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title text-center mb-2"><?= htmlspecialchars($sapi['kategori'] ?? 'N/A') ?></h5>
                            <p class="card-text text-center">Pemilik: <strong><?= htmlspecialchars($sapi['nama_pemilik'] ?? 'N/A'); ?></strong></p>
                            <p class="card-text text-center small">
                                Harga Awal: <strong>Rp<?= number_format($sapi['harga_awal'] ?? 0); ?></strong><br>
                                Harga Tertinggi: <strong>Rp<?= number_format($sapi['harga_tertinggi'] ?? 0); ?></strong><br>
                                Batas Waktu: <strong><?= date('d M Y H:i', strtotime($sapi['batas_waktu'])); ?></strong>
                            </p>
                            <a href="detail_lelang.php?id=<?= $sapi['id_sapi']; ?>" class="btn btn-success w-100">Detail Lelang</a>
                            <?php
                            // Display Edit/Delete buttons only if the logged-in seller is the owner of this auction
                            // We use 'lelang_id_user' which is the 'id_user' from the 'lelang' table
                            if (isset($_SESSION['id_user']) && $_SESSION['id_user'] == $sapi['lelang_id_user']) {
                            ?>
                                <div class="d-flex justify-content-between mt-2">
                                    <a href="edit_lelang.php?id=<?= htmlspecialchars($sapi['id_sapi']) ?>" class="btn btn-info btn-sm flex-grow-1 me-1">Edit</a>
                                    <a href="hapus_lelang.php?id=<?= htmlspecialchars($sapi['id_sapi']) ?>" class="btn btn-danger btn-sm flex-grow-1 ms-1" onclick="return confirm('Apakah Anda yakin ingin menghapus lelang ini?');">Hapus</a>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col text-center">
                <a href="prosedur.php" class="btn btn-dark btn-lg w-50 mb-2">Prosedur Lelang</a><br>
            </div>
        </div>
    </div>
    <?php include '../footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>