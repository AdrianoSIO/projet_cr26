<?php
require_once '../donnée/auth.php'; // inclut la logique sécurisée
$idRole = $_SESSION['idRole'] ?? null;
$login = $_SESSION['login'] ?? '';
$idUtilisateur = $_SESSION['idUtilisateur'] ?? '';

// Le reste du code comme tu as déjà.

?>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  body {
    background-image: url('../images/cinema.jpg');
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-position: center center;
    z-index: -1;
  }
</style>

<!-- Logo centré au-dessus des deux navbars -->
<div class="d-flex justify-content-center align-items-center py-3" style="background-color: #232323; border-color: rgb(206, 0, 0); color: #fff;">
  <img src="../images/logo.png" alt="Logo" style="height:100px; margin-left: 150px;">
  <span class="navbar-brand mb-0 h1 text-center text-white fw-bold ms-3">
    Le fauteuil rouge
    <br>
    <small class="text-white-50 fs-6">gestion des stocks</small><br>
    <span>Connecté(e) en tant que <strong><?= htmlspecialchars($_SESSION['login']) ?></strong></span>
  </span>
  <?php 
  

  if ($idRole == 1 || $idRole == 2): ?>
    <div class="d-flex flex-column align-items-start" style="margin-left: 20px;">
      <a href="../communication/panneau.php" class="navbar-brand text-white px-0 py-1">Espace Admin</a>
    </div>
  <?php endif; ?> 
  <?php if (empty($_SESSION['login'])): ?>
      <a href="#" class="navbar-brand text-white mx-2" style="margin-right: 20px;">Connexion</a>
      <?php else: ?>
      <a href="../communication/deco.php" class="navbar-brand text-white mx-2" style="margin-right: 20px;">Déconnexion</a>
      <a href="compte.php" class="navbar-brand text-white mx-2" style="margin-right: 20px;">Mot de passe</a>
  <?php endif; ?>
</div> 
 
<!-- Navbar principale -->
<nav class="navbar navbar-expand-lg" style="background-color: #232323; min-height: 0;">
</nav>
<nav class="navbar" style="background-color: #c40000; min-height: 35px;">
  <div class="container d-flex justify-content-center align-items-center">

      <a href="produit.php" class="navbar-brand text-white mx-2" style="margin-right: 20px;">Produit</a>
      <a href="historique.php" class="navbar-brand text-white mx-2" style="margin-left: 20px;">Suivi</a>
      <a href="mouvement.php" class="navbar-brand text-white mx-2" style="margin-left: 20px;">Mouvement</a>
      </div>
</nav>
