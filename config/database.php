<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '5433');
define('DB_NAME', 'Registro_Farmacias'); // Default PostgreSQL database
define('DB_USER', 'postgres');
define('DB_PASS', '1234');

// Admin credentials (hardcoded as requested)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

/**
 * Create database connection
 * @return PDO|false Returns PDO connection or false on failure
 */
function getDBConnection() {
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    session_start();
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}




$pdo = getDBConnection();

if ($pdo === false) {
    die("❌ getDBConnection() devolvió false");
}




?>