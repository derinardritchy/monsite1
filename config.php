<?php
// ============================================
// BiroTech v3 — Configuration Principale
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'birotech_db');
define('SITE_NAME', 'BiroTech');
define('SITE_URL', 'http://localhost/birotech');
define('PRIX_ACCES', 1500);

if(session_status() === PHP_SESSION_NONE) session_start();

// Connexion DB singleton
function getDB() {
    static $conn = null;
    if($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
        if($conn->connect_error) {
            die('<div style="font-family:sans-serif;padding:30px;background:#1a1a2e;color:#ff4757;text-align:center">
                <h2>❌ Erè koneksyon baz done</h2>
                <p>'.$conn->connect_error.'</p>
                <p>Verifye <code>includes/config.php</code> — DB_USER ak DB_PASS ou.</p>
            </div>');
        }
    }
    return $conn;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// Safe escape — FIX: use getDB() not $mysqli
function escape($data) {
    return getDB()->real_escape_string($data ?? '');
}

function clean($data) {
    return htmlspecialchars(strip_tags(trim($data ?? '')));
}

function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if(isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function getCurrentUser() {
    if(!isLoggedIn()) return null;
    $db = getDB();
    $id = (int)$_SESSION['user_id'];
    $r = $db->query("SELECT * FROM users WHERE id=$id LIMIT 1");
    return ($r && $r->num_rows > 0) ? $r->fetch_assoc() : null;
}

// Check if column exists in table (pou evite erè si vye BDD)
function columnExists($table, $column) {
    $db = getDB();
    $r = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $r && $r->num_rows > 0;
}
?>
