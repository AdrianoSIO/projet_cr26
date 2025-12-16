<?php
include '../donnée/connect.php'; // Inclut le fichier de connexion à la base de données

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Définit le mode d'erreur de PDO sur Exception
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Erreur de connexion à la base de données : ' . htmlspecialchars($e->getMessage()) . '</div>'; // Affiche un message d'erreur si la connexion échoue
    exit; // Arrête l'exécution du script en cas d'erreur
}

$message = ''; // Initialise la variable pour les messages d'information ou d'erreur
$marqueProche = ''; // Initialise la variable pour stocker une marque similaire trouvée

// Traitement de l'ajout de marque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nouvelle_marque'])) { // Vérifie si le formulaire d'ajout a été soumis
    $nouvelleMarque = trim($_POST['nouvelle_marque']); // Récupère et nettoie le nom de la nouvelle marque
    $confirmer = $_POST['confirmer_insertion'] ?? 'non'; // Récupère la confirmation d'insertion ou 'non' par défaut

    if ($nouvelleMarque !== '') { // Vérifie que le champ n'est pas vide
        // Vérifie si la marque existe exactement
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM Marque WHERE LOWER(nom) = LOWER(:nom)"); // Prépare une requête pour vérifier l'existence exacte de la marque
        $stmtCheck->execute([':nom' => $nouvelleMarque]); // Exécute la requête avec le nom fourni
        $existe = $stmtCheck->fetchColumn(); // Récupère le résultat (nombre d'occurrences)

        if ($existe > 0) { // Si la marque existe déjà
            $message = '<div class="alert alert-warning text-center">La marque <strong>' . htmlspecialchars($nouvelleMarque) . '</strong> existe déjà.</div>'; // Affiche un message d'avertissement
        } else {
            // Vérifie les marques similaires ou contenant l'une l'autre
            $stmtAll = $pdo->query("SELECT nom FROM Marque"); // Récupère tous les noms de marques existants
            $trouveProche = false; // Initialise le drapeau de marque similaire trouvée

            while ($row = $stmtAll->fetch(PDO::FETCH_ASSOC)) { // Parcourt chaque marque existante
                $marqueExistante = strtolower($row['nom']); // Met le nom existant en minuscules
                $input = strtolower($nouvelleMarque); // Met le nom saisi en minuscules

                $distance = levenshtein($input, $marqueExistante); // Calcule la distance de Levenshtein entre les deux noms
                similar_text($input, $marqueExistante, $pourcentage); // Calcule le pourcentage de similarité entre les deux noms

                // Sous-chaîne stricte ou similarité
                if (
                    strpos($marqueExistante, $input) !== false || // Vérifie si le nom saisi est une sous-chaîne du nom existant
                    strpos($input, $marqueExistante) !== false || // Vérifie si le nom existant est une sous-chaîne du nom saisi
                    $distance <= 3 || // Vérifie si la distance de Levenshtein est faible (<=3)
                    $pourcentage >= 70 // Vérifie si la similarité est supérieure ou égale à 70%
                ) {
                    $marqueProche = $row['nom']; // Stocke le nom de la marque similaire trouvée
                    $trouveProche = true; // Active le drapeau de marque similaire trouvée
                    break; // Arrête la boucle dès qu'une marque proche est trouvée
                }
            }

            if ($trouveProche && $confirmer !== 'oui') { // Si une marque similaire est trouvée et que l'utilisateur n'a pas confirmé
                $message = '
                <form method="post" class="text-center">
                    <input type="hidden" name="nouvelle_marque" value="' . htmlspecialchars($nouvelleMarque) . '">
                    <input type="hidden" name="confirmer_insertion" value="oui">
                    <div class="alert alert-warning">
                        <p>La marque que vous essayez d\'ajouter ressemble ou contient <strong>' . htmlspecialchars($marqueProche) . '</strong>.</p>
                        <p>Souhaitez vous quand même l\'ajouter?</p>
                        <button type="submit" class="btn btn-sm btn-outline-success me-2">Oui</button>
                        <a href="' . $_SERVER['REQUEST_URI'] . '" class="btn btn-sm btn-outline-secondary">Non</a>
                    </div>
                </form>'; // Affiche un formulaire de confirmation à l'utilisateur
            } else {
                $stmtInsert = $pdo->prepare("INSERT INTO Marque (nom) VALUES (:nom)"); // Prépare la requête d'insertion de la nouvelle marque
                $stmtInsert->bindValue(':nom', $nouvelleMarque, PDO::PARAM_STR); // Lie la valeur du nom à la requête
                $stmtInsert->execute(); // Exécute l'insertion
                header("Location: " . $_SERVER['REQUEST_URI']); // Redirige vers la même page pour éviter la double soumission
                exit; // Arrête le script après la redirection
            }
        }
    } else {
        $message = '<div class="alert alert-warning text-center">Veuillez entrer un nom de marque.</div>'; // Affiche un message si le champ est vide
    }
}


// Pagination
$parPage = 30; // Définit le nombre d'éléments par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1; // Récupère le numéro de page courant
$offset = ($page - 1) * $parPage; // Calcule l'offset pour la requête SQL

$total = $pdo->query("SELECT COUNT(*) FROM Marque")->fetchColumn(); // Récupère le nombre total de marques

$sqlMarques = "SELECT id_marque, nom FROM Marque ORDER BY id_marque ASC LIMIT " . (int)$offset . ", " . (int)$parPage; // Prépare la requête pour récupérer les marques paginées
$stmtMarques = $pdo->prepare($sqlMarques); // Prépare la requête SQL
$stmtMarques->execute(); // Exécute la requête

$queryParams = $_GET; // Récupère les paramètres GET de l'URL
unset($queryParams['page']); // Retire le paramètre 'page' pour la pagination
$urlBase = basename($_SERVER['PHP_SELF']); // Récupère le nom du fichier courant
$urlBase .= (!empty($queryParams)) ? '?' . http_build_query($queryParams) . '&' : '?'; // Construit la base de l'URL pour la pagination
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"> <!-- Définit l'encodage des caractères -->
    <title>Marques</title> <!-- Titre de la page -->
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Responsive design -->
    <!-- Ajoutez ici vos liens CSS (Bootstrap, etc.) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Lien vers Bootstrap -->
</head>
<body>
<?php include '../visuel/barre.php'; ?> <!-- Inclut la barre de navigation -->

<div class="container my-4">
    <div class="card">
        <div class="card-header" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem; text-align: center;">
            Liste des marques <!-- Titre de la liste des marques -->
        </div>
        
        <div class="card-body">
            <!-- Formulaire d'ajout de marque -->
            <form method="post" class="mb-4 d-flex" style="max-width:400px; margin:auto;">
                <input type="text" name="nouvelle_marque" class="form-control me-2" placeholder="Nom de la nouvelle marque" required> <!-- Champ de saisie du nom de la marque -->
                <button type="submit" class="btn btn-danger btn-sm" style="background-color:rgb(23, 192, 17);">Ajouter</button> <!-- Bouton d'ajout -->
            </form>

            <?php if ($message) echo $message; ?> <!-- Affiche les messages d'information ou d'erreur -->

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID</th> <!-- Colonne ID -->
                            <th>Nom de la marque</th> <!-- Colonne nom de la marque -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($marque = $stmtMarques->fetch(PDO::FETCH_ASSOC)) : ?> <!-- Parcourt les marques récupérées -->
                            <tr>
                                <td><?= htmlspecialchars($marque['id_marque']) ?></td> <!-- Affiche l'ID de la marque -->
                                <td><?= htmlspecialchars($marque['nom']) ?></td> <!-- Affiche le nom de la marque -->
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

            <?php include '../visuel/pagination.php'; ?> <!-- Inclut la pagination -->

        </div>
    </div>
    </body>
    </html>
</div>
