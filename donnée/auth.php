<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../index.php");
    exit();
}

include '../donnée/connect.php'; // Connexion PDO

// Récupérer l'idUtilisateur lié au compte connecté
$login = $_SESSION['login'];

// On récupère idUtilisateur et idRole seulement si pas déjà en session
if (!isset($_SESSION['idUtilisateur']) || !isset($_SESSION['idRole'])) {
    $stmt = $pdo->prepare("SELECT c.idUtilisateur, d.idRole
                           FROM comptes c
                           JOIN disposer d ON c.idUtilisateur = d.idUtilisateur
                           WHERE c.login = ?");
    $stmt->execute([$login]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $_SESSION['idUtilisateur'] = $row['idUtilisateur'];
        $_SESSION['idRole'] = $row['idRole'];
    } else {
        // Pas trouvé, on déconnecte par sécurité
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
}

function isAdmin(): bool {
    return isset($_SESSION['idRole']) && $_SESSION['idRole'] == 1;
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../communication/erreur.php');
        exit();
    }
}

// Utilisateur connecté mais pas admin
$hideButtons = (isset($_SESSION['idRole']) && $_SESSION['idRole'] != 1);
