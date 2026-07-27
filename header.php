<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titre_page ?? 'Perruques Elegance - Boutique en ligne' ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="index.php" class="logo">Perruques <span>Elegance</span></a>
            <nav class="nav">
                <a href="index.php">Accueil</a>
                <a href="index.php?categorie=all">Boutique</a>
                <a href="panier.php">
                    Panier (<?= compterPanier() ?>)
                </a>
            </nav>
        </div>
    </header>
    <main class="container">
