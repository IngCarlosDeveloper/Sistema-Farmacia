<?php
// Database configuration
// Para Railway + Neon usamos variables de entorno
define('DB_HOST', getenv('DB_HOST'));      // Ej: ep-hidden-heart-ahmgzcqz-pooler.c-3.us-east-1.aws.neon.tech
define('DB_PORT', getenv('DB_PORT'));      // Ej: 5432
define('DB_NAME', getenv('DB_NAME'));      // Ej: neondb
define('DB_USER', getenv('DB_USER'));      // Ej: neondb_owner
define('DB_PASS', getenv('DB_PASS'));      // Tu password real de Neon

// Admin credentials (hardcoded)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

/**
 * Create database connection
 * @return PDO|false Returns PDO connection or false on failure
 */
function getDBConnection() {
    try {
        // Nota: sslmode=require obligatorio para Neon
        $dsn = "pgsql:host=" . DB_HOST .
               ";port=" . DB_PORT .
               ";dbname=" . DB_NAME .
               ";sslmode=require";

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

// Inicializamos la conexion
$pdo = getDBConnection();
if ($pdo === false) {
    die("❌ Error: no se pudo conectar a la base de datos");
}
