<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Erreur 403 access forbidden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light min-vh-100 d-flex justify-content-center align-items-center">
    <div class="alert alert-danger text-center p-5 shadow-lg white-text">
        <h1 class="display-1 fw-bold">403</h1>
        <p class="fs-3 mb-2">Accès interdit</p>
        <p class="mb-2">Vous n'avez pas l'autorisation d'accéder à cette page.</p>
        <p class="mb-4">Si vous pensez qu'il s'agit d'une erreur, veuillez contacter l'administrateur du site.</p>
        <p>Redirection vers la page du jeu dans <span id="countdown">5</span> secondes...</p>
        <?php
        session_start();
        require '../donnée/connect.php';
        if (!empty($_SESSION['id'])){
            echo '<p class="mt-3">Vous êtes déjà connecté(e) en tant que <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></p>';
        } else {
            echo '<p class="mt-3">Vous êtes déconnecté(e)</p>';
        }
        ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
