<?php
require_once 'config/database.php';

$id = (int)($_GET['id'] ?? 0);
$pageTitle = 'Commande Confirmee';
require_once 'includes/header.php';
?>

<div class="success-page">
    <i class="fas fa-check-circle"></i>
    <h1>Commande Confirmee !</h1>
    <p>Merci pour votre achat. Votre commande <strong>#<?= $id ?></strong> a bien ete enregistree.</p>
    <p>Vous recevrez un email de confirmation avec les details de votre livraison.</p>
    <a href="index.php" class="btn btn-primary btn-lg"><i class="fas fa-shopping-bag"></i> Retour a la boutique</a>
</div>

<?php require_once 'includes/footer.php'; ?>
