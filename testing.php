<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
            background-color: #f4f7f6;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #ecf0f1;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
            flex-grow: 1;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            transition: background-color 0.3s ease;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #34495e;
            border-left: 5px solid #1abc9c;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            background-color: #ecf0f1;
            display: flex;
            flex-direction: column;
        }

        .header {
            background-color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid #1abc9c;
        }

        .user-info span {
            font-weight: bold;
            color: #555;
        }

        .content-area {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            flex-grow: 1;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="#" class="active" data-content="beranda">Beranda</a></li>
            <li><a href="#" data-content="data-sapi">Data Sapi</a></li>
            <li><a href="#" data-content="lelang">Lelang</a></li>
            <li><a href="#" data-content="data-user">Data User</a></li>
            <li><a href="#" data-content="pesan">Pesan</a></li>
            <li><a href="#" data-content="profile">Profile</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">
            <h1 id="page-title">Beranda</h1>
            <div class="user-info">
                <img src="https://via.placeholder.com/40" alt="User Avatar">
                <span>Admin Name</span>
            </div>
        </div>
        <div class="content-area" id="content-display">
            <p>Selamat datang di halaman Beranda. Ini adalah area utama untuk melihat ringkasan aktivitas.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
            const contentDisplay = document.getElementById('content-display');
            const pageTitle = document.getElementById('page-title');

            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all links
                    sidebarLinks.forEach(item => item.classList.remove('active'));

                    // Add active class to the clicked link
                    this.classList.add('active');

                    // Update page title
                    pageTitle.textContent = this.textContent;

                    // Load content based on data-content attribute
                    const contentType = this.getAttribute('data-content');
                    loadContent(contentType);
                });
            });

            function loadContent(type) {
                let contentHTML = '';
                switch (type) {
                    case 'beranda':
                        contentHTML = '<p>Selamat datang di halaman <strong>Beranda</strong>. Ini adalah area utama untuk melihat ringkasan aktivitas.</p>';
                        contentHTML += '<p>Anda bisa menambahkan statistik, grafik, atau informasi penting lainnya di sini.</p>';
                        break;
                    case 'data-sapi':
                        contentHTML = '<h2>Data Sapi</h2><p>Di sini Anda dapat mengelola data sapi, seperti menambah, mengubah, atau menghapus informasi sapi.</p>';
                        contentHTML += '<ul><li>Sapi A (ID: 001)</li><li>Sapi B (ID: 002)</li><li>Sapi C (ID: 003)</li></ul>';
                        break;
                    case 'lelang':
                        contentHTML = '<h2>Lelang</h2><p>Halaman ini akan menampilkan daftar lelang yang sedang berlangsung atau yang akan datang.</p>';
                        contentHTML += '<p>Anda bisa memantau penawaran, status lelang, dan mengelola pemenang.</p>';
                        break;
                    case 'data-user':
                        contentHTML = '<h2>Data User</h2><p>Kelola informasi pengguna, hak akses, dan detail akun lainnya di sini.</p>';
                        contentHTML += '<table><tr><th>Nama</th><th>Email</th><th>Role</th></tr><tr><td>Budi</td><td>budi@example.com</td><td>Pembeli</td></tr><tr><td>Ani</td><td>ani@example.com</td><td>Penjual</td></tr></table>';
                        break;
                    case 'pesan':
                        contentHTML = '<h2>Pesan</h2><p>Ini adalah kotak masuk pesan Anda. Balas atau arsipkan pesan dari pengguna.</p>';
                        contentHTML += '<ul><li>Pesan baru dari John Doe tentang lelang sapi.</li><li>Pesan lama dari Jane Smith tentang akunnya.</li></ul>';
                        break;
                    case 'profile':
                        contentHTML = '<h2>Profile Admin</h2><p>Lihat dan ubah informasi profil Anda, seperti nama, email, atau kata sandi.</p>';
                        contentHTML += '<p><strong>Nama:</strong> Admin Name</p><p><strong>Email:</strong> admin@example.com</p>';
                        break;
                    default:
                        contentHTML = '<p>Konten tidak ditemukan.</p>';
                }
                contentDisplay.innerHTML = contentHTML;
            }
        });
    </script>
</body>

</html>