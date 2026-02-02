<?php
// Model Citation - Gère l'accès aux données de la table citation
class Citation {
    private $conn;
    private $table_name = "citation";

    public $id;
    public $nom;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Récupérer toutes les citations
    public function getAll() {
        $query = "SELECT id, nom, description FROM " . $this->table_name . " ORDER BY id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Récupérer une citation par ID
    public function getById($id) {
        $query = "SELECT id, nom, description FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        
        if($row) {
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            $this->description = $row['description'];
            return true;
        }
        
        return false;
    }

    // Compter le nombre total de citations
    public function count() {
        if (!$this->conn) {
            return 0;
        }
        
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch();
        return $row['total'];
    }

    // Récupérer une citation aléatoire basée sur la date (change chaque jour)
    public function getDailyCitation() {
        // Vérifier la connexion
        if (!$this->conn) {
            return false;
        }
        
        // Compter le nombre total de citations
        $total = $this->count();
        
        if ($total == 0) {
            return false;
        }
        
        // Utiliser la date du jour comme seed pour avoir la même citation toute la journée
        $seed = date('Ymd');
        $index = ($seed % $total) + 1;
        
        // Récupérer la citation à cet index
        $query = "SELECT id, nom, description FROM " . $this->table_name . " ORDER BY id ASC LIMIT :index, 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':index', $index - 1, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch();
        
        if($row) {
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            $this->description = $row['description'];
            return true;
        }
        
        return false;
    }
}
?>
