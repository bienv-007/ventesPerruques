<?php
$pageTitle = 'Perruques Élégance - Boutique de Perruques pour Dames';
require_once 'config/database.php';
require_once 'includes/header.php';

$pdo = getConnection();

// Filtres
$search = $_GET['search'] ?? '';
$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$promoOnly = isset($_GET['promo']) ? 1 : 0;

// Construction de la requête
$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(p.nom LIKE ? OR p.description LIKE ? OR p.couleur LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($catId) {
    $where[] = "p.categorie_id = ?";
    $params[] = $catId;
}
if ($promoOnly) {
    $where[] = "p.est_promo = 1";
}

$whereSQL = implode(' AND ', $where);
$sql = "SELECT p.*, c.nom as categorie_nom FROM produits p 
        LEFT JOIN categories c ON p.categorie_id = c.id 
        WHERE $whereSQL ORDER BY p.date_ajout DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();

foreach ($produits as &$prod) {
    $prod['est_promo'] = $prod['est_promo'] ?? 0;
    $prod['prix_promo'] = $prod['prix_promo'] ?? null;
    $prod['materiau'] = $prod['materiau'] ?? '';
    $prod['style'] = $prod['style'] ?? '';
}
unset($prod);

// Categories pour sidebar
$categories = $pdo->query("SELECT c.*, COUNT(p.id) as nb_produits FROM categories c LEFT JOIN produits p ON c.id = p.categorie_id GROUP BY c.id ORDER BY c.nom")->fetchAll();
?>

<section class="hero">
    <div class="hero-content">
        <h1>Trouvez la perruque qui vous ressemble</h1>
        <p>Découvrez notre collection de perruques de qualité pour femmes. Naturelles, synthétiques, courtes, longues...</p>
        <a href="#produits" class="btn btn-primary btn-lg">Voir la collection</a>
    </div>
</section>

<div class="shop-layout">
    <aside class="sidebar">
        <div class="filter-box">
            <h3><i class="fas fa-filter"></i> Catégories</h3>
            <ul class="category-list">
                <li class="<?= $catId == 0 ? 'active' : '' ?>">
                    <a href="index.php">Tous les produits</a>
                </li>
                <?php foreach ($categories as $cat): ?>
                <li class="<?= $catId == $cat['id'] ? 'active' : '' ?>">
                    <a href="index.php?cat=<?= $cat['id'] ?>"><?= sanitize($cat['nom']) ?> (<?= $cat['nb_produits'] ?>)</a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="filter-box">
            <h3><i class="fas fa-truck"></i> Livraison</h3>
            <p>Livraison gratuite à partir de 100€ d'achat.</p>
        </div>
        <div class="filter-box">
            <h3><i class="fas fa-shield-alt"></i> Garantie</h3>
            <p>Satisfait ou remboursé sous 30 jours.</p>
        </div>
    </aside>

    <div class="products-section" id="produits">
        <div class="section-header">
            <h2>
                <?php if ($search): ?>
                    Résultats pour "<?= sanitize($search) ?>"
                <?php elseif ($catId): ?>
                    <?= $categories[array_search($catId, array_column($categories, 'id'))]['nom'] ?? 'Produits' ?>
                <?php elseif ($promoOnly): ?>
                    <i class="fas fa-tag"></i> Promotions
                <?php else: ?>
                    Nos Perruques
                <?php endif; ?>
            </h2>
            <span class="count"><?= count($produits) ?> produit(s)</span>
        </div>

        <?php if (empty($produits)): ?>
            <div class="no-products">
                <i class="fas fa-search fa-3x"></i>
                <h3>Aucun produit trouvé</h3>
                <p>Essayez de modifier vos critères de recherche.</p>
                <a href="index.php" class="btn btn-primary">Voir tous les produits</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($produits as $prod): ?>
                <div class="product-card">
                    <a href="produit.php?id=<?= $prod['id'] ?>">
                        <div class="product-image">
                            <?php if ($prod['image']): ?>
                                <img src="uploads/<?= sanitize($prod['image']) ?>" alt="<?= sanitize($prod['nom']) ?>">
                            <?php else: ?>
                                <div class="no-image"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <?php if ($prod['est_promo']): ?>
                                <span class="promo-badge">PROMO</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <?php if ($prod['categorie_nom']): ?>
                                <span class="product-category"><?= sanitize($prod['categorie_nom']) ?></span>
                            <?php endif; ?>
                            <h3 class="product-name"><?= sanitize($prod['nom']) ?></h3>
                            <div class="product-meta">
                                <span><i class="fas fa-palette"></i> <?= sanitize($prod['couleur']) ?></span>
                                <span><i class="fas fa-ruler"></i> <?= sanitize($prod['longueur']) ?></span>
                            </div>
                            <div class="product-price">
                                <?php if ($prod['est_promo'] && $prod['prix_promo']): ?>
                                    <span class="price-old"><?= number_format($prod['prix'], 2, ',', '.') ?> €</span>
                                    <span class="price-new"><?= number_format($prod['prix_promo'], 2, ',', '.') ?> €</span>
                                <?php else: ?>
                                    <span class="price"><?= number_format($prod['prix'], 2, ',', '.') ?> €</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($prod['stock'] > 0): ?>
                                <span class="stock in-stock"><i class="fas fa-check-circle"></i> En stock</span>
                            <?php else: ?>
                                <span class="stock out-of-stock"><i class="fas fa-times-circle"></i> Rupture</span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php if ($prod['stock'] > 0): ?>
                    <form method="POST" action="panier.php" class="add-to-cart-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="produit_id" value="<?= $prod['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-cart-plus"></i> Ajouter au panier</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
