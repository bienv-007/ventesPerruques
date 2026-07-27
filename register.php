<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$old = ['nom' => '', 'prenom' => '', 'email' => '', 'telephone' => '', 'adresse' => '', 'ville' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $old = compact('nom', 'prenom', 'email', 'telephone', 'adresse', 'ville');

    if (empty($nom)) $errors[] = 'Le nom est obligatoire.';
    if (empty($prenom)) $errors[] = 'Le prénom est obligatoire.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if (strlen($password) < 6) $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    if ($password !== $password_confirm) $errors[] = 'Les mots de passe ne correspondent pas.';

    if (empty($errors)) {
        $pdo = getConnection();
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, adresse, ville) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $hash, $telephone, $adresse, $ville]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'client';

        redirect('index.php');
    }
}

$pageTitle = 'Inscription - Perruques Élégance';
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-user-plus fa-3x"></i>
            <h1>Créer un compte</h1>
            <p>Rejoignez la communauté Perruques Élégance</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="nom"><i class="fas fa-user"></i> Nom *</label>
                    <input type="text" id="nom" name="nom" value="<?= sanitize($old['nom']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="prenom"><i class="fas fa-user"></i> Prénom *</label>
                    <input type="text" id="prenom" name="prenom" value="<?= sanitize($old['prenom']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                <input type="email" id="email" name="email" value="<?= sanitize($old['email']) ?>" required placeholder="votre@email.com">
            </div>
            <div class="form-group">
                <label for="telephone"><i class="fas fa-phone"></i> Téléphone</label>
                <input type="tel" id="telephone" name="telephone" value="<?= sanitize($old['telephone']) ?>" placeholder="+33 6 00 00 00 00">
            </div>
            <div class="form-group">
                <label for="adresse"><i class="fas fa-map-marker-alt"></i> Adresse</label>
                <input type="text" id="adresse" name="adresse" value="<?= sanitize($old['adresse']) ?>" placeholder="123 Rue de la Paix">
            </div>
            <div class="form-group">
                <label for="ville"><i class="fas fa-city"></i> Ville</label>
                <input type="text" id="ville" name="ville" value="<?= sanitize($old['ville']) ?>" placeholder="Paris">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Mot de passe * (6+ caractères)</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="password_confirm"><i class="fas fa-lock"></i> Confirmer *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-user-plus"></i> S'inscrire</button>
        </form>

        <div class="auth-footer">
            <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
