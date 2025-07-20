<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session (important for checking login status)
session_start();

include '../koneksi.php'; // Ensure this path is correct for your database connection

// --- IMPORTANT: Check login status and user role ---
// Only 'Penjual' (Seller) can access this form
if (!isset($_SESSION['id_user']) || $_SESSION['nama_role'] !== 'Penjual') {
    // If not logged in or not a seller, redirect to login page with an error message
    header("Location: ../auth/login.php?error=Akses tidak diizinkan. Anda harus login sebagai Penjual untuk membuat lelang.");
    exit();
}

// Get the logged-in seller's user ID from the session
$id_user_penjual_login = $_SESSION['id_user'];

// Fetch all categories from the 'macamSapi' table
$kategoriQuery = mysqli_query($koneksi, "SELECT id_macamSapi, name FROM macamSapi ORDER BY name ASC");
$kategori_options = [];
if ($kategoriQuery) {
    while ($row = mysqli_fetch_assoc($kategoriQuery)) {
        $kategori_options[] = $row;
    }
}

// Get the selected category from the URL (GET parameter)
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$dataSapi = [];

if ($jenis != '') {
    // Get category ID
    $stmt_get_kategori = mysqli_prepare($koneksi, "SELECT id_macamSapi FROM macamSapi WHERE name = ?");
    if ($stmt_get_kategori) {
        mysqli_stmt_bind_param($stmt_get_kategori, "s", $jenis);
        mysqli_stmt_execute($stmt_get_kategori);
        $result_get_kategori = mysqli_stmt_get_result($stmt_get_kategori);
        $kategori = mysqli_fetch_assoc($result_get_kategori);
        mysqli_stmt_close($stmt_get_kategori);

        if ($kategori) {
            $id_macam = $kategori['id_macamSapi'];

            // Determine the specific cattle table name based on $jenis
            $specific_sapi_table = '';
            switch ($jenis) {
                case 'Sapi Sonok':
                    $specific_sapi_table = 'sapisonok';
                    break;
                case 'Sapi Kerap':
                    $specific_sapi_table = 'sapikerap';
                    break;
                case 'Sapi Tangghek':
                    $specific_sapi_table = 'sapitangghek';
                    break;
                case 'Sapi Ternak':
                    $specific_sapi_table = 'sapiternak';
                    break;
                case 'Sapi Potong':
                    $specific_sapi_table = 'sapipotong';
                    break;
                default:
                    // Handle unknown type or log an error
                    $specific_sapi_table = '';
                    break;
            }

            if ($specific_sapi_table != '') {
                // Fetch cattle data from 'data_sapi' AND the specific cattle table
                // Only show cattle that are NOT yet in the 'lelang' table
                $queryData = "
                    SELECT ds.id_sapi, ds.nama_pemilik, ss.nama_sapi, ds.harga_sapi
                    FROM data_sapi ds
                    LEFT JOIN $specific_sapi_table ss ON ds.id_sapi = ss.id_sapi
                    LEFT JOIN lelang l ON ds.id_sapi = l.id_sapi
                    WHERE ds.id_macamSapi = ?
                      AND ds.id_user_penjual = ?
                      AND l.id_sapi IS NULL
                ";
                $stmt_data = mysqli_prepare($koneksi, $queryData);

                if ($stmt_data) {
                    mysqli_stmt_bind_param($stmt_data, "ii", $id_macam, $id_user_penjual_login);
                    mysqli_stmt_execute($stmt_data);
                    $result_data = mysqli_stmt_get_result($stmt_data);

                    while ($row = mysqli_fetch_assoc($result_data)) {
                        $dataSapi[] = $row;
                    }
                    mysqli_stmt_close($stmt_data);
                } else {
                    die("Error prepared statement for data sapi: " . mysqli_error($koneksi));
                }
            } else {
                // If specific_sapi_table is empty, it means an invalid 'jenis' was provided.
                echo "<p class='alert alert-warning'>Jenis sapi tidak dikenali atau tidak ada data terkait.</p>";
            }
        }
    } else {
        die("Error prepared statement for category: " . mysqli_error($koneksi));
    }
}

// Set default current datetime for 'batas_waktu' input
$currentDateTime = new DateTime();
// Set timezone to WIB (Western Indonesian Time) which is UTC+7
$currentDateTime->setTimezone(new DateTimeZone('Asia/Jakarta'));
$defaultBatasWaktu = $currentDateTime->format('Y-m-d\TH:i');
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Lelang Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 800px;
            margin-top: 50px;
            margin-bottom: 50px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            /* Needed for absolute positioning of the close button */
        }

        h3 {
            color: #007bff;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-label {
            font-weight: bold;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            width: 100%;
            /* Make button full width */
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .close-button {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            text-decoration: none;
            color: #6c757d;
        }

        .close-button:hover {
            color: #343a40;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <a href="javascript:history.back()" class="close-button" aria-label="Close">&times;</a>

        <h3>Pendaftaran Lelang Sapi</h3>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="jenis" class="form-label">Pilih Jenis Sapi</label>
                <select name="jenis" id="jenis" class="form-select" onchange="this.form.submit()" required>
                    <option value="">-- Pilih Jenis --</option>
                    <?php foreach ($kategori_options as $kategori) : ?>
                        <option value="<?= htmlspecialchars($kategori['name']); ?>" <?= ($kategori['name'] == $jenis) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kategori['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if ($jenis != '') : ?>
            <form method="POST" action="proses_lelang.php">
                <input type="hidden" name="jenis" value="<?= htmlspecialchars($jenis); ?>">
                <div class="mb-3">
                    <label for="id_sapi" class="form-label">Pilih Sapi</label>
                    <select name="id_sapi" id="id_sapi" class="form-select" required>
                        <option value="">-- Pilih Sapi --</option>
                        <?php if (empty($dataSapi)): ?>
                            <option value="" disabled>Tidak ada sapi yang Anda miliki untuk jenis ini atau sudah terdaftar dalam lelang.</option>
                        <?php else: ?>
                            <?php foreach ($dataSapi as $sapi) : ?>
                                <option value="<?= htmlspecialchars($sapi['id_sapi']); ?>">
                                    <?= "Nama Sapi: " . htmlspecialchars($sapi['nama_sapi']) . " - Pemilik: " . htmlspecialchars($sapi['nama_pemilik']) . " - Harga: Rp" . number_format($sapi['harga_sapi']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Awal (Limit)</label>
                    <input type="number" name="harga_awal" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Uang Jaminan (Harga Tertinggi Sementara)</label>
                    <input type="number" name="harga_tertinggi" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Batas Waktu</label>
                    <input type="datetime-local" name="batas_waktu" class="form-control" value="<?= $defaultBatasWaktu; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status (Akan diset Pending, Menunggu Persetujuan Admin)</label>
                    <input type="text" class="form-control" value="Pending" readonly>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Ajukan Lelang</button>
            </form>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>