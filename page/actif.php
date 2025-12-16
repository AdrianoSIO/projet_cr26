<title>Produits Actifs</title>
<?php include '../visuel/barre.php'; ?>

<div class="container my-4">
    <div class="card">
        <div class="card-header text-center" style="background-color: #232323; color: white; font-size: 1.5rem;">
            Liste des produits actifs
        </div>
        <div class="card-body">

        <?php
        include '../donnée/connect.php';
        // Traitement pour rendre un produit inactif
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rendre_inactif'])) {
            $idProduit = (int)$_POST['id_produit'];
            $update = $pdo->prepare("UPDATE Produit SET Actif = 0 WHERE id_produit = :id");
            $update->execute([':id' => $idProduit]);
            header("Location: " . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
            exit;
        }

        // Recherche
        $recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';//trim enleve les espaces invisibles avant et après la chaîne 

        // Pagination
        $parPage = 20;
        // Vérifie si 'page' existe dans l'URL, est un nombre et supérieur à 0, sinon on met 1 par défaut
        $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $parPage;

        // Compter total produits actifs avec filtre
        $countSql = "SELECT COUNT(*) FROM Produit WHERE Actif = 1";
        $params = [];
        if (!empty($recherche)) {
            $countSql .= " AND (nom LIKE :search OR format LIKE :search)";
            $params[':search'] = '%' . $recherche . '%';
        }
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Requête principale
        $sql = "SELECT id_produit, nom, format FROM Produit WHERE Actif = 1";
        if (!empty($recherche)) {
            $sql .= " AND (nom LIKE :search OR format LIKE :search)";
        }
        $sql .= " ORDER BY id_produit ASC LIMIT :offset, :parPage";

        $stmt = $pdo->prepare($sql);
        if (!empty($recherche)) {
            $stmt->bindValue(':search', '%' . $recherche . '%', PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':parPage', $parPage, PDO::PARAM_INT);
        $stmt->execute();

        // Construction de l'URL de base pour pagination
        $queryParams = $_GET;
        unset($queryParams['page']);
        $urlBase = basename($_SERVER['PHP_SELF']) . '?' . http_build_query($queryParams) . '&';
        ?><!-- unset($queryParams['page']) supprime le paramètre de pagination pour éviter les doublons, 
        basename($_SERVER['PHP_SELF']) récupère le nom du fichier courant, 
        et http_build_query($queryParams) reconstruit l'URL avec tous les filtres 
        sauf la page, 
        facilitant la création de liens de pagination propres.-->

        <!-- Formulaire de recherche -->
        <form method="get" class="mb-3">
            <div class="input-group">
                <input type="text" name="recherche" class="form-control" placeholder="Rechercher par nom ou format..." value="<?= htmlspecialchars($recherche) ?>">
                <button type="submit" class="btn btn-dark">Rechercher</button>
            </div>
        </form>

        <div class="card mt-3">
            <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.2rem;">
                Produits Actifs
            </div>
            <div class="card-body">
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
                                        <button type="submit" name="rendre_inactif" class="btn btn-danger btn-sm" style="background-color: #232323;"onclick="return confirm('Êtes-vous sûr de vouloir désactiver ce produit ?');">Rendre inactif</button><!-- affiche une boîte de confirmation lors du clic et n'exécute l'action que si l'utilisateur confirme.-->
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                        <tr>
                    <td colspan="4" class="text-center">
                        <a href="../communication/panneau.php" class="btn btn-secondary w-70 mt-3" style="background-color: rgb(206, 0, 0); color: white; font-size: 1rem;">Retour</a>
                    </td>
                  </tr>
                    </table>
                </div>

                <!-- Pagination -->
                <?php include '../visuel/pagination.php'; ?>

            </div>
        </div>

        </div>
    </div>
</div>
