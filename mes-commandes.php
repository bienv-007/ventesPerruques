<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Message de succes
$orderSuccess = $_SESSION['order_success'] ?? null;
unset($_SESSION['order_success']);

$commandes = $pdo->prepare("
    SELECT c.*, 
        (SELECT COUNT(*) FROM details_commande WHERE commande_id = c.id) as nb_articles
    FROM commandes c
    WHERE c.utilisateur_id = ?
    ORDER BY c.date_commande DESC
");
$commandes->execute([$userId]);
$commandes = $commandes->fetchAll();

$pageTitle = 'Mes commandes - Perruques Élégance';
require_once 'includes/header.php';
?>

<div class="page-title">
    <h1><i class="fas fa-box"></i> Mes commandes</h1>
</div>

<?php if ($orderSuccess): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Commande #<?= $orderSuccess ?> passée avec succès ! Nous la préparons dès maintenant.
    </div>
<?php endif; ?>

<?php if (empty($commandes)): ?>
    <div class="empty-state">
        <i class="fas fa-box-open fa-4x"></i>
        <h2>Aucune commande</h2>
        <p>Vous n'avez pas encore passé de commande.</p>
        <a href="index.php" class="btn btn-primary btn-lg"><i class="fas fa-shopping-bag"></i> Commencer vos achats</a>
    </div>
<?php else: ?>
    <div class="orders-list">
        <?php foreach ($commandes as $cmd): ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <strong>Commande #<?= $cmd['id'] ?></strong>
                    <small><?= date('d/m/Y à H:i', strtotime($cmd['date_commande'])) ?></small>
                </div>
                <span class="order-status status-<?= $cmd['statut'] ?>">
                    <?php
                    $statuts = [
                        'en_attente' => '⏳ En attente',
                        'validee' => '✅ Validée',
                        'expediee' => '🚚 Expédiée',
                        'livree' => '📦 Livrée',
                        'annulee' => '❌ Annulée'
                    ];
                    echo $statuts[$cmd['statut']] ?? $cmd['statut'];
                    ?>
                </span>
            </div>
            <div class="order-body">
                <div class="order-info">
                    <span><i class="fas fa-map-marker-alt"></i> <?= sanitize($cmd['adresse_livraison']) ?>, <?= sanitize($cmd['ville_livraison']) ?></span>
                    <span><i class="fas fa-credit-card"></i> <?= ucwords(str_replace('_', ' ', $cmd['mode_paiement'])) ?></span>
                </div>
            </div>
            <div class="order-footer">
                <span class="order-total"><?= number_format($cmd['total'], 2, ',', '.') ?> €</span>
                <a href="commande-detail.php?id=<?= $cmd['id'] ?>" class="btn btn-outline">Voir détails</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
