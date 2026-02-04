<?php
session_start();

require_once __DIR__ . '/../../controller/users.php';
require_once __DIR__ . '/../../controller/role.php';

$userController = new UserController();
$roleController = new RoleController();

// Vérifier si l'utilisateur est connecté et admin
if (!$userController->isLoggedIn() || !$userController->isAdmin()) {
    header('Location: /view/front/home.php');
    exit();
}

// Récupérer tous les rôles
$roles = $roleController->getAllRoles();

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
        <h1 class="mb-4 text-center page-title">Gestion des Rôles</h1>
        
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
        
        <div class="mb-3">
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="bi bi-plus-circle"></i> Ajouter un rôle
            </button>
        </div>
        
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nom du rôle</th>
                                <th>Nombre d'utilisateurs</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role): ?>
                            <?php
                                // Compter le nombre d'utilisateurs avec ce rôle
                                require_once __DIR__ . '/../../config/Database.php';
                                $database = new Database();
                                $db = $database->getConnection();
                                $stmt = $db->prepare("SELECT COUNT(*) as count FROM utilisateur WHERE role_id = :role_id");
                                $stmt->bindParam(':role_id', $role['id']);
                                $stmt->execute();
                                $userCount = $stmt->fetch()['count'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($role['id']); ?></td>
                                <td>
                                    <?php if ($role['id'] == 1): ?>
                                        <span class="badge bg-danger me-2">Admin</span>
                                    <?php endif; ?>
                                    <?php if ($role['id'] == 2): ?>
                                        <span class="badge bg-success me-2">Utilisateur</span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($role['nom']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $userCount; ?> utilisateur(s)</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" 
                                            onclick="editRole(<?php echo $role['id']; ?>, '<?php echo htmlspecialchars($role['nom'], ENT_QUOTES); ?>')">
                                        <i class="bi bi-pencil"></i> Modifier
                                    </button>
                                    <?php if ($userCount == 0): ?>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete(<?php echo $role['id']; ?>, '<?php echo htmlspecialchars($role['nom'], ENT_QUOTES); ?>')">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Ce rôle est utilisé par des utilisateurs">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajouter Rôle -->
    <div class="modal fade" id="addRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Ajouter un Rôle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/controller/role.php?action=create">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom du rôle *</label>
                            <input type="text" name="nom" class="form-control" required placeholder="Ex: Modérateur, Éditeur...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Modifier Rôle -->
    <div class="modal fade" id="editRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Modifier un Rôle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/controller/role.php?action=update">
                    <input type="hidden" name="id" id="edit_role_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom du rôle *</label>
                            <input type="text" name="nom" id="edit_nom" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Supprimer Rôle -->
    <div class="modal fade" id="deleteRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmer la Suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/controller/role.php?action=delete">
                    <input type="hidden" name="id" id="delete_role_id">
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer le rôle <strong id="delete_role_nom"></strong> ?</p>
                        <p class="text-danger">Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function editRole(id, nom) {
            document.getElementById('edit_role_id').value = id;
            document.getElementById('edit_nom').value = nom;
            
            var modal = new bootstrap.Modal(document.getElementById('editRoleModal'));
            modal.show();
        }
        
        function confirmDelete(id, nom) {
            document.getElementById('delete_role_id').value = id;
            document.getElementById('delete_role_nom').textContent = nom;
            
            var modal = new bootstrap.Modal(document.getElementById('deleteRoleModal'));
            modal.show();
        }
    </script>
    
    <?php include_once __DIR__ . '/../templates/footer.php'; ?>
</body>
