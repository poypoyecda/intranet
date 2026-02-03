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

    // Récupérer les citations avec pagination
    public function getPaginatedCitations($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->citation->getPaginated($offset, $perPage);
        $citations = $stmt->fetchAll();
        
        $total = $this->citation->count();
        $totalPages = ceil($total / $perPage);
        
        return [
            'citations' => $citations,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'perPage' => $perPage
        ];
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

    // CREATE - Créer une nouvelle citation
    public function createCitation($nom, $description) {
        // Validation
        if (empty($nom) || empty($description)) {
            return ['success' => false, 'message' => 'Tous les champs sont requis.'];
        }

        // Définir les propriétés
        $this->citation->nom = $nom;
        $this->citation->description = $description;

        // Créer la citation
        if ($this->citation->create()) {
            return ['success' => true, 'message' => 'Citation créée avec succès.', 'id' => $this->citation->id];
        }

        return ['success' => false, 'message' => 'Erreur lors de la création de la citation.'];
    }

    // UPDATE - Mettre à jour une citation
    public function updateCitation($id, $nom, $description) {
        // Récupérer la citation actuelle
        if (!$this->citation->getById($id)) {
            return ['success' => false, 'message' => 'Citation introuvable.'];
        }

        // Validation
        if (empty($nom) || empty($description)) {
            return ['success' => false, 'message' => 'Tous les champs sont requis.'];
        }

        // Mettre à jour les propriétés
        $this->citation->nom = $nom;
        $this->citation->description = $description;

        if ($this->citation->update()) {
            return ['success' => true, 'message' => 'Citation mise à jour avec succès.'];
        }

        return ['success' => false, 'message' => 'Erreur lors de la mise à jour.'];
    }

    // DELETE - Supprimer une citation
    public function deleteCitation($id) {
        if (!$this->citation->getById($id)) {
            return ['success' => false, 'message' => 'Citation introuvable.'];
        }

        if ($this->citation->delete()) {
            return ['success' => true, 'message' => 'Citation supprimée avec succès.'];
        }

        return ['success' => false, 'message' => 'Erreur lors de la suppression.'];
    }

    // GESTION ADMIN - Gérer la création d'une citation (requiert admin)
    public function handleCreateCitation() {
        session_start();
        
        // Vérifier que l'utilisateur est admin
        if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
            $_SESSION['error_message'] = 'Accès non autorisé.';
            header('Location: /view/front/home.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            $result = $this->createCitation($nom, $description);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }

        header('Location: /view/front/gestion-citations.php');
        exit();
    }

    // GESTION ADMIN - Gérer la mise à jour d'une citation (requiert admin)
    public function handleUpdateCitation() {
        session_start();
        
        // Vérifier que l'utilisateur est admin
        if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
            $_SESSION['error_message'] = 'Accès non autorisé.';
            header('Location: /view/front/home.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            $result = $this->updateCitation($id, $nom, $description);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }

        header('Location: /view/front/gestion-citations.php');
        exit();
    }

    // GESTION ADMIN - Gérer la suppression d'une citation (requiert admin)
    public function handleDeleteCitation() {
        session_start();
        
        // Vérifier que l'utilisateur est admin
        if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
            $_SESSION['error_message'] = 'Accès non autorisé.';
            header('Location: /view/front/home.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            
            $result = $this->deleteCitation($id);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }

        header('Location: /view/front/gestion-citations.php');
        exit();
    }
}

// Gestion des actions si appelé directement
if (basename($_SERVER['PHP_SELF']) === 'citations.php' && isset($_GET['action'])) {
    $controller = new CitationController();
    
    switch ($_GET['action']) {
        case 'create':
            $controller->handleCreateCitation();
            break;
        case 'update':
            $controller->handleUpdateCitation();
            break;
        case 'delete':
            $controller->handleDeleteCitation();
            break;
        default:
            header('Location: /view/front/home.php');
            exit();
    }
}
?>
