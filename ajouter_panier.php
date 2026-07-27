<?php
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('index.php');
}

$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ? AND stock > 0");
$stmt->execute([$id]);
$produit = $stmt->fetch();

if (!$produit) {
    redirect('index.php');
}

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = ['items' => [], 'total_items' => 0, 'total_prix' => 0];
}

$panier = &$_SESSION['panier'];

if (isset($panier['items'][$id])) {
    if ($panier['items'][$id]['quantite'] < $produit['stock']) {
        $panier['items'][$id]['quantite']++;
    }
} else {
    $panier['items'][$id] = [
        'nom' => $produit['nom'],
        'prix' => $produit['prix'],
        'quantite' => 1
    ];
}

$panier['total_items'] = 0;
$panier['total_prix'] = 0;
foreach ($panier['items'] as $item) {
    $panier['total_items'] += $item['quantite'];
    $panier['total_prix'] += $item['quantite'] * $item['prix'];
}

redirect('panier.php');
