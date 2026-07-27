<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Actions panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $produitId = (int)$_POST['produit_id'];
        $quantite = max(1, (int)($_POST['quantite'] ?? 1));

        // Verifier si deja dans le panier
        $check = $pdo->prepare("SELECT id, quantite FROM panier WHERE utilisateur_id = ? AND produit_id = ?");
        $check->execute([$userId, $produitId]);
        $existing = $check->fetch();

        if ($existing) {
            $newQty = $existing['quantite'] + $quantite;
            $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
            $stmt->execute([$newQty, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO panier (utilisateur_id, produit_id, quantite) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $produitId, $quantite]);
        }
        redirect('panier.php');
    }

    if ($action === 'update') {
        $panierId = (int)$_POST['panier_id'];
        $quantite = max(1, (int)$_POST['quantite']);
        $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ? AND utilisateur_id = ?");
        $stmt->execute([$quantite, $panierId, $userId]);
        redirect('panier.php');
    }

    if ($action === 'remove') {
        $panierId = (int)$_POST['panier_id'];
        $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ? AND utilisateur_id = ?");
        $stmt->execute([$panierId, $userId]);
        redirect('panier.php');
    }
}

// Recuperer le panier
$stmt = $pdo->prepare("
    SELECT pa.*, p.nom, p.prix, p.prix_promo, p.est_promo, p.image, p.stock, p.couleur, p.longueur
    FROM panier pa
    JOIN produits p ON pa.produit_id = p.id
    WHERE pa.utilisateur_id = ?
    ORDER BY pa.date_ajout DESC
");
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

foreach ($items as &$item) {
    $item['est_promo'] = $item['est_promo'] ?? 0;
    $item['prix_promo'] = $item['prix_promo'] ?? null;
}
unset($item);

$total = 0;
foreach ($items as &$item) {
    $prixUnitaire = ($item['est_promo'] && $item['prix_promo']) ? $item['prix_promo'] : $item['prix'];
    $item['prix_total'] = $prixUnitaire * $item['quantite'];
    $total += $item['prix_total'];
}
unset($item);

$pageTitle = 'Mon Panier - Perruques Élégance';
require_once 'includes/header.php';
?>

<div class="page-title">
    <h1><i class="fas fa-shopping-cart"></i> Mon Panier</h1>
</div>

<?php if (empty($items)): ?>
    <div class="empty-state">
        <i class="fas fa-shopping-cart fa-4x"></i>
        <h2>Votre panier est vide</h2>
        <p>Vous n'avez pas encore ajouté de perruque à votre panier.</p>
        <a href="index.php" class="btn btn-primary btn-lg"><i class="fas fa-shopping-bag"></i> Commencer vos achats</a>
    </div>
<?php else: ?>
    <div class="cart-container">
        <div class="cart-items">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="cart-product">
                            <a href="produit.php?id=<?= $item['produit_id'] ?>" class="cart-product-info">
                                <div class="cart-product-img">
                                    <?php if ($item['image']): ?>
                                        <img src="uploads/<?= sanitize($item['image']) ?>" alt="<?= sanitize($item['nom']) ?>">
                                    <?php else: ?>
                                        <div class="no-image small"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <strong><?= sanitize($item['nom']) ?></strong>
                                    <small><?= sanitize($item['couleur']) ?> / <?= sanitize($item['longueur']) ?></small>
                                </div>
                            </a>
                        </td>
                        <td class="cart-price">
                            <?php if ($item['est_promo'] && $item['prix_promo']): ?>
                                <span class="price-old"><?= number_format($item['prix'], 2, ',', '.') ?> €</span>
                                <span class="price-new"><?= number_format($item['prix_promo'], 2, ',', '.') ?> €</span>
                            <?php else: ?>
                                <?= number_format($item['prix'], 2, ',', '.') ?> €
                            <?php endif; ?>
                        </td>
                        <td class="cart-qty">
                            <form method="POST" class="qty-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="panier_id" value="<?= $item['id'] ?>">
                                <input type="number" name="quantite" value="<?= $item['quantite'] ?>" min="1" max="<?= $item['stock'] ?>" onchange="this.form.submit()">
                            </form>
                        </td>
                        <td class="cart-total"><?= number_format($item['prix_total'], 2, ',', '.') ?> €</td>
                        <td class="cart-remove">
                            <form method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="panier_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-remove" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cart-summary">
            <h3>Récapitulatif</h3>
            <div class="summary-line">
                <span>Sous-total</span>
                <span><?= number_format($total, 2, ',', '.') ?> €</span>
            </div>
            <div class="summary-line">
                <span>Livraison</span>
                <span><?= $total >= 100 ? 'Gratuite' : '9,99 €' ?></span>
            </div>
            <?php if ($total < 100): ?>
            <div class="free-shipping-notice">
                <i class="fas fa-truck"></i> Ajoutez <?= number_format(100 - $total, 2, ',', '.') ?> € pour la livraison gratuite !
            </div>
            <?php endif; ?>
            <div class="summary-line total">
                <span>Total</span>
                <span><?= number_format($total + ($total >= 100 ? 0 : 9.99), 2, ',', '.') ?> €</span>
            </div>
            <a href="commande.php" class="btn btn-primary btn-lg btn-block"><i class="fas fa-lock"></i> Passer la commande</a>
            <a href="index.php" class="btn btn-outline btn-block"><i class="fas fa-arrow-left"></i> Continuer mes achats</a>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
