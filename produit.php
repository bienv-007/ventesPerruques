<?php
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$pdo = getConnection();
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch();

if (!$produit) {
    redirect('index.php');
}

$produit['est_promo'] = $produit['est_promo'] ?? 0;
$produit['prix_promo'] = $produit['prix_promo'] ?? null;
$produit['materiau'] = $produit['materiau'] ?? '';
$produit['style'] = $produit['style'] ?? '';

$pageTitle = $produit['nom'] . ' - Perruques Élégance';
require_once 'includes/header.php';

// Produits similaires
$similaires = $pdo->prepare("SELECT * FROM produits WHERE categorie_id = ? AND id != ? ORDER BY RAND() LIMIT 4");
$similaires->execute([$produit['categorie_id'], $id]);
$similaires = $similaires->fetchAll();

foreach ($similaires as &$s) {
    $s['est_promo'] = $s['est_promo'] ?? 0;
    $s['prix_promo'] = $s['prix_promo'] ?? null;
}
unset($s);
?>

<div class="breadcrumb">
    <a href="index.php">Accueil</a> &raquo;
    <a href="index.php?cat=<?= $produit['categorie_id'] ?>"><?= sanitize($produit['categorie_nom'] ?? 'Produits') ?></a> &raquo;
    <span><?= sanitize($produit['nom']) ?></span>
</div>

<div class="product-detail">
    <div class="product-detail-image">
        <?php if ($produit['image']): ?>
            <img src="uploads/<?= sanitize($produit['image']) ?>" alt="<?= sanitize($produit['nom']) ?>">
        <?php else: ?>
            <div class="no-image large"><i class="fas fa-image fa-5x"></i></div>
        <?php endif; ?>
        <?php if ($produit['est_promo']): ?>
            <span class="promo-badge large">PROMO</span>
        <?php endif; ?>
    </div>

    <div class="product-detail-info">
        <?php if ($produit['categorie_nom']): ?>
            <span class="product-category"><?= sanitize($produit['categorie_nom']) ?></span>
        <?php endif; ?>
        <h1><?= sanitize($produit['nom']) ?></h1>

        <div class="product-detail-price">
            <?php if ($produit['est_promo'] && $produit['prix_promo']): ?>
                <span class="price-old"><?= number_format($produit['prix'], 2, ',', '.') ?> €</span>
                <span class="price-new"><?= number_format($produit['prix_promo'], 2, ',', '.') ?> €</span>
                <span class="discount">-<?= round((1 - $produit['prix_promo'] / $produit['prix']) * 100) ?>%</span>
            <?php else: ?>
                <span class="price"><?= number_format($produit['prix'], 2, ',', '.') ?> €</span>
            <?php endif; ?>
        </div>

        <div class="product-detail-desc">
            <p><?= nl2br(sanitize($produit['description'])) ?></p>
        </div>

        <div class="product-specs">
            <div class="spec"><i class="fas fa-palette"></i> <strong>Couleur:</strong> <?= sanitize($produit['couleur']) ?></div>
            <div class="spec"><i class="fas fa-ruler"></i> <strong>Longueur:</strong> <?= sanitize($produit['longueur']) ?></div>
            <div class="spec"><i class="fas fa-feather"></i> <strong>Matériau:</strong> <?= sanitize($produit['materiau']) ?></div>
            <div class="spec"><i class="fas fa-magic"></i> <strong>Style:</strong> <?= sanitize($produit['style']) ?></div>
        </div>

        <?php if ($produit['stock'] > 0): ?>
            <div class="stock in-stock"><i class="fas fa-check-circle"></i> En stock (<?= $produit['stock'] ?> disponible(s))</div>
        <?php else: ?>
            <div class="stock out-of-stock"><i class="fas fa-times-circle"></i> Rupture de stock</div>
        <?php endif; ?>

        <?php if ($produit['stock'] > 0): ?>
        <form method="POST" action="panier.php" class="add-to-cart-detail">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="produit_id" value="<?= $produit['id'] ?>">
            <div class="quantity-control">
                <label>Quantité:</label>
                <input type="number" name="quantite" value="1" min="1" max="<?= $produit['stock'] ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-cart-plus"></i> Ajouter au panier</button>
        </form>
        <?php endif; ?>

        <div class="trust-badges">
            <div class="badge"><i class="fas fa-truck"></i> Livraison rapide</div>
            <div class="badge"><i class="fas fa-undo"></i> Retour 30 jours</div>
            <div class="badge"><i class="fas fa-shield-alt"></i> Paiement sécurisé</div>
        </div>
    </div>
</div>

<?php if (!empty($similaires)): ?>
<section class="similar-products">
    <h2>Produits similaires</h2>
    <div class="products-grid">
        <?php foreach ($similaires as $s): ?>
        <div class="product-card">
            <a href="produit.php?id=<?= $s['id'] ?>">
                <div class="product-image">
                    <?php if ($s['image']): ?>
                        <img src="uploads/<?= sanitize($s['image']) ?>" alt="<?= sanitize($s['nom']) ?>">
                    <?php else: ?>
                        <div class="no-image"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                    <?php if ($s['est_promo']): ?><span class="promo-badge">PROMO</span><?php endif; ?>
                </div>
                <div class="product-info">
                    <h3 class="product-name"><?= sanitize($s['nom']) ?></h3>
                    <div class="product-price">
                        <?php if ($s['est_promo'] && $s['prix_promo']): ?>
                            <span class="price-old"><?= number_format($s['prix'], 2, ',', '.') ?> €</span>
                            <span class="price-new"><?= number_format($s['prix_promo'], 2, ',', '.') ?> €</span>
                        <?php else: ?>
                            <span class="price"><?= number_format($s['prix'], 2, ',', '.') ?> €</span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
