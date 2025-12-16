<?php
require '../visuel/barre.php';
require '../donnée/connect.php';

$title = "Produits";

if (empty($_SESSION['id'])) {
    header('Location: ../index.php');
    exit;
}

$idUtilisateur = (int)$_SESSION['id'];

// Initialiser le panier
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Variable pour les messages d'erreur
$errorMessage = '';
$successMessage = '';

// Traitement AJAX pour mise à jour du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update'])) {
    header('Content-Type: application/json');
    
    $idProduit = (int)($_POST['id_produit'] ?? 0);
    $quantite = max(0, (int)($_POST['quantite'] ?? 0));
    $action = $_POST['action'] ?? 'ajouter';
    
    // Vérifier que le produit est actif
    $stmtVerif = $pdo->prepare("SELECT actif, stock_actuel FROM Produit WHERE id_produit = ?");
    $stmtVerif->execute([$idProduit]);
    $rowVerif = $stmtVerif->fetch(PDO::FETCH_ASSOC);
    
    if (!$rowVerif || $rowVerif['actif'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Produit inactif']);
        exit;
    }
    
    if ($quantite > 0) {
        $_SESSION['panier'][$idProduit] = [
            'quantite' => $quantite,
            'action' => $action,
        ];
    } else {
        if (isset($_SESSION['panier'][$idProduit])) {
            unset($_SESSION['panier'][$idProduit]);
        }
    }
    
    // Calculer le nouveau stock théorique pour affichage
    $stockActuel = (int)$rowVerif['stock_actuel'];
    $stockTheorique = $stockActuel;
    
    if (isset($_SESSION['panier'][$idProduit])) {
        $detailsPanier = $_SESSION['panier'][$idProduit];
        if ($detailsPanier['action'] === 'ajouter') {
            $stockTheorique = $stockActuel - $detailsPanier['quantite'];
        } else {
            $stockTheorique = $stockActuel + $detailsPanier['quantite'];
        }
    }
    
    echo json_encode([
        'success' => true, 
        'stock_theorique' => $stockTheorique,
        'panier_count' => count($_SESSION['panier'])
    ]);
    exit;
}

// Suppression produit du panier
if (isset($_GET['supprimer'])) {
    $idSuppr = (int)$_GET['supprimer'];
    if (isset($_SESSION['panier'][$idSuppr])) {
        unset($_SESSION['panier'][$idSuppr]);
        header('Location: produit.php');
        exit;
    }
}

// Vider le panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vider_panier'])) {
    $_SESSION['panier'] = [];
    header('Location: produit.php');
    exit;
}

// Mise à jour panier (quantités + actions) - Version classique pour les gros changements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantites']) && isset($_POST['id_produits']) && isset($_POST['actions']) && !isset($_POST['valider_panier'])) {
    foreach ($_POST['id_produits'] as $i => $idProduit) {
        $idProduit = (int)$idProduit;
        $quantite = max(0, (int)($_POST['quantites'][$i] ?? 0));
        $action = $_POST['actions'][$i] ?? 'ajouter';

        // Vérifier que le produit est actif avant modification panier
        $stmtVerif = $pdo->prepare("SELECT actif FROM Produit WHERE id_produit = ?");
        $stmtVerif->execute([$idProduit]);
        $rowVerif = $stmtVerif->fetch(PDO::FETCH_ASSOC);
        if (!$rowVerif || $rowVerif['actif'] == 0) {
            $errorMessage = "Impossible de modifier le panier avec un produit inactif (ID: $idProduit).";
            continue;
        }

        if ($quantite > 0) {
            $_SESSION['panier'][$idProduit] = [
                'quantite' => $quantite,
                'action' => $action,
            ];
        } else {
            // Si quantité = 0, supprimer du panier
            if (isset($_SESSION['panier'][$idProduit])) {
                unset($_SESSION['panier'][$idProduit]);
            }
        }
    }
    
    // Redirection après mise à jour pour éviter la resoumission
    header('Location: produit.php');
    exit;
}

// Validation du panier (création commande)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_panier']) && !empty($_SESSION['panier'])) {
    try {
        $pdo->beginTransaction();

        // Vérifier que tous les produits du panier sont encore actifs
        $produitsInactifs = [];
        foreach ($_SESSION['panier'] as $idProduit => $details) {
            $stmtVerif = $pdo->prepare("SELECT actif, nom FROM Produit WHERE id_produit = ?");
            $stmtVerif->execute([$idProduit]);
            $produit = $stmtVerif->fetch(PDO::FETCH_ASSOC);
            if (!$produit || $produit['actif'] == 0) {
                $produitsInactifs[] = $produit['nom'] ?? "ID: $idProduit";
            }
        }
        
        if (!empty($produitsInactifs)) {
            throw new Exception("Produits inactifs dans le panier : " . implode(', ', $produitsInactifs));
        }

        // Calcul montant total
        $montantTotal = 0;
        $stmtPrix = $pdo->prepare("SELECT Vente FROM Produit WHERE id_produit = ? AND actif = 1");
        foreach ($_SESSION['panier'] as $idProduit => $details) {
            $stmtPrix->execute([$idProduit]);
            $row = $stmtPrix->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new Exception("Produit non trouvé ou inactif : ID $idProduit");
            }
            $prixU = $row['Vente'] ?? 0;
            $montantTotal += $prixU * $details['quantite'];
        }

        if ($montantTotal <= 0) {
            throw new Exception("Le montant total de la commande doit être supérieur à 0");
        }

        // Insertion commande
        $stmtCommande = $pdo->prepare("INSERT INTO Commande (date_commande, montant, idUtilisateur) VALUES (NOW(), ?, ?)");
        $stmtCommande->execute([$montantTotal, $idUtilisateur]);
        $idCommande = $pdo->lastInsertId();

        // Préparer requêtes
        $stmtAddition = $pdo->prepare("INSERT INTO Ligne_commande (id_commande, id_produit, quantite, prixU) VALUES (?, ?, ?, ?)");
        $stmtMouvement = $pdo->prepare("INSERT INTO Mouvement (id_produit, type_mouvement, date_mouvement, quantite, raison) VALUES (?, ?, NOW(), ?, ?)");
        $stmtStock = $pdo->prepare("SELECT stock_actuel, Vente FROM Produit WHERE id_produit = ? AND actif = 1");
        $stmtUpdate = $pdo->prepare("UPDATE Produit SET stock_actuel = ? WHERE id_produit = ?");

        foreach ($_SESSION['panier'] as $idProduit => $details) {
            $quantite = $details['quantite'];
            $action = $details['action'];

            $stmtStock->execute([$idProduit]);
            $prod = $stmtStock->fetch(PDO::FETCH_ASSOC);
            if (!$prod) {
                throw new Exception("Produit non trouvé : ID $idProduit");
            }

            $stockActuel = (int)$prod['stock_actuel'];
            $prixU = (float)$prod['Vente'];

            if ($action === 'ajouter') {
                // Vérifier si assez de stock
                if ($stockActuel < $quantite) {
                    throw new Exception("Stock insuffisant pour le produit ID $idProduit (stock: $stockActuel, demandé: $quantite)");
                }
                $nouveauStock = $stockActuel - $quantite;
                $typeMouv = 'sortie';
            } else { // retirer
                $nouveauStock = $stockActuel + $quantite;
                $typeMouv = 'entrée';
            }

            $stmtUpdate->execute([$nouveauStock, $idProduit]);
            $stmtAddition->execute([$idCommande, $idProduit, $quantite, $prixU]);
            $raison = "Commande n°$idCommande";
            $stmtMouvement->execute([$idProduit, $typeMouv, $quantite, $raison]);
        }

        $pdo->commit();
        $_SESSION['panier'] = [];
        header('Location: produit.php?success=1');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMessage = "Erreur lors de la validation : " . htmlspecialchars($e->getMessage());
    }
}

// Pagination & filtres
$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$afficherInactifs = isset($_GET['afficher_inactifs']) && $_GET['afficher_inactifs'] === '1';

$where = [];
$params = [];
if ($search !== '') {
    $where[] = "(p.nom LIKE :search OR m.nom LIKE :search OR c.nom LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!$afficherInactifs) {
    $where[] = "p.actif = 1";
}
$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

$stmtCount = $pdo->prepare("
    SELECT COUNT(*) 
    FROM Produit p
    LEFT JOIN Marque m ON p.id_marque = m.id_marque
    LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
    LEFT JOIN Fournisseur f ON p.id_fournisseur = f.id_fournisseur
    $whereSql
");
$stmtCount->execute($params);
$totalProduits = (int)$stmtCount->fetchColumn();
$totalPages = (int)ceil($totalProduits / $limit);
$ligneModifiee = null;

// Gestion de la mise à jour manuelle du panier (bouton "Mettre à jour le panier (Manuel)")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_panier']) && isset($_POST['quantites']) && isset($_POST['id_produits']) && isset($_POST['actions'])) {
    $dernierModif = null;
    foreach ($_POST['id_produits'] as $i => $idProduit) {
        $idProduit = (int)$idProduit;
        $quantite = max(0, (int)($_POST['quantites'][$i] ?? 0));
        $action = $_POST['actions'][$i] ?? 'ajouter';

        // Vérifier que le produit est actif avant modification panier
        $stmtVerif = $pdo->prepare("SELECT actif FROM Produit WHERE id_produit = ?");
        $stmtVerif->execute([$idProduit]);
        $rowVerif = $stmtVerif->fetch(PDO::FETCH_ASSOC);
        if (!$rowVerif || $rowVerif['actif'] == 0) {
            $stmtNomProduit = $pdo->prepare("SELECT nom FROM Produit WHERE id_produit = ?");
            $stmtNomProduit->execute([$idProduit]);
            $nomProduit = $stmtNomProduit->fetchColumn();
            $errorMessage = "Impossible de modifier le panier avec un produit inactif (Produit: " . htmlspecialchars($nomProduit) . ").";
            continue;
        }

        if ($quantite > 0) {
            $_SESSION['panier'][$idProduit] = [
                'quantite' => $quantite,
                'action' => $action,
            ];
            $dernierModif = $idProduit;
        } else {
            if (isset($_SESSION['panier'][$idProduit])) {
                unset($_SESSION['panier'][$idProduit]);
                $dernierModif = $idProduit;
            }
        }
    }
    // Redirection propre avec ancre sur la ligne modifiée
    if ($dernierModif !== null) {
        header('Location: produit.php?page=' . $page . '&search=' . urlencode($search) . '&afficher_inactifs=' . ($afficherInactifs ? 1 : 0) . '#produit-' . $dernierModif);
        exit;
    } else {
        header('Location: produit.php?page=' . $page . '&search=' . urlencode($search) . '&afficher_inactifs=' . ($afficherInactifs ? 1 : 0));
        exit;
    }
}

// Détecter si une ligne doit être surlignée (après redirection)
if (isset($_SERVER['REQUEST_URI'])) {
    if (preg_match('/#produit-(\d+)$/', $_SERVER['REQUEST_URI'], $m)) {
        $ligneModifiee = (int)$m[1];
    }
}
$sql = "
    SELECT p.*, m.nom AS nom_marque, c.nom AS nom_categorie, f.nom AS nom_fournisseur
    FROM Produit p
    LEFT JOIN Marque m ON p.id_marque = m.id_marque
    LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
    LEFT JOIN Fournisseur f ON p.id_fournisseur = f.id_fournisseur
    $whereSql
    ORDER BY p.id_produit ASC
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Vérifier si panier non vide
$panierNonVide = false;
foreach ($_SESSION['panier'] as $item) {
    if (!empty($item['quantite']) && $item['quantite'] > 0) {
        $panierNonVide = true;
        break;
    }
}

// Messages de succès
if (isset($_GET['success'])) {
    $successMessage = "Commande validée avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Produits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .stock-updated {
            background-color:rgb(102, 0, 150) !important;
            transition: background-color 0.3s ease;
        }
        .panier-badge {
            position: relative;
        }
        .panier-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: rgb(206, 0, 0);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
<div class="container my-4">
    <div class="card shadow">
  <div class="card-header text-center" style="background-color: #232323; color: white; font-size: 1.5rem;">
            <span>Liste des produits</span>
            <div class="panier-badge">
                <span class="panier-count" id="panierCount"><?= count($_SESSION['panier']) ?></span>
            </div>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger m-3"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success m-3"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <!-- Affichage du contenu du panier -->
        <?php if (!empty($_SESSION['panier'])): ?>
            <div class="alert alert-info m-3">
                <h5>Panier actuel :</h5>
                <ul class="mb-0" id="panierContent">
                    <?php foreach ($_SESSION['panier'] as $idProd => $details): ?>
                        <?php
                        $stmtNomProduit = $pdo->prepare("SELECT nom FROM Produit WHERE id_produit = ?");
                        $stmtNomProduit->execute([$idProd]);
                        $nomProduit = $stmtNomProduit->fetchColumn();
                        ?>
                        <li id="panier-item-<?= $idProd ?>">Produit: <?= htmlspecialchars($nomProduit) ?> - Quantité: <?= $details['quantite'] ?> - Action: <?= $details['action'] ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="get" class="mb-3 d-flex align-items-center gap-2 p-3">
            <input type="search" name="search" class="form-control" placeholder="Rechercher..." style="border-color: #232323value="<?= htmlspecialchars($search) ?>/>
            <div class="form-check">
                <input type="checkbox" id="afficher_inactifs" name="afficher_inactifs" value="1" class="form-check-input" style="border-color: #232323<?= $afficherInactifs ? 'checked' : '' ?>">
                <label for="afficher_inactifs" class="form-check-label">Afficher les produits inactifs</label>
            </div>
            <button type="submit" class="btn btn-primary" style="background-color: rgb(206, 0, 0);">Filtrer</button>
        </form>

        <form method="post" id="panierForm">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-secondary">
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Marque</th>
                        <th>Catégorie</th>
                        <th>Fournisseur</th>
                        <th>Stock actuel</th>
                        <th>Prix Vente</th>
                        <th>Quantité</th>
                        <th>Action</th>
                        <th>Enlever</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = $offset + 1;
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        $idProduit = $row['id_produit'];
                        $quantitePanier = $_SESSION['panier'][$idProduit]['quantite'] ?? 0;
                        $actionPanier = $_SESSION['panier'][$idProduit]['action'] ?? 'ajouter';
                        $estInactif = $row['actif'] == 0;
                        
                        // Calculer le stock théorique
                        $stockActuel = (int)$row['stock_actuel'];
                        $stockTheorique = $stockActuel;
                        if (isset($_SESSION['panier'][$idProduit])) {
                            if ($_SESSION['panier'][$idProduit]['action'] === 'ajouter') {
                                $stockTheorique = $stockActuel + $_SESSION['panier'][$idProduit]['quantite'];
                            } else {
                                $stockTheorique = $stockActuel - $_SESSION['panier'][$idProduit]['quantite'];
                            }
                        }
                    ?>
                        <tr <?= $estInactif ? 'class="table-secondary"' : '' ?>>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['nom']) . ' ' . htmlspecialchars($row['format']) ?></td>
                            <td><?= htmlspecialchars($row['nom_marque']) ?></td>
                            <td><?= htmlspecialchars($row['nom_categorie']) ?></td>
                            <td><?= htmlspecialchars($row['nom_fournisseur']) ?></td>
                            <td>
                                <span id="stock-<?= $idProduit ?>" class="<?= $stockTheorique != $stockActuel ? 'text-primary fw-bold' : '' ?>">
                                    <?= $stockTheorique ?>
                                    <?php if ($stockTheorique != $stockActuel): ?>
                                        <small class="text-muted">(<?= $stockActuel ?>)</small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td><?= number_format((float)$row['Vente'], 3, ',', ' ') ?> €</td>
                            <td>
                                <input
                                    type="number"
                                    name="quantites[]"
                                    min="0"
                                    max="1000"
                                    value="<?= htmlspecialchars($quantitePanier) ?>"
                                    class="form-control quantite-input"
                                    data-product-id="<?= $idProduit ?>"
                                    data-stock-actuel="<?= $stockActuel ?>"
                                    <?= $estInactif ? 'disabled' : '' ?>
                                />
                                <input type="hidden" name="id_produits[]" value="<?= $idProduit ?>" />
                            </td>
                            <td>
                                <select name="actions[]" class="form-select action-select" data-product-id="<?= $idProduit ?>" <?= $estInactif ? 'disabled' : '' ?>>
                                    <option value="ajouter" <?= $actionPanier === 'ajouter' ? 'selected' : '' ?>>Ajouter</option>
                                    <option value="retirer" <?= $actionPanier === 'retirer' ? 'selected' : '' ?>>Retirer</option>
                                </select>
                            </td>
                            <td>
                                <?php if (!$estInactif && $quantitePanier > 0): ?>
                                    <a href="?supprimer=<?= $idProduit ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce produit du panier ?');">X</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 p-3">
                <div>
                  <button type="submit" name="update_panier" class="btn btn-primary" style="background-color: #232323; color: white;">Mettre à jour le panier (Manuel)</button>
                  <button type="submit" name="vider_panier" class="btn btn-secondary" style="background-color: rgb(206, 0, 0); color: white;" <?= $panierNonVide ? '' : 'disabled' ?>>Vider le panier</button>
                </div>
                <div>
                    <button type="submit" name="valider_panier" class="btn btn-success" style="background-color:rgb(23, 192, 17); color: white;" <?= $panierNonVide ? '' : 'disabled' ?> onclick="return confirm('Êtes-vous sûr de vouloir valider cette commande ?');">Valider la commande</button>
                </div>
            </div>
        </form>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&afficher_inactifs=<?= $afficherInactifs ? 1 : 0 ?>">Précédent</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Précédent</span></li>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&afficher_inactifs=<?= $afficherInactifs ? 1 : 0 ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&afficher_inactifs=<?= $afficherInactifs ? 1 : 0 ?>">Suivant</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Suivant</span></li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let updateTimeout;
    
    // Fonction pour mettre à jour le panier via AJAX
    function updatePanier(productId, quantite, action) {
        const formData = new FormData();
        formData.append('ajax_update', '1');
        formData.append('id_produit', productId);
        formData.append('quantite', quantite);
        formData.append('action', action);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour l'affichage du stock
                const stockElement = document.getElementById('stock-' + productId);
                const stockActuel = parseInt(document.querySelector('[data-product-id="' + productId + '"]').dataset.stockActuel);
                
                if (data.stock_theorique !== stockActuel) {
                    stockElement.innerHTML = '<span class="text-primary fw-bold">' + data.stock_theorique + '</span> <small class="text-muted">(' + stockActuel + ')</small>';
                    stockElement.classList.add('stock-updated');
                } else {
                    stockElement.innerHTML = stockActuel;
                    stockElement.classList.remove('stock-updated');
                }
                
                // Mettre à jour le compteur du panier
                document.getElementById('panierCount').textContent = data.panier_count;
                
                // Mettre à jour l'affichage du contenu du panier (optionnel - recharger la page pour voir le détail complet)
                updatePanierDisplay(productId, quantite, action);
                
                // Effet visuel temporaire
                setTimeout(() => {
                    stockElement.classList.remove('stock-updated');
                }, 2000);
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
        });
    }
    
    // Fonction pour mettre à jour l'affichage du panier
    function updatePanierDisplay(productId, quantite, action) {
        const panierContent = document.getElementById('panierContent');
        if (!panierContent) return;
        
        const existingItem = document.getElementById('panier-item-' + productId);
        
        if (quantite > 0) {
            const itemText = 'Produit ID: ' + productId + ' - Quantité: ' + quantite + ' - Action: ' + action;
            
            if (existingItem) {
                existingItem.textContent = itemText;
            } else {
                const newItem = document.createElement('li');
                newItem.id = 'panier-item-' + productId;
                newItem.textContent = itemText;
                panierContent.appendChild(newItem);
            }
        } else {
            if (existingItem) {
                existingItem.remove();
            }
        }
    }
    
    // Écouter les changements sur les champs quantité
    document.querySelectorAll('.quantite-input').forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(updateTimeout);
            const productId = this.dataset.productId;
            const quantite = parseInt(this.value) || 0;
            const actionSelect = document.querySelector('.action-select[data-product-id="' + productId + '"]');
            const action = actionSelect ? actionSelect.value : 'ajouter';
            
            // Attendre 500ms après la dernière frappe avant de mettre à jour
            updateTimeout = setTimeout(() => {
                updatePanier(productId, quantite, action);
            }, 500);
        });
    });
    
    // Écouter les changements sur les sélecteurs d'action
    document.querySelectorAll('.action-select').forEach(select => {
        select.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const quantiteInput = document.querySelector('.quantite-input[data-product-id="' + productId + '"]');
            const quantite = quantiteInput ? (parseInt(quantiteInput.value) || 0) : 0;
            
            if (quantite > 0) {
                updatePanier(productId, quantite, this.value);
            }
        });
    });
});
</script>
</body>
</html>