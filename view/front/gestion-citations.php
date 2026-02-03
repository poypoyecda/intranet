<?php
session_start();

require_once __DIR__ . '/../../controller/citations.php';
require_once __DIR__ . '/../../controller/users.php';

$userController = new UserController();

// Vérifier si l'utilisateur est connecté et admin
if (!$userController->isLoggedIn() || !$userController->isAdmin()) {
    header('Location: /view/front/home.php');
    exit();
}

$citationController = new CitationController();

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$paginationData = $citationController->getPaginatedCitations($page, $perPage);
$citations = $paginationData['citations'];
$totalPages = $paginationData['totalPages'];
$currentPage = $paginationData['currentPage'];

$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<head>
    <?php include_once '../templates/head.php'; ?>
</head>
<body class="bodyhome">
    <?php include_once __DIR__ . '/../templates/header.php'; ?>
    
    <div class="container mt-3">
        <h1 class="mb-3 text-center">Gestion des Citations</h1>
        
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
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCitationModal">
                <i class="bi bi-plus-circle"></i> Ajouter une citation
            </button>
        </div>
        
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Auteur</th>
                                <th>Citation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citations as $citation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($citation['id']); ?></td>
                                <td><?php echo htmlspecialchars($citation['nom']); ?></td>
                                <td><?php echo htmlspecialchars($citation['description']); ?></td>
                                <td class="text-nowrap">
                                    <button class="btn btn-sm btn-primary" 
                                            onclick="editCitation(<?php echo $citation['id']; ?>, '<?php echo htmlspecialchars($citation['nom'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($citation['description'], ENT_QUOTES); ?>')">
                                        <i class="bi bi-pencil"></i> Modifier
                                    </button>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete(<?php echo $citation['id']; ?>, '<?php echo htmlspecialchars($citation['nom'], ENT_QUOTES); ?>')">
                                        <i class="bi bi-trash"></i> Supprimer
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Pagination des citations">
                    <ul class="pagination justify-content-center mb-0 mt-3">
                        <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">Précédent</a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == 1 || $i == $totalPages || abs($i - $currentPage) <= 2): ?>
                                <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php elseif (abs($i - $currentPage) == 3): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajouter Citation -->
    <div class="modal fade" id="addCitationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Ajouter une Citation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/controller/citations.php?action=create">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Auteur *</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Citation *</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
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
    
    <!-- Modal Modifier Citation -->
    <div class="modal fade" id="editCitationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Modifier une Citation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/controller/citations.php?action=update">
                    <input type="hidden" name="id" id="edit_citation_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Auteur *</label>
                            <input type="text" name="nom" id="edit_nom" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Citation *</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="4" required></textarea>
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
    
    <!-- Modal Supprimer Citation -->
    <div class="modal fade" id="deleteCitationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmer la Suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/controller/citations.php?action=delete">
                    <input type="hidden" name="id" id="delete_citation_id">
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer la citation de <strong id="delete_citation_nom"></strong> ?</p>
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
        function editCitation(id, nom, description) {
            document.getElementById('edit_citation_id').value = id;
            document.getElementById('edit_nom').value = nom;
            document.getElementById('edit_description').value = description;
            
            var modal = new bootstrap.Modal(document.getElementById('editCitationModal'));
            modal.show();
        }
        
        function confirmDelete(id, nom) {
            document.getElementById('delete_citation_id').value = id;
            document.getElementById('delete_citation_nom').textContent = nom;
            
            var modal = new bootstrap.Modal(document.getElementById('deleteCitationModal'));
            modal.show();
        }
    </script>
    
    <?php include_once __DIR__ . '/../templates/footer.php'; ?>
</body>
