-- Script d'initialisation de la base de données
-- Ce fichier sera exécuté automatiquement lors de la première création du conteneur MySQL

USE intranet_db;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    admin BOOLEAN NOT NULL DEFAULT 0,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des citations
CREATE TABLE IF NOT EXISTS citation (
    id INT UNSIGNED AUTO_INCREMENT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion d'un utilisateur admin de test (mot de passe: admin123)
INSERT INTO utilisateur (username, password, email, admin) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@jadenet.local', 1);

-- Insertion de quelques citations de test
INSERT INTO citation (nom, description) VALUES 
('Gandhi', 'La vie est un mystère qu''il faut vivre, et non un problème à résoudre.'),
('Albert Einstein', 'La logique vous mènera d''un point A à un point B. L''imagination vous mènera partout.'),
('Victor Hugo', 'La vie est une fleur dont l''amour est le miel.'),
('Confucius', 'Choisissez un travail que vous aimez et vous n''aurez pas à travailler un seul jour de votre vie.'),
('Nelson Mandela', 'L''éducation est l''arme la plus puissante qu''on puisse utiliser pour changer le monde.');
