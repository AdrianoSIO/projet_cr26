<title>Définition des Produits</title>
<?php
require_once '../donnée/connect.php';
require_once '../visuel/barre.php';
// Récupérer tous les produits
$produits = $pdo->query("SELECT id_produit, nom, format, id_marque, id_categorie, id_fournisseur, HT, reduction FROM Produit")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les catégories et fournisseurs
$categories = $pdo->query("SELECT id_categorie, nom FROM Categorie")->fetchAll(PDO::FETCH_KEY_PAIR);
$fournisseurs = $pdo->query("SELECT id_fournisseur, nom FROM Fournisseur")->fetchAll(PDO::FETCH_KEY_PAIR);
$marques = $pdo->query("SELECT id_marque, nom FROM Marque")->fetchAll(PDO::FETCH_KEY_PAIR);
// Traitement du changement (simple exemple)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produit'])) {
    $sql = "UPDATE Produit SET ";
    $fields = [];
    $params = [];

    if (isset($_POST['id_categorie'])) {
        $fields[] = "id_categorie = ?";
        $params[] = $_POST['id_categorie'];
    }

    if (isset($_POST['id_fournisseur'])) {
        $fields[] = "id_fournisseur = ?";
        $params[] = $_POST['id_fournisseur'];
    }

    if (isset($_POST['id_marque'])) {
        $fields[] = "id_marque = ?";
        $params[] = $_POST['id_marque'];
    }

    if (isset($_POST['reduction'])) {
        $fields[] = "reduction = ?";
        $params[] = $_POST['reduction'];

        // Mettre promo à 0 si reduction = 0, sinon promo = 1
        $fields[] = "promo = ?";
        $params[] = ($_POST['reduction'] == 0) ? 0 : 1;
    }

    if (!empty($fields)) {
        $sql .= implode(', ', $fields) . " WHERE id_produit = ?";
        $params[] = $_POST['id_produit'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    header("Location: groupe.php");
    exit;
}


if ($produits):
?>
<div class="container my-4">
  <div class="card">
  <div class="card-header text-center" style="background-color:  rgb(206,0,0); color: white; font-size: 1.5rem;">
    Liste des produits
  </div>
  <div class="card-body">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Produit</th>
                <th>Nom</th>
                <th>Format</th>
                <th>Marque</th>
                <th>Catégorie</th>
                <th>Fournisseur</th>
                <th>HT</th>
                <th>Réduction (%)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $produit): ?>
                <tr>
                    <td><?= htmlspecialchars($produit['id_produit']) ?></td>
                    <td><?= $produit['nom'] ?></td>
                    <td><?= $produit['format'] ?></td>
                    <td>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                            <select name="id_marque" onchange="this.form.submit()">
                                <?php foreach ($marques as $id => $nom): ?>
                                    <option value="<?= $id ?>" <?= $id == $produit['id_marque'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nom) ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if ($produit['id_marque'] === null): ?>
                                    <option value="" selected>Inconnu</option>
                                <?php endif; ?>
                            </select>
                        </form>
                    </td>
                    <td>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                            <select name="id_categorie" onchange="this.form.submit()">
                                <?php foreach ($categories as $id => $nom): ?>
                                    <option value="<?= $id ?>" <?= $id == $produit['id_categorie'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nom) ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if ($produit['id_categorie'] === null): ?>
                                    <option value="" selected>Inconnu</option>
                                <?php endif; ?>
                            </select>
                        </form>
                    </td>
                    <td>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                            <select name="id_fournisseur" onchange="this.form.submit()">
                                <?php foreach ($fournisseurs as $id => $nom): ?>
                                    <option value="<?= $id ?>" <?= $id == $produit['id_fournisseur'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nom) ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if ($produit['id_fournisseur'] === null): ?>
                                    <option value="" selected>Inconnu</option>
                                <?php endif; ?>
                            </select>
                        </form>
                    </td>
                    <td><?= $produit['HT'] !== null ? htmlspecialchars($produit['HT']) : 'Inconnu' ?></td>
                    <td>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                            <select name="reduction" onchange="this.form.submit()">
                                <?php foreach ([0,5, 10, 15, 20] as $val): ?>
                                    <option value="<?= $val ?>" <?= $produit['reduction'] == $val ? 'selected' : '' ?>>
                                        <?= $val ?>%
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
