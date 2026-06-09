-- ne pas toucher, sert pour infinity free

CREATE TABLE IF NOT EXISTS utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    mail VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    description TEXT NULL,
    image_profil VARCHAR(255) NULL,
    ville VARCHAR(100) NULL,
    region VARCHAR(100) NULL,
    poisson DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    role ENUM('UTILISATEUR','ADMIN') NOT NULL DEFAULT 'UTILISATEUR',
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    est_banni BOOLEAN NOT NULL DEFAULT FALSE,
    raison_ban TEXT NULL,
    date_fin_ban DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorie (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    est_un_service TINYINT(1) NOT NULL DEFAULT 0,
    id_categorie_parent INT NULL,
    KEY fk_categorie_parent (id_categorie_parent),
    CONSTRAINT fk_categorie_parent
        FOREIGN KEY (id_categorie_parent) REFERENCES categorie(id_categorie)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS objet (
    id_objet INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    image VARCHAR(255) NULL,
    description TEXT NULL,
    prix DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    est_dispo TINYINT(1) NOT NULL DEFAULT 1,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    id_categorie INT NOT NULL,
    KEY idx_objet_utilisateur (id_utilisateur),
    KEY idx_objet_categorie (id_categorie),
    CONSTRAINT fk_objet_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_objet_categorie
        FOREIGN KEY (id_categorie) REFERENCES categorie(id_categorie)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposition (
    id_proposition INT AUTO_INCREMENT PRIMARY KEY,
    prix_poisson DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    etat_proposition ENUM('ATTENTE','ACCEPTEE','REFUSEE') NOT NULL DEFAULT 'ATTENTE',
    date_proposition DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_demandeur INT NOT NULL,
    id_receveur INT NOT NULL,
    KEY idx_proposition_demandeur (id_demandeur),
    KEY idx_proposition_receveur (id_receveur),
    KEY idx_proposition_etat (etat_proposition),
    CONSTRAINT fk_proposition_demandeur
        FOREIGN KEY (id_demandeur) REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_proposition_receveur
        FOREIGN KEY (id_receveur) REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposition_objet_demande (
    id_proposition INT NOT NULL,
    id_objet INT NOT NULL,
    PRIMARY KEY (id_proposition, id_objet),
    KEY fk_pod_objet (id_objet),
    CONSTRAINT fk_pod_proposition
        FOREIGN KEY (id_proposition) REFERENCES proposition(id_proposition)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pod_objet
        FOREIGN KEY (id_objet) REFERENCES objet(id_objet)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposition_objet_offert (
    id_proposition INT NOT NULL,
    id_objet INT NOT NULL,
    PRIMARY KEY (id_proposition, id_objet),
    KEY fk_poo_objet (id_objet),
    CONSTRAINT fk_poo_proposition
        FOREIGN KEY (id_proposition) REFERENCES proposition(id_proposition)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_poo_objet
        FOREIGN KEY (id_objet) REFERENCES objet(id_objet)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS message_chat (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    date_heure DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_proposition INT NOT NULL,
    id_utilisateur INT NOT NULL,
    KEY idx_message_proposition (id_proposition),
    KEY idx_message_utilisateur (id_utilisateur),
    CONSTRAINT fk_message_proposition
        FOREIGN KEY (id_proposition) REFERENCES proposition(id_proposition)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_message_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS favori (
    id_utilisateur INT NOT NULL,
    id_objet INT NOT NULL,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_utilisateur, id_objet),
    KEY idx_favori_objet (id_objet),
    CONSTRAINT fk_favori_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_favori_objet
        FOREIGN KEY (id_objet) REFERENCES objet(id_objet)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorie (nom, est_un_service)
SELECT 'Electronique', 0 WHERE NOT EXISTS (SELECT 1 FROM categorie WHERE nom = 'Electronique');
INSERT INTO categorie (nom, est_un_service)
SELECT 'Maison', 0 WHERE NOT EXISTS (SELECT 1 FROM categorie WHERE nom = 'Maison');
INSERT INTO categorie (nom, est_un_service)
SELECT 'Mode', 0 WHERE NOT EXISTS (SELECT 1 FROM categorie WHERE nom = 'Mode');
INSERT INTO categorie (nom, est_un_service)
SELECT 'Sport', 0 WHERE NOT EXISTS (SELECT 1 FROM categorie WHERE nom = 'Sport');
INSERT INTO categorie (nom, est_un_service)
SELECT 'Livres', 0 WHERE NOT EXISTS (SELECT 1 FROM categorie WHERE nom = 'Livres');
INSERT INTO categorie (nom, est_un_service)
SELECT 'Jeux', 0 WHERE NOT EXISTS (SELECT 1 FROM categorie WHERE nom = 'Jeux');
INSERT INTO categorie (nom, est_un_service)
SELECT 'Services', 1 WHERE NOT EXISTS (SELECT 1 FROM categorie WHERE nom = 'Services');
