<?php
// Indique que la réponse sera en format JSON
header('Content-Type: application/json');

// Inclut le fichier de connexion à la base de données
include '../donnée/connect.php';

// Récupère et nettoie le nom du produit depuis la requête POST
$nom = trim($_POST['nom'] ?? '');

// Récupère et nettoie le format du produit, ou le met à null si vide
$format = trim($_POST['format'] ?? '');
$format = $format === '' ? null : $format;

// Récupère l'id de la marque et de la catégorie depuis la requête POST
$id_marque = $_POST['id_marque'] ?? null;
$id_categorie = $_POST['id_categorie'] ?? null;

// Vérifie que les champs obligatoires sont présents, sinon retourne exists: false
if (!$nom || !$id_marque || !$id_categorie) {
    echo json_encode(['exists' => false]);
    exit;
}

try {
    // Prépare une requête SQL pour vérifier si un produit existe déjà avec ces critères
    $sql = "SELECT COUNT(*) FROM Produit 
            WHERE LOWER(nom) = LOWER(:nom) 
            AND id_marque = :id_marque 
            AND id_categorie = :id_categorie 
            AND ((format IS NULL AND :format IS NULL) OR LOWER(format) = LOWER(:format))";

    // Prépare et exécute la requête avec les paramètres fournis
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom' => $nom,
        ':format' => $format,
        ':id_marque' => $id_marque,
        ':id_categorie' => $id_categorie
    ]);

    // Récupère le résultat : true si au moins un produit existe, false sinon
    $exists = $stmt->fetchColumn() > 0;

    // Retourne le résultat au format JSON
    echo json_encode(['exists' => $exists]);
} catch (PDOException $e) {
    // En cas d'erreur de base de données, retourne un message d'erreur
    echo json_encode(['error' => 'Erreur de base de données']);
}