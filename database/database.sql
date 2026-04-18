-- ============================================================
--  Nexora – Base de données (Version Finale Hébergement)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Région ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Region (
    id_region     INT AUTO_INCREMENT,
    nom_region     VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_region)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Département ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Departement (
    id_departement  INT AUTO_INCREMENT,
    nom_departement VARCHAR(100) NOT NULL,
    id_region       INT NOT NULL,
    PRIMARY KEY (id_departement),
    FOREIGN KEY (id_region) REFERENCES Region(id_region) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Ville ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Ville (
    id_ville        INT AUTO_INCREMENT,
    nom_ville       VARCHAR(100) NOT NULL,
    id_departement  INT NOT NULL,
    PRIMARY KEY (id_ville),
    FOREIGN KEY (id_departement) REFERENCES Departement(id_departement) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Quartier ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Quartier (
    id_quartier  INT AUTO_INCREMENT,
    nom_quartier VARCHAR(150) NOT NULL,
    id_ville     INT NOT NULL,
    PRIMARY KEY (id_quartier),
    FOREIGN KEY (id_ville) REFERENCES Ville(id_ville) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Utilisateur ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Utilisateur (
    id_utilisateur       INT AUTO_INCREMENT,
    email_utilisateur    VARCHAR(150) NOT NULL,
    mot_de_passe         VARCHAR(255) NOT NULL,
    est_prestataire      TINYINT(1) NOT NULL DEFAULT 0,
    est_client           TINYINT(1) NOT NULL DEFAULT 0,
    est_admin            TINYINT(1) NOT NULL DEFAULT 0,
    est_valide           TINYINT(1) NOT NULL DEFAULT 0,
    datecrea_utilisateur DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    nom_utilisateur      VARCHAR(50) NOT NULL,
    prenom_utilisateur   VARCHAR(100) NOT NULL,
    num_utilisateur      VARCHAR(15) NOT NULL,
    id_quartier          INT NOT NULL,
    PRIMARY KEY (id_utilisateur),
    UNIQUE KEY (email_utilisateur),
    FOREIGN KEY (id_quartier) REFERENCES Quartier(id_quartier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Portefeuille (Wallet) ─────────────────────────────────
-- Ajouté pour la gestion des transactions Nexora
CREATE TABLE IF NOT EXISTS Portefeuille (
    id_portefeuille INT AUTO_INCREMENT,
    id_utilisateur  INT NOT NULL,
    solde           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    derniere_maj    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_portefeuille),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Catégorie (Avec icône) ────────────────────────────────
CREATE TABLE IF NOT EXISTS Categorie (
    id_categorie   INT AUTO_INCREMENT,
    nom_categorie  VARCHAR(100) NOT NULL,
    icone_categorie VARCHAR(50) DEFAULT 'bi-tag', -- La modif qu'on a faite pour le CSS
    PRIMARY KEY (id_categorie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Service ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Service (
    id_service   INT AUTO_INCREMENT,
    nom_service  VARCHAR(100) NOT NULL,
    id_categorie INT NOT NULL,
    PRIMARY KEY (id_service),
    FOREIGN KEY (id_categorie) REFERENCES Categorie(id_categorie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Prestation ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Prestation (
    id_prestation         INT AUTO_INCREMENT,
    titre_prestation      VARCHAR(150),
    description_prestation TEXT,
    prix_prestation       DECIMAL(10,2),
    datecrea_prestation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    est_active            TINYINT(1) NOT NULL DEFAULT 1,
    id_service            INT NOT NULL,
    id_utilisateur        INT NOT NULL,
    PRIMARY KEY (id_prestation),
    FOREIGN KEY (id_service)     REFERENCES Service(id_service),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Commande ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Commande (
    id_commande    INT AUTO_INCREMENT,
    date_commande  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    montant_total  DECIMAL(12,2) NOT NULL DEFAULT 0,
    statut         TINYINT(1) NOT NULL DEFAULT 0,
    id_quartier    INT NOT NULL,
    id_utilisateur INT NOT NULL,
    PRIMARY KEY (id_commande),
    FOREIGN KEY (id_quartier)    REFERENCES Quartier(id_quartier),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Cibler ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cibler (
    id_prestation  INT NOT NULL,
    id_commande    INT NOT NULL,
    prix_unitaire  DECIMAL(10,2) NOT NULL,
    evaluation     TINYINT(1) UNSIGNED NULL,
    commentaire    TEXT NULL,
    quantite       INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id_prestation, id_commande),
    FOREIGN KEY (id_prestation) REFERENCES Prestation(id_prestation),
    FOREIGN KEY (id_commande)   REFERENCES Commande(id_commande)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  Données de démarrage
-- ============================================================

-- Régions & Villes
INSERT INTO Region (nom_region) VALUES ('Abidjan'),('Yamoussoukro'),('Bouaké');
INSERT INTO Departement (nom_departement, id_region) VALUES ('Cocody',1),('Plateau',1);
INSERT INTO Ville (nom_ville, id_departement) VALUES ('Abidjan',1),('Abidjan',2);
INSERT INTO Quartier (nom_quartier, id_ville) VALUES ('Riviera',1),('Plateau Centre',2);

-- Catégories (Avec les icônes Bootstrap pour ton nouveau CSS)
INSERT INTO Categorie (nom_categorie, icone_categorie) VALUES
    ('Coiffure', 'bi-scissors'),
    ('Plomberie', 'bi-droplet'),
    ('Laverie', 'bi-water'),
    ('Garde d\'enfants', 'bi-person-heart'),
    ('Électricité', 'bi-lightning-charge'),
    ('Ménage', 'bi-stars'),
    ('Cours particuliers', 'bi-book'),
    ('Jardinage', 'bi-flower1');

-- Services
INSERT INTO Service (nom_service, id_categorie) VALUES
    ('Coupe et coiffage',1), ('Réparation fuite',2), ('Lavage linge',3),
    ('Garde domicile',4), ('Dépannage électrique',5), ('Ménage complet',6),
    ('Cours de maths',7), ('Entretien jardin',8);

-- Compte administrateur (admin1234)
INSERT INTO Utilisateur
    (email_utilisateur, mot_de_passe, est_admin, est_valide, nom_utilisateur, prenom_utilisateur, num_utilisateur, id_quartier)
VALUES (
    'Admin@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHwf9Bz5i',
    1, 1, 'Admin', 'Nexora', '0000000000', 1
);