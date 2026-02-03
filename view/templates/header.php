<?php
require_once __DIR__ . '/../../controller/users.php';

$userController = new UserController();
$isLoggedIn = $userController->isLoggedIn();
$currentUser = $userController->getCurrentUser();

$loginError = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid1">
        <img src="../../assets/images/feuille.png" alt="Feuille logo" width="70" height="70" class="d-inline-block align-text-top" style="margin-left: 20px;">
        <h1 class="jadenet">Jade.Net</h1>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <?php if ($isLoggedIn): ?>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="/view/front/home.php">accueil</a>
                </li>        
                <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Liens utiles
                </a>
                <ul class="dropdown-menu">
                    <li><a target="_blank" class="dropdown-item" href="https://jadeprojects.fr">Site Jade Projects</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a target="_blank" class="dropdown-item" href="https://node184-eu.n0c.com/webmail/?_task=mail&_mbox=INBOX">Messagerie</a></li>
                    <li><a target="_blank" class="dropdown-item" href="https://calendar.google.com/calendar/u/0/r/month?pli=1">Calendrier</a></li>
                </ul>
                </li>
                <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Comptes
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Liste des comptes</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Catégories de budget</a></li>            
                    <li><a class="dropdown-item" href="#">Liste des écheanciers</a></li>
                </ul>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/view/front/espace-personnel.php">Espace Personnel</a>
                </li>
                <?php if ($currentUser && $currentUser['admin'] == 1): ?>
                <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Administration
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/view/front/gestion-utilisateurs.php">Gestion des utilisateurs</a></li>
                    <li><a class="dropdown-item" href="/view/front/gestion-citations.php">Gestion des citations</a></li>
                </ul>
                </li>
                <?php endif; ?>
            </ul>
            <?php else: ?>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>
            <?php endif; ?>
            
            <div class="d-flex flex-column align-items-end" style="margin-right: 20px; position: relative;">
                <?php if ($isLoggedIn && $currentUser): ?>
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-semibold text-dark" style="font-size: 16px;">
                            Bonjour, <?php echo htmlspecialchars($currentUser['username']); ?>
                        </span>
                        <a href="/controller/users.php?action=logout" class="btn btn-danger">
                            Déconnexion
                        </a>
                    </div>
                <?php else: ?>
                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#loginCollapse" aria-expanded="false">
                        Connexion
                    </button>
                    
                    <div class="collapse position-absolute top-100 end-0 mt-2" id="loginCollapse" style="min-width: 300px; z-index: 1000;">
                        <div class="card shadow">
                            <div class="card-body">
                                <?php if ($loginError): ?>
                                    <div class="alert alert-danger py-2 mb-3" role="alert">
                                        <?php echo htmlspecialchars($loginError); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="/controller/users.php?action=login">
                                    <div class="mb-3">
                                        <label class="form-label">Nom d'utilisateur</label>
                                        <input type="text" name="username" class="form-control" required autofocus>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mot de passe</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Se connecter</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
  </div>
</nav>
