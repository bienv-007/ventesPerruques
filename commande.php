<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Recuperer panier
$stmt = $pdo->prepare("
    SELECT pa.*, p.nom, p.prix, p.prix_promo, p.est_promo, p.stock
    FROM panier pa JOIN produits p ON pa.produit_id = p.id
    WHERE pa.utilisateur_id = ?
");
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

if (empty($items)) {
    redirect('panier.php');
}

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

$fraisLivraison = $total >= 100 ? 0 : 9.99;
$grandTotal = $total + $fraisLivraison;

// Recuperer infos utilisateur
$user = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$user->execute([$userId]);
$user = $user->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = trim($_POST['adresse'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $modePaiement = $_POST['mode_paiement'] ?? '';

    if (empty($adresse)) $errors[] = 'L\'adresse de livraison est obligatoire.';
    if (empty($ville)) $errors[] = 'La ville est obligatoire.';
    if (empty($modePaiement)) $errors[] = 'Choisissez un mode de paiement.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Creer la commande
            $stmt = $pdo->prepare("INSERT INTO commandes (utilisateur_id, total, statut, adresse_livraison, ville_livraison, telephone_livraison, mode_paiement) VALUES (?, ?, 'en_attente', ?, ?, ?, ?)");
            $stmt->execute([$userId, $grandTotal, $adresse, $ville, $telephone, $modePaiement]);
            $commandeId = $pdo->lastInsertId();

            // Details de commande + decrementer stock
            foreach ($items as $item) {
                $prixUnitaire = ($item['est_promo'] && $item['prix_promo']) ? $item['prix_promo'] : $item['prix'];
                $stmt = $pdo->prepare("INSERT INTO details_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
                $stmt->execute([$commandeId, $item['produit_id'], $item['quantite'], $prixUnitaire]);

                $stmt = $pdo->prepare("UPDATE produits SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantite'], $item['produit_id']]);
            }

            // Vider le panier
            $stmt = $pdo->prepare("DELETE FROM panier WHERE utilisateur_id = ?");
            $stmt->execute([$userId]);

            $pdo->commit();

            $_SESSION['order_success'] = $commandeId;
            redirect('mes-commandes.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Erreur lors de la commande. Veuillez réessayer.';
        }
    }
}

$pageTitle = 'Finaliser la commande - Perruques Élégance';
require_once 'includes/header.php';
?>

<div class="page-title">
    <h1><i class="fas fa-credit-card"></i> Finaliser la commande</h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>
        <ul><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="checkout-layout">
    <form method="POST" class="checkout-form">
        <h2><i class="fas fa-shipping-fast"></i> Adresse de livraison</h2>
        <div class="form-group">
            <label>Nom complet</label>
            <input type="text" value="<?= sanitize($user['prenom'] . ' ' . $user['nom']) ?>" readonly>
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone *</label>
            <input type="tel" id="telephone" name="telephone" value="<?= sanitize($user['telephone'] ?? '') ?>" required placeholder="+33 6 00 00 00 00">
        </div>
        <div class="form-group">
            <label for="adresse">Adresse de livraison *</label>
            <input type="text" id="adresse" name="adresse" value="<?= sanitize($user['adresse'] ?? '') ?>" required placeholder="123 Rue de la Paix, Apt 4">
        </div>
        <div class="form-group">
            <label for="ville">Ville *</label>
            <input type="text" id="ville" name="ville" value="<?= sanitize($user['ville'] ?? '') ?>" required placeholder="Paris">
        </div>

        <h2><i class="fas fa-credit-card"></i> Mode de paiement</h2>
        <div class="payment-options">
            <label class="payment-option">
                <input type="radio" name="mode_paiement" value="paiement_a_la_livraison" required>
                <span class="payment-label"><i class="fas fa-money-bill-wave"></i> Paiement à la livraison</span>
            </label>
            <label class="payment-option">
                <input type="radio" name="mode_paiement" value="virement_bancaire">
                <span class="payment-label"><i class="fas fa-university"></i> Virement bancaire</span>
            </label>
            <label class="payment-option">
                <input type="radio" name="mode_paiement" value="mobile_money">
                <span class="payment-label"><i class="fas fa-mobile-alt"></i> Mobile Money</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-check"></i> Confirmer la commande (<?= number_format($grandTotal, 2, ',', '.') ?> €)</button>
    </form>

    <div class="checkout-summary">
        <h2>Récapitulatif</h2>
        <div class="checkout-items">
            <?php foreach ($items as $item): ?>
            <div class="checkout-item">
                <div class="checkout-item-info">
                    <strong><?= sanitize($item['nom']) ?></strong>
                    <small>Qté: <?= $item['quantite'] ?> x <?= number_format(($item['est_promo'] && $item['prix_promo']) ? $item['prix_promo'] : $item['prix'], 2, ',', '.') ?> €</small>
                </div>
                <span><?= number_format($item['prix_total'], 2, ',', '.') ?> €</span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="summary-lines">
            <div class="summary-line">
                <span>Sous-total</span>
                <span><?= number_format($total, 2, ',', '.') ?> €</span>
            </div>
            <div class="summary-line">
                <span>Livraison</span>
                <span><?= $fraisLivraison == 0 ? 'Gratuite' : number_format($fraisLivraison, 2, ',', '.') . ' €' ?></span>
            </div>
            <div class="summary-line total">
                <span>Total</span>
                <span><?= number_format($grandTotal, 2, ',', '.') ?> €</span>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
