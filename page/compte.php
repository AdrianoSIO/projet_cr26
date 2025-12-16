<?php
$title = "Changer le mot de passe";
require '../visuel/barre.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <?php if (!empty($_SESSION['redirect'])): ?>
        <meta http-equiv="refresh" content="4;url=../index.php">
    <?php unset($_SESSION['redirect']); endif; ?>
</head>
<body>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
                    Changer le mot de passe
                </div>
                <div class="card-body">
                    <?php
                    if (empty($_SESSION['id'])) {
                        echo '<div class="alert alert-warning text-center">Vous devez être connecté pour changer votre mot de passe.</div>';
                        echo '<div class="text-center"><a href="../index.php" class="btn btn-primary">Se connecter</a></div>';
                    } else {
                        try {
                            require '../donnée/connect.php';

                            if (!empty($_POST['nouveau_md']) && !empty($_POST['confirmer_md'])) {
                                $nouveau_md = $_POST['nouveau_md'];
                                $confirmer_md = $_POST['confirmer_md'];
                                $idUtilisateur = $_SESSION['id'];

                                if ($nouveau_md !== $confirmer_md) {
                                    echo '<div class="alert alert-danger text-center mt-3">Les nouveaux mots de passe ne correspondent pas.</div>';
                                } elseif (strlen($nouveau_md) < 6) {
                                    echo '<div class="alert alert-danger text-center mt-3">Le nouveau mot de passe doit contenir au moins 6 caractères.</div>';
                                } else {
                                    $newHash = password_hash($nouveau_md, PASSWORD_DEFAULT);
                                    $update = $pdo->prepare("UPDATE comptes SET mdp = :mdp WHERE idUtilisateur = :id");
                                    $update->execute([':mdp' => $newHash, ':id' => $idUtilisateur]);

                                    echo '<div class="alert alert-success text-center mt-3">Mot de passe mis à jour avec succès. Déconnexion en cours...</div>';

                                    $_SESSION = [];
                                    session_destroy();
                                    $_SESSION['redirect'] = true;
                                    header("Location: ../index.php");
                                    exit();
                                }
                            }
                        } catch (PDOException $e) {
                            echo '<div class="alert alert-danger mt-3">Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                    }
                    ?>

                    <?php if (!empty($_SESSION['id'])): ?>
                        <form action="" method="post" class="mt-3">
                            <div class="mb-3">
                                <label for="nouveau_md" class="form-label">Nouveau mot de passe</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="nouveau_md" name="nouveau_md" required oninput="checkStrength(this.value)">
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleVisibility('nouveau_md', this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                                <div class="progress mt-2">
                                    <div id="password-strength" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirmer_md" class="form-label">Confirmer le nouveau mot de passe</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmer_md" name="confirmer_md" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleVisibility('confirmer_md', this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" style="background-color:rgb(23, 192, 17);">Changer le mot de passe</button>
                        </form>

                        <tr>
                    <td colspan="4" class="text-center">
                        <a href="../communication/panneau.php" class="btn btn-secondary w-70 mt-3" style="background-color: rgb(206, 0, 0); color: white; font-size: 1rem;">Retour</a>
                    </td>
                  </tr>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleVisibility(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}

function checkStrength(password) {
    const bar = document.getElementById('password-strength');
    let strength = 0;
    if (password.length >= 6) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;

    const percent = (strength / 5) * 100;
    bar.style.width = percent + '%';

    bar.classList.remove('bg-danger', 'bg-warning', 'bg-success');
    if (percent < 40) {
        bar.classList.add('bg-danger');
    } else if (percent < 80) {
        bar.classList.add('bg-warning');
    } else {
        bar.classList.add('bg-success');
    }
}
</script>

</body>
</html>
