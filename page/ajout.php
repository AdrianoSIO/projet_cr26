<title>Ajout produits</title>
<?php include '../visuel/barre.php'; ?> 

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
            Nouveauté ou Futur ajout de produit
        </div>
        <div class="card-body">
            <?php
            include '../donnée/connect.php';
            $message = '';
    
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajout_produit'])) {
                $nom = trim($_POST['nom'] ?? '');
                $format = trim($_POST['format'] ?? '');
                $format = $format === '' ? null : $format;
                $actif = ($_POST['actif'] ?? '1') === '1' ? 1 : 0;
                $id_marque = $_POST['id_marque'] ?? null;
                $id_categorie = $_POST['id_categorie'] ?? null;

                $stock_actuel = is_numeric($_POST['stock_actuel'] ?? '') ? intval($_POST['stock_actuel']) : 0;
                $seuil_min = is_numeric($_POST['seuil_min'] ?? '') ? intval($_POST['seuil_min']) : 0;
                $seuil_max = is_numeric($_POST['seuil_max'] ?? '') ? intval($_POST['seuil_max']) : 0;

                if ($nom && $id_marque && $id_categorie) {
                    try {
                        $checkSql = "SELECT COUNT(*) FROM Produit 
                                     WHERE LOWER(nom) = LOWER(:nom) 
                                     AND id_marque = :id_marque 
                                     AND id_categorie = :id_categorie 
                                     AND id_fournisseur = :id_fournisseur
                                     AND ((format IS NULL AND :format IS NULL) OR LOWER(format) = LOWER(:format))";
                        $check = $pdo->prepare($checkSql);
                        $check->execute([
                            ':nom' => $nom,
                            ':format' => $format,
                            ':id_marque' => $id_marque,
                            ':id_categorie' => $id_categorie,
                            ':id_fournisseur' => $id_fournisseur
                        ]);
                        $exists = $check->fetchColumn();

                        if ($exists > 0) {
                            $message = '<div class="alert alert-warning">Ce produit existe déjà avec les mêmes paramètres.</div>';
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO Produit (nom, format, actif, id_marque, id_categorie, id_fournisseur)
                                                   VALUES (:nom, :format, :actif, :id_marque, :id_categorie, :id_fournisseur)");
                            $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
                            $stmt->bindValue(':format', $format, $format === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                            $stmt->bindValue(':actif', $actif, PDO::PARAM_INT);
                            $stmt->bindValue(':id_marque', $id_marque, PDO::PARAM_INT);
                            $stmt->bindValue(':id_categorie', $id_categorie, PDO::PARAM_INT);
                            $stmt->bindValue(':id_fournisseur', $id_fournisseur, PDO::PARAM_INT);
                            $stmt->execute();

                            $id_produit = $pdo->lastInsertId();

                            $stmtStock = $pdo->prepare("INSERT INTO QuantiteStock (id_produit, stock_actuel, seuil_min, seuil_max)
                                                        VALUES (:id_produit, :stock_actuel, :seuil_min, :seuil_max)");
                            $stmtStock->execute([
                                ':id_produit' => $id_produit,
                                ':stock_actuel' => $stock_actuel,
                                ':seuil_min' => $seuil_min,
                                ':seuil_max' => $seuil_max
                            ]);

                            $message = '<div class="alert alert-success">Produit ajouté avec succès, avec ses paramètres de stock.</div>';
                        }
                    } catch (PDOException $e) {
                        $message = '<div class="alert alert-danger">Erreur lors de l\'ajout : ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                } else {
                    $message = '<div class="alert alert-warning">Veuillez remplir tous les champs obligatoires.</div>';
                }
            }

            try {
                $stmt = $pdo->query("SELECT id_marque, nom FROM Marque ORDER BY nom");
                $marques = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Erreur lors de la récupération des marques : ' . htmlspecialchars($e->getMessage()) . '</div>';
            }

            try {
                $stmt = $pdo->query("SELECT id_categorie, nom FROM Categorie ORDER BY nom");
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Erreur lors de la récupération des catégories : ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>

            <?= $message ?>
            <form method="post" id="form-ajout">
    <input type="hidden" name="ajout_produit" value="1">

    <div class="form-group" style=" font-size: 1rem; text-align: center;">
        <label>Nom</label>
        <input type="text" style="border-color: #232323;" name="nom" class="form-control" required>
    </div>

    <div class="form-group"style="  font-size: 1rem; text-align: center;">
        <label>Format</label>
        <input type="text" name="format" class="form-control" style="border-color: #232323;">
    </div>

    <div class="form-group"style="  font-size: 1rem; text-align: center;">
        <label>Actif</label>
        <select name="actif" class="form-control" style="border-color: #232323;">
            <option value="1" selected>Oui</option>
            <option value="0">Non</option>
        </select>
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Marque</label>
        <select name="id_marque" class="form-control"style="border-color: #232323;"  required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($marques as $marque): ?>
                <option value="<?= $marque['id_marque'] ?>"><?= htmlspecialchars($marque['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Catégorie</label>
        <select name="id_categorie" class="form-control" style="border-color: #232323;" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id_categorie'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Fournisseur</label>
        <select name="id_fournisseur" class="form-control" style="border-color: #232323;" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($fournisseurs as $fr): ?>
                <option value="<?= $fr['id_fournisseur'] ?>"><?= htmlspecialchars($fr['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Stock actuel</label>
        <input type="number" name="stock_actuel" class="form-control" min="0" value="0" style="border-color: #232323;">
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Seuil</label>
        <input type="number" name="seuil" class="form-control" min="0" value="0" style="border-color: #232323;">
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Quantité (Qte)</label>
        <input type="number" name="qte" class="form-control" min="0" value="0" style="border-color: #232323;">
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Prix HT</label>
        <input type="number" step="0.01" name="HT" class="form-control" value="0" style="border-color: #232323;" >
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Réduction</label>
        <input type="number" step="0.01" name="reduction" class="form-control" value="0" style="border-color: #232323;">
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Pourcentage</label>
        <input type="number" step="0.01" name="pourcentage" class="form-control" value="0" style="border-color: #232323;">
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Prix de vente</label>
        <input type="number" step="0.01" name="vente" class="form-control" value="0"style="border-color: #232323;" >
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>TTC</label>
        <input type="number" step="0.01" name="TTC" class="form-control" value="0" style="border-color: #232323;" >
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Prix Unitaire</label>
        <input type="number" step="0.01" name="U" class="form-control" value="0" style="border-color: #232323;">
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Estimation</label>
        <input type="number" step="0.01" name="estimation" class="form-control" value="0" style="border-color: #232323;">
    </div>

    <div class="form-group"style=" font-size: 1rem; text-align: center;">
        <label>Marge</label>
        <input type="number" step="0.01" name="marge" class="form-control" value="0" style="border-color: #232323;">
    </div>

<button type="button" class="btn btn-primary mt-3" style="background-color:rgb(23, 192, 17); color:white;" onclick="verifierProduit()">Ajouter</button>
</form>

                </div>
                <tr>
                    <td colspan="4" class="text-center">
                        <a href="../communication/panneau.php" class="btn btn-secondary w-70 mt-3" style="background-color: rgb(206, 0, 0);  font-size: 1rem;">Retour</a>
                    </td>
                  </tr>
            </form>
            
        </div>
    </div>
</div>


<!-- Modal Bootstrap -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">Produit déjà existant</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" id="modalBodyText">
        Ce produit existe déjà. Voulez-vous quand même l'ajouter ?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btnOui">Oui (annuler)</button>
        <button type="button" class="btn btn-danger" id="btnNon">Non, ajouter quand même</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script>
function verifierProduit() {
    // Récupère le formulaire avec l'id 'form-ajout
    const form = document.getElementById('form-ajout');
    // Crée un objet FormData à partir du formulaire (pour envoyer les données du formulaire)
    const formData = new FormData(form);
    // Récupère la valeur du champ 'nom' (nom du produit)
    const nomProduit = document.getElementById('nom').value;

    // Envoie une requête POST à 'chack_produit.php' avec les données du formulaire
    fetch('chack_produit.php', {
        method: 'POST',
        body: formData
    })
    // Quand la réponse arrive, la transforme en JSON
    .then(res => res.json()) // échange des données en JSON entre serveur et navigateur
    .then(data => {
        // Si le produit existe déjà (réponse du serveur)
        if (data.exists) {
            // Modifie le texte du corps du modal pour prévenir l'utilisateur
            document.getElementById('modalBodyText').textContent = 
                `Le produit "${nomProduit}" existe déjà. Voulez-vous quand même l'ajouter ?`;

            // Récupère l'élément du modal de confirmation
            const modalElement = document.getElementById('confirmModal');
            // Crée une instance du modal Bootstrap
            const modal = new bootstrap.Modal(modalElement);
            // Affiche le modal
            modal.show();

            // Pour éviter que les anciens événements restent, on clone les boutons Oui et Non
            const oldBtnOui = document.getElementById('btnOui');
            const newBtnOui = oldBtnOui.cloneNode(true);
            oldBtnOui.parentNode.replaceChild(newBtnOui, oldBtnOui);
            // replaceChild remplace l'ancien bouton par le nouveau, ce qui permet de réinitialiser les événements.
            const oldBtnNon = document.getElementById('btnNon');
            const newBtnNon = oldBtnNon.cloneNode(true);
            oldBtnNon.parentNode.replaceChild(newBtnNon, oldBtnNon);

            // Quand on clique sur Oui, on ferme juste le modal (le produit n'est pas ajouté)
            newBtnOui.onclick = function () {
                modal.hide();
            };

            // Quand on clique sur Non, on ferme le modal ET on soumet le formulaire (le produit est ajouté)
            newBtnNon.onclick = function () { //definit l'event non
                modal.hide(); // ferme le modal
                form.submit(); // soumet le formulaire pour ajouter le produit
            };

        } else {
            // Si le produit n'existe pas, on soumet directement le formulaire
            form.submit();
        }
    })
    // Si une erreur se produit lors de la requête
    .catch(err => { // en cas d'erreur
        console.error("Erreur lors de la vérification :", err); // affiche l'erreur dans la console
        alert("Erreur lors de la vérification du produit."); // affiche une alerte à l'utilisateur
    });
}
</script>
<script src="../donnée/verif.js"></script>