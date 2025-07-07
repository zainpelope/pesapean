<?php

include 'koneksi.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil input utama
    $id_macamSapi = $_POST['id_macamSapi'];
    $macam_nama = $_POST['macam_nama'];
    $foto_sapi = $_FILES['foto_sapi']['name'];
    $harga_sapi = $_POST['harga_sapi'];
    $nama_pemilik = $_POST['nama_pemilik'];
    $alamat_pemilik = $_POST['alamat_pemilik'];
    $nomor_pemilik = $_POST['nomor_pemilik'];
    $email_pemilik = $_POST['email_pemilik'];
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    // Upload file
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($foto_sapi);
    move_uploaded_file($_FILES["foto_sapi"]["tmp_name"], $target_file);

    // Simpan ke data_sapi
    $query = "INSERT INTO data_sapi (id_macamSapi, foto_sapi, harga_sapi, nama_pemilik, alamat_pemilik, nomor_pemilik, email_pemilik, createdAt, updatedAt)
              VALUES ('$id_macamSapi', '$foto_sapi', '$harga_sapi', '$nama_pemilik', '$alamat_pemilik', '$nomor_pemilik', '$email_pemilik', '$createdAt', '$updatedAt')";
    if (mysqli_query($koneksi, $query)) {
        $id_sapi = mysqli_insert_id($koneksi);

        // Simpan ke tabel sesuai jenis
        switch ($macam_nama) {
            case 'Sapi Kerap':
                $nama = $_POST['kerap_nama'];
                $fisik = $_POST['kerap_fisik'];
                $lari = $_POST['kerap_lari'];
                $penghargaan = $_POST['kerap_penghargaan'];
                mysqli_query($koneksi, "INSERT INTO sapiKerap (id_sapi, nama_sapi, ketahanan_fisik, kecepatan_lari, penghargaan)
                    VALUES ('$id_sapi', '$nama', '$fisik', '$lari', '$penghargaan')");
                break;

            case 'Sapi Sonok':
                // Simpan ke sapiSonok
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

                mysqli_query($koneksi, "INSERT INTO sapiSonok (id_sapi, nama_sapi, umur, lingkar_dada, panjang_badan, tinggi_pundak, tinggi_punggung, panjang_wajah, lebar_punggul, lebar_dada, tinggi_kaki, kesehatan)
        VALUES ('$id_sapi', '$nama', '$umur', '$dada', '$panjang', '$tinggi_pundak', '$tinggi_punggung', '$wajah', '$punggul', '$dada_lebar', '$kaki', '$kesehatan')");

                $id_sapiSonok = mysqli_insert_id($koneksi);

                // Simpan ke generasiSatu
                $gen1_pejantan = $_POST['gen1_pejantan'];
                $gen1_jenis_pejantan = $_POST['gen1_jenis_pejantan'];
                $gen1_induk = $_POST['gen1_induk'];
                $gen1_jenis_induk = $_POST['gen1_jenis_induk'];
                $gen1_kakek = $_POST['gen1_kakek'];
                $updatedAt = date('Y-m-d H:i:s');

                mysqli_query($koneksi, "INSERT INTO generasiSatu (sapiSonok, namaPejantanGenerasiSatu, jenisPejantanGenerasiSatu, namaIndukGenerasiSatu, jenisIndukGenerasiSatu, namaKakekPejantanGenerasiSatu, updatedAt)
        VALUES ('$id_sapiSonok', '$gen1_pejantan', '$gen1_jenis_pejantan', '$gen1_induk', '$gen1_jenis_induk', '$gen1_kakek', '$updatedAt')");

                // Simpan ke generasiDua
                $gen2_pejantan = $_POST['gen2_pejantan'];
                $gen2_jenis_pejantan = $_POST['gen2_jenis_pejantan'];
                $gen2_induk = $_POST['gen2_induk'];
                $gen2_jenis_induk = $_POST['gen2_jenis_induk'];
                $gen2_jenis_kakek = $_POST['gen2_jenis_kakek'];
                $gen2_nenek = $_POST['gen2_nenek'];
                $createdAt = date('Y-m-d H:i:s');

                mysqli_query($koneksi, "INSERT INTO generasiDua (sapiSonok, namaPejantanGenerasiDua, jenisPejantanGenerasiDua, namaIndukGenerasiDua, jenisIndukGenerasiDua, jenisKakekPejantanGenerasiDua, namaNenekIndukGenerasiDua, createdAt, updatedAt)
        VALUES ('$id_sapiSonok', '$gen2_pejantan', '$gen2_jenis_pejantan', '$gen2_induk', '$gen2_jenis_induk', '$gen2_jenis_kakek', '$gen2_nenek', '$createdAt', '$updatedAt')");
                break;


            case 'Sapi Tangeh':
                $tinggi = $_POST['tangeh_tinggi'];
                $panjang = $_POST['tangeh_panjang'];
                $dada = $_POST['tangeh_dada'];
                $bobot = $_POST['tangeh_bobot'];
                $latihan = $_POST['tangeh_latihan'];
                $jarak = $_POST['tangeh_jarak'];
                $prestasi = $_POST['tangeh_prestasi'];
                $kesehatan = $_POST['tangeh_kesehatan'];
                mysqli_query($koneksi, "INSERT INTO sapiTangeh (id_sapi, tinggi_badan, panjang_badan, lingkar_dada, bobot_badan, intensitas_latihan, jarak_latihan, prestasi, kesehatan)
                    VALUES ('$id_sapi', '$tinggi', '$panjang', '$dada', '$bobot', '$latihan', '$jarak', '$prestasi', '$kesehatan')");
                break;

            case 'Sapi Potong':
                $nama = $_POST['potong_nama'];
                $berat = $_POST['potong_berat'];
                $persentase = $_POST['potong_persen'];
                mysqli_query($koneksi, "INSERT INTO sapiPotong (id_sapi, nama_sapi, berat_badan, persentase_daging)
                    VALUES ('$id_sapi', '$nama', '$berat', '$persentase')");
                break;

            case 'Sapi Termak':
                $nama = $_POST['termak_nama'];
                $kesuburan = $_POST['termak_subur'];
                $riwayat = $_POST['termak_riwayat'];
                mysqli_query($koneksi, "INSERT INTO sapiTermak (id_sapi, nama_sapi, kesuburan, riwayat_kesehatan)
                    VALUES ('$id_sapi', '$nama', '$kesuburan', '$riwayat')");
                break;
        }

        echo "<p style='color:green;'>Data sapi berhasil disimpan ke semua tabel.</p>";
    } else {
        echo "<p style='color:red;'>Gagal: " . mysqli_error($koneksi) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Tambah Data Sapi</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
    <h2>Form Tambah Data Sapi</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Macam Sapi:</label>
        <select name="id_macamSapi" id="macamSapi" required>
            <option value="">-- Pilih Macam Sapi --</option>
            <?php
            $result = mysqli_query($koneksi, "SELECT * FROM macamSapi");
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='{$row['id_macamSapi']}' data-nama='{$row['name']}'>{$row['name']}</option>";
            }
            ?>
        </select>
        <input type="hidden" name="macam_nama" id="macamNama">
        <br><br>

        <label>Foto Sapi:</label>
        <input type="file" name="foto_sapi" required><br><br>

        <label>Harga Sapi:</label>
        <input type="number" name="harga_sapi" required><br><br>

        <label>Nama Pemilik:</label>
        <input type="text" name="nama_pemilik" required><br><br>

        <label>Alamat Pemilik:</label>
        <textarea name="alamat_pemilik" required></textarea><br><br>

        <label>Nomor Pemilik:</label>
        <input type="text" name="nomor_pemilik" required><br><br>

        <label>Email Pemilik:</label>
        <input type="email" name="email_pemilik" required><br><br>

        <!-- Form Tambahan Jenis Sapi -->
        <div id="formJenis"></div>

        <button type="submit">Simpan</button>
    </form>

    <script>
        const forms = {
            "Sapi Kerap": `
        <h4>Form Sapi Kerap</h4>
        <input type="text" name="kerap_nama" placeholder="Nama Sapi"><br>
        <input type="text" name="kerap_fisik" placeholder="Ketahanan Fisik"><br>
        <input type="text" name="kerap_lari" placeholder="Kecepatan Lari"><br>
        <input type="text" name="kerap_penghargaan" placeholder="Penghargaan"><br>
    `,
            "Sapi Sonok": `
    <h4>Form Sapi Sonok</h4>
    <input type="text" name="sonok_nama" placeholder="Nama Sapi"><br>
    <input type="text" name="sonok_umur" placeholder="Umur"><br>
    <input type="text" name="sonok_dada" placeholder="Lingkar Dada"><br>
    <input type="text" name="sonok_panjang" placeholder="Panjang Badan"><br>
    <input type="text" name="sonok_tinggi_pundak" placeholder="Tinggi Pundak"><br>
    <input type="text" name="sonok_tinggi_punggung" placeholder="Tinggi Punggung"><br>
    <input type="text" name="sonok_wajah" placeholder="Panjang Wajah"><br>
    <input type="text" name="sonok_punggul" placeholder="Lebar Punggul"><br>
    <input type="text" name="sonok_dada_lebar" placeholder="Lebar Dada"><br>
    <input type="text" name="sonok_kaki" placeholder="Tinggi Kaki"><br>
    <input type="text" name="sonok_kesehatan" placeholder="Kesehatan"><br><br>

    <h4>Generasi Satu</h4>
    <input type="text" name="gen1_pejantan" placeholder="Nama Pejantan"><br>
    <input type="text" name="gen1_jenis_pejantan" placeholder="Jenis Pejantan"><br>
    <input type="text" name="gen1_induk" placeholder="Nama Induk"><br>
    <input type="text" name="gen1_jenis_induk" placeholder="Jenis Induk"><br>
    <input type="text" name="gen1_kakek" placeholder="Nama Kakek Pejantan"><br><br>

    <h4>Generasi Dua</h4>
    <input type="text" name="gen2_pejantan" placeholder="Nama Pejantan Generasi Dua"><br>
    <input type="text" name="gen2_jenis_pejantan" placeholder="Jenis Pejantan Generasi Dua"><br>
    <input type="text" name="gen2_induk" placeholder="Nama Induk Generasi Dua"><br>
    <input type="text" name="gen2_jenis_induk" placeholder="Jenis Induk Generasi Dua"><br>
    <input type="text" name="gen2_jenis_kakek" placeholder="Jenis Kakek Pejantan"><br>
    <input type="text" name="gen2_nenek" placeholder="Nama Nenek Induk"><br>
`,
            "Sapi Tangeh": `
        <h4>Form Sapi Tangeh</h4>
        <input type="text" name="tangeh_tinggi" placeholder="Tinggi Badan"><br>
        <input type="text" name="tangeh_panjang" placeholder="Panjang Badan"><br>
        <input type="text" name="tangeh_dada" placeholder="Lingkar Dada"><br>
        <input type="text" name="tangeh_bobot" placeholder="Bobot Badan"><br>
        <input type="text" name="tangeh_latihan" placeholder="Intensitas Latihan"><br>
        <input type="text" name="tangeh_jarak" placeholder="Jarak Latihan"><br>
        <input type="text" name="tangeh_prestasi" placeholder="Prestasi"><br>
        <input type="text" name="tangeh_kesehatan" placeholder="Kesehatan"><br>
    `,
            "Sapi Potong": `
        <h4>Form Sapi Potong</h4>
        <input type="text" name="potong_nama" placeholder="Nama Sapi"><br>
        <input type="text" name="potong_berat" placeholder="Berat Badan"><br>
        <input type="text" name="potong_persen" placeholder="Persentase Daging"><br>
    `,
            "Sapi Termak": `
        <h4>Form Sapi Termak</h4>
        <input type="text" name="termak_nama" placeholder="Nama Sapi"><br>
        <input type="text" name="termak_subur" placeholder="Kesuburan"><br>
        <input type="text" name="termak_riwayat" placeholder="Riwayat Kesehatan"><br>
    `
        };

        $('#macamSapi').change(function() {
            const selected = $('#macamSapi option:selected').attr('data-nama');
            $('#macamNama').val(selected);
            $('#formJenis').html(forms[selected] || '');
        });
    </script>

</body>

</html>