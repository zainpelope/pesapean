<?php
$host = "localhost"; // Or your database host (e.g., "127.0.0.1")
$username = "root"; // Your database username
$password = "root"; // Your database password
$database = "sapi"; // Your database name

// Create connection
$koneksi = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}
// Optionally, you can add an echo to confirm connection during testing
// echo "Connected successfully";
