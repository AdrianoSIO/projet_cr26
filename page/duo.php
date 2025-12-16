<?php
include '../donnée/connect.php';
include '../visuel/barre.php';

// Récupération des produits pour les listes déroulantes
$stmtProduits = $pdo->query("SELECT id_produit, nom, format FROM Produit ORDER BY nom ASC");
$produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

// Récupération des associations existantes
$stmtAssoc = $pdo->query("SELECT id_produit1, id_produit2 FROM Assoc ORDER BY id_produit1 ASC");
$associations = $stmtAssoc->fetchAll(PDO::FETCH_ASSOC);

// Traitement de l'ajout d'une association
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_assoc'])) {
    $id_produit1 = (int)($_POST['id_produit1'] ?? 0);
    $id_produit2 = (int)($_POST['id_produit2'] ?? 0);

    if ($id_produit1 > 0 && $id_produit2 > 0) {
        // Insertion dans la table Assoc
        $stmt = $pdo->prepare("INSERT INTO Assoc (id_produit1, id_produit2) VALUES (:id_produit1, :id_produit2)");
        $stmt->execute([
            ':id_produit1' => $id_produit1,
            ':id_produit2' => $id_produit2
        ]);

        // Mise à jour de la colonne association dans la table Produit
        $stmtUpdate = $pdo->prepare("UPDATE Produit SET association = 1 WHERE id_produit IN (:id_produit1, :id_produit2)");
        $stmtUpdate->execute([
            ':id_produit1' => $id_produit1,
            ':id_produit2' => $id_produit2
        ]);

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Traitement de la suppression d'une association
if (isset($_POST['supprimer_assoc']) && is_numeric($_POST['_supprimer'])) {
    $id_assoc = (int)$_POST['_supprimer'];
    $stmt = $pdo->prepare("DELETE FROM Assoc WHERE id_assoc = :id_assoc");
    $stmt->execute([':id_assoc' => $id_assoc]);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<!-- Gestion des Associations -->
<div class="container my-4">
    <div class="card mb-4">
        <div class="card-header" style="background-color: #232323; color: #fff;">
            Gestion des Associations
        </div>
        <div class="card-body">
            <!-- Formulaire d'ajout d'association -->
            <form method="post" class="row g-3 mb-3">
                <div class="col-md-5">
                    <select name="id_produit1" class="form-control" required>
                        <option value="" disabled selected>Choisir produit 1</option>
                        <?php foreach ($produits as $produit): ?>
                            <option value="<?= htmlspecialchars($produit['id_produit']) ?>">
                                <?= htmlspecialchars($produit['nom'] . ' ' . $produit['format']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <select name="id_produit2" class="form-control" required>
                        <option value="" disabled selected>Choisir produit 2</option>
                        <?php foreach ($produits as $produit): ?>
                            <option value="<?= htmlspecialchars($produit['id_produit']) ?>">
                                <?= htmlspecialchars($produit['nom'] . ' ' . $produit['format']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="ajouter_assoc" class="btn btn-success w-100">Ajouter l'association</button>
                </div>
            </form>

            <!-- Tableau des associations -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Produit 1</th>
                            <th>Produit 2</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($associations)) : ?>
                            <tr><td colspan="3" class="text-center">Aucune association trouvée.</td></tr>
                        <?php else : ?>
                            <?php foreach ($associations as $assoc) : ?>
                                <tr>
                                    <td>
                                        <?php
                                        $produit1 = array_filter($produits, fn($p) => $p['id_produit'] == $assoc['id_produit1']);
                                        echo htmlspecialchars(reset($produit1)['nom'] . ' ' . reset($produit1)['format']);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $produit2 = array_filter($produits, fn($p) => $p['id_produit'] == $assoc['id_produit2']);
                                        echo htmlspecialchars(reset($produit2)['nom'] . ' ' . reset($produit2)['format']);
                                        ?>
                                    </td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="_supprimer" value="<?= htmlspecialchars($assoc['id_assoc'] ?? '') ?>">
                                            <button type="submit" name="supprimer_assoc" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette association ?');">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
