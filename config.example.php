<?php
/*
 * Database Configuration Example
 * 
 * INSTRUCTIONS:
 * 1. Copy this file and rename it to 'config.php'
 * 2. Update the values below with your database credentials
 * 3. DO NOT commit the actual config.php to version control
 */

// Database Connection Settings
$host = 'localhost';           // Database host (usually 'localhost')
$user = 'root';                // Your MySQL username
$pass = '';                    // Your MySQL password
$db   = 'kantin_online';       // Database name

// Create Connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check Connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set Charset
mysqli_set_charset($conn, "utf8mb4");

/*
 * NOTES:
 * - Make sure MySQL server is running
 * - Database 'kantin_online' must exist (import database.sql first)
 * - For XAMPP: username='root', password='' (empty)
 * - For Laragon: username='root', password='' (empty)
 * - For production: use strong password!
 */
?>
