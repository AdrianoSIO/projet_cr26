<?php

$title = "Stockage";
include '../visuel/barre.php';
include '../donnée/connect.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('<div class="alert alert-danger">Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// Récupération des filtres depuis GET et POST
$id_produit = $_GET['id_produit'] ?? $_POST['id_produit'] ?? '';
$type_mouvement = $_GET['type_mouvement'] ?? $_POST['type_mouvement'] ?? '';
$date_mouvement = $_GET['date_mouvement'] ?? $_POST['date_mouvement'] ?? '';
$raison = trim($_GET['raison'] ?? $_POST['raison'] ?? '');

// Récupération de l'idUtilisateur et du rôle depuis la session
$idUtilisateur = $_SESSION['idUtilisateur'] ?? null;
$idRole = $_SESSION['idRole'] ?? null;

if (!$idUtilisateur || !$idRole) {
    die('<div class="alert alert-danger">Erreur : Utilisateur non identifié ou rôle non défini.</div>');
}

$where = [];
$params = [];

// Application conditionnelle du filtre utilisateur sur commandes si pas admin/gestionnaire
if (!in_array($idRole, [1, 2])) {
    $where[] = "c.idUtilisateur = :idUtilisateur";
    $params[':idUtilisateur'] = $idUtilisateur;
}

if (!empty($id_produit)) {
    $where[] = "m.id_produit = :id_produit";
    $params[':id_produit'] = $id_produit;
}
if (!empty($type_mouvement) && in_array($type_mouvement, ['entrée', 'sortie'])) {
    $where[] = "m.type_mouvement = :type_mouvement";
    $params[':type_mouvement'] = $type_mouvement;
}
if (!empty($date_mouvement)) {
    $where[] = "DATE(m.date_mouvement) = :date_mouvement";
    $params[':date_mouvement'] = $date_mouvement;
}
if (!empty($raison)) {
    $where[] = "m.raison LIKE :raison";
    $params[':raison'] = '%' . $raison . '%';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

// Pagination
$limit = 50;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count total
$sqlCount = "
    SELECT COUNT(*) 
    FROM Mouvement m
    INNER JOIN Produit p ON m.id_produit = p.id_produit
    LEFT JOIN Commande c ON CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(m.raison, 'n°', -1), ' ', 1) AS UNSIGNED) = c.id_commande
    $whereSql
";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalMouvements = (int)$stmtCount->fetchColumn();
$totalPages = (int)ceil($totalMouvements / $limit);

// Récupération des mouvements
$sql = "
    SELECT 
        m.id_mouvement, 
        m.id_produit, 
        p.nom AS nom_produit, 
        p.format AS format_produit,
        m.type_mouvement,
        m.date_mouvement,
        m.quantite,
        m.raison,
        c.id_commande,
        c.idUtilisateur AS commandeur_id,
        u.nom AS commandeur_nom,
        u.prenom AS commandeur_prenom
    FROM Mouvement m
    INNER JOIN Produit p ON m.id_produit = p.id_produit
    LEFT JOIN Commande c ON CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(m.raison, 'n°', -1), ' ', 1) AS UNSIGNED) = c.id_commande
    LEFT JOIN comptes u ON c.idUtilisateur = u.idUtilisateur
    $whereSql
    ORDER BY m.date_mouvement DESC, m.id_mouvement DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);

// Bind des paramètres
foreach ($params as $key => $value) {
    if ($key === ':idUtilisateur' || $key === ':id_produit') {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour construire l'URL avec les paramètres de filtre (utile pour pagination)
function buildUrl($params) {
    $urlParams = [];
    if (!empty($params['id_produit'])) $urlParams[] = 'id_produit=' . urlencode($params['id_produit']);
    if (!empty($params['type_mouvement'])) $urlParams[] = 'type_mouvement=' . urlencode($params['type_mouvement']);
    if (!empty($params['date_mouvement'])) $urlParams[] = 'date_mouvement=' . urlencode($params['date_mouvement']);
    if (!empty($params['raison'])) $urlParams[] = 'raison=' . urlencode($params['raison']);
    return '?' . implode('&', $urlParams);
}

$baseUrl = buildUrl([
    'id_produit' => $id_produit,
    'type_mouvement' => $type_mouvement,
    'date_mouvement' => $date_mouvement,
    'raison' => $raison
]);

// Récupération des informations utilisateur pour affichage
$sqlUser = "SELECT nom, prenom FROM comptes WHERE idUtilisateur = :idUtilisateur";
$stmtUser = $pdo->prepare($sqlUser);
$stmtUser->bindValue(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
$stmtUser->execute();
$userInfo = $stmtUser->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-entree { background-color: #28a745; }
        .badge-sortie { background-color: #dc3545; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>

<div class="container my-4">
    <div class="card">
        <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
            Mouvements de stock
        </div>
        <div class="card-body">

            <!-- Formulaire de filtre -->
            <form method="get" class="row g-3 mb-4">
                <div class="col-md-2">
                    <label class="form-label">ID Produit</label>
                    <input type="number" name="id_produit" class="form-control" placeholder="ID Produit" style="border-color: #232323;" value="<?= htmlspecialchars($id_produit) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type_mouvement" class="form-select" style="border-color: #232323;" >
                        <option style="border-color: #232323;" value="">Tous les mouvements</option>
                        <option style="border-color: #232323;" value="entrée" <?= $type_mouvement === 'entrée' ? 'selected' : '' ?>>Entrée</option>
                        <option style="border-color: #232323;" value="sortie" <?= $type_mouvement === 'sortie' ? 'selected' : '' ?>>Sortie</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date_mouvement" class="form-control" style="border-color: #232323;" value="<?= htmlspecialchars($date_mouvement) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fauteur de trouble</label>
                    <input type="text" name="raison" class="form-control" placeholder="Rechercher dans N° Commande" style="border-color: #232323;" value="<?= htmlspecialchars($raison) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 me-1" style="background-color: rgb(206, 0, 0); border-color: rgb(206, 0, 0);">Filtrer</button>
                </div>
                <?php if (!empty($id_produit) || !empty($type_mouvement) || !empty($date_mouvement) || !empty($raison)): ?>
                <div class="col-12">
                    <a href="?" class="btn btn-outline-secondary btn-sm">Effacer les filtres</a>
                </div>
                <?php endif; ?>
            </form>

            <!-- Tableau des mouvements -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID Mouvement</th>
                            <th>Produit</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Quantité</th>
                            <th>N° Commande</th>
                            <th>Fauteur de trouble</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mouvements)) : ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-search"></i> Aucun mouvement trouvé pour vos commandes avec ces critères.
                            </td></tr>
                        <?php else : ?>
                            <?php foreach ($mouvements as $m) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['id_mouvement']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($m['nom_produit']) ?></strong>
                                        <?php if (!empty($m['format_produit'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($m['format_produit']) ?></small>
                                        <?php endif; ?>
                                        <br><small class="text-muted">ID: <?= htmlspecialchars($m['id_produit']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $m['type_mouvement'] === 'entrée' ? 'badge-entree' : 'badge-sortie' ?>">
                                            <?= $m['type_mouvement'] === 'entrée' ? '↗️ Entrée' : '↘️ Sortie' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($m['date_mouvement'])) ?>
                                    </td>
                                    <td>
                                        <strong class="<?= $m['type_mouvement'] === 'entrée' ? 'text-success' : 'text-danger' ?>">
                                            <?= $m['type_mouvement'] === 'entrée' ? '+' : '-' ?><?= htmlspecialchars($m['quantite']) ?>
                                        </strong>
                                    </td>
                                    <td><?= htmlspecialchars($m['raison']) ?></td>
                                    <td>
                                        <?php if ($m['id_commande']): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($m['id_commande']) ?></span>
                                            <?php if (in_array($idRole, [1, 2])): ?>
                                                <br>
                                                <small class="text-muted">
                                                    Commandé par : <?= htmlspecialchars($m['commandeur_prenom'] . ' ' . $m['commandeur_nom']) ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Navigation des pages" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Bouton Précédent -->
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $baseUrl ?>&page=<?= $page - 1 ?>">Précédent</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Précédent</span>
                        </li>
                    <?php endif; ?>

                    <!-- Pages -->
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);

                    if ($start > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= $baseUrl ?>&page=1">1</a></li>
                        <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($p = $start; $p <= $end; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl ?>&page=<?= $p ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end < $totalPages): ?>
                        <?php if ($end < $totalPages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item"><a class="page-link" href="<?= $baseUrl ?>&page=<?= $totalPages ?>"><?= $totalPages ?></a></li>
                    <?php endif; ?>

                    <!-- Bouton Suivant -->
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $baseUrl ?>&page=<?= $page + 1 ?>">Suivant</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Suivant</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Bootstrap et FontAwesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
