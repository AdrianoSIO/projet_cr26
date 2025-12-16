<title>Marge</title>
<?php include '../visuel/barre.php'; ?>

<style>
  .table-wrapper {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
  }
  thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 10;
    border-bottom: 2px solid #ddd;
  }
</style>

<div class="container my-4">
  <div class="card">
    <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
      Informations sur les produits
    </div>
    <div class="card-body">

      <!-- FORMULAIRE DE RECHERCHE -->
      <form method="get" class="mb-3">
        <div class="input-group">
          <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Rechercher par nom ou unité"
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
          />
          <button type="submit" class="btn btn-primary">Rechercher</button>
        </div>
      </form>

      <?php
      include '../donnée/connect.php';

      try {
          $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
          die('<div class="alert alert-danger">Erreur de connexion : ' . htmlspecialchars($e->getMessage()) . '</div>');
      }

      // Mise à jour en POST (inchangée)
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $id = key($_POST['qte']);
          $id = (int)$id;
          $qte = isset($_POST['qte'][$id]) ? (float)$_POST['qte'][$id] : 0;
          $HT = isset($_POST['HT'][$id]) ? (float)$_POST['HT'][$id] : 0;
          $Vente = isset($_POST['Vente'][$id]) ? (float)$_POST['Vente'][$id] : 0;
          $pourcentage = isset($_POST['pourcentage'][$id]) ? (float)$_POST['pourcentage'][$id] : 5.50;
          $reduction = isset($_POST['reduction'][$id]) ? (int)$_POST['reduction'][$id] : 0;

          $promo = ($reduction > 0) ? 1 : 0;

          $updateSql = "UPDATE Produit SET qte = :qte, HT = :HT, pourcentage = :pourcentage, Vente = :Vente, reduction = :reduction, promo = :promo WHERE id_produit = :id";
          $stmt = $pdo->prepare($updateSql);
          $stmt->execute([
              ':qte' => $qte,
              ':HT' => $HT,
              ':pourcentage' => $pourcentage,
              ':Vente' => $Vente,
              ':reduction' => $reduction,
              ':promo' => $promo,
              ':id' => $id,
          ]);

          header("Location: ?page=" . ($_GET['page'] ?? 1) . "&row=" . $id . (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''));
          exit;
      }

      // Pagination
      $parPage = 30;
      $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
      $offset = ($page - 1) * $parPage;

      // Recherche
      $search = trim($_GET['search'] ?? '');

      // Requête COUNT avec filtre si recherche
      if ($search !== '') {
          $countStmt = $pdo->prepare("SELECT COUNT(*) FROM Produit WHERE nom LIKE :search OR U LIKE :search");
          $countStmt->execute([':search' => "%$search%"]);
          $total = (int)$countStmt->fetchColumn();

          $sqlProduit = "SELECT * FROM Produit WHERE nom LIKE :search OR U LIKE :search ORDER BY nom ASC LIMIT :offset, :parPage";
          $stmtProduit = $pdo->prepare($sqlProduit);
          $stmtProduit->bindValue(':search', "%$search%", PDO::PARAM_STR);
      } else {
          $countStmt = $pdo->prepare("SELECT COUNT(*) FROM Produit");
          $countStmt->execute();
          $total = (int)$countStmt->fetchColumn();

          $sqlProduit = "SELECT * FROM Produit ORDER BY nom ASC LIMIT :offset, :parPage";
          $stmtProduit = $pdo->prepare($sqlProduit);
      }
      $stmtProduit->bindValue(':offset', $offset, PDO::PARAM_INT);
      $stmtProduit->bindValue(':parPage', $parPage, PDO::PARAM_INT);
      $stmtProduit->execute();

      // Préparer une fonction pour récupérer noms associés (cache pour éviter trop de requêtes)
      $associatedNamesCache = [];

    function getAssociatedProductNames($pdo, $ids) {
        global $associatedNamesCache;

        $ids = array_filter([$id_produit1 ?? 0, $id_produit2 ?? 0], function($id) {
          return !empty($id) && $id > 0;
        });

        $names = [];

        foreach ($ids as $id) {
          if (isset($associatedNamesCache[$id])) {
            $names[] = $associatedNamesCache[$id];
          } else {
            $stmt = $pdo->prepare("SELECT nom, format FROM Produit WHERE id_produit = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $name = $result['nom'] ?? '';
            $format = $result['format'] ?? '';
            $associatedNamesCache[$id] = ['name' => $name, 'format' => $format];
            if ($name) $names[] = $name;
          }
        }

        return implode(', ', $names);
    }
      ?>

      <div class="table-wrapper">
        <table class="table table-bordered align-middle">
          <thead class="table-secondary">
            <tr>
              <th style="min-width:60px;">ID</th>
              <th style="min-width:60px;">Nom</th>
              <th style="min-width:60px;">Quantité</th>
              <th style="min-width:110px;">Prix HT</th>
              <th style="min-width:60px;">Reduction (%)</th>
              <th style="min-width:60px;">HT avec Reduc</th>
              <th style="min-width:110px;">TVA (%)</th>
              <th style="min-width:60px;">Prix TTC</th>
              <th style="min-width:60px;">Prix Unitaire</th>
              <th style="min-width:60px;">Estimation</th>
              <th style="min-width:110px;">Prix de Vente</th>
              <th style="min-width:60px;">Marge</th>
              <th style="min-width:150px;">Produits associés</th> <!-- Nouvelle colonne -->
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $stmtProduit->fetch(PDO::FETCH_ASSOC)) :
              $isUpdated = (isset($_GET['row']) && $_GET['row'] == $row['id_produit']);

              // Récupérer produits associés si association=1
              $associatedProducts = '';
              if (!empty($row['association']) && $row['association'] == 1) {
                  $assocStmt = $pdo->prepare("SELECT id_produit1, id_produit2 FROM Assoc WHERE id_produit1 = :id OR id_produit2 = :id");
                  $assocStmt->execute([':id' => $row['id_produit']]);
                  $assocResults = $assocStmt->fetchAll(PDO::FETCH_ASSOC);

                  $associatedIds = [];
                  foreach ($assocResults as $assocRow) {
                      if ($assocRow['id_produit1'] != $row['id_produit']) {
                          $associatedIds[] = $assocRow['id_produit1'];
                      }
                      if ($assocRow['id_produit2'] != $row['id_produit']) {
                          $associatedIds[] = $assocRow['id_produit2'];
                      }
                  }

                  if (!empty($associatedIds)) {
                      $associatedProducts = getAssociatedProductNames($pdo, ...$associatedIds);
                  }
              }
                  
                  
              
            ?>
              <form method="post" action="?page=<?= $page ?>&<?= $search !== '' ? 'search=' . urlencode($search) : '' ?>">
                <tr id="row-<?= $row['id_produit'] ?>" class="<?= $isUpdated ? 'table-success' : '' ?>">
                  <td><?= htmlspecialchars($row['id_produit']) ?></td>
                  <td><?= htmlspecialchars($row['nom']) ?> <?= htmlspecialchars($row['format']) ?></td>
                  <td>
                    <input type="number" step="1" class="form-control form-control-sm" name="qte[<?= $row['id_produit'] ?>]" value="<?= htmlspecialchars($row['qte']) ?>" min="0" onchange="this.form.submit()">
                  </td>
                  <td class="d-flex align-items-center">
                    <input type="number" step="0.20" class="form-control form-control-sm" name="HT[<?= $row['id_produit'] ?>]" value="<?= htmlspecialchars($row['HT']) ?>" onchange="this.form.submit()">
                    <span class="ms-1">€</span>
                  </td>
                  <td>
                    <select name="reduction[<?= $row['id_produit'] ?>]" class="form-select form-select-sm" onchange="this.form.submit()">
                      <option value="0" <?= ($row['reduction'] == 0) ? 'selected' : '' ?>>0%</option>
                      <option value="5" <?= ($row['reduction'] == 5) ? 'selected' : '' ?>>5%</option>
                      <option value="10" <?= ($row['reduction'] == 10) ? 'selected' : '' ?>>10%</option>
                      <option value="15" <?= ($row['reduction'] == 15) ? 'selected' : '' ?>>15%</option>
                      <option value="20" <?= ($row['reduction'] == 20) ? 'selected' : '' ?>>20%</option>
                    </select>
                  </td>
                  <td><input type="text" class="form-control-plaintext" readonly value="<?= htmlspecialchars($row['prix_final']) ?> €"></td>
                  <td>
                    <select name="pourcentage[<?= $row['id_produit'] ?>]" class="form-select form-select-sm" onchange="this.form.submit()">
                      <option value="5.50" <?= ($row['pourcentage'] == 5.50) ? 'selected' : '' ?>>5.50%</option>
                      <option value="20" <?= ($row['pourcentage'] == 20) ? 'selected' : '' ?>>20%</option>
                    </select>
                  </td>
                  <td><input type="text" class="form-control-plaintext" readonly value="<?= htmlspecialchars($row['TTC']) ?> €"></td>
                  <td><input type="text" class="form-control-plaintext" readonly value="<?= htmlspecialchars($row['U']) ?> €"></td>
                  <td><input type="text" class="form-control-plaintext" readonly value="<?= htmlspecialchars($row['Estimation']) ?> €"></td>
                  <td class="d-flex align-items-center">
                    <input type="number" step="0.20" class="form-control form-control-sm" name="Vente[<?= $row['id_produit'] ?>]" value="<?= htmlspecialchars($row['Vente']) ?>" onchange="this.form.submit()">
                    <span class="ms-1">€</span>
                  </td>
                  <td><input type="text" class="form-control-plaintext" readonly value="<?= htmlspecialchars($row['Marge']) ?> €"></td>
                  <td><input type="text" class="form-control-plaintext" readonly value="<?= htmlspecialchars($associatedProducts) ?>"></td> <!-- Champ produits associés -->
                </tr>
              </form>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <tr>
        <td colspan="4" class="text-center">
          <a href="../communication/panneau.php" class="btn btn-secondary w-70 mt-3" style="background-color: rgb(206, 0, 0); color: white; font-size: 1rem;">Retour</a>
        </td>
      </tr>

      <?php include '../visuel/pagination.php'; ?>

    </div>
  </div>
</div>

<?php if (isset($_GET['row'])) : ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const row = document.getElementById("row-<?= (int)$_GET['row'] ?>");
      if (row) {
        row.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
      }
    });
  </script>
<?php endif; ?>
