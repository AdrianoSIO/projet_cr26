<?php
// Définit le type de contenu de la réponse en JSON
header('Content-Type: application/json');

// Récupère et décode les données JSON envoyées dans la requête HTTP
$input = json_decode(file_get_contents('php://input'), true);

// Vérifie si les données sont valides, sinon retourne une erreur
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

// Récupère et convertit les champs nécessaires depuis les données reçues
$id = (int)$input['id'];
$qte = (int)$input['qte'];
$prix_HT = (float)$input['prix_HT'];
$pourcentage = (int)$input['pourcentage'];
$prix_Vente = (float)$input['prix_Vente'];

// Inclut le fichier de connexion à la base de données (variables de connexion)
include '../donnée/connect.php';

try {
    // Crée une nouvelle connexion PDO à la base de données
    $pdo = new PDO('mysql:host=' . $DB_HOST . ';dbname=razanateraa_cinema;charset=utf8', $DB_USER, $DB_PASS);
    // Définit le mode d’erreur pour afficher les exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prépare la requête SQL pour mettre à jour la table Marge
    $sql = "UPDATE Marge 
            SET qte = :qte, prix_HT = :prix_HT, pourcentage = :pourcentage, prix_Vente = :prix_Vente
            WHERE id_marge = :id";
    $stmt = $pdo->prepare($sql);

    // Exécute la requête avec les valeurs reçues
    $success = $stmt->execute([
        ':qte' => $qte,
        ':prix_HT' => $prix_HT,
        ':pourcentage' => $pourcentage,
        ':prix_Vente' => $prix_Vente,
        ':id' => $id
    ]);

    // Retourne un message de succès ou d’erreur selon le résultat de la requête
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur SQL']);
    }
} catch (PDOException $e) {
    // Gère les erreurs de connexion ou d’exécution SQL
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;