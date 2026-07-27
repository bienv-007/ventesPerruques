-- ============================================
-- Base de donnees: Boutique de Perruques Dames
-- ============================================

CREATE DATABASE IF NOT EXISTS perruques_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perruques_db;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    ville VARCHAR(100),
    role ENUM('client', 'admin') DEFAULT 'client',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255)
) ENGINE=InnoDB;

-- Table des produits (perruques)
CREATE TABLE IF NOT EXISTS produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    categorie_id INT,
    couleur VARCHAR(100),
    longueur VARCHAR(50),
    materiau VARCHAR(100),
    style VARCHAR(100),
    est_promo TINYINT(1) DEFAULT 0,
    prix_promo DECIMAL(10,2) DEFAULT NULL,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table du panier
CREATE TABLE IF NOT EXISTS panier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    produit_id INT NOT NULL,
    quantite INT DEFAULT 1,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des commandes
CREATE TABLE IF NOT EXISTS commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    statut ENUM('en_attente', 'validee', 'expediee', 'livree', 'annulee') DEFAULT 'en_attente',
    adresse_livraison TEXT NOT NULL,
    ville_livraison VARCHAR(100) NOT NULL,
    telephone_livraison VARCHAR(20),
    mode_paiement VARCHAR(50),
    date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des details de commande
CREATE TABLE IF NOT EXISTS details_commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    produit_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Donnees de test
-- ============================================

-- Admin cree automatiquement via install.php (mot de passe: 12345)

-- Categories
INSERT INTO categories (nom, description) VALUES
('Perruques Naturelles', 'Perruques en cheveux naturels humains de haute qualite'),
('Perruques Synthetiques', 'Perruques en fibres synthetiques a prix avantageux'),
('Perruques Courtes', 'Perruques au style court et moderne'),
('Perruques Longues', 'Perruques aux cheveux longs et elegants'),
('Perruques Bouclees', 'Perruques avec boucles definies'),
('Perruques Afros', 'Perruques style afro et crepus');

-- Produits
INSERT INTO produits (nom, description, prix, stock, categorie_id, couleur, longueur, materiau, style, est_promo, prix_promo) VALUES
('Perruque Lace Front Naturelle', 'Perruque en cheveux naturels avec lace front invisible pour un look ultra-realiste', 189.99, 25, 1, 'Noir 1B', '45 cm', 'Cheveux humains', 'Droit', 0, NULL),
('Perruque Bob Chic Synthetique', 'Perruque synthetique haute temperature resistant, style bob elegant', 59.99, 40, 2, 'Chocolat', '30 cm', 'Kanekalon', 'Bob', 1, 39.99),
('Perruque Afro Kinky Naturelle', 'Perruque en cheveux afro naturels, volume et texture authentique', 149.99, 15, 6, 'Noir', '25 cm', 'Cheveux humains', 'Afro', 0, NULL),
('Perruque Ondulee Ombre', 'Perruque synthetique avec degrades de couleur ondulee', 69.99, 30, 5, 'Noir/Marron', '50 cm', 'Synthetique', 'Ondule', 1, 49.99),
('Perruque Courte Pixie', 'Perruque courte style pixie, legere et facile a coiffer', 44.99, 50, 3, 'Gris', '15 cm', 'Synthetique', 'Court', 0, NULL),
('Perruque Longue Droite Luxe', 'Perruque longue cheveux lisses et soyeux, finition premium', 219.99, 10, 4, 'Blond', '65 cm', 'Cheveux humains', 'Droit', 0, NULL),
('Perruque Bouclee Festival', 'Perruque bouclee colorée, parfaite pour les occasions speciales', 79.99, 20, 5, 'Rouge', '40 cm', 'Synthetique', 'Boucle', 1, 59.99),
('Perruque Naturelle Body Wave', 'Perruque cheveux naturels ondules, style body wave seduisant', 179.99, 18, 1, 'Brun', '40 cm', 'Cheveux humains', 'Ondule', 0, NULL),
('Perruque Afro Courte', 'Perruque afro courte et stylée, look naturel', 89.99, 22, 6, 'Noir 2', '20 cm', 'Synthetique', 'Afro', 0, NULL),
('Perruque Lace Front Ombre Balayage', 'Perruque lace front avec technique balayage, effet sublime', 199.99, 12, 1, 'Blond/Brune', '45 cm', 'Cheveux humains', 'Ondule', 1, 169.99);
