<?php
session_start();

require_once __DIR__ . '/users.php';

// Déconnexion
$userController = new UserController();
$userController->logout();

// Redirection vers home
header('Location: /view/front/home.php');
exit();
?>
