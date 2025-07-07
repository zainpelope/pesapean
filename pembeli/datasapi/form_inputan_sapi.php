<?php include '../../koneksi.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Form Input Semua Jenis Sapi</title>
    <style>
        .form-section {
            display: none;
            margin-top: 20px;
        }
    </style>
    <script>
        function showForm() {
            var selected = document.getElementById("jenis_sapi").value;
            var sections = document.getElementsByClassName("form-section");

            // Sembunyikan semua
            for (var i = 0; i < sections.length; i++) {
                sections[i].style.display = "none";
            }

            // Tampilkan yang dipilih
            if (selected) {
                document.getElementById(selected).style.display = "block";
            }
        }
    </script>
</head>

<body>

    <h2>Form Tambah Data Sapi</h2>

    <label>Pilih Jenis Sapi:</label>
    <select id="jenis_sapi" onchange="showForm()" required>
        <option value="">-- Pilih Jenis --</option>
        <option value="sonok">Sapi Sonok</option>
        <option value="kerap">Sapi Kerap</option>
        <option value="tangeh">Sapi Tangeh</option>
        <option value="termak">Sapi Termak</option>
        <option value="potong">Sapi Potong</option>
    </select>

    <!-- ========== FORM SONOK ========== -->
    <div id="sonok" class="form-section">
        <h3>Form Sapi Sonok</h3>
        <form method="POST" action="sonok.php">
            <?php include '../../pembeli/datasapi/sapi_sonok/sapi_sonok.php'; ?>
        </form>
    </div>

    <!-- ========== FORM KERAP ========== -->
    <div id="kerap" class="form-section">
        <h3>Form Sapi Kerap</h3>
        <form method="POST" action="../../pembeli/datasapi/data_sapi.php?jenis=kerap">
            <?php include '../../pembeli/datasapi/sapi_kerap/sapi_kerap.php'; ?>
        </form>
    </div>

    <!-- ========== FORM TANGEH ========== -->
    <div id="tangeh" class="form-section">
        <h3>Form Sapi Tangeh</h3>
        <form method="POST" action="tang.php">
            <?php include '../../pembeli/datasapi/sapi_sonok/sapi_tangghek.php'; ?>
        </form>
    </div>

    <!-- ========== FORM TERMAK ========== -->
    <div id="termak" class="form-section">
        <h3>Form Sapi Termak</h3>
        <form method="POST" action="ternak.php">
            <?php include '../../pembeli/datasapi/sapi_sonok/sapi_ternak.php'; ?>
        </form>
    </div>

    <!-- ========== FORM POTONG ========== -->
    <div id="potong" class="form-section">
        <h3>Form Sapi Potong</h3>
        <form method="POST" action="potong.php">
            <?php include '../../pembeli/datasapi/sapi_sonok/sapi_potong.php'; ?>
        </form>
    </div>

</body>

</html>