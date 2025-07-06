<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }

        .nav-link.active {
            color: red !important;
        }

        .btn-filter {
            margin: 5px;
        }

        .foto-sapi {
            width: 100%;
            height: 250px;
            background-color: #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .detail-sapi {
            font-size: 14px;
        }

        .chat-btn {
            position: absolute;
            right: 20px;
            top: 20px;
        }

        .generation-box {
            background-color: #555;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .contact-box {
            background-color: #ccc;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
        <a class="navbar-brand" href="#"><button class="btn btn-secondary">logo</button></a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Peta Interaktif</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">Data Sapi</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Lelang</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Filter Jenis Sapi -->
    <div class="text-center mt-3">
        <button class="btn btn-secondary btn-filter">sapi sonok</button>
        <button class="btn btn-secondary btn-filter">sapi kerap</button>
        <button class="btn btn-secondary btn-filter">sapi tangghe'</button>
        <button class="btn btn-secondary btn-filter">sapi ternak</button>
        <button class="btn btn-secondary btn-filter">sapi potong</button>
    </div>

    <!-- Konten Utama -->
    <div class="container mt-4 position-relative">
        <div class="foto-sapi">foto sapi</div>

        <div class="row">
            <div class="col-md-10 detail-sapi">
                <p><strong style="color:red;">Harga sapi</strong></p>
                <p>nama_sapi</p>
                <p>umur</p>
                <p>lingkar_dada</p>
                <p>panjang_badan</p>
                <p>tinggi_badan</p>
                <p>tinggi_punuk</p>
                <p>lebar_dahi</p>
                <p>panjang_wajah</p>
                <p>lebar_pinggul</p>
                <p>lebar_dada</p>
                <p>tinggi_kaki</p>
                <p>warna_bulu</p>
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary chat-btn">chat penjual</button>
            </div>
        </div>

        <!-- Generasi -->
        <div class="generation-box">Generasi 1</div>
        <div class="generation-box">Generasi 2</div>

        <!-- Kontak -->
        <div class="contact-box mt-3">contact person</div>
    </div>

</body>

</html>