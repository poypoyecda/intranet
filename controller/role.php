<?php
// Controller Role - Gère la logique métier des rôles
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/role.php';

class RoleController {
    private $db;
    private $role;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->role = new Role($this->db);
    }

    // READ - Récupérer tous les rôles
    public function getAllRoles() {
        $stmt = $this->role->getAll();
        return $stmt->fetchAll();
    }

    // READ - Récupérer un rôle par ID
    public function getRoleById($id) {
        if ($this->role->getById($id)) {
            return [
                'id' => $this->role->id,
                'nom' => $this->role->nom
            ];
        }
        return null;
    }

    // READ - Récupérer un rôle par nom
    public function getRoleByNom($nom) {
        if ($this->role->getByNom($nom)) {
            return [
                'id' => $this->role->id,
                'nom' => $this->role->nom
            ];
        }
        return null;
    }

    // CREATE - Créer un nouveau rôle
    public function createRole($nom) {
        // Vérifier si le nom existe déjà
        if ($this->role->nomExists($nom)) {
            return ['success' => false, 'message' => 'Ce nom de rôle existe déjà.'];
        }

        $this->role->nom = $nom;

        if ($this->role->create()) {
            return ['success' => true, 'message' => 'Rôle créé avec succès.', 'id' => $this->role->id];
        }

        return ['success' => false, 'message' => 'Erreur lors de la création du rôle.'];
    }

    // UPDATE - Mettre à jour un rôle
    public function updateRole($id, $nom) {
        if (!$this->role->getById($id)) {
            return ['success' => false, 'message' => 'Rôle introuvable.'];
        }

        // Vérifier si le nouveau nom est déjà pris par un autre rôle
        $existingRole = $this->getRoleByNom($nom);
        if ($existingRole && $existingRole['id'] != $id) {
            return ['success' => false, 'message' => 'Ce nom de rôle est déjà utilisé.'];
        }

        $this->role->nom = $nom;

        if ($this->role->update()) {
            return ['success' => true, 'message' => 'Rôle mis à jour avec succès.'];
        }

        return ['success' => false, 'message' => 'Erreur lors de la mise à jour.'];
    }

    // DELETE - Supprimer un rôle
    public function deleteRole($id) {
        if (!$this->role->getById($id)) {
            return ['success' => false, 'message' => 'Rôle introuvable.'];
        }

        // Empêcher la suppression si le rôle est utilisé par des utilisateurs
        if ($this->role->isUsedByUsers()) {
            return ['success' => false, 'message' => 'Ce rôle est utilisé par des utilisateurs et ne peut pas être supprimé.'];
        }

        if ($this->role->delete()) {
            return ['success' => true, 'message' => 'Rôle supprimé avec succès.'];
        }

        return ['success' => false, 'message' => 'Erreur lors de la suppression.'];
    }

    // GESTION ADMIN - Gérer la création d'un rôle (requiert admin)
    public function handleCreateRole() {
        session_start();
        require_once __DIR__ . '/users.php';
        $userController = new UserController();
        
        if (!$userController->isAdmin()) {
            $_SESSION['error_message'] = 'Accès non autorisé.';
            header('Location: /view/front/home.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            
            if (empty($nom)) {
                $_SESSION['error_message'] = 'Le nom du rôle est requis.';
                header('Location: /view/front/gestion-roles.php');
                exit();
            }
            
            $result = $this->createRole($nom);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }

        header('Location: /view/front/gestion-roles.php');
        exit();
    }

    // GESTION ADMIN - Gérer la mise à jour d'un rôle (requiert admin)
    public function handleUpdateRole() {
        session_start();
        require_once __DIR__ . '/users.php';
        $userController = new UserController();
        
        if (!$userController->isAdmin()) {
            $_SESSION['error_message'] = 'Accès non autorisé.';
            header('Location: /view/front/home.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            
            if ($id <= 0 || empty($nom)) {
                $_SESSION['error_message'] = 'Données invalides.';
                header('Location: /view/front/gestion-roles.php');
                exit();
            }
            
            $result = $this->updateRole($id, $nom);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }

        header('Location: /view/front/gestion-roles.php');
        exit();
    }

    // GESTION ADMIN - Gérer la suppression d'un rôle (requiert admin)
    public function handleDeleteRole() {
        session_start();
        require_once __DIR__ . '/users.php';
        $userController = new UserController();
        
        if (!$userController->isAdmin()) {
            $_SESSION['error_message'] = 'Accès non autorisé.';
            header('Location: /view/front/home.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                $_SESSION['error_message'] = 'ID rôle invalide.';
                header('Location: /view/front/gestion-roles.php');
                exit();
            }
            
            $result = $this->deleteRole($id);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }

        header('Location: /view/front/gestion-roles.php');
        exit();
    }
}

// Gestion des actions si appelé directement
if (basename($_SERVER['PHP_SELF']) === 'role.php' && isset($_GET['action'])) {
    $controller = new RoleController();
    
    switch ($_GET['action']) {
        case 'create':
            $controller->handleCreateRole();
            break;
        case 'update':
            $controller->handleUpdateRole();
            break;
        case 'delete':
            $controller->handleDeleteRole();
            break;
        default:
            header('Location: /view/front/home.php');
            exit();
    }
}
?>
