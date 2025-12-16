<?php
include_once '../donnée/connect.php';
// Récupération des rôles
$roles = $pdo->query("SELECT idRole, NomRole FROM roles")->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $login = $_POST['login'];
  $password = $_POST['password'];
  $idRole = $_POST['role'];
  $nom = $_POST['nom'];
  $prenom = $_POST['prenom'];

  if (!empty($login) && !empty($password) && !empty($idRole) && !empty($nom) && !empty($prenom)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insertion dans la table comptes
    $stmt = $pdo->prepare("INSERT INTO comptes (login, mdp, nom, prenom) VALUES (?, ?, ?, ?)");
    $stmt->execute([$login, $hashedPassword, $nom, $prenom]);

    $idUtilisateur = $pdo->lastInsertId();

    // Insertion dans la table disposer
    $stmt2 = $pdo->prepare("INSERT INTO disposer (idUtilisateur, idRole) VALUES (?, ?)");
    $stmt2->execute([$idUtilisateur, $idRole]);

    echo "<div class='alert alert-success text-center'>Compte créé avec succès !</div>";
    header("Refresh: 5; url=../index.php"); // Redirection après 5 secondes
  } else {
    echo "<div class='alert alert-danger text-center'>Veuillez remplir tous les champs.</div>";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
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
  <meta charset="UTF-8">
  <title>Création de Compte</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 80vh;">

<div class="card shadow p-4" style="width: 100%; max-width: 430px;">
  <h5 class="mb-4 text-center">Création de compte</h5>

  <form id="loginForm" method="POST" novalidate>
  <div class="mb-3">
    <label for="login" class="form-label">Login</label>
    <input type="text" class="form-control" id="login" name="login" style="border-color: #232323;" required
       pattern="^[a-zA-Z0-9._-]{4,20}$"
       title="Le login doit contenir entre 4 et 20 caractères alphanumériques, points, tirets ou underscores.">
    <div class="invalid-feedback">
    Veuillez entrer un login valide (4-20 caractères, lettres, chiffres, . _ -).
    </div>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label">Mot de passe</label>
    <input type="password" class="form-control" id="password" name="password" style="border-color: #232323;" required
       pattern="^(?=.*[A-Za-zÀ-ÿ])(?=.*\d)[A-Za-zÀ-ÿ\d!@#$%^&*()_+\-=\[\]{};\\|,.<>\/?~`]{8,}$"
       title="Mot de passe de 8 caractères minimum avec lettres (accents autorisés), chiffres. Pas d'espaces.">
    <div id="passwordHelp" class="invalid-feedback">
    Mot de passe de plus de 8 caractères avec des lettres et chiffres requis. Accents autorisés, pas d'espaces.
    </div>
  </div>

  <!-- Bouton pour ouvrir le modal -->
  <div class="mb-3">
    <button type="button" class="btn btn-outline-secondary w-100" data-bs-toggle="modal" style="border-color: #232323;" data-bs-target="#nomPrenomModal">
    Saisir nom/prénom
    </button>
  </div>
  <!-- Champs cachés pour nom et prénom -->
  <input type="hidden" id="nom" name="nom" required>
  <input type="hidden" id="prenom" name="prenom" required>

  <div class="mb-3">
    <label for="role" class="form-label">Rôle</label>
    <select class="form-select" id="role" name="role" style="border-color: #232323;" required>
    <option value="">-- Sélectionner un rôle --</option>
    <?php foreach ($roles as $role): ?>
      <option value="<?= $role['idRole'] ?>"><?= htmlspecialchars($role['NomRole']) ?></option>
    <?php endforeach; ?>
    </select>
    <div class="invalid-feedback">Veuillez sélectionner un rôle.</div>
  </div>

  <button type="submit" class="btn w-100" style="background-color:rgb(23, 192, 17); color: white; font-size: 1rem;">
    Créer le compte
  </button>
  </form>
  <a href="../communication/panneau.php" class="btn btn-secondary w-100 mt-3" style="background-color: rgb(206, 0, 0); color: white; font-size: 1rem;">Retour</a>
</div>

<!-- Modal Bootstrap pour nom et prénom -->
<div class="modal fade" id="nomPrenomModal" tabindex="-1" aria-labelledby="nomPrenomModalLabel" aria-hidden="true">
  <div class="modal-dialog">
  <div class="modal-content">
    <form id="modalNomPrenomForm" novalidate>
    <div class="modal-header">
      <h5 class="modal-title" id="nomPrenomModalLabel">Saisir Nom et Prénom</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
    </div>
    <div class="modal-body">
      <div class="mb-3">
      <label for="modalNom" class="form-label">Nom</label>
      <input type="text" class="form-control" id="modalNom" required pattern="^[A-Za-zÀ-ÿ '-]{2,50}$" title="Entrez un nom valide.">
      <div class="invalid-feedback">Veuillez entrer un nom valide.</div>
      </div>
      <div class="mb-3">
      <label for="modalPrenom" class="form-label">Prénom</label>
      <input type="text" class="form-control" id="modalPrenom" required pattern="^[A-Za-zÀ-ÿ '-]{2,50}$" title="Entrez un prénom valide.">
      <div class="invalid-feedback">Veuillez entrer un prénom valide.</div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
      <button type="submit" class="btn btn-primary">Valider</button>
    </div>
    </form>
  </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const form = document.getElementById('loginForm');
  const passwordInput = document.getElementById('password');

  form.addEventListener('submit', function (e) {
  // Vérifie que nom/prénom sont bien remplis
  if (!document.getElementById('nom').value || !document.getElementById('prenom').value) {
    e.preventDefault();
    e.stopPropagation();
    alert('Veuillez saisir le nom et le prénom via le bouton dédié.');
    return;
  }
  if (!form.checkValidity()) {
    e.preventDefault();
    e.stopPropagation();
  }
  form.classList.add('was-validated');
  });

  passwordInput.addEventListener('input', function () {
  const valid = /^(?=.*[A-Za-zÀ-ÿ])(?=.*\d)[A-Za-zÀ-ÿ\d!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?~`]{8,}$/.test(passwordInput.value);
  passwordInput.classList.toggle('is-invalid', !valid);
  });

  // Modal gestion
  const modalForm = document.getElementById('modalNomPrenomForm');
  modalForm.addEventListener('submit', function(e) {
  e.preventDefault();
  e.stopPropagation();
  if (modalForm.checkValidity()) {
    // Copie les valeurs dans les champs cachés du formulaire principal
    document.getElementById('nom').value = document.getElementById('modalNom').value;
    document.getElementById('prenom').value = document.getElementById('modalPrenom').value;
    // Ferme le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('nomPrenomModal'));
    modal.hide();
  }
  modalForm.classList.add('was-validated');
  });
</script>

</body>
</html>