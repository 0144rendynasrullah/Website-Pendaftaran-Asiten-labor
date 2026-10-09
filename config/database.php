<?php
// config/database.php

$host = 'localhost';
$dbname = 'db_pendaftaran_aslab';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// ===== SESSION START =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== HELPER FUNCTIONS =====
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getUserName() {
    return $_SESSION['full_name'] ?? 'User';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function redirect($url) {
    // Deteksi posisi file untuk path yang benar
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    
    if ($current_dir === 'admin' || $current_dir === 'kepala-lab') {
        header("Location: ../" . $url);
    } elseif ($current_dir === 'pages') {
        header("Location: " . $url);
    } else {
        header("Location: pages/" . $url);
    }
    exit();
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>