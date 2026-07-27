<?php require_once __DIR__ . '/../config/database.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Perruques Elegance' ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <a href="index.php"><i class="fas fa-crown"></i> Perruques Élégance</a>
                </div>
                <div class="search-bar">
                    <form action="index.php" method="GET">
                        <input type="text" name="search" placeholder="Rechercher une perruque..." value="<?= sanitize($_GET['search'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="header-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="panier.php" class="btn-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge" id="cart-count"><?= getCartCount() ?></span>
                        </a>
                        <div class="dropdown">
                            <button class="btn-icon dropdown-toggle">
                                <i class="fas fa-user"></i> <?= sanitize($_SESSION['user_prenom'] ?? '') ?>
                            </button>
                            <div class="dropdown-menu">
                                <a href="mon-compte.php"><i class="fas fa-user-circle"></i> Mon compte</a>
                                <a href="mes-commandes.php"><i class="fas fa-box"></i> Mes commandes</a>
                                <?php if (isAdmin()): ?>
                                    <a href="admin/index.php"><i class="fas fa-cog"></i> Administration</a>
                                <?php endif; ?>
                                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                        <a href="register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="index.php?cat=1" class="active-cat">Naturelles</a></li>
                    <li><a href="index.php?cat=2" class="active-cat">Synthétiques</a></li>
                    <li><a href="index.php?cat=3" class="active-cat">Courtes</a></li>
                    <li><a href="index.php?cat=4" class="active-cat">Longues</a></li>
                    <li><a href="index.php?cat=5" class="active-cat">Bouclées</a></li>
                    <li><a href="index.php?cat=6" class="active-cat">Afros</a></li>
                    <li><a href="index.php?promo=1" class="promo-link"><i class="fas fa-tag"></i> Promos</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="main-content container">
