<?php
// Controller Utilisateur - Gère la logique métier et les sessions
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/users.php';

class UserController {
    private $db;
    private $user;

    public function __construct() {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    // CREATE - Créer un nouvel utilisateur
    public function createUser($username, $password, $email, $admin = 0) {
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
        $this->user->admin = $admin;

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
                'admin' => $this->user->admin,
                'date_creation' => $this->user->date_creation,
                'date_modification' => $this->user->date_modification
            ];
        }
        return null;
    }

    // UPDATE - Mettre à jour un utilisateur (edit)
    public function updateUser($id, $username, $email, $admin) {
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
        $this->user->admin = $admin;

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
                $_SESSION['admin'] = $this->user->admin;
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
        return $this->isLoggedIn() && isset($_SESSION['admin']) && $_SESSION['admin'] == 1;
    }

    // OBTENIR - Obtenir les informations de l'utilisateur connecté
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'admin' => $_SESSION['admin']
            ];
        }
        return null;
    }
}
?>
