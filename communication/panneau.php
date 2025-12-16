<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panneau</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../visuel/nav.php'; ?>
<body class="bg-light">
    <div class="container py-5">
    <div class="card bg-dark text-white">
    <h1 class="mb-4 text-center text-white">Panneau de Contrôle</h1>
    <?php if (isset($_SESSION['idRole']) && $_SESSION['idRole'] == 1): ?>
        
            <div class="row justify-content-center">
                <div class="col-md-4 mb-3 d-flex">
                    <div class="card shadow-sm flex-fill h-100">
                        <div class="card-body text-center d-flex flex-column">
                            <h6 class="card-title">Comptes</h6>
                            <p class="card-text flex-grow-1">Consulter les comptes utilisateurs pouvant<br> se connecter et les gérer.</p>
                            <a href="../page/admin.php" class="btn btn-warning mt-auto" style="background-color: rgb(206, 0, 0); border-color:  #232323; color: #fff;">Voir les comptes</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill h-100">
                    <div class="card-body text-center d-flex flex-column">
                            <h6 class="card-title">Création</h6>
                            <p class="card-text flex-grow-1">Créer un nouveau compte pour un utilisateur.</p>
                            <a href="création.php" class="btn btn-success mt-auto" style="background-color: #232323; border-color: rgb(206, 0, 0); color: #fff;">Création</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
                <div class="col-md-4 mb-3 d-flex">
                    <div class="card shadow-sm flex-fill h-100">
                        <div class="card-body text-center d-flex flex-column">
                            <h6 class="card-title">Produit Suppression</h6>
                            <p class="card-text flex-grow-1">Consulter les produits pouvant<br> être supprimés.</p>
                            <a href="../page/supp.php" class="btn btn-warning mt-auto" style="background-color: rgb(206, 0, 0); border-color:  #232323; color: #fff;">Voir les produits</a>
                        </div>
                    </div>
                </div>
            <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="card-title">Marge</h6>
                        <p class="card-text flex-grow-1">Cette page sert à définir les valeurs<br>des marges des différents produits.</p>
                        <a href="../page/marge.php" class="btn btn-success mt-auto" style="background-color: #232323; border-color: rgb(206, 0, 0); color: #fff;">Marge</a>
                    </div>
                </div>
            </div>
        <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="card-title">Nouveaux Produits</h6>
                        <p class="card-text flex-grow-1">Ajouter un nouveau produit <br>inactif ou actif.</p>
                        <a href="../page/ajout.php" class="btn btn-primary mt-auto" style="background-color: rgb(206, 0, 0); border-color:  #232323; color: #fff;">Nouveauté</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="card-title">Marque</h6>
                        <p class="card-text flex-grow-1">Gérer les marques disponibles et en ajouter.</p>
                        <a href="../page/marque.php" class="btn btn-primary mt-auto" style="background-color: #232323; border-color: rgb(206, 0, 0); color: #fff;">Marque</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="card-title">Association</h6>
                        <p class="card-text flex-grow-1">Gérer les associations de produits.</p>
                        <a href="../page/duo.php" class="btn btn-primary mt-auto" style="background-color: rgb(206, 0, 0); border-color:  #232323; color: #fff;">Association</a>
                    </div>
                </div>      
            </div>
            <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="card-title">Fournisseur</h6>
                        <p class="card-text flex-grow-1">Gérer les fournisseurs.</p>
                        <a href="../page/fournisseur.php" class="btn btn-primary mt-auto" style="background-color: #232323; border-color:  rgb(206, 0, 0); color: #fff;">Fournisseur</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="card-title">Stockage</h6>
                        <p class="card-text flex-grow-1">Gérer les quantites de chaques produits présents en stock.</p>
                        <a href="../page/stock.php" class="btn btn-primary mt-auto" style="background-color: rgb(206, 0, 0); border-color:  #232323; color: #fff;">Stockage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="card-title">Produits inactifs</h6>
                        <p class="card-text flex-grow-1">Gérer les produits qui ne vont plus être vendu</p>
                        <a href="../page/inactif.php" class="btn btn-primary mt-auto" style="background-color: #232323; border-color:  rgb(206, 0, 0); color: #fff;">Inactif</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="card-title">Produits actifs</h6>
                        <p class="card-text flex-grow-1">Gérer les produits étant inactifs de retour en vente.</p>
                        <a href="../page/actif.php" class="btn btn-primary mt-auto" style="background-color: rgb(206, 0, 0); border-color: #232323; color: #fff;">Actif</a>
                    </div>
                </div>
    </div>
        </div>
    </div>
</body>
</html>