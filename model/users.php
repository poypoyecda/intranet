<?php
// Model Utilisateur - Gère l'accès aux données de la table utilisateur
class User {
    private $conn;
    private $table_name = "utilisateur";

    public $id;
    public $username;
    public $password;
    public $email;
    public $admin;
    public $date_creation;
    public $date_modification;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE - Créer un nouvel utilisateur
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password, email, admin) 
                  VALUES (:username, :password, :email, :admin)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash du mot de passe
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Bind des paramètres
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':admin', $this->admin);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // READ - Récupérer tous les utilisateurs
    public function getAll() {
        $query = "SELECT id, username, email, admin, date_creation, date_modification 
                  FROM " . $this->table_name . " 
                  ORDER BY date_creation DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // READ - Récupérer un utilisateur par ID
    public function getById($id) {
        $query = "SELECT id, username, email, admin, date_creation, date_modification 
                  FROM " . $this->table_name . " 
                  WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        
        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->admin = $row['admin'];
            $this->date_creation = $row['date_creation'];
            $this->date_modification = $row['date_modification'];
            return true;
        }
        
        return false;
    }

    // READ - Récupérer un utilisateur par username
    public function getByUsername($username) {
        $query = "SELECT id, username, password, email, admin, date_creation, date_modification 
                  FROM " . $this->table_name . " 
                  WHERE username = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $row = $stmt->fetch();
        
        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password']; // Hash stocké
            $this->email = $row['email'];
            $this->admin = $row['admin'];
            $this->date_creation = $row['date_creation'];
            $this->date_modification = $row['date_modification'];
            return true;
        }
        
        return false;
    }

    // UPDATE - Mettre à jour un utilisateur
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, 
                      email = :email, 
                      admin = :admin 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind des paramètres
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':admin', $this->admin);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // UPDATE - Mettre à jour le mot de passe
    public function updatePassword($new_password) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password = :password 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash du nouveau mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // DELETE - Supprimer un utilisateur
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // VÉRIFIER - Vérifier le mot de passe
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    // VÉRIFIER - Vérifier si un username existe déjà
    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // VÉRIFIER - Vérifier si un email existe déjà
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>
