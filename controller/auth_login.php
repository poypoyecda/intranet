<?php
session_start();

require_once __DIR__ . '/users.php';

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
$userController = new UserController();
$result = $userController->login($username, $password);

if (!$result['success']) {
    $_SESSION['login_error'] = $result['message'];
    header('Location: /view/front/home.php');
    exit();
}

// Connexion réussie - redirection vers home
header('Location: /view/front/home.php');
exit();
?>
