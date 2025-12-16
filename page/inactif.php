<?php
// produits_inactifs.php
$title = "Produits Inactifs";
include '../visuel/barre.php';
include '../donnée/connect.php'; // doit définir $pdo

// Traitement pour rendre actif
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rendre_actif'])) {
    $idProduit = (int)$_POST['id_produit'];
    $update = $pdo->prepare("UPDATE Produit SET actif = 1 WHERE id_produit = :id");
    $update->execute([':id' => $idProduit]);
    header("Location: " . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit;
}

// Récupérer le mot-clé de recherche
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';

// Pagination
$parPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $parPage;

// Compter les résultats filtrés
$countSql = "SELECT COUNT(*) FROM Produit WHERE actif = 0";
$params = [];
if ($recherche !== '') {
    $countSql .= " AND (nom LIKE :search OR format LIKE :search)";
    $params[':search'] = '%' . $recherche . '%';
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pagesTotales = max(1, ceil($total / $parPage));

// Récupérer les produits inactifs avec recherche et pagination
$sql = "SELECT id_produit, nom, format FROM Produit WHERE actif = 0";
if ($recherche !== '') {
    $sql .= " AND (nom LIKE :search OR format LIKE :search)";
}
$sql .= " ORDER BY id_produit ASC LIMIT :offset, :parPage";

$stmt = $pdo->prepare($sql);
if ($recherche !== '') {
    $stmt->bindValue(':search', '%' . $recherche . '%', PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':parPage', $parPage, PDO::PARAM_INT);
$stmt->execute();

// URL de base pour pagination (sans le paramètre page)
$queryParams = $_GET;
unset($queryParams['page']);
$urlBase = basename($_SERVER['PHP_SELF']);
if (!empty($queryParams)) {
    $urlBase .= '?' . http_build_query($queryParams) . '&';
} else {
    $urlBase .= '?';
}
?>

<div class="container my-4">
    <div class="card">
        <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
            Liste des produits inactifs et indisponibles à la vente
        </div>
        <div class="card-body">

            <!-- Formulaire de recherche -->
            <form method="get" class="mb-3">
                <div class="input-group">
                    <input type="text" name="recherche" class="form-control" placeholder="Rechercher par nom ou format..." value="<?= htmlspecialchars($recherche) ?>">
                    <button type="submit" class="btn btn-dark">Rechercher</button>
                </div>
            </form>

            <div class="card mt-3">
                <div class="card-header text-center" style="background-color: #232323; color: white; font-size: 1.5rem;">
                    Produits Inactifs
                </div>
                <div class="card-body">
                    <?php if ($total > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Identifiant Produit</th>
                                    <th>Nom du produit</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id_produit']) ?></td>
                                        <td><?= htmlspecialchars($row['nom']) ?> <?= htmlspecialchars($row['format']) ?></td>
                                        <td>
                                            <form method="post" style="margin:0;">
                                                <input type="hidden" name="id_produit" value="<?= htmlspecialchars($row['id_produit']) ?>">
                                                <button type="submit" name="rendre_actif" class="btn btn-success btn-sm" style="background-color:rgb(23, 192, 17);"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir réactiver ce produit ?');"
                                                >Rendre actif</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <tr>
                    <td colspan="4" class="text-center">
                        <a href="../communication/panneau.php" class="btn btn-secondary w-70 mt-3" style="background-color: rgb(206, 0, 0); color: white; font-size: 1rem;">Retour</a>
                    </td>
                  </tr>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Pagination produits inactifs">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="<?= $urlBase ?>page=<?= $page - 1 ?>">Précédent</a></li>
                            <?php else: ?>
                                <li class="page-item disabled"><span class="page-link">Précédent</span></li>
                            <?php endif; ?>

                            <?php
                            // Affichage simple des pages, éviter trop de pages
                            $maxPagesToShow = 7;
                            $startPage = max(1, $page - intval($maxPagesToShow / 2));
                            $endPage = min($pagesTotales, $startPage + $maxPagesToShow - 1);
                            if ($endPage - $startPage + 1 < $maxPagesToShow) {
                                $startPage = max(1, $endPage - $maxPagesToShow + 1);
                            }
                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $urlBase ?>page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $pagesTotales): ?>
                                <li class="page-item"><a class="page-link" href="<?= $urlBase ?>page=<?= $page + 1 ?>">Suivant</a></li>
                            <?php else: ?>
                                <li class="page-item disabled"><span class="page-link">Suivant</span></li>
                            <?php endif; ?>
                        </ul>
                    </nav>

                    <?php else: ?>
                        <p class="text-center">Aucun produit inactif trouvé.</p>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
