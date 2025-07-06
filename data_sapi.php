<?php
include 'koneksi.php';
$result = mysqli_query($koneksi, "SELECT ds.*, ms.name AS jenis_sapi 
    FROM data_sapi ds 
    LEFT JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi 
    ORDER BY ds.id_sapi DESC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">
    <h3>Data Sapi</h3>
    <a href="tambah_sapi.php" class="btn btn-primary mb-3">+ Tambah Data</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Foto</th>
                <th>Jenis</th>
                <th>Harga</th>
                <th>Nama Pemilik</th>
                <th>Nomor</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id_sapi'] ?></td>
                    <td>
                        <?php if (!empty($row['foto_sapi']) && file_exists("uploads/" . $row['foto_sapi'])): ?>
                            <img src="uploads/<?= $row['foto_sapi'] ?>" width="100">
                        <?php else: ?>
                            <span class="text-danger">Foto tidak tersedia</span>
                        <?php endif; ?>
                    </td>

                    <td><?= $row['jenis_sapi'] ?></td>
                    <td>Rp <?= number_format($row['harga_sapi'], 0, ',', '.') ?></td>
                    <td><?= $row['nama_pemilik'] ?></td>
                    <td><?= $row['nomor_pemilik'] ?></td>
                    <td>
                        <a href="edit_sapi.php?id=<?= $row['id_sapi'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="hapus_sapi.php?id=<?= $row['id_sapi'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-sm btn-danger">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>