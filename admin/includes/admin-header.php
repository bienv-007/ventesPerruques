<?php
$pageTitleAdmin = $pageTitleAdmin ?? 'Admin';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitleAdmin ?> - Admin Perruques</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <div class="admin-overlay" id="adminOverlay"></div>

    <aside class="admin-sidebar" id="adminSidebar">
        <a href="index.php" class="admin-sidebar-brand">
            <div class="brand-icon"><i class="fas fa-crown"></i></div>
            <div class="brand-text">
                <strong>Perruques</strong>
                <small>Panneau d'administration</small>
            </div>
        </a>

        <nav class="admin-sidebar-nav">
            <div class="nav-section">Principal</div>
            <a href="index.php" class="<?= $currentPage == 'index' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>

            <div class="nav-section">Gestion</div>
            <a href="produits.php" class="<?= $currentPage == 'produits' || $currentPage == 'produit-edit' ? 'active' : '' ?>">
                <i class="fas fa-box"></i> Produits
            </a>
            <a href="categories.php" class="<?= $currentPage == 'categories' || $currentPage == 'category-edit' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Catégories
            </a>
            <a href="commandes.php" class="<?= $currentPage == 'commandes' || $currentPage == 'commande-edit' ? 'active' : '' ?>">
                <i class="fas fa-shopping-bag"></i> Commandes
            </a>
            <a href="clients.php" class="<?= $currentPage == 'clients' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Clients
            </a>

            <div class="nav-section">Lien rapide</div>
            <a href="../index.php">
                <i class="fas fa-store"></i> Voir la boutique
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </aside>

    <div class="admin-main">
        <div class="admin-topbar">
            <div class="admin-topbar-left">
                <h1><?= $pageTitleAdmin ?></h1>
            </div>
            <div class="admin-topbar-right">
                <span><i class="fas fa-user-shield"></i> <?= sanitize($_SESSION['user_prenom'] ?? 'Admin') ?></span>
            </div>
        </div>
        <div class="admin-content">
