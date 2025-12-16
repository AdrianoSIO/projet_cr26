function validerChamps(form) {
    const nom = form.nom.value.trim();
    const idMarque = form.id_marque.value;
    const idCategorie = form.id_categorie.value;
    const idFournisseur = form.id_fournisseur.value;
    const HT = parseFloat(form.HT.value) || 0;
    const vente = parseFloat(form.vente.value) || 0;

    if (!nom || !idMarque || !idCategorie || !idFournisseur) {
        alert("Veuillez remplir tous les champs obligatoires.");
        return false;
    }

    if (HT <= 0 || vente <= 0) {
        alert("Le prix HT et le prix de vente doivent être supérieurs à zéro.");
        return false;
    }

    if (vente < HT) {
        return confirm("Le prix de vente est inférieur au prix HT. Voulez-vous continuer ?");
    }

    return true;
}

function verifierProduit() {
    const form = document.getElementById('form-ajout');
    if (!validerChamps(form)) return;

    const formData = new FormData(form);
    const nomProduit = form.nom.value;

    fetch('chack_produit.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.exists) {
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            document.getElementById('modalBodyText').textContent = 
                `Le produit "${nomProduit}" existe déjà. Voulez-vous quand même l'ajouter ?`;
            modal.show();

            const btnOui = document.getElementById('btnOui');
            const btnNon = document.getElementById('btnNon');
            const newOui = btnOui.cloneNode(true);
            const newNon = btnNon.cloneNode(true);
            btnOui.replaceWith(newOui);
            btnNon.replaceWith(newNon);

            newOui.onclick = () => modal.hide();
            newNon.onclick = () => {
                modal.hide();
                form.submit();
            };
        } else {
            form.submit();
        }
    })
    .catch(err => {
        console.error(err);
        alert("Erreur lors de la vérification du produit.");
    });
}
