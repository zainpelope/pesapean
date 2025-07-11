<?php
session_start(); // Mulai sesi

// Hancurkan semua variabel sesi
session_unset();

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login setelah logout
header("Location: ../auth/login.php?message=Anda telah berhasil logout.");
exit();
