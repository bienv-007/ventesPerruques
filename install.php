<?php
/*
 * ============================================
 *  FICHIER D'INSTALLATION
 *  1. Importez perruques_db.sql dans phpMyAdmin
 *  2. Accedez a http://localhost/perruques/install.php
 *  3. Supprimez ce fichier apres installation !
 * ============================================
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'perruques_db';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Creer la base si elle n'existe pas
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");

        // Creer les tables
        $sql = file_get_contents(__DIR__ . '/perruques_db.sql');
        // Retirer CREATE DATABASE et USE pour eviter les erreurs
        $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
        $sql = preg_replace('/USE\s+`?perruques_db`?\s*;/i', '', $sql);

        $pdo->exec($sql);

        // Creer l'admin avec le mot de passe 12345
        $hash = password_hash('12345', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute(['Admin', 'Super', 'admin@perr.com', $hash]);

        $message = 'Installation reussie ! Vous pouvez vous connecter.';
    } catch (PDOException $e) {
        $error = 'Erreur : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Perruques Elegance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .install-card { background: #fff; border-radius: 16px; padding: 40px; max-width: 500px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); text-align: center; }
        .install-card h1 { color: #333; margin-bottom: 10px; }
        .install-card h1 i { color: #667eea; }
        .install-card p { color: #666; margin-bottom: 20px; }
        .install-card .info { background: #f0f4ff; border-radius: 8px; padding: 15px; margin: 20px 0; text-align: left; font-size: 14px; color: #555; }
        .install-card .info strong { color: #333; }
        .btn { display: inline-block; padding: 14px 40px; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; text-decoration: none; transition: transform 0.2s; }
        .btn:hover { transform: translateY(-2px); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .credentials { background: #fff3cd; border-radius: 8px; padding: 15px; margin-top: 20px; text-align: left; }
        .credentials h3 { margin-bottom: 10px; color: #856404; }
        .credentials p { margin: 5px 0; font-family: monospace; font-size: 14px; color: #333; }
    </style>
</head>
<body>
    <div class="install-card">
        <h1><i class="fas fa-crown"></i> Installation</h1>
        <p>Boutique de Perruques pour Dames</p>

        <?php if ($message): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?= $message ?></div>
            <a href="login.php" class="btn"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
            <p style="margin-top:15px;color:#e74c3c;font-size:13px;"><i class="fas fa-exclamation-triangle"></i> IMPORTANT : Supprimez le fichier install.php apres utilisation !</p>
        <?php elseif ($error): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <a href="install.php" class="btn"><i class="fas fa-redo"></i> Reessayer</a>
        <?php else: ?>
            <div class="info">
                <p><strong>Ce script va :</strong></p>
                <ul style="margin-left:20px;margin-top:8px;">
                    <li>Creer la base de donnees <code>perruques_db</code></li>
                    <li>Creer toutes les tables</li>
                    <li>Inserer les produits de test</li>
                    <li>Creer le compte administrateur</li>
                </ul>
            </div>
            <p><strong>Pret ?</strong></p>
            <form method="POST">
                <button type="submit" class="btn"><i class="fas fa-download"></i> Installer maintenant</button>
            </form>
            <div class="credentials">
                <h3><i class="fas fa-key"></i> Identifiants admin :</h3>
                <p><strong>Email :</strong> admin@perr.com</p>
                <p><strong>Mot de passe :</strong> 12345</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
