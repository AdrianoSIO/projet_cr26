<?php
$title = "Gestion des produits";
require '../visuel/barre.php';
require '../donnée/connect.php';

// Pagination
$parPage = 30;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $parPage;

// Recherche
$search = $_GET['search'] ?? '';
$params = [];
$where = '';

if (!empty($search)) {
    $where = "WHERE nom LIKE :search OR format LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Suppression sécurisée
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM Produit WHERE id_produit = :id");
    $stmt->execute([':id' => $delete_id]);
    header("Location: supp.php?deleted=1");
    exit();
}

// Comptage total pour pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM Produit $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$pagesTotales = ceil($total / $parPage);

// Récupération produits avec pagination + recherche
$sql = "SELECT id_produit, nom, format FROM Produit $where ORDER BY id_produit ASC LIMIT :offset, :limit";
$stmtProd = $pdo->prepare($sql);

foreach ($params as $key => $val) {
    $stmtProd->bindValue($key, $val, PDO::PARAM_STR);
}
$stmtProd->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtProd->bindValue(':limit', $parPage, PDO::PARAM_INT);

$stmtProd->execute();
$produits = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body>
<div class="container my-4">
    <div class="card shadow">
  <div class="card-header text-center" style="background-color: #232323; color: white; font-size: 1.5rem;">
    <?= htmlspecialchars($title) ?>
    </div>
    <div class="card-body">
    <!-- Formulaire recherche -->
    <form method="get" class="mb-3 row g-2 justify-content-center">
        <div class="col-auto">
            <input style="border-color: #232323;" type="text" name="search" class="form-control" placeholder="Rechercher produit..." value="<?= htmlspecialchars($search) ?>" />
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary" style="background-color: rgb(206,0,0); color: white;">Rechercher</button>
            <a href="supp.php" class="btn btn-secondary" style="background-color: #232323; color: white;">Réinitialiser</a>
        </div>
    </form>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success text-center">Produit supprimé avec succès.</div>
    <?php endif; ?>

    <table class="table table-bordered table-hover align-middle text-center">
        <thead class="table-secondary">
            <tr>
                <th>ID Produit</th>
                <th>Nom</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($produits)): ?>
            <tr><td colspan="4">Aucun produit trouvé.</td></tr>
        <?php else: ?>
            <?php foreach ($produits as $produit): ?>
            <tr>
                <td><?= htmlspecialchars($produit['id_produit']) ?></td>
                <td><?= htmlspecialchars($produit['nom']) ?> <?= htmlspecialchars($produit['format']) ?></td>
                <td>
                    <button 
                        class="btn btn-danger btn-sm" 
                        style="background-color: #232323"
                        data-bs-toggle="modal" 
                        data-bs-target="#confirmDeleteModal" 
                        data-id="<?= $produit['id_produit'] ?>" 
                        data-nom="<?= htmlspecialchars($produit['nom']) ?>">
                        <i class="bi bi-trash"></i> Supprimer
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav aria-label="Pagination">
      <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $pagesTotales; $p++): ?>
            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
      </ul>
    </nav>

    <div class="text-center">
        <a href="../communication/panneau.php" class="btn btn-secondary mt-3" style="background-color: rgb(206, 0, 0); color: white;">Retour</a>
    </div>
</div>

<!-- Modal de confirmation suppression -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmer la suppression</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="delete_id" id="delete_id" />
        <p>Voulez-vous vraiment supprimer le produit <strong id="delete_nom"></strong> ?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-danger">Supprimer</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var confirmDeleteModal = document.getElementById('confirmDeleteModal');
confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var id = button.getAttribute('data-id');
    var nom = button.getAttribute('data-nom');
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_nom').textContent = nom;
});
</script>
</body>
</html>
