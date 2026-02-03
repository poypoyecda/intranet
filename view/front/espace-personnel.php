<?php
session_start();

require_once __DIR__ . '/../../controller/users.php';

$userController = new UserController();

if (!$userController->isLoggedIn()) {
    header('Location: /view/front/home.php');
    exit();
}

$currentUser = $userController->getCurrentUser();
$fullUserData = $userController->getUserById($currentUser['id']);

$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<head>
    <?php include_once '../templates/head.php'; ?>
</head>
<body class="bodyhome">
    <?php include_once __DIR__ . '/../templates/header.php'; ?>
    
    <div class="container mt-5">
        <h1 class="mb-4 text-center page-title">Espace Personnel</h1>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Mes Informations</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nom d'utilisateur</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($currentUser['username']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Statut</label>
                            <p class="form-control-plaintext">
                                <?php if ($currentUser['admin'] == 1): ?>
                                    <span class="badge bg-danger">Administrateur</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Utilisateur</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <?php if ($fullUserData): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Membre depuis</label>
                            <p class="form-control-plaintext">
                                <?php echo date('d/m/Y à H:i', strtotime($fullUserData['date_creation'])); ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Dernière modification</label>
                            <p class="form-control-plaintext">
                                <?php echo date('d/m/Y à H:i', strtotime($fullUserData['date_modification'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-warning">
                        <h4 class="mb-0">Sécurité</h4>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary w-100 mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#changePasswordForm" aria-expanded="false">
                            Modifier mon mot de passe
                        </button>
                        
                        <div class="collapse" id="changePasswordForm">
                            <form method="POST" action="/controller/users.php?action=change_password">
                                <div class="mb-3">
                                    <label class="form-label">Mot de passe actuel *</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nouveau mot de passe *</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                    <small class="text-muted">Minimum 6 caractères</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Confirmer le nouveau mot de passe *</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                
                                <button type="submit" class="btn btn-success w-100">Changer le mot de passe</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
    <?php include_once __DIR__ . '/../templates/footer.php'; ?>
</body>
