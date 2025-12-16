<?php
require '../donnée/connect.php';
require '../visuel/barre.php';
requireAdmin();
if (isset($_SESSION['idRole']) && $_SESSION['idRole'] == 1): 
// Gérer la mise à jour de mot de passe
if (isset($_POST['update_password']) && is_numeric($_POST['idUtilisateur'])) {
    $userId = intval($_POST['idUtilisateur']);
    $newPassword = $_POST['new_password'] ?? '';

    $stmtLogin = $pdo->prepare("SELECT login FROM comptes WHERE idUtilisateur = :id");
    $stmtLogin->execute([':id' => $userId]);
    $userLogin = $stmtLogin->fetchColumn();

    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            $error = "Erreur (ID: $userId) : Le mot de passe doit contenir au moins 6 caractères pour l'utilisateur <strong>" . htmlspecialchars($userLogin) . "</strong>.";
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE comptes SET mdp = :mdp WHERE idUtilisateur = :id");
            $stmt->execute([':mdp' => $hash, ':id' => $userId]);
            $message = "Succès : Mot de passe mis à jour pour l'utilisateur <strong>" . htmlspecialchars($userLogin) . "</strong>.";
        }
    } else {
        $error = "Erreur (ID: $userId) : Le mot de passe ne peut pas être vide pour l'utilisateur <strong>" . htmlspecialchars($userLogin) . "</strong>.";
    }
}

// Gérer la suppression de compte
if (isset($_POST['delete_user']) && is_numeric($_POST['idUtilisateur'])) {
    $userId = intval($_POST['idUtilisateur']);

    $stmtCheck = $pdo->prepare("SELECT login FROM comptes WHERE idUtilisateur = :id");
    $stmtCheck->execute([':id' => $userId]);
    $userLogin = $stmtCheck->fetchColumn();

    if ($userLogin) {
        $stmtDelete = $pdo->prepare("DELETE FROM comptes WHERE idUtilisateur = :id");
        if ($stmtDelete->execute([':id' => $userId])) {
            $message = "Le compte <strong>" . htmlspecialchars($userLogin) . "</strong> a été supprimé. Les commandes associées sont conservées.";
        } else {
            $error = "Erreur : Impossible de supprimer l'utilisateur <strong>" . htmlspecialchars($userLogin) . "</strong>.";
        }
    } else {
        $error = "Utilisateur non trouvé.";
    }
}

$sql = "
    SELECT c.idUtilisateur, c.login 
    FROM comptes c
    JOIN disposer d ON c.idUtilisateur = d.idUtilisateur
    WHERE d.idRole != 1
    ORDER BY c.idUtilisateur
";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des comptes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <div class="card shadow">
                <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
            <h3>Liste des comptes</h3>
        </div>
        <div class="card-body">
            <?php if (isset($message)): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Login</th>
                        <th>Nouveau mot de passe</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['idUtilisateur']) ?></td>
                        <td><?= htmlspecialchars($user['login']) ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="idUtilisateur" value="<?= $user['idUtilisateur'] ?>">
                                <div class="input-group input-group-sm">
                                    <input type="password" name="new_password" id="pwd-<?= $user['idUtilisateur'] ?>" class="form-control" placeholder="Mot de passe" minlength="6" required style="border-color: #232323;">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword(<?= $user['idUtilisateur'] ?>)">
                                        <i id="icon-<?= $user['idUtilisateur'] ?>" class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                        </td>
                        <td>
                                <button type="submit" name="update_password" class="btn btn-warning btn-sm me-2">Mettre à jour</button>
                            </form>
                            <!-- Bouton supprimer -->
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-user-id="<?= $user['idUtilisateur'] ?>">Supprimer</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
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

<!-- Modal de confirmation -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmation de suppression</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Êtes-vous sûr de vouloir supprimer ce compte ? Les commandes liées seront conservées.
      </div>
      <div class="modal-footer">
        <form method="post">
            <input type="hidden" name="idUtilisateur" id="modal-user-id">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" name="delete_user" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById('pwd-' + id);
    const icon = document.getElementById('icon-' + id);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        input.type = "password";
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}

// Remplir l'ID de l'utilisateur dans le modal
const confirmDeleteModal = document.getElementById('confirmDeleteModal');
confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const userId = button.getAttribute('data-user-id');
    document.getElementById('modal-user-id').value = userId;
});
</script>
<?php endif; // Vérification du rôle admin ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
