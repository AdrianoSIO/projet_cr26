<?php
$title = "Gestion des Fournisseurs";
include '../visuel/barre.php';
include '../donnée/connect.php';


// Ajouter un fournisseur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nom'])) {
    $nom = trim($_POST['nom']);
    if ($nom !== '') {
        $stmt = $pdo->prepare("INSERT INTO Fournisseur (nom) VALUES (:nom)");
        $stmt->execute([':nom' => $nom]);
    }
}

// Supprimer un fournisseur
if (isset($_GET['supprimer']) && is_numeric($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM Fournisseur WHERE no = :id");
    $stmt->execute([':id' => $id]);
}

// Récupérer la liste des fournisseurs
$stmt = $pdo->query("SELECT id_fournisseur, nom FROM Fournisseur ORDER BY id_fournisseur ASC");
$fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>h
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <div class="card">
        <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
            Gestion des Fournisseurs
        </div>
        <div class="card-body">

            <!-- Formulaire d'ajout -->
            <form method="post" class="row g-3 mb-4">
                <div class="col-md-8">
                    <input type="text" name="nom" class="form-control" placeholder="Nom du fournisseur" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100" style="background-color: rgb(206, 0, 0)">Ajouter</button>
                </div>
            </form>

            <!-- Tableau des fournisseurs -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fournisseurs)) : ?>
                            <tr><td colspan="3" class="text-center">Aucun fournisseur trouvé.</td></tr>
                        <?php else : ?>
                            <?php foreach ($fournisseurs as $f): ?>
                                <tr>
                                    <td><?= htmlspecialchars($f['id_fournisseur']) ?></td>
                                    <td><?= htmlspecialchars($f['nom']) ?></td>
                                    <td>
                                        <a href="?supprimer=<?= $f['id_fournisseur'] ?>" class="btn btn-danger btn-sm" style="background-color: #232323;" onclick="return confirm('Supprimer ce fournisseur ?');">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    
                </table>
                <tr>
                    <td colspan="4" class="text-center">
                        <a href="../communication/panneau.php" class="btn btn-secondary w-70 mt-3" style="background-color: rgb(206, 0, 0); color: white; font-size: 1rem;">Retour</a>
                    </td>
                  </tr>
            </div>

        </div>
    </div>
</div>
</body>
</html>