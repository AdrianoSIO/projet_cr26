# Le Fauteuil Rouge - Système de Gestion de Stock

## 📋 Description

Application web de gestion de stock pour un cinéma, permettant la gestion complète des produits, des commandes, des mouvements de stock et des utilisateurs avec différents niveaux d'accès.

## 🎯 Fonctionnalités Principales

### Gestion des Utilisateurs
- **Authentification sécurisée** avec mot de passe hashé (PASSWORD_DEFAULT)
- **3 niveaux de rôles** :
  - Super Administrateur (ID: 1) - Accès complet
  - Administrateur (ID: 2) - Gestion avancée
  - Utilisateur (ID: 3) - Accès restreint
- Changement de mot de passe avec vérification de force
- Création et suppression de comptes (admin uniquement)

### Gestion des Produits
- **CRUD complet** sur les produits
- Gestion des marques, catégories et fournisseurs
- **Détection de doublons** avec algorithme de similarité (Levenshtein + similar_text)
- Produits actifs/inactifs
- Associations entre produits
- Gestion des marges et prix (HT, TTC, TVA)

### Gestion des Stocks
- **Suivi en temps réel** du stock actuel
- Seuils de stock configurables
- Mouvements de stock (entrées/sorties)
- **Mise à jour AJAX** pour fluidité
- Historique complet des mouvements

### Système de Commandes
- **Panier dynamique** avec mise à jour en temps réel
- Actions : Ajouter/Retirer du stock
- Validation de commande avec transactions PDO
- Historique détaillé des commandes
- Calcul automatique des montants

### Interface Utilisateur
- Design responsive (Bootstrap 5.3)
- **Recherche avancée** avec filtres multiples
- **Pagination** sur toutes les listes
- Notifications visuelles (succès/erreur)
- Thème personnalisé (rouge #c40000 / noir #232323)

## 🛠️ Technologies Utilisées

### Backend
- **PHP 8.2+**
- **MySQL/MariaDB** (version 10.11+)
- **PDO** pour les requêtes préparées
- Sessions PHP pour l'authentification

### Frontend
- **HTML5 / CSS3**
- **Bootstrap 5.3.3**
- **Bootstrap Icons**
- **JavaScript vanilla** (pas de framework)

### Sécurité
- Requêtes préparées (protection injection SQL)
- Hachage de mots de passe (password_hash)
- Validation des entrées côté serveur
- Gestion des rôles et permissions
- Protection CSRF avec vérification de session

## 📦 Installation

### Prérequis
- Serveur web (Apache/Nginx)
- PHP 8.2 ou supérieur
- MySQL/MariaDB 10.11+
- Accès PHPMyAdmin (recommandé)

### Étapes d'installation

1. **Cloner ou télécharger le projet**
```bash
git clone [votre-repo]
cd fauteuil-rouge
```

2. **Configurer la base de données**

Importer le fichier SQL :
```sql
-- Importer donnée/bdd.sql dans votre base de données
```

3. **Configurer la connexion**

Modifier `donnée/connect.php` :
```php
$DB_HOST = 'localhost';
$DB_USER = 'votre_utilisateur';
$DB_PASS = 'votre_mot_de_passe';
$DB_NAME = 'nom_de_votre_base';
```

4. **Configuration du serveur**

Structure des dossiers :
```
/var/www/html/
├── communication/
├── donnée/
├── images/
├── page/
├── visuel/
└── index.php
```

5. **Permissions**
```bash
chmod 755 -R .
chmod 644 donnée/connect.php
```

6. **Accéder à l'application**
```
http://localhost/index.php
```

## 👤 Comptes par Défaut

### Super Administrateur
- **Login** : Adriano
- **Mot de passe** : [voir base de données hashée]

### Utilisateur Test
- **Login** : Anthonio
- **Mot de passe** : [voir base de données hashée]

⚠️ **Important** : Changez immédiatement les mots de passe par défaut !

## 📂 Structure du Projet

```
├── communication/
│   ├── création.php       # Création de comptes
│   ├── deco.php          # Déconnexion
│   ├── erreur.php        # Page erreur 403
│   └── panneau.php       # Panneau administrateur
├── donnée/
│   ├── auth.php          # Authentification et autorisations
│   ├── connect.php       # Connexion base de données
│   ├── bdd.sql          # Structure base de données
│   ├── chack_produit.php # Vérification doublons produits
│   └── verif.js         # Validations JavaScript
├── images/
│   ├── logo.png         # Logo du cinéma
│   └── cinema.jpg       # Image de fond
├── page/
│   ├── produit.php      # Gestion produits & panier
│   ├── historique.php   # Historique commandes
│   ├── mouvement.php    # Mouvements de stock
│   ├── stock.php        # Gestion des stocks
│   ├── marge.php        # Gestion des marges
│   ├── admin.php        # Gestion utilisateurs
│   ├── ajout.php        # Ajout produit
│   ├── actif.php        # Produits actifs
│   ├── inactif.php      # Produits inactifs
│   ├── supp.php         # Suppression produits
│   ├── marque.php       # Gestion marques
│   ├── fournisseur.php  # Gestion fournisseurs
│   ├── duo.php          # Associations produits
│   └── compte.php       # Changement mot de passe
├── visuel/
│   ├── barre.php        # Barre navigation (pages internes)
│   ├── nav.php          # Navigation (communication/)
│   ├── special.php      # Navigation (index.php)
│   └── pagination.php   # Composant pagination
└── index.php            # Page de connexion
```

## 🔑 Fonctionnalités Détaillées

### 1. Gestion du Panier (produit.php)
- Ajout/retrait de produits
- Mise à jour en temps réel (AJAX)
- Calcul du stock théorique
- Validation avec vérification de stock
- Transactions sécurisées

### 2. Historique des Commandes (historique.php)
- Affichage par utilisateur (ou global pour admin)
- Filtres : N° commande, date, produit, utilisateur
- Détails : lignes de commande, montants
- Pagination avancée

### 3. Mouvements de Stock (mouvement.php)
- Entrées et sorties
- Filtres multiples
- Association avec commandes
- Identification de l'utilisateur

### 4. Gestion des Marges (marge.php)
- Calcul automatique : TTC, U, Estimation, Marge
- Gestion TVA (5.5% / 20%)
- Réductions configurables
- Mise à jour en temps réel

### 5. Détection de Doublons (chack_produit.php)
Utilise 3 méthodes :
- Comparaison exacte (LOWER)
- Distance de Levenshtein (≤ 3)
- Similarité textuelle (≥ 70%)

## 🔒 Sécurité

### Mesures Implémentées
1. **Injection SQL** : Requêtes préparées PDO
2. **XSS** : htmlspecialchars() sur toutes les sorties
3. **CSRF** : Vérification de session
4. **Mots de passe** : Hachage PASSWORD_DEFAULT
5. **Autorisations** : Contrôle des rôles (auth.php)
6. **Sessions** : Régénération d'ID, timeout

### Recommandations Supplémentaires
- Activer HTTPS en production
- Utiliser des variables d'environnement pour les credentials
- Implémenter un rate limiting sur la connexion
- Ajouter des logs de sécurité
- Mettre en place des backups automatiques

## 🐛 Dépannage

### Problème de connexion à la base
```php
// Vérifier connect.php
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

### Erreur 403 Accès refusé
- Vérifier le rôle de l'utilisateur dans la table `disposer`
- S'assurer que `auth.php` est bien inclus

### Le panier ne se met pas à jour
- Vérifier la console JavaScript (F12)
- Vérifier les permissions de session PHP

### Produits non affichés
- Vérifier le champ `actif` dans la base
- Tester avec le filtre "Afficher inactifs"

## 📈 Évolutions Futures

### Court Terme
- [ ] Export Excel des commandes
- [ ] Graphiques de stock (Chart.js)
- [ ] Notifications par email
- [ ] Import CSV de produits

### Moyen Terme
- [ ] Application mobile
- [ ] Multi-devises
- [ ] Gestion de plusieurs entrepôts

### Long Terme
- [ ] IA pour prédiction de stock
- [ ] Intégration avec caisses enregistreuses
- [ ] Module de facturation
- [ ] Dashboard analytique avancé

## 👥 Contributeurs

- **Adriano Razanatera** - Moi


---

**Version** : 1.0.0  
**Dernière mise à jour** : Décembre 2025
