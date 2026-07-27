<?php
require_once 'config/database.php';

if (!isLoggedIn()) redirect('login.php');

$pdo = getConnection();
$id = (int)($_GET['id'] ?? 0);

$cmd = $pdo->prepare("SELECT * FROM commandes WHERE id = ? AND utilisateur_id = ?");
$cmd->execute([$id, $_SESSION['user_id']]);
$cmd = $cmd->fetch();

if (!$cmd) redirect('mes-commandes.php');

$details = $pdo->prepare("
    SELECT dc.*, p.nom, p.image, p.couleur
    FROM details_commande dc JOIN produits p ON dc.produit_id = p.id
    WHERE dc.commande_id = ?
");
$details->execute([$id]);
$details = $details->fetchAll();

$pageTitle = "Commande #$id - Perruques Élégance";
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <a href="mes-commandes.php">Mes commandes</a> &raquo; <span>Commande #<?= $id ?></span>
</div>

<div class="page-title">
    <h1><i class="fas fa-receipt"></i> Commande #<?= $id ?></h1>
</div>

<div class="order-detail">
    <div class="order-detail-header">
        <div>
            <p><strong>Date:</strong> <?= date('d/m/Y à H:i', strtotime($cmd['date_commande'])) ?></p>
            <p><strong>Statut:</strong> <?= ucwords(str_replace('_', ' ', $cmd['statut'])) ?></p>
        </div>
        <div>
            <p><strong>Livraison:</strong> <?= sanitize($cmd['adresse_livraison']) ?>, <?= sanitize($cmd['ville_livraison']) ?></p>
            <p><strong>Paiement:</strong> <?= ucwords(str_replace('_', ' ', $cmd['mode_paiement'])) ?></p>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix unitaire</th>
                <th>Quantité</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $d): ?>
            <tr>
                <td>
                    <div class="cart-product-info">
                        <div class="cart-product-img small">
                            <?php if ($d['image']): ?>
                                <img src="uploads/<?= sanitize($d['image']) ?>" alt="">
                            <?php else: ?>
                                <div class="no-image small"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong><?= sanitize($d['nom']) ?></strong>
                            <small><?= sanitize($d['couleur']) ?></small>
                        </div>
                    </div>
                </td>
                <td><?= number_format($d['prix_unitaire'], 2, ',', '.') ?> €</td>
                <td><?= $d['quantite'] ?></td>
                <td><?= number_format($d['prix_unitaire'] * $d['quantite'], 2, ',', '.') ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="order-total-line">
        <strong>Total: <?= number_format($cmd['total'], 2, ',', '.') ?> €</strong>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
