<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'perruques_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

function estConnecte(): bool {
    return isset($_SESSION['utilisateur']);
}

function estAdmin(): bool {
    return estConnecte() && $_SESSION['utilisateur']['role'] === 'admin';
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function montant(float $montant): string {
    return number_format($montant, 2, ',', ' ') . ' &euro;';
}

function compterPanier(): int {
    return $_SESSION['panier']['total_items'] ?? 0;
}
