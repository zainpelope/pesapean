<?php
include '../koneksi.php'; // Ensure this path is correct based on your folder structure

// Define the mapping between macamSapi IDs and their respective table names
$tabelMapping = [
    '1' => 'sapiSonok',
    '2' => 'sapiKerap',
    '3' => 'sapiTangghek',
    '4' => 'sapiTernak',
    '5' => 'sapiPotong'
];

$populationByYearGender = [];
$distinctYears = [];

// --- 1. Fetch Distinct Years ---
// Query to get all unique years from the 'createdAt' column in data_sapi
// Only include years that are valid (not 0) and not in the future
$yearQuery = mysqli_query($koneksi, "SELECT DISTINCT YEAR(createdAt) AS data_year FROM data_sapi WHERE createdAt IS NOT NULL AND YEAR(createdAt) > 0 AND YEAR(createdAt) <= YEAR(CURDATE()) ORDER BY data_year DESC");

if ($yearQuery) {
    while ($row = mysqli_fetch_assoc($yearQuery)) {
        $distinctYears[] = $row['data_year'];
    }
} else {
    // Handle query error if any
    error_log("Error fetching distinct years: " . mysqli_error($koneksi));
}

// --- 2. Populate Data for Each Year ---
foreach ($distinctYears as $year) {
    $populationByYearGender[$year] = []; // Initialize array for current year
    $overallMaleCount = 0;
    $overallFemaleCount = 0;

    // Fetch all cow categories to iterate through them
    $kategoriQuery = mysqli_query($koneksi, "SELECT * FROM macamSapi ORDER BY name ASC");

    if ($kategoriQuery) {
        while ($kat = mysqli_fetch_assoc($kategoriQuery)) {
            $categoryId = $kat['id_macamSapi'];
            $categoryName = $kat['name'];
            $tableName = $tabelMapping[$categoryId] ?? null; // Get the specific table name

            if ($tableName) {
                // --- Count Male Cows for this Category and Year ---
                $maleCountQuery = mysqli_query($koneksi, "
                    SELECT COUNT(*) AS total_male
                    FROM $tableName s
                    JOIN data_sapi d ON s.id_sapi = d.id_sapi
                    WHERE YEAR(d.createdAt) = '" . mysqli_real_escape_string($koneksi, $year) . "'
                    AND d.jenis_kelamin = 'jantan'
                ");
                $maleResult = mysqli_fetch_assoc($maleCountQuery);
                $totalMale = $maleResult['total_male'] ?? 0; // Default to 0 if no result

                // --- Count Female Cows for this Category and Year ---
                $femaleCountQuery = mysqli_query($koneksi, "
                    SELECT COUNT(*) AS total_female
                    FROM $tableName s
                    JOIN data_sapi d ON s.id_sapi = d.id_sapi
                    WHERE YEAR(d.createdAt) = '" . mysqli_real_escape_string($koneksi, $year) . "'
                    AND d.jenis_kelamin = 'betina'
                ");
                $femaleResult = mysqli_fetch_assoc($femaleCountQuery);
                $totalFemale = $femaleResult['total_female'] ?? 0; // Default to 0 if no result

                // Store category-specific data
                $populationByYearGender[$year][] = [
                    'category' => $categoryName,
                    'male' => $totalMale,
                    'female' => $totalFemale,
                    'total_category' => $totalMale + $totalFemale
                ];
                $overallMaleCount += $totalMale;
                $overallFemaleCount += $totalFemale;
            }
        }
    } else {
        error_log("Error fetching categories: " . mysqli_error($koneksi));
    }

    // Store overall totals for the current year
    $populationByYearGender[$year]['_overall_'] = [
        'male' => $overallMaleCount,
        'female' => $overallFemaleCount,
        'total' => $overallMaleCount + $overallFemaleCount
    ];
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesapean - Populasi Sapi</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">

    <style>
        /* Variabel CSS untuk konsistensi */
        :root {
            --primary-color: rgb(240, 161, 44);
            /* Biru utama */
            --secondary-color: #28a745;
            /* Hijau */
            --tertiary-color: #6c757d;
            /* Abu-abu */
            --dark-color: #333;
            /* Warna gelap untuk navbar */
            --dark-text: #212529;
            --light-bg: #f8f9fa;
            --white-bg: #ffffff;
            --border-color: #dee2e6;
            --box-shadow-light: 0 4px 15px rgba(0, 0, 0, 0.08);
            --box-shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.15);
            --border-radius-sm: 8px;
            --border-radius-md: 10px;
            --border-radius-lg: 12px;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            background-color: var(--light-bg);
            color: var(--dark-text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* --- Header dan Navigasi Utama --- */
        .main-header {
            background-color: var(--dark-color);
            /* Menggunakan dark-color untuk background navbar */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            /* Bayangan sedikit lebih gelap untuk kontras */
            padding: 15px 0;
            position: relative;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo a {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.8em;
            color: var(--primary-color);
            /* Warna logo tetap primary-color */
            text-decoration: none;
        }

        .nav-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .nav-links li {
            margin-left: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--white-bg);
            /* Warna teks link navbar jadi putih */
            font-weight: 600;
            font-size: 1em;
            padding: 5px 0;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--primary-color);
            /* Warna hover tetap primary-color */
        }

        .auth-links .btn {
            padding: 10px 20px;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white-bg);
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }

        /* --- Navigasi Sekunder (Map Sapi, Rute Sapi, Populasi Sapi) --- */
        .secondary-navbar {
            background-color: var(--light-bg);
            padding: 12px 0;
            text-align: center;
            border-bottom: 1px solid #eee;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .secondary-navbar a {
            color: #555;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: var(--border-radius-sm);
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s ease;
            font-weight: 600;
            font-size: 1.05em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .secondary-navbar a:hover {
            background-color: #e9ecef;
            color: #333;
            transform: translateY(-2px);
        }

        .secondary-navbar a.active {
            background-color: var(--primary-color);
            color: var(--white-bg);
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
            pointer-events: none;
            /* Nonaktifkan klik pada link aktif */
        }

        /* --- Konten Utama Container --- */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 30px;
            background-color: var(--white-bg);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow-medium);
            transition: all 0.3s ease;
        }

        /* Judul Bagian */
        .section-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5em;
            color: var(--dark-text);
            margin-bottom: 35px;
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
            text-align: center;
            /* Center the title */
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 80px;
            height: 5px;
            background-color: var(--primary-color);
            border-radius: 3px;
        }

        .year-section {
            margin-bottom: 40px;
            border: 1px solid var(--border-color);
            padding: 25px;
            border-radius: var(--border-radius-md);
            background-color: var(--light-bg);
            box-shadow: var(--box-shadow-light);
        }

        h3 {
            font-family: 'Montserrat', sans-serif;
            color: var(--primary-color);
            /* Ubah warna h3 menjadi primary-color */
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 700;
            /* Tebalkan h3 */
            padding-bottom: 10px;
            border-bottom: 3px solid var(--secondary-color);
            /* Garis bawah yang lebih menonjol */
            display: inline-block;
            /* Agar garis bawah hanya sepanjang teks */
            font-size: 1.8em;
            /* Ukuran font lebih besar */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            /* Ensures rounded corners apply to table */
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            /* Hanya border bawah */
        }

        th {
            background-color: var(--primary-color);
            /* Biru utama untuk header tabel */
            color: var(--white-bg);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.95em;
        }

        tr:nth-child(even) {
            background-color: #f2f7fc;
            /* Warna latar belakang selang-seling */
        }

        tr:hover {
            background-color: #e0f2ff;
            /* Warna hover yang lebih terang */
        }

        .overall-total {
            font-weight: bold;
            background-color: #d1ecf1 !important;
            /* Warna khusus untuk total keseluruhan */
            color: var(--dark-text);
        }

        .overall-total td {
            font-size: 1.1em;
        }

        .no-data-message {
            text-align: center;
            padding: 25px;
            color: #666;
            font-style: italic;
            background-color: #ffe0b2;
            /* Latar belakang oranye terang */
            border: 1px solid #ffcc80;
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-light);
            font-size: 1.1em;
            margin-top: 30px;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .navbar {
                padding: 0 15px;
            }

            .nav-links li {
                margin-left: 20px;
            }

            .container {
                margin: 30px auto;
                padding: 25px;
                max-width: 90%;
            }

            .section-title {
                font-size: 2em;
            }

            .secondary-navbar a {
                margin: 0 8px;
                padding: 8px 15px;
                font-size: 1em;
            }

            h3 {
                font-size: 1.5em;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px 20px;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
                margin-top: 15px;
            }

            .nav-links li {
                margin: 0 0 10px 0;
            }

            .auth-links {
                width: 100%;
                text-align: center;
                margin-top: 10px;
            }

            .auth-links .btn {
                width: calc(100% - 20px);
                margin: 0 10px;
            }

            .container {
                margin: 20px auto;
                padding: 20px;
                border-radius: var(--border-radius-sm);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .section-title {
                font-size: 1.8em;
                margin-bottom: 25px;
            }

            .secondary-navbar {
                flex-direction: column;
                gap: 10px;
                padding: 10px 0;
            }

            .secondary-navbar a {
                margin: 0;
                width: 80%;
                text-align: center;
                justify-content: center;
            }

            .year-section {
                padding: 15px;
            }

            h3 {
                font-size: 1.3em;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
                border-radius: var(--border-radius);
                overflow: hidden;
            }

            td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }

            td:before {
                position: absolute;
                top: 0;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: 600;
                content: attr(data-label);
                color: var(--primary-color);
            }

            .overall-total td {
                text-align: right;
                padding-left: 10px;
            }

            .overall-total td:before {
                content: "";
                /* Hide data-label for total row */
            }

            /* Specific labels for responsive table cells */
            td:nth-of-type(1):before {
                content: "Kategori Sapi";
            }

            td:nth-of-type(2):before {
                content: "Jantan";
            }

            td:nth-of-type(3):before {
                content: "Betina";
            }

            /* Removing total kategori, as it's not in your new table structure */
            /* td:nth-of-type(4):before {
                content: "Total Kategori";
            } */
        }
    </style>
</head>

<body>
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
                <a href="#login" class="btn btn-primary">Profile</a>
            </div>
        </nav>
    </header>

    <div class="secondary-navbar">
        <a href="peta.php">Map Sapi</a>
        <a href="rute.php">Rute Sapi</a>
        <a href="populasi.php" class="active">Populasi Sapi</a>
    </div>

    <div class="container">
        <h2 class="section-title">Populasi Sapi Berdasarkan Tahun dan Jenis Kelamin</h2>

        <?php if (!empty($distinctYears)) : ?>
            <?php foreach ($distinctYears as $year) : ?>
                <div class="year-section">
                    <h3>Tahun: <?= htmlspecialchars($year) ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Kategori Sapi</th>
                                <th>Jantan</th>
                                <th>Betina</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hasCategoryData = false;
                            foreach ($populationByYearGender[$year] as $key => $data) :
                                // Check if it's a category row (not the '_overall_' data)
                                if ($key !== '_overall_' && is_array($data) && isset($data['category'])) :
                                    // Only display if there are actually cows in this category for this year
                                    if ($data['total_category'] > 0) {
                                        $hasCategoryData = true;
                            ?>
                                        <tr>
                                            <td data-label="Kategori Sapi"><?= htmlspecialchars($data['category']) ?></td>
                                            <td data-label="Jantan"><?= $data['male'] ?></td>
                                            <td data-label="Betina"><?= $data['female'] ?></td>
                                        </tr>
                                <?php
                                    }
                                endif;
                            endforeach;

                            // Display a message if no category data for this year
                            if (!$hasCategoryData) : ?>
                                <tr>
                                    <td colspan="3" class="no-data-message" data-label="Pesan">Tidak ada data sapi untuk tahun ini.</td>
                                </tr>
                            <?php endif;
                            ?>
                            <tr class="overall-total">
                                <td data-label="Total Keseluruhan">Total Keseluruhan Tahun <?= htmlspecialchars($year) ?></td>
                                <td data-label="Jantan"><?= $populationByYearGender[$year]['_overall_']['male'] ?></td>
                                <td data-label="Betina"><?= $populationByYearGender[$year]['_overall_']['female'] ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="no-data-message">Tidak ada data populasi sapi yang ditemukan di database.</p>
        <?php endif; ?>
    </div>

    <?php include '../footer.php';
    ?>
</body>

</html>