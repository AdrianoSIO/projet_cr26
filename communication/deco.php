<?php
session_start();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <?php if (!empty($_SESSION['id'])): ?>
            <h1 class="mb-4" style="color: white;">Déconnexion</h1>
            <form action="#" method="post" class="d-inline-block">
                <button type="submit" name="deco" class="btn btn-danger" style="background-color: rgb(206, 0, 0); color: white; font-size: 1rem;"
                        onclick="return confirm('Voulez-vous vraiment vous déconnecter ?');">
                    Déconnexion
                </button>
            </form>
            <!-- Bouton Retour ajouté -->
            <a href="../page/produit.php" class="btn btn-secondary ms-3" style="background-color:rgb(23, 192, 17);  color: white; font-size: 1rem;">
                Retour
            </a>

            <div class="alert alert-success mt-4">
                Connecté(e) en tant que <strong><?php echo htmlspecialchars($_SESSION['login']); ?></strong>
            </div>
        <?php else: ?>
            <h1 style="color: white;">Vous êtes déconnecté(e).</h1>
            <div>
                <h2 style="color: white;">Redirection vers la page d'accueil dans <span id="countdown">5</span> secondes...</h2>
            </div>
            <script>
                let countdown = 5;
                const countdownElement = document.getElementById('countdown');
                const interval = setInterval(() => {
                    countdown--;
                    countdownElement.textContent = countdown;
                    if (countdown <= 0) {
                        clearInterval(interval);
                        window.location.href = '../index.php';
                    }
                }, 1000);
            </script>
        <?php endif; ?>
    </div>

    <?php
    if (isset($_POST['deco'])) {
        session_unset();
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    ?>
    <!-- Bootstrap JS (optionnel) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
