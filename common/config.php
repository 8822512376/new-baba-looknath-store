<?php
// --- MASTER CONFIGURATION FILE ---
// --- Location: common/config.php ---

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Show all errors for easier debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- DATABASE CREDENTIALS (THE ONLY PLACE YOU NEED TO SET THIS) ---
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'new_baba_looknath_db');

// --- Create and Check Connection ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // Stop the script with a clear error message if connection fails
    die("FATAL ERROR: Database connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");

// --- Global function for user login check ---
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>
