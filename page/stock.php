<?php
include '../donnée/connect.php';
include '../visuel/barre.php';
?>
<title>Stock Produits</title>

<div class="container my-4">
    <div class="card">
        <div class="card-header" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem; text-align: center;">
            Niveau de stock des produits (table Produit)
        </div>
        <div class="card-body">
            <?php
            $message = '';
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ids = $_POST['id_produit'];
                $seuils = $_POST['seuil'];
                $stocks = $_POST['stock_actuel'];
                $nomsErreurs = [];

                if (isset($_POST['update_one'])) {
                    $i = (int)$_POST['ligne_index'];
                    $id_produit = (int)$ids[$i];
                    $seuil = (int)$seuils[$i];
                    $stock_actuel = (int)$stocks[$i];

                    $stmtNom = $pdo->prepare("SELECT nom FROM Produit WHERE id_produit = :id");
                    $stmtNom->execute([':id' => $id_produit]);
                    $nom = $stmtNom->fetchColumn();

                    $stmt = $pdo->prepare("UPDATE Produit SET seuil = :seuil, stock_actuel = :stock WHERE id_produit = :id");
                    $ok = $stmt->execute([
                        ':seuil' => $seuil,
                        ':stock' => $stock_actuel,
                        ':id' => $id_produit
                    ]);

                    $message = $ok
                        ? '<div class="alert alert-success text-center">Produit <strong>' . htmlspecialchars($nom) . '</strong> mis à jour.</div>'
                        : '<div class="alert alert-danger text-center">Erreur lors de la mise à jour de <strong>' . htmlspecialchars($nom) . '</strong>.</div>';
                }

                if (isset($_POST['update_all'])) {
                    foreach ($ids as $i => $id_produit) {
                        $id_produit = (int)$id_produit;
                        $seuil = (int)$seuils[$i];
                        $stock_actuel = (int)$stocks[$i];

                        $stmtNom = $pdo->prepare("SELECT nom FROM Produit WHERE id_produit = :id");
                        $stmtNom->execute([':id' => $id_produit]);
                        $nom = $stmtNom->fetchColumn();

                        $stmt = $pdo->prepare("UPDATE Produit SET seuil = :seuil, stock_actuel = :stock WHERE id_produit = :id");
                        $ok = $stmt->execute([
                            ':seuil' => $seuil,
                            ':stock' => $stock_actuel,
                            ':id' => $id_produit
                        ]);

                        if (!$ok) $nomsErreurs[] = $nom ?: "ID $id_produit";
                    }

                    $message = empty($nomsErreurs)
                        ? '<div class="alert alert-success text-center">Tous les produits ont été mis à jour avec succès.</div>'
                        : '<div class="alert alert-danger text-center">Erreur pour : <strong>' . implode(', ', array_map('htmlspecialchars', $nomsErreurs)) . '</strong></div>';
                }
            }

            $parPage = 30;
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $parPage;

            $params = [];
            $where = '';

            if (!empty($search)) {
                $where = "WHERE nom LIKE :search OR format LIKE :search";
                $params[':search'] = '%' . $search . '%';
            }

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM Produit $where");
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            $pagesTotales = ceil($total / $parPage);

            $sql = "SELECT id_produit, nom, format, seuil, stock_actuel FROM Produit $where ORDER BY id_produit ASC";
            if (empty($search)) {
                $sql .= " LIMIT :offset, :limit";
            }
            $stmtProd = $pdo->prepare($sql);
            if (!empty($search)) {
                $stmtProd->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
            } else {
                $stmtProd->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmtProd->bindValue(':limit', $parPage, PDO::PARAM_INT);
            }
            $stmtProd->execute();
            ?>

            <?= $message ?>

            <!-- Formulaire recherche avec saisie différée -->
            <form method="get" class="mb-3 d-flex justify-content-center" id="searchForm">
                <input type="text" name="search" id="searchInput" class="form-control w-50 me-2"
                       placeholder="Rechercher un produit..."
                       value="<?= htmlspecialchars($search) ?>">
                <?php if (!empty($search)) : ?>
                    <a href="stock.php" class="btn btn-secondary ms-2">Réinitialiser</a>
                <?php endif; ?>
            </form>

            <!-- Formulaire modification -->
            <form method="post" id="stockForm">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>ID Produit</th>
                                <th>Nom</th>
                                <th>Seuil</th>
                                <th>Stock Actuel</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            <?php while ($row = $stmtProd->fetch(PDO::FETCH_ASSOC)) : ?>
                                <tr>
                                    <td>
                                        <input type="hidden" name="id_produit[]" value="<?= $row['id_produit'] ?>">
                                        <?= $row['id_produit'] ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['nom']) ?> <?= htmlspecialchars($row['format']) ?></td>
                                    <td>
                                        <input type="number" name="seuil[]" value="<?= $row['seuil'] ?>"
                                               class="form-control" min="0" data-index="<?= $i ?>">
                                    </td>
                                    <td>
                                        <input type="number" name="stock_actuel[]" value="<?= $row['stock_actuel'] ?>"
                                               class="form-control" data-index="<?= $i ?>">
                                    </td>
                                    <td>
                                        <button type="submit" name="update_one" value="1"
                                                id="btn-update-<?= $i ?>"
                                                class="btn btn-danger btn-sm"
                                                style="background-color: rgb(206, 0, 0);"
                                                onclick="document.getElementById('ligne_index').value = <?= $i ?>;">
                                            Modifier
                                        </button>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            <?php endwhile; ?>
                        </tbody>

                    </table>                        
                    <tr>
                    <td colspan="4" class="text-center">
                        <a href="../communication/panneau.php" class="btn btn-secondary w-70 mt-3" style="background-color: rgb(206, 0, 0); color: white; font-size: 1rem;">Retour</a>
                    </td>
                  </tr>
                    <input type="hidden" name="ligne_index" id="ligne_index" value="">

                    <div class="text-center mt-3">
                        <button type="submit" name="update_all" class="btn btn-danger" style="background-color:rgb(23, 192, 17); color: white; font-size: 1rem;">Valider tous les produits</button>
                    </div>
                </div>
            </form>

            <?php if (empty($search) && $pagesTotales > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Précédent</a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $pagesTotales; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $pagesTotales): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Suivant</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
document.querySelectorAll('input[name="seuil[]"], input[name="stock_actuel[]"]').forEach(input => {
    input.addEventListener('change', function () {
        const index = this.dataset.index;
        document.getElementById('ligne_index').value = index;
        document.getElementById('btn-update-' + index).click();
    });
});

// Saisie différée avec délai (solution 2)
let debounceTimer;
const searchInput = document.getElementById('searchInput');
const form = document.getElementById('searchForm');

searchInput.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        form.submit();
    }, 500);
});
</script>
