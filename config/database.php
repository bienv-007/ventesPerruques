<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'perruques_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion: " . $e->getMessage());
    }
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function getCartCount() {
    if (!isLoggedIn()) return 0;
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite), 0) as total FROM panier WHERE utilisateur_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch()['total'];
}
