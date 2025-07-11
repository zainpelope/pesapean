<?php
include 'koneksi.php';

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
    <title>Populasi Sapi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
            background-color: #f4f4f4;
        }

        .navbar {
            background-color: #333;
            overflow: hidden;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 25px;
        }

        .year-section {
            margin-bottom: 40px;
            border: 1px solid #e0e0e0;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        h3 {
            color: #0056b3;
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 5px;
            display: inline-block;
            /* To make border-bottom only as wide as text */
        }

        table {
            width: 100%;
            /* Make table full width within its container */
            border-collapse: collapse;
            margin-top: 15px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .overall-total {
            font-weight: bold;
            background-color: #dff0d8;
            /* Light green for emphasis */
            color: #28a745;
        }

        .no-data-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>

<body>

    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="peta.php">Map Sapi</a>
        <a href="rute.php">Rute Sapi</a>
        <a href="populasi.php">Populasi Sapi</a>
    </div>

    <div class="container">
        <h2>Populasi Sapi Berdasarkan Tahun dan Jenis Kelamin</h2>

        <?php if (!empty($distinctYears)): ?>
            <?php foreach ($distinctYears as $year): ?>
                <div class="year-section">
                    <h3>Tahun: <?= htmlspecialchars($year) ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Kategori Sapi</th>
                                <th>Jantan</th>
                                <th>Betina</th>
                                <th>Total Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hasCategoryData = false;
                            foreach ($populationByYearGender[$year] as $data):
                                // Check if it's a category row (not the '_overall_' data)
                                if (is_array($data) && isset($data['category'])):
                                    // Only display if there are actually cows in this category for this year
                                    if ($data['total_category'] > 0) {
                                        $hasCategoryData = true;
                            ?>
                                        <tr>
                                            <td><?= htmlspecialchars($data['category']) ?></td>
                                            <td><?= $data['male'] ?></td>
                                            <td><?= $data['female'] ?></td>
                                            <td><?= $data['total_category'] ?></td>
                                        </tr>
                                <?php
                                    }
                                endif;
                            endforeach;

                            // Display a message if no category data for this year
                            if (!$hasCategoryData) : ?>
                                <tr>
                                    <td colspan="4" class="no-data-message">Tidak ada data sapi untuk tahun ini.</td>
                                </tr>
                            <?php endif;
                            ?>
                            <tr class="overall-total">
                                <td>Total Keseluruhan Tahun <?= htmlspecialchars($year) ?></td>
                                <td><?= $populationByYearGender[$year]['_overall_']['male'] ?></td>
                                <td><?= $populationByYearGender[$year]['_overall_']['female'] ?></td>
                                <td><?= $populationByYearGender[$year]['_overall_']['total'] ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data-message">Tidak ada data populasi sapi yang ditemukan di database.</p>
        <?php endif; ?>
    </div>

</body>

</html>