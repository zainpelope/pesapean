<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session (important for checking login status and getting user ID)
session_start();

// Include your database connection file
include '../koneksi.php';

// Ensure the user is logged in as a seller
if (!isset($_SESSION['id_user']) || $_SESSION['nama_role'] !== 'Penjual') {
    // If not logged in or not a seller, redirect to the login page or display an error message
    header("Location: ../auth/login.php?error=Anda harus login sebagai Penjual untuk menambah data sapi.");
    exit();
}

// Get the ID of the currently logged-in seller from the session
$id_user_penjual = $_SESSION['id_user'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get main inputs from the form
    $id_macamSapi = $_POST['id_macamSapi'];
    $macam_nama = $_POST['macam_nama'];
    $foto_sapi = $_FILES['foto_sapi']['name'];
    $harga_sapi = $_POST['harga_sapi'];
    $nama_pemilik = $_POST['nama_pemilik'];
    $alamat_pemilik = $_POST['alamat_pemilik'];
    $nomor_pemilik = $_POST['nomor_pemilik'];
    $email_pemilik = $_POST['email_pemilik'];
    $jenis_kelamin = $_POST['jenis_kelamin']; // New gender input

    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');
    // Get latitude and longitude from manual input
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Upload location for the cow photo
    // Important: Create a separate 'uploads_sapi' directory for cow images to keep them organized
    $target_dir = "../uploads_sapi/"; // Changed target directory
    $target_file = $target_dir . basename($foto_sapi);

    // Ensure the 'uploads_sapi/' directory exists, if not, create it
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // 0777 grants full permissions (should be adjusted in production)
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["foto_sapi"]["tmp_name"], $target_file)) {
        // Query to save data to the `data_sapi` table
        // **Add the `id_user_penjual` column to the list of columns and bound values**
        $query = "INSERT INTO data_sapi (id_macamSapi, foto_sapi, harga_sapi, nama_pemilik, alamat_pemilik, nomor_pemilik, email_pemilik, createdAt, updatedAt, latitude, longitude, jenis_kelamin, id_user_penjual)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // Add ? for id_user_penjual
        $stmt = mysqli_prepare($koneksi, $query);

        // Bind parameters to the statement
        // The format string "isdssssssddsi" means:
        // i = id_macamSapi (int)
        // s = foto_sapi (string)
        // d = harga_sapi (double)
        // s = nama_pemilik (string)
        // s = alamat_pemilik (string)
        // s = nomor_pemilik (string)
        // s = email_pemilik (string)
        // s = createdAt (string)
        // s = updatedAt (string)
        // d = latitude (double)
        // d = longitude (double)
        // s = jenis_kelamin (string)
        // i = id_user_penjual (int) <-- NEWLY ADDED
        mysqli_stmt_bind_param(
            $stmt,
            "isdssssssddsi", // <-- CORRECTED BINDING STRING (Added 'i' at the end)
            $id_macamSapi,
            $foto_sapi,
            $harga_sapi,
            $nama_pemilik,
            $alamat_pemilik,
            $nomor_pemilik,
            $email_pemilik,
            $createdAt,
            $updatedAt,
            $latitude,
            $longitude,
            $jenis_kelamin,
            $id_user_penjual // <-- Add this parameter
        );

        if (mysqli_stmt_execute($stmt)) {
            $id_sapi = mysqli_insert_id($koneksi); // Get the ID of the newly inserted cow

            // Logic to save data to specific tables based on cow type
            // Ensure you also insert the correct $id_sapi into these detail tables
            switch ($macam_nama) {
                case 'Sapi Kerap':
                    $nama = $_POST['kerap_nama'];
                    $fisik = $_POST['kerap_fisik'];
                    $lari = $_POST['kerap_lari'];
                    $penghargaan = $_POST['kerap_penghargaan'];
                    $queryKerap = "INSERT INTO sapiKerap (id_sapi, nama_sapi, ketahanan_fisik, kecepatan_lari, penghargaan) VALUES (?, ?, ?, ?, ?)";
                    $stmtKerap = mysqli_prepare($koneksi, $queryKerap);
                    mysqli_stmt_bind_param($stmtKerap, "issss", $id_sapi, $nama, $fisik, $lari, $penghargaan);
                    mysqli_stmt_execute($stmtKerap);
                    mysqli_stmt_close($stmtKerap);
                    break;

                case 'Sapi Sonok':
                    $nama = $_POST['sonok_nama'];
                    $umur = $_POST['sonok_umur'];
                    $dada = $_POST['sonok_dada'];
                    $panjang = $_POST['sonok_panjang'];
                    $tinggi_pundak = $_POST['sonok_tinggi_pundak'];
                    $tinggi_punggung = $_POST['sonok_tinggi_punggung'];
                    $wajah = $_POST['sonok_wajah'];
                    $punggul = $_POST['sonok_punggul'];
                    $dada_lebar = $_POST['sonok_dada_lebar'];
                    $kaki = $_POST['sonok_kaki'];
                    $kesehatan = $_POST['sonok_kesehatan'];

                    $querySonok = "INSERT INTO sapiSonok (id_sapi, nama_sapi, umur, lingkar_dada, panjang_badan, tinggi_pundak, tinggi_punggung, panjang_wajah, lebar_punggul, lebar_dada, tinggi_kaki, kesehatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtSonok = mysqli_prepare($koneksi, $querySonok);
                    // Ensure the order and type of parameters match the columns in the sapiSonok table
                    mysqli_stmt_bind_param($stmtSonok, "isssssssssss", $id_sapi, $nama, $umur, $dada, $panjang, $tinggi_pundak, $tinggi_punggung, $wajah, $punggul, $dada_lebar, $kaki, $kesehatan);
                    mysqli_stmt_execute($stmtSonok);
                    $id_sapiSonok = mysqli_insert_id($koneksi); // ID for sonok table
                    mysqli_stmt_close($stmtSonok);

                    // Generation One
                    $gen1_pejantan = $_POST['gen1_pejantan'];
                    $gen1_jenis_pejantan = $_POST['gen1_jenis_pejantan'];
                    $gen1_induk = $_POST['gen1_induk'];
                    $gen1_jenis_induk = $_POST['gen1_jenis_induk'];
                    $gen1_kakek = $_POST['gen1_kakek'];
                    // updatedAt is already retrieved above

                    $queryGen1 = "INSERT INTO generasiSatu (sapiSonok, namaPejantanGenerasiSatu, jenisPejantanGenerasiSatu, namaIndukGenerasiSatu, jenisIndukGenerasiSatu, namaKakekPejantanGenerasiSatu, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmtGen1 = mysqli_prepare($koneksi, $queryGen1);
                    mysqli_stmt_bind_param($stmtGen1, "issssss", $id_sapiSonok, $gen1_pejantan, $gen1_jenis_pejantan, $gen1_induk, $gen1_jenis_induk, $gen1_kakek, $updatedAt);
                    mysqli_stmt_execute($stmtGen1);
                    mysqli_stmt_close($stmtGen1);

                    // Generation Two
                    $gen2_pejantan = $_POST['gen2_pejantan'];
                    $gen2_jenis_pejantan = $_POST['gen2_jenis_pejantan'];
                    $gen2_induk = $_POST['gen2_induk'];
                    $gen2_jenis_induk = $_POST['gen2_jenis_induk'];
                    $gen2_jenis_kakek = $_POST['gen2_jenis_kakek'];
                    $gen2_nenek = $_POST['gen2_nenek'];
                    // createdAt is already retrieved above

                    $queryGen2 = "INSERT INTO generasiDua (sapiSonok, namaPejantanGenerasiDua, jenisPejantanGenerasiDua, namaIndukGenerasiDua, jenisIndukGenerasiDua, jenisKakekPejantanGenerasiDua, namaNenekIndukGenerasiDua, createdAt, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtGen2 = mysqli_prepare($koneksi, $queryGen2);
                    mysqli_stmt_bind_param($stmtGen2, "issssssss", $id_sapiSonok, $gen2_pejantan, $gen2_jenis_pejantan, $gen2_induk, $gen2_jenis_induk, $gen2_jenis_kakek, $gen2_nenek, $createdAt, $updatedAt);
                    mysqli_stmt_execute($stmtGen2);
                    mysqli_stmt_close($stmtGen2);
                    break;

                case 'Sapi Tangghek':
                    $tinggi = $_POST['tangeh_tinggi'];
                    $panjang = $_POST['tangeh_panjang'];
                    $dada = $_POST['tangeh_dada'];
                    $bobot = $_POST['tangeh_bobot'];
                    $latihan = $_POST['tangeh_latihan'];
                    $jarak = $_POST['tangeh_jarak'];
                    $prestasi = $_POST['tangeh_prestasi'];
                    $kesehatan = $_POST['tangeh_kesehatan'];
                    $queryTangghek = "INSERT INTO sapiTangghek (id_sapi, tinggi_badan, panjang_badan, lingkar_dada, bobot_badan, intensitas_latihan, jarak_latihan, prestasi, kesehatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtTangghek = mysqli_prepare($koneksi, $queryTangghek);
                    mysqli_stmt_bind_param($stmtTangghek, "issssssss", $id_sapi, $tinggi, $panjang, $dada, $bobot, $latihan, $jarak, $prestasi, $kesehatan);
                    mysqli_stmt_execute($stmtTangghek);
                    mysqli_stmt_close($stmtTangghek);
                    break;

                case 'Sapi Potong':
                    $nama = $_POST['potong_nama'];
                    $berat = $_POST['potong_berat'];
                    $persentase = $_POST['potong_persen'];
                    $queryPotong = "INSERT INTO sapiPotong (id_sapi, nama_sapi, berat_badan, persentase_daging) VALUES (?, ?, ?, ?)";
                    $stmtPotong = mysqli_prepare($koneksi, $queryPotong);
                    mysqli_stmt_bind_param($stmtPotong, "isss", $id_sapi, $nama, $berat, $persentase);
                    mysqli_stmt_execute($stmtPotong);
                    mysqli_stmt_close($stmtPotong);
                    break;

                case 'Sapi Ternak':
                    $nama = $_POST['termak_nama'];
                    $kesuburan = $_POST['termak_subur'];
                    $riwayat = $_POST['termak_riwayat'];
                    $queryTernak = "INSERT INTO sapiTernak (id_sapi, nama_sapi, kesuburan, riwayat_kesehatan) VALUES (?, ?, ?, ?)";
                    $stmtTernak = mysqli_prepare($koneksi, $queryTernak);
                    mysqli_stmt_bind_param($stmtTernak, "isss", $id_sapi, $nama, $kesuburan, $riwayat);
                    mysqli_stmt_execute($stmtTernak);
                    mysqli_stmt_close($stmtTernak);
                    break;
            }

            // Redirect to the seller's cattle data page after successful insertion
            header("Location: ../penjual/data_sapi.php?jenis=all"); // Or 'data_sapi.php' if the default is to show all seller's cows
            exit();
        } else {
            echo "<div class='alert alert-danger' role='alert'>Failed to save main cow data: " . mysqli_error($koneksi) . "</div>";
        }
        mysqli_stmt_close($stmt); // Close the main statement
    } else {
        echo "<div class='alert alert-danger' role='alert'>Failed to upload cow photo. Error: " . $_FILES['foto_sapi']['error'] . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Tambah Data Sapi</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
        }

        h2 {
            color: #007bff;
            margin-bottom: 30px;
            text-align: center;
        }

        h4 {
            color: #6c757d;
            margin-top: 25px;
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }

        .form-group label {
            font-weight: bold;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
        <h2>Form Tambah Data Sapi</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="macamSapi">Jenis Sapi:</label>
                <select name="id_macamSapi" id="macamSapi" class="form-control" required>
                    <option value="">-- Pilih Jenis Sapi --</option>
                    <?php
                    $result = mysqli_query($koneksi, "SELECT id_macamSapi, name FROM macamSapi");
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['id_macamSapi']}' data-nama='{$row['name']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
                <input type="hidden" name="macam_nama" id="macamNama">
            </div>

            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin:</label>
                <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="jantan">Jantan</option>
                    <option value="betina">Betina</option>
                </select>
            </div>

            <div class="form-group">
                <label for="foto_sapi">Foto Sapi:</label>
                <input type="file" name="foto_sapi" id="foto_sapi" class="form-control-file" required>
            </div>

            <div class="form-group">
                <label for="harga_sapi">Harga Sapi:</label>
                <input type="number" name="harga_sapi" id="harga_sapi" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="nama_pemilik">Nama Pemilik:</label>
                <input type="text" name="nama_pemilik" id="nama_pemilik" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="alamat_pemilik">Alamat Pemilik:</label>
                <textarea name="alamat_pemilik" id="alamat_pemilik" class="form-control" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="nomor_pemilik">Nomor Pemilik:</label>
                <input type="text" name="nomor_pemilik" id="nomor_pemilik" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email_pemilik">Email Pemilik:</label>
                <input type="email" name="email_pemilik" id="email_pemilik" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="latitude">Latitude:</label>
                <input type="text" name="latitude" id="latitude" class="form-control" placeholder="Contoh: -7.2575" required>
            </div>

            <div class="form-group">
                <label for="longitude">Longitude:</label>
                <input type="text" name="longitude" id="longitude" class="form-control" placeholder="Contoh: 112.7522" required>
            </div>

            <div id="formJenis"></div>
            <button type="submit" class="btn btn-primary btn-block">Simpan</button>
            <a href="../penjual/data_sapi.php?jenis=all" class="btn btn-secondary btn-block">Kembali</a>
        </form>
    </div>

    <script>
        const forms = {
            "Sapi Kerap": `
                <h4>Form Sapi Kerap</h4>
                <div class="form-group">
                    <input type="text" name="kerap_nama" class="form-control" placeholder="Nama Sapi" required>
                </div>
                <div class="form-group">
                    <input type="text" name="kerap_fisik" class="form-control" placeholder="Ketahanan Fisik" required>
                </div>
                <div class="form-group">
                    <input type="text" name="kerap_lari" class="form-control" placeholder="Kecepatan Lari" required>
                </div>
                <div class="form-group">
                    <input type="text" name="kerap_penghargaan" class="form-control" placeholder="Penghargaan" required>
                </div>
            `,
            "Sapi Sonok": `
                <h4>Form Sapi Sonok</h4>
                <div class="form-group">
                    <input type="text" name="sonok_nama" class="form-control" placeholder="Nama Sapi" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_umur" class="form-control" placeholder="Umur" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_dada" class="form-control" placeholder="Lingkar Dada" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_panjang" class="form-control" placeholder="Panjang Badan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_tinggi_pundak" class="form-control" placeholder="Tinggi Pundak" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_tinggi_punggung" class="form-control" placeholder="Tinggi Punggung" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_wajah" class="form-control" placeholder="Panjang Wajah" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_punggul" class="form-control" placeholder="Lebar Punggul" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_dada_lebar" class="form-control" placeholder="Lebar Dada" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_kaki" class="form-control" placeholder="Tinggi Kaki" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_kesehatan" class="form-control" placeholder="Kesehatan" required>
                </div>

                <h4>Generasi Satu</h4>
                <div class="form-group">
                    <input type="text" name="gen1_pejantan" class="form-control" placeholder="Nama Pejantan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen1_jenis_pejantan" class="form-control" placeholder="Jenis Pejantan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen1_induk" class="form-control" placeholder="Nama Induk" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen1_jenis_induk" class="form-control" placeholder="Jenis Induk" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen1_kakek" class="form-control" placeholder="Nama Kakek Pejantan" required>
                </div>

                <h4>Generasi Dua</h4>
                <div class="form-group">
                    <input type="text" name="gen2_pejantan" class="form-control" placeholder="Nama Pejantan Generasi Dua" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen2_jenis_pejantan" class="form-control" placeholder="Jenis Pejantan Generasi Dua" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen2_induk" class="form-control" placeholder="Nama Induk Generasi Dua" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen2_jenis_induk" class="form-control" placeholder="Jenis Induk Generasi Dua" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen2_jenis_kakek" class="form-control" placeholder="Jenis Kakek Pejantan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen2_nenek" class="form-control" placeholder="Nama Nenek Induk" required>
                </div>
            `,
            "Sapi Tangghek": `
                <h4>Form Sapi Tangghek</h4>
                <div class="form-group">
                    <input type="text" name="tangeh_tinggi" class="form-control" placeholder="Tinggi Badan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_panjang" class="form-control" placeholder="Panjang Badan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_dada" class="form-control" placeholder="Lingkar Dada" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_bobot" class="form-control" placeholder="Bobot Badan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_latihan" class="form-control" placeholder="Intensitas Latihan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_jarak" class="form-control" placeholder="Jarak Latihan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_prestasi" class="form-control" placeholder="Prestasi" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_kesehatan" class="form-control" placeholder="Kesehatan" required>
                </div>
            `,
            "Sapi Potong": `
                <h4>Form Sapi Potong</h4>
                <div class="form-group">
                    <input type="text" name="potong_nama" class="form-control" placeholder="Nama Sapi" required>
                </div>
                <div class="form-group">
                    <input type="text" name="potong_berat" class="form-control" placeholder="Berat Badan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="potong_persen" class="form-control" placeholder="Persentase Daging" required>
                </div>
            `,
            "Sapi Ternak": `
                <h4>Form Sapi Ternak</h4>
                <div class="form-group">
                    <input type="text" name="termak_nama" class="form-control" placeholder="Nama Sapi" required>
                </div>
                <div class="form-group">
                    <input type="text" name="termak_subur" class="form-control" placeholder="Kesuburan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="termak_riwayat" class="form-control" placeholder="Riwayat Kesehatan" required>
                </div>
            `
        };

        // Event listener when cow type changes
        $('#macamSapi').change(function() {
            const selected = $('#macamSapi option:selected').attr('data-nama');
            $('#macamNama').val(selected); // Update hidden input with cow type name
            $('#formJenis').html(forms[selected] || ''); // Display additional form based on cow type
        });
    </script>
</body>

</html>