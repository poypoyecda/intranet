<?php
// Model Role - Gère l'accès aux données de la table role
class Role {
    private $conn;
    private $table_name = "role";

    public $id;
    public $nom;

    public function __construct($db) {
        $this->conn = $db;
    }

    // READ - Récupérer tous les rôles
    public function getAll() {
        $query = "SELECT id, nom FROM " . $this->table_name . " ORDER BY id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // READ - Récupérer un rôle par ID
    public function getById($id) {
        $query = "SELECT id, nom FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        
        if($row) {
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            return true;
        }
        
        return false;
    }

    // READ - Récupérer un rôle par nom
    public function getByNom($nom) {
        $query = "SELECT id, nom FROM " . $this->table_name . " WHERE nom = :nom LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->execute();
        
        $row = $stmt->fetch();
        
        if($row) {
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            return true;
        }
        
        return false;
    }

    // CREATE - Créer un nouveau rôle
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (nom) VALUES (:nom)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $this->nom);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // UPDATE - Mettre à jour un rôle
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET nom = :nom WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // DELETE - Supprimer un rôle
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // VÉRIFIER - Vérifier si un nom de rôle existe déjà
    public function nomExists($nom) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE nom = :nom LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // VÉRIFIER - Vérifier si un rôle est utilisé par des utilisateurs
    public function isUsedByUsers() {
        $query = "SELECT COUNT(*) as count FROM utilisateur WHERE role_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        return $row['count'] > 0;
    }
}
?>
