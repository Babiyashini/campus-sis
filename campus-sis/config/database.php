<?php
// Database Configuration
define("DB_HOST", "localhost");
define("DB_NAME", "sis");
define("DB_USER", "root");
define("DB_PASS", "");

// Application Configuration
define("APP_NAME", "Campus SIS");
define("APP_URL", "http://localhost/campus-sis/");
define("UPLOAD_PATH", $_SERVER["DOCUMENT_ROOT"] . "/campus-sis/assets/uploads/");

// Session Configuration
ini_set("session.cookie_httponly", 1);
ini_set("session.use_only_cookies", 1);
ini_set("session.cookie_secure", 0);

// Error Reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION["user_id"]);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . APP_URL . "auth/login.php");
        exit();
    }
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>
