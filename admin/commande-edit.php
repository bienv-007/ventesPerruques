<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) redirect('../index.php');

$pdo = getConnection();
$id = (int)($_GET['id'] ?? 0);

$cmd = $pdo->prepare("
    SELECT c.*, u.nom, u.prenom, u.email, u.telephone as user_tel
    FROM commandes c JOIN utilisateurs u ON c.utilisateur_id = u.id
    WHERE c.id = ?
");
$cmd->execute([$id]);
$cmd = $cmd->fetch();

if (!$cmd) redirect('commandes.php');

$details = $pdo->prepare("
    SELECT dc.*, p.nom, p.image, p.couleur
    FROM details_commande dc JOIN produits p ON dc.produit_id = p.id
    WHERE dc.commande_id = ?
");
$details->execute([$id]);
$details = $details->fetchAll();

$pageTitleAdmin = "Commande #$id";
require_once 'includes/admin-header.php';
?>

<div class="admin-topbar-breadcrumb" style="margin-bottom:24px;">
    <a href="commandes.php">Commandes</a> <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i> <span>Commande #<?= $id ?></span>
</div>

<div class="order-detail">
    <div class="order-detail-header">
        <div>
            <h3><i class="fas fa-user"></i> Informations client</h3>
            <p><strong>Nom :</strong> <?= sanitize($cmd['prenom'] . ' ' . $cmd['nom']) ?></p>
            <p><strong>Email :</strong> <?= sanitize($cmd['email']) ?></p>
            <p><strong>Tél :</strong> <?= sanitize($cmd['telephone_livraison'] ?? $cmd['user_tel'] ?? '—') ?></p>
        </div>
        <div>
            <h3><i class="fas fa-receipt"></i> Détails commande</h3>
            <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($cmd['date_commande'])) ?></p>
            <p><strong>Statut :</strong>
                <span class="order-status status-<?= $cmd['statut'] ?>">
                    <?= ucwords(str_replace('_', ' ', $cmd['statut'])) ?>
                </span>
            </p>
            <p><strong>Paiement :</strong> <?= ucwords(str_replace('_', ' ', $cmd['mode_paiement'])) ?></p>
        </div>
        <div>
            <h3><i class="fas fa-map-marker-alt"></i> Livraison</h3>
            <p><?= sanitize($cmd['adresse_livraison']) ?></p>
            <p><strong><?= sanitize($cmd['ville_livraison']) ?></strong></p>
        </div>
    </div>

    <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--dark);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-box" style="color:var(--primary);"></i> Articles commandés
    </h3>

    <table class="data-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Couleur</th>
                <th>Prix unitaire</th>
                <th>Quantité</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $d): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <?php if ($d['image']): ?>
                            <img src="../uploads/<?= sanitize($d['image']) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid var(--border-light);">
                        <?php endif; ?>
                        <strong><?= sanitize($d['nom']) ?></strong>
                    </div>
                </td>
                <td><?= sanitize($d['couleur']) ?></td>
                <td><?= number_format($d['prix_unitaire'], 2, ',', '.') ?> €</td>
                <td><span class="badge-count"><?= $d['quantite'] ?></span></td>
                <td><strong><?= number_format($d['prix_unitaire'] * $d['quantite'], 2, ',', '.') ?> €</strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="order-total-line">
        <strong>Total : <?= number_format($cmd['total'], 2, ',', '.') ?> €</strong>
    </div>

    <div class="form-actions">
        <a href="commandes.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Retour aux commandes</a>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
