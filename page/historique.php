<?php
require '../visuel/barre.php';
require '../donnée/connect.php';

// Vérifie connexion
if (empty($_SESSION['id'])) {
    header('Location: ../index.php');
    exit;
}

$idUtilisateur = (int)$_SESSION['id'];
$idRole = (int)($_SESSION['idRole'] ?? 0);
$title = "Commandes détaillées";

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Recherche et paramètres
$search = trim($_GET['search'] ?? '');
$params = [];
$whereClauses = [];

// Recherche sur numéro de commande, produit, date, login, prénom, nom
if ($search !== '') {
    $params[':search'] = $search . '%';
    $whereClauses[] = "(lc.id_commande LIKE :search
        OR lc.id_produit LIKE :search
        OR c.date_commande LIKE :search
        OR comp.login LIKE :search
        OR comp.nom LIKE :search
        OR comp.prenom LIKE :search)";
}

// Filtrage par utilisateur si pas admin/manager
if (!in_array($idRole, [1, 2], true)) {
    $params[':currentUser'] = $idUtilisateur;
    $whereClauses[] = "c.idUtilisateur = :currentUser";
}

// Compose la clause WHERE
$whereSQL = '';
if (!empty($whereClauses)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Compte total
$countSql = "
    SELECT COUNT(*) 
    FROM Ligne_commande lc
    JOIN Commande c ON lc.id_commande = c.id_commande
    LEFT JOIN comptes comp ON c.idUtilisateur = comp.idUtilisateur
    $whereSQL
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

// Requête de données
$dataSql = "
    SELECT 
        lc.id_ligne,
        lc.id_commande,
        c.date_commande,
        c.idUtilisateur,
        lc.id_produit,
        lc.prixU,
        lc.quantite,
        (lc.prixU * lc.quantite) AS montant_ligne,
        comp.login,
        comp.nom,
        comp.prenom,
        p.nom AS nom_produit
    FROM Ligne_commande lc
    JOIN Commande c ON lc.id_commande = c.id_commande
    LEFT JOIN comptes comp ON c.idUtilisateur = comp.idUtilisateur
    LEFT JOIN Produit p ON lc.id_produit = p.id_produit
  
    $whereSQL
    ORDER BY lc.id_ligne DESC
    LIMIT :limit OFFSET :offset
";
$dataStmt = $pdo->prepare($dataSql);
foreach ($params as $k => $v) {
    $dataStmt->bindValue($k, $v, PDO::PARAM_STR);
}
$dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$dataStmt->execute();
$rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

// Organisation par commande
$commandes = [];
foreach ($rows as $row) {
    $idCommande = $row['id_commande'];
    if (!isset($commandes[$idCommande])) {
        $commandes[$idCommande] = [
            'date_commande' => $row['date_commande'],
            'login' => $row['login'],
            'nom' => $row['nom'],
            'prenom' => $row['prenom'],
            'lignes' => [],
            'total_commande' => 0
        ];
    }
    $commandes[$idCommande]['lignes'][] = $row;
    $commandes[$idCommande]['total_commande'] += $row['montant_ligne'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
  <div class="card">
  <div class="card-header text-center" style="background-color: #232323; color: white; font-size: 1.5rem;">
      <?= htmlspecialchars($title) ?>
    </div>
    <div class="card-body">
      <form method="get" class="mb-3 d-flex" role="search">
        <input name="search" class="form-control me-2" style="border-color: #232323"
               placeholder="Rechercher (n° de commande, produit, date (XXXX-XX-XX), login, nom, prénom)…"
               value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary" style="background-color: rgb(206,0,0)">Rechercher</button>
      </form>

      <?php if (empty($commandes)): ?>
        <div class="alert alert-warning text-center">Aucun résultat trouvé.</div>
      <?php else: ?>
        <?php foreach ($commandes as $idCommande => $cmd): ?>
          <div class="card mb-4 shadow">
            <div class="card-header bg-light fw-bold">
              <div class="row">
                <div class="col-md-8">
                  Commande n° <?= htmlspecialchars($idCommande) ?> — Le <?= htmlspecialchars($cmd['date_commande']) ?>
                  <br>
                  <small class="text-muted">
                    Client : <?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom'] . ' (' . $cmd['login'] . ')') ?>
                  </small>
                </div>
              </div>
            </div>
            <div class="card-body p-0">
              <table class="table table-bordered mb-0">
                <thead class="table-secondary">
                  <tr>
                    <th>Produit</th>
                    <th>Prix U (€)</th>
                    <th>Quantité</th>
                    <th>Montant ligne (€)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cmd['lignes'] as $ligne): ?>
                    <tr>
                      <td><?= htmlspecialchars($ligne['nom_produit'] ?? 'Aucun produit') ?></td>
                      <td><?= number_format($ligne['prixU'], 2, ',', ' ') ?> €</td>
                      <td class="text-end"><?= htmlspecialchars($ligne['quantite']) ?></td>
                      <td class="text-end"><?= number_format($ligne['montant_ligne'], 2, ',', ' ') ?> €</td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr class="table-dark fw-bold">
                    <td colspan="3" class="text-end" style="background-color:  rgb(206,0,0); color: #fff;">
                      Total commande :
                    </td>
                    <td class="text-end"style="background-color: #232323; color: #fff;"><?= number_format($cmd['total_commande'], 2, ',', ' ') ?> €</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Pagination -->
      <nav aria-label="Pagination" class="d-flex justify-content-center">
        <ul class="pagination">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">Précédent</a>
          </li>
          <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Suivant</a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</div>
</body>
</html>
