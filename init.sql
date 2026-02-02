-- Script d'initialisation de la base de données
-- Ce fichier sera exécuté automatiquement lors de la première création du conteneur MySQL

CREATE DATABASE IF NOT EXISTS intranet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE intranet;

-- Exemple de table (à adapter selon vos besoins)
-- CREATE TABLE users (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     username VARCHAR(50) NOT NULL UNIQUE,
--     email VARCHAR(100) NOT NULL UNIQUE,
--     password VARCHAR(255) NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );
