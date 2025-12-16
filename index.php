<?php
session_start();
$title = "Connexion";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN pour les icônes œil -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<style>
  body {
    background-image: url('images/cinema.jpg');
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-position: center center;
    z-index: -1;
  }
</style>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
                    Connexion
                </div>
                <div class="card-body">
                    <?php
                    try {
                        require 'donnée/connect.php';

                        if (!empty($_POST['id']) && !empty($_POST['md'])) {
                            $id = $_POST['id'];
                            $md = $_POST['md'];

                            $sql = "SELECT * FROM comptes WHERE login = :login";
                            $statement = $pdo->prepare($sql);
                            $statement->bindParam(':login', $id);
                            $statement->execute();

                            if ($statement->rowCount() == 1) {
                                $row = $statement->fetch();
                                $hash = $row['mdp'];
                                if (password_verify($md, $hash)) {
                                    // Stockage des infos dans la session
                                    $_SESSION['id'] = $row['idUtilisateur'];
                                    $_SESSION['login'] = $id;

                                    // Récupérer idRole depuis disposer
                                    $stmtRole = $pdo->prepare("SELECT idRole FROM disposer WHERE idUtilisateur = ?");
                                    $stmtRole->execute([$row['idUtilisateur']]);
                                    $roleRow = $stmtRole->fetch(PDO::FETCH_ASSOC);
                                    $_SESSION['idRole'] = $roleRow ? $roleRow['idRole'] : null;

                                    // Redirection immédiate après connexion
                                    header("Location: page/produit.php");
                                    exit();
                                } else {
                                    echo '<div class="alert alert-danger text-center mt-3">Mot de passe incorrect.</div>';
                                }
                            } else {
                                echo '<div class="alert alert-danger text-center mt-3">Identifiant incorrect.</div>';
                            }
                            $statement->closeCursor();
                        }
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-danger mt-3">Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>

                    <?php if (empty($_SESSION['id'])): ?>
                        <form action="" method="post" class="mt-3">
                            <div class="mb-3">
                                <label for="id" class="form-label">Identifiant</label>
                                <input type="text" class="form-control" id="id" name="id" required>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="md" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="md" name="md" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" style="background-color: rgb(206, 0, 0)">Connexion</button>
                            <div class="mt-3 text-center">
                                Pas de compte ? Demandez à votre administrateur de vous en créer un.
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-success text-center">
                            Connecté(e) en tant que <strong><?= htmlspecialchars($_SESSION['login']) ?></strong>
                            <?php header("Refresh: 2; url=page/produit.php"); ?>
                        </div>
                        <div class="text-center">
                            <a href="communication/deco.php" class="btn btn-outline-danger">Déconnexion</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#md');
    const eyeIcon = document.querySelector('#eyeIcon');

    togglePassword.addEventListener('click', function () {
        // bascule entre password et text
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        // bascule l'icône
        eyeIcon.classList.toggle('bi-eye');
        eyeIcon.classList.toggle('bi-eye-slash');
    });
</script>

</body>
</html>
