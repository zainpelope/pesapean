<?php
include 'koneksi.php';
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM data_sapi WHERE id_sapi = $id"));
$macam = mysqli_query($koneksi, "SELECT * FROM macamSapi");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $harga = $_POST['harga_sapi'];
    $nama = $_POST['nama_pemilik'];
    $alamat = $_POST['alamat_pemilik'];
    $nomor = $_POST['nomor_pemilik'];
    $email = $_POST['email_pemilik'];
    $jenis = $_POST['id_macamSapi'];

    if ($_FILES['foto']['name']) {
        $foto = $_FILES['foto']['name'];
        $tmp = $_FILES['foto']['tmp_name'];
        move_uploaded_file($tmp, "../uploads/$foto");
    } else {
        $foto = $data['foto_sapi'];
    }

    $now = date('Y-m-d H:i:s');
    mysqli_query($koneksi, "UPDATE data_sapi SET 
        foto_sapi = '$foto',
        harga_sapi = '$harga',
        nama_pemilik = '$nama',
        alamat_pemilik = '$alamat',
        nomor_pemilik = '$nomor',
        email_pemilik = '$email',
        updatedAt = '$now',
        id_macamSapi = '$jenis'
        WHERE id_sapi = $id");

    header("Location: data_sapi.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">
    <h3>Edit Data Sapi</h3>
    <form method="post" enctype="multipart/form-data">
        <img src="../uploads/<?= $data['foto_sapi'] ?>" width="150" class="mb-2"><br>
        <div class="mb-2">
            <label>Foto Baru (jika ingin diganti)</label>
            <input type="file" name="foto" class="form-control">
        </div>
        <div class="mb-2">
            <label>Harga Sapi</label>
            <input type="number" name="harga_sapi" value="<?= $data['harga_sapi'] ?>" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Nama Pemilik</label>
            <input type="text" name="nama_pemilik" value="<?= $data['nama_pemilik'] ?>" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Alamat Pemilik</label>
            <input type="text" name="alamat_pemilik" value="<?= $data['alamat_pemilik'] ?>" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Nomor Pemilik</label>
            <input type="text" name="nomor_pemilik" value="<?= $data['nomor_pemilik'] ?>" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Email Pemilik</label>
            <input type="email" name="email_pemilik" value="<?= $data['email_pemilik'] ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Jenis Sapi</label>
            <select name="id_macamSapi" class="form-control" required>
                <?php while ($m = mysqli_fetch_assoc($macam)): ?>
                    <option value="<?= $m['id_macamSapi'] ?>" <?= $data['id_macamSapi'] == $m['id_macamSapi'] ? 'selected' : '' ?>>
                        <?= $m['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button class="btn btn-primary">Update</button>
        <a href="data_sapi.php" class="btn btn-secondary">Kembali</a>
    </form>
</body>

</html>