<?php
// Controller Utilisateur - Gère la logique métier et les sessions
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/users.php';
require_once __DIR__ . '/../controller/role.php';

class UserController {
    private $db;
    private $user;
    private $roleController;

    public function __construct() {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        $this->roleController = new RoleController();
    }

    // CREATE - Créer un nouvel utilisateur
    public function createUser($username, $password, $email, $role_id = 2) {
        // Vérifier si le username existe déjà
        if ($this->user->usernameExists($username)) {
            return ['success' => false, 'message' => 'Ce nom d\'utilisateur existe déjà.'];
        }

        // Vérifier si l'email existe déjà
        if ($this->user->emailExists($email)) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
        }

        // Définir les propriétés
        $this->user->username = $username;
        $this->user->password = $password; // Sera hashé dans le model
        $this->user->email = $email;
        $this->user->role_id = $role_id;

        // Créer l'utilisateur
        if ($this->user->create()) {
            return ['success' => true, 'message' => 'Utilisateur créé avec succès.', 'id' => $this->user->id];
        }

        return ['success' => false, 'message' => 'Erreur lors de la création de l\'utilisateur.'];
    }

    // READ - Récupérer tous les utilisateurs
    public function getAllUsers() {
        $stmt = $this->user->getAll();
        return $stmt->fetchAll();
    }

    // READ - Récupérer un utilisateur par ID
    public function getUserById($id) {
        if ($this->user->getById($id)) {
            return [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'role_id' => $this->user->role_id,
                'date_creation' => $this->user->date_creation,
                'date_modification' => $this->user->date_modification
            ];
        }
        return null;
    }

    // UPDATE - Mettre à jour un utilisateur (edit)
    public function updateUser($id, $username, $email, $role_id) {
        // Récupérer l'utilisateur actuel
        if (!$this->user->getById($id)) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
        }

        // Vérifier si le nouveau username est déjà pris par un autre utilisateur
        if ($username !== $this->user->username && $this->user->usernameExists($username)) {
            return ['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà pris.'];
        }

        // Vérifier si le nouveau email est déjà pris par un autre utilisateur
        if ($email !== $this->user->email && $this->user->emailExists($email)) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
        }

        // Mettre à jour les propriétés
        $this->user->username = $username;
        $this->user->email = $email;
        $this->user->role_id = $role_id;

        if ($this->user->update()) {
            return ['success' => true, 'message' => 'Utilisateur mis à jour avec succès.'];
        }

        return ['success' => false, 'message' => 'Erreur lors de la mise à jour.'];
    }

    // UPDATE - Changer le mot de passe
    public function changePassword($id, $new_password) {
        if (!$this->user->getById($id)) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
        }

        if ($this->user->updatePassword($new_password)) {
            return ['success' => true, 'message' => 'Mot de passe modifié avec succès.'];
        }

        return ['success' => false, 'message' => 'Erreur lors de la modification du mot de passe.'];
    }

    // DELETE - Supprimer un utilisateur
    public function deleteUser($id) {
        if (!$this->user->getById($id)) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
        }

        // Empêcher la suppression si c'est l'utilisateur connecté
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            return ['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'];
        }

        if ($this->user->delete()) {
            return ['success' => true, 'message' => 'Utilisateur supprimé avec succès.'];
        }

        return ['success' => false, 'message' => 'Erreur lors de la suppression.'];
    }

    // CONNEXION - Authentifier un utilisateur
    public function login($username, $password) {
        if ($this->user->getByUsername($username)) {
            if ($this->user->verifyPassword($password)) {
                // Stocker les informations en session
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                $_SESSION['email'] = $this->user->email;
                $_SESSION['role_id'] = $this->user->role_id;
                $_SESSION['logged_in'] = true;

                return ['success' => true, 'message' => 'Connexion réussie.'];
            }
            return ['success' => false, 'message' => 'Mot de passe incorrect.'];
        }
        return ['success' => false, 'message' => 'Utilisateur introuvable.'];
    }

    // DÉCONNEXION - Déconnecter l'utilisateur
    public function logout() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        return ['success' => true, 'message' => 'Déconnexion réussie.'];
    }

    // VÉRIFIER - Vérifier si l'utilisateur est connecté
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // VÉRIFIER - Vérifier si l'utilisateur est admin
    public function isAdmin() {
        return $this->isLoggedIn() && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
    }

    // OBTENIR - Obtenir les informations de l'utilisateur connecté
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'] ?? null,
                'username' => $_SESSION['username'] ?? null,
                'email' => $_SESSION['email'] ?? null,
                'role_id' => $_SESSION['role_id'] ?? 2
            ];
        }
        return null;
    }

    // GESTION ADMIN - Gérer la création d'un utilisateur (requiert admin)
    public function handleCreateUser() {
        if (!$this->isAdmin()) {
            $_SESSION['error_message'] = 'Accès non autorisé.';
            header('Location: /view/front/home.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $email = trim($_POST['email'] ?? '');
            $role_id = (int)($_POST['role_id'] ?? 2);
            
            if (empty($username) || empty($password) || empty($email)) {
                $_SESSION['error_message'] = 'Tous les champs sont requis.';
                header('Location: /view/front/gestion-utilisateurs.php');
                exit();
            }
            
            if (strlen($password) < 6) {
                $_SESSION['error_message'] = 'Le mot de passe doit contenir au moins 6 caractères.';
                header('Location: /view/front/gestion-utilisateurs.php');
                exit();
            }
            
            $result = $this->createUser($username, $password, $email, $role_id);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }

        header('Location: /view/front/gestion-utilisateurs.php');
        exit();
    }

    // GESTION ADMIN - Gérer la mise à jour d'un utilisateur (requiert admin)
    public function handleUpdateUser() {
        if (!$this->isAdmin()) {
            $_SESSION['error_message'] = 'Accès non autorisé.';
            header('Location: /view/front/home.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role_id = (int)($_POST['role_id'] ?? 2);
            
            if ($id <= 0 || empty($username) || empty($email)) {
                $_SESSION['error_message'] = 'Données invalides.';
                header('Location: /view/front/gestion-utilisateurs.php');
                exit();
            }
            
            $result = $this->updateUser($id, $username, $email, $role_id);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }

        header('Location: /view/front/gestion-utilisateurs.php');
        exit();
    }

    // GESTION ADMIN - Gérer la suppression d'un utilisateur (requiert admin)
    public function handleDeleteUser() {
        if (!$this->isAdmin()) {
            $_SESSION['error_message'] = 'Accès non autorisé.';
            header('Location: /view/front/home.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                $_SESSION['error_message'] = 'ID utilisateur invalide.';
                header('Location: /view/front/gestion-utilisateurs.php');
                exit();
            }
            
            $result = $this->deleteUser($id);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }

        header('Location: /view/front/gestion-utilisateurs.php');
        exit();
    }
    
    // AUTHENTIFICATION - Gérer la connexion
    public function handleLogin() {
        // Vérifier que c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /view/front/home.php');
            exit();
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Veuillez remplir tous les champs.';
            header('Location: /view/front/home.php');
            exit();
        }

        // Tentative de connexion
        $result = $this->login($username, $password);

        if (!$result['success']) {
            $_SESSION['login_error'] = $result['message'];
            header('Location: /view/front/home.php');
            exit();
        }

        // Connexion réussie - redirection vers home
        header('Location: /view/front/home.php');
        exit();
    }

    // AUTHENTIFICATION - Gérer la déconnexion
    public function handleLogout() {
        $this->logout();
        header('Location: /view/front/home.php');
        exit();
    }

    // AUTHENTIFICATION - Gérer le changement de mot de passe
    public function handleChangePassword() {
        if (!$this->isLoggedIn()) {
            header('Location: /view/front/home.php');
            exit();
        }

        // Vérifier que c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /view/front/espace-personnel.php');
            exit();
        }

        $currentUser = $this->getCurrentUser();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error_message'] = 'Tous les champs sont obligatoires.';
            header('Location: /view/front/espace-personnel.php');
            exit();
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['error_message'] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
            header('Location: /view/front/espace-personnel.php');
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = 'Les mots de passe ne correspondent pas.';
            header('Location: /view/front/espace-personnel.php');
            exit();
        }

        // Vérifier le mot de passe actuel
        if (!$this->user->getByUsername($currentUser['username']) || !$this->user->verifyPassword($currentPassword)) {
            $_SESSION['error_message'] = 'Le mot de passe actuel est incorrect.';
            header('Location: /view/front/espace-personnel.php');
            exit();
        }

        // Changer le mot de passe
        $result = $this->changePassword($currentUser['id'], $newPassword);

        if ($result['success']) {
            $_SESSION['success_message'] = 'Mot de passe modifié avec succès.';
        } else {
            $_SESSION['error_message'] = $result['message'];
        }

        header('Location: /view/front/espace-personnel.php');
        exit();
    }
}

// Gestion des actions si appelé directement
if (basename($_SERVER['PHP_SELF']) === 'users.php' && isset($_GET['action'])) {
    $controller = new UserController();
    
    switch ($_GET['action']) {
        case 'login':
            $controller->handleLogin();
            break;
        case 'logout':
            $controller->handleLogout();
            break;
        case 'change_password':
            $controller->handleChangePassword();
            break;
        case 'create':
            $controller->handleCreateUser();
            break;
        case 'update':
            $controller->handleUpdateUser();
            break;
        case 'delete':
            $controller->handleDeleteUser();
            break;
        default:
            header('Location: /view/front/home.php');
            exit();
    }
}
?>
