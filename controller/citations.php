<?php
// Controller Citation - Gère la logique métier et fait le lien entre Model et View
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/citations.php';

class CitationController {
    private $db;
    private $citation;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->citation = new Citation($this->db);
    }

    // Récupérer toutes les citations
    public function getAllCitations() {
        $stmt = $this->citation->getAll();
        return $stmt->fetchAll();
    }

    // Récupérer une citation par ID
    public function getCitationById($id) {
        if ($this->citation->getById($id)) {
            return [
                'id' => $this->citation->id,
                'nom' => $this->citation->nom,
                'description' => $this->citation->description
            ];
        }
        return null;
    }

    // Récupérer la citation du jour (change toutes les 24h)
    public function getDailyCitation() {
        if ($this->citation->getDailyCitation()) {
            return [
                'id' => $this->citation->id,
                'nom' => $this->citation->nom,
                'description' => $this->citation->description
            ];
        }
        
        // Citation par défaut si aucune citation n'existe en base
        return [
            'id' => 0,
            'nom' => 'Anonyme',
            'description' => 'Aucune citation disponible pour le moment.'
        ];
    }
}
?>
