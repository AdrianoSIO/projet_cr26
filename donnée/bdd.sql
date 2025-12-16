-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : jeu. 19 juin 2025 à 17:42
-- Version du serveur : 10.11.11-MariaDB-0+deb12u1
-- Version de PHP : 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `razanateraa_stock`
--

-- --------------------------------------------------------

--
-- Structure de la table `Categorie`
--

CREATE TABLE `Categorie` (
  `id_categorie` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Categorie`
--

INSERT INTO `Categorie` (`id_categorie`, `nom`, `description`) VALUES
(1, 'Bieres pression', 'Il s\'agit d\'une Boisson Interdite au Mineurs'),
(2, 'Confiseries', 'Gourmandise et petites nourritures'),
(3, 'Boisson Chaudes', 'Boisson servi chaude '),
(4, 'Boissons', 'Il s\'agit de boisson servi froides ou tièdes '),
(5, 'Dons', 'Il s\'agit de dons '),
(6, 'Glaces', 'Glaces servis froides'),
(7, 'Divers', 'Mettre ici les produits différents'),
(8, 'Pop-corn', 'Ce sont des pop-corn (global)'),
(9, 'Nourriture chaudes', 'Plat nécessitant une mise en chaleur pour la dégustation');

-- --------------------------------------------------------

--
-- Structure de la table `Commande`
--

CREATE TABLE `Commande` (
  `id_commande` int(11) NOT NULL,
  `idUtilisateur` int(11) DEFAULT NULL,
  `date_commande` datetime NOT NULL,
  `montant` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Commande`
--

INSERT INTO `Commande` (`id_commande`, `idUtilisateur`, `date_commande`, `montant`) VALUES
(1, 2, '2025-06-16 19:20:22', 30.1),
(2, 2, '2025-06-17 14:46:37', 9.03),
(3, 2, '2025-06-17 14:49:55', 9.03),
(4, 2, '2025-06-17 14:50:05', 3.01),
(5, 11, '2025-06-17 17:40:16', 0.57),
(6, 11, '2025-06-17 17:40:43', 0.19),
(7, 11, '2025-06-17 17:41:12', 0.38),
(8, 11, '2025-06-17 17:52:39', 0.76),
(9, 2, '2025-06-17 19:32:28', 0.38);

-- --------------------------------------------------------

--
-- Structure de la table `comptes`
--

CREATE TABLE `comptes` (
  `idUtilisateur` int(20) NOT NULL,
  `login` varchar(255) NOT NULL,
  `prenom` varchar(11) NOT NULL,
  `nom` varchar(11) DEFAULT NULL,
  `mdp` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `comptes`
--

INSERT INTO `comptes` (`idUtilisateur`, `login`, `prenom`, `nom`, `mdp`) VALUES
(1, 'Phillipe ', 'Phillipe ', NULL, '$2y$10$AjBv5KtxHrA7Q8XReQrx8u2rOsRKAhlcuRnYa8mNQZ3UIDmfNx1Y.'),
(2, 'Adriano', 'Adriano', 'Razanatera', '$2y$10$fWL.NYi8GKVnWg9LeEL7yuPQXEZ.3ZM.h/fuBVazadLam9aE6tQiG'),
(6, 'razanateraa', 'Adriano', 'Antier', '$2y$10$XAq.isx4NGrhS5UrQsqIUOgOXUa7b/eMtZ8sJEZJFRpg7GkvP0kOu'),
(11, 'Anthonio', 'Anthonio', 'Sanchez', '$2y$10$Ntf30y9WBsXkBkD5VsMKle6Kc.9TOJIsQIgCkomfAR1ysMmFc2BE6'),
(12, 'Théodore', 'Théodore', 'Auzureau', '$2y$10$rXiSZuglC7LFTDwLdMARdeVjVVUQGK.nBaEKr8zi89umgklTWAKS.');

-- --------------------------------------------------------

--
-- Structure de la table `disposer`
--

CREATE TABLE `disposer` (
  `idRole` int(11) NOT NULL,
  `idUtilisateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `disposer`
--

INSERT INTO `disposer` (`idRole`, `idUtilisateur`) VALUES
(1, 1),
(1, 2),
(1, 12),
(3, 6),
(3, 11);

-- --------------------------------------------------------

--
-- Structure de la table `Fournisseur`
--

CREATE TABLE `Fournisseur` (
  `id_fournisseur` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Fournisseur`
--

INSERT INTO `Fournisseur` (`id_fournisseur`, `nom`) VALUES
(1, 'Leclerc'),
(2, 'Carrefour'),
(3, 'Super U'),
(4, 'SuperGroup'),
(5, 'Benoit Promotion'),
(6, 'Confisud'),
(7, 'Liboureau'),
(8, 'Poivre & Miel'),
(9, 'Les jardins de l\'orbrie'),
(10, 'Les fruits de Clazay');

-- --------------------------------------------------------

--
-- Structure de la table `Groupe`
--

CREATE TABLE `Groupe` (
  `id_pack` int(11) NOT NULL,
  `nom_pack` varchar(100) DEFAULT NULL,
  `montant` float(10,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Groupe`
--

INSERT INTO `Groupe` (`id_pack`, `nom_pack`, `montant`) VALUES
(1, 'Kitkat Ball moyen', 8.550),
(2, 'Kitkat Ball grand', 4.550);

-- --------------------------------------------------------

--
-- Structure de la table `GrPR`
--

CREATE TABLE `GrPR` (
  `id_pack` int(11) DEFAULT NULL,
  `id_produit` int(11) DEFAULT NULL,
  `quantite` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `GrPR`
--

INSERT INTO `GrPR` (`id_pack`, `id_produit`, `quantite`) VALUES
(1, 98, 1),
(1, 126, 1),
(2, 99, 1);

-- --------------------------------------------------------

--
-- Structure de la table `Ligne_commande`
--

CREATE TABLE `Ligne_commande` (
  `id_ligne` int(11) NOT NULL,
  `id_commande` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `prixU` float NOT NULL,
  `quantite` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Ligne_commande`
--

INSERT INTO `Ligne_commande` (`id_ligne`, `id_commande`, `id_produit`, `prixU`, `quantite`) VALUES
(1, 1, 1, 3.01, 10),
(2, 2, 1, 3.01, 3),
(3, 3, 1, 3.01, 3),
(4, 4, 1, 3.01, 1),
(5, 5, 41, 0, 3),
(6, 5, 4, 0.19, 3),
(7, 6, 4, 0.19, 1),
(8, 7, 4, 0.19, 2),
(9, 8, 4, 0.19, 4),
(10, 8, 41, 0, 3),
(11, 9, 4, 0.19, 2),
(12, 9, 40, 0, 2);

-- --------------------------------------------------------

--
-- Structure de la table `Marque`
--

CREATE TABLE `Marque` (
  `id_marque` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Marque`
--

INSERT INTO `Marque` (`id_marque`, `nom`) VALUES
(1, 'Belin'),
(2, 'Bounty'),
(47, 'Café'),
(3, 'Chamallows'),
(4, 'Chips'),
(46, 'Chocolat'),
(5, 'Coca Cola'),
(7, 'Desperados'),
(49, 'Diabolo'),
(12, 'Extrême'),
(9, 'Fanta'),
(10, 'Fuze tea'),
(48, 'Galopin'),
(11, 'Goût Glace'),
(6, 'Haribo'),
(45, 'Heineken'),
(32, 'Jus de reve '),
(33, 'Kinder Bueno'),
(34, 'Kit Kat'),
(35, 'Lion'),
(31, 'Lipton'),
(36, 'M&M\'S'),
(13, 'Magnum'),
(37, 'Maltesers'),
(38, 'Mentos'),
(39, 'Monster'),
(40, 'Oasis'),
(41, 'Orangina'),
(42, 'Pecheresse'),
(43, 'Perrier'),
(44, 'Petillant'),
(50, 'Pizza'),
(14, 'Pop-corn'),
(15, 'Pur Jus de pomme'),
(16, 'Redbull'),
(17, 'San Pelligrino'),
(18, 'Sceau'),
(19, 'Sirop'),
(20, 'Skittles'),
(30, 'Smarties'),
(21, 'Snickers'),
(22, 'Sprite'),
(23, 'Sucettes'),
(24, 'Super Frite'),
(25, 'Thé mémé'),
(26, 'Tourtel twist'),
(27, 'Tropico'),
(28, 'twix'),
(29, 'Vittel'),
(8, 'Volvic');

-- --------------------------------------------------------

--
-- Structure de la table `Mouvement`
--

CREATE TABLE `Mouvement` (
  `id_mouvement` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `type_mouvement` enum('entrée','sortie','casse','ajustement') NOT NULL,
  `date_mouvement` datetime NOT NULL,
  `quantite` float NOT NULL,
  `raison` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Mouvement`
--

INSERT INTO `Mouvement` (`id_mouvement`, `id_produit`, `type_mouvement`, `date_mouvement`, `quantite`, `raison`) VALUES
(1, 1, 'entrée', '2025-06-16 19:20:22', 10, 'Commande n°1'),
(2, 1, 'entrée', '2025-06-17 14:46:37', 3, 'Commande n°2'),
(3, 1, 'entrée', '2025-06-17 14:49:55', 3, 'Commande n°3'),
(4, 1, 'entrée', '2025-06-17 14:50:05', 1, 'Commande n°4'),
(5, 41, 'sortie', '2025-06-17 17:40:16', 3, 'Commande n°5'),
(6, 4, 'sortie', '2025-06-17 17:40:16', 3, 'Commande n°5'),
(7, 4, 'sortie', '2025-06-17 17:40:43', 1, 'Commande n°6'),
(8, 4, 'sortie', '2025-06-17 17:41:12', 2, 'Commande n°7'),
(9, 4, 'sortie', '2025-06-17 17:52:39', 4, 'Commande n°8'),
(10, 41, 'sortie', '2025-06-17 17:52:39', 3, 'Commande n°8'),
(11, 4, 'sortie', '2025-06-17 19:32:28', 2, 'Commande n°9'),
(12, 40, 'sortie', '2025-06-17 19:32:28', 2, 'Commande n°9');

-- --------------------------------------------------------

--
-- Structure de la table `Produit`
--

CREATE TABLE `Produit` (
  `id_produit` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `format` varchar(50) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL,
  `id_marque` int(11) DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `id_fournisseur` int(11) DEFAULT NULL,
  `seuil` int(11) DEFAULT NULL,
  `stock_actuel` int(11) DEFAULT 0,
  `qte` float(10,3) NOT NULL,
  `HT` float(10,3) NOT NULL,
  `promo` tinyint(1) NOT NULL,
  `reduction` int(11) NOT NULL DEFAULT 0,
  `pourcentage` float(10,2) NOT NULL DEFAULT 5.50,
  `Vente` float(10,3) NOT NULL,
  `TTC` float(10,3) GENERATED ALWAYS AS (round(case when `pourcentage` = 5.50 then `HT` * 1.055 else `HT` * 1.2 end,2)) STORED,
  `U` float(10,3) GENERATED ALWAYS AS (round(`TTC` / nullif(`qte`,0),2)) STORED,
  `Estimation` float(10,2) GENERATED ALWAYS AS (round(`U` * 3,0)) STORED,
  `Marge` float(10,3) GENERATED ALWAYS AS (round(`Vente` / nullif(`U`,0),3)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Produit`
--

INSERT INTO `Produit` (`id_produit`, `nom`, `format`, `actif`, `id_marque`, `id_categorie`, `id_fournisseur`, `seuil`, `stock_actuel`, `qte`, `HT`, `promo`, `reduction`, `pourcentage`, `Vente`) VALUES
(1, '1664 Blonde', '25cl', 0, 45, 1, 1, 100, 33, 15.000, 8.000, 0, 5, 5.50, 3.010),
(2, '1664 Blonde', '50cl', 0, 45, 1, NULL, 100, 18, 50.000, 30.000, 0, 0, 5.00, 3.000),
(4, 'Barb\'a box', NULL, 1, NULL, 2, NULL, 100, 1032, 38.950, 15.000, 0, 0, 5.50, 0.190),
(5, 'Monaco', NULL, 1, 1, 2, NULL, 100, 4009, 4.000, 5.000, 0, 0, 5.00, 2.000),
(6, 'Pizza', NULL, 1, 1, 2, NULL, 10, 46, 10.000, 20.000, 0, 0, 5.50, 15.000),
(7, 'Bounty', NULL, 1, 2, 2, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(8, 'Café ', NULL, 0, 47, 3, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(9, 'Cafe à emporter', NULL, 0, 47, 3, NULL, 10, 1, 10.000, 20.000, 0, 0, 5.50, 15.000),
(10, 'Cafe alllonge', NULL, 0, 47, 3, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(11, 'Café au lait à emporter', NULL, 0, 47, 3, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(12, 'Café creme', NULL, 0, 47, 3, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(13, 'Café creme à emporter', NULL, 0, 47, 3, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(14, 'Café deca', NULL, 0, 47, 3, NULL, 10, 20, 10.000, 20.010, 0, 0, 5.50, 15.000),
(15, 'Café Soluble', NULL, 0, 47, 3, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(16, 'Chamallows', NULL, 1, 3, 2, NULL, 10, 9, 30.010, 26.660, 0, 0, 20.00, 3.000),
(17, 'Chips à l\'ancienne', NULL, 1, 4, 2, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(18, 'Chips jura', '125g', 0, 4, 2, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(19, 'Chocolat a emporter', NULL, 0, 46, 3, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(20, 'Chocolat chaud', NULL, 0, 46, 3, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(21, 'Coca Verre', '33cl', 1, 6, 4, NULL, 10, 18, 0.000, 0.000, 0, 0, 5.50, 2.300),
(22, 'Coca Cherry', NULL, 1, 6, 4, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(23, 'Coca Cherry', '50cl', 1, 6, 4, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(24, 'Coca Cola canette', '33cl', 1, 6, 4, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(25, 'Coca Cola pet', '50cl', 1, 6, 4, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(26, 'Coca Cola zero', '33cl', 1, 6, 4, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(27, 'Coca Cola zero ', '50cl', 1, 6, 4, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(28, 'Delir pik', '120g', 1, 6, 2, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(29, 'Desperados', '33g', 1, 7, 4, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(30, 'Diabolo', NULL, 0, 49, 4, NULL, 10, 20, 10.000, 20.000, 0, 0, 5.50, 15.000),
(31, 'Don Association', '0.4', 0, NULL, 6, NULL, NULL, NULL, 10.000, 20.000, 0, 0, 5.50, 15.000),
(32, 'Don Association', '0.9', 0, NULL, 6, NULL, NULL, NULL, 10.000, 20.000, 0, 0, 5.50, 15.000),
(33, 'Don Association', '1.5', 0, NULL, 6, NULL, NULL, NULL, 10.000, 20.000, 0, 0, 5.50, 15.000),
(34, 'Don Association', '3', 0, NULL, 6, NULL, NULL, NULL, 10.000, 20.000, 0, 0, 5.50, 15.000),
(35, 'Dragibus ', '120', 1, 6, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(36, 'Dragibus Soft\r\n', NULL, 1, 6, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(37, 'Eau Petillante', '50', 1, 8, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(38, 'Fanta orange', '33', 1, 9, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(39, 'Fanta Petillant', '50', 1, 9, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(40, 'Fraise Tagada', '120', 1, 6, 2, NULL, NULL, 3, 10.000, 20.000, 0, 0, 5.50, 15.000),
(41, 'Fuze tea', '50', 1, 10, 4, NULL, NULL, 8, 10.000, 20.000, 0, 0, 5.50, 15.000),
(42, 'Galopin', NULL, 0, 49, 1, NULL, NULL, 1, 10.000, 20.000, 0, 0, 5.50, 15.000),
(43, 'Glace à l\'eau', NULL, 1, 11, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(44, 'Glace Chocolat', NULL, 1, 11, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(45, 'Glace Chocolat Pistache', NULL, 1, 11, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(46, 'Glace crême brulé', NULL, 1, 11, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(47, 'Glace extrême', NULL, 1, 12, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(48, 'Glace Magnum', NULL, 1, 13, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(49, 'Glace Magnum Chocolat', NULL, 1, 13, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(50, 'Pizza Campagnarde', NULL, 0, NULL, 9, NULL, NULL, 9, 10.000, 20.000, 0, 0, 5.50, 15.000),
(51, 'Pizza Dauphinoise', NULL, 0, NULL, 9, NULL, NULL, 9, 10.000, 20.000, 0, 0, 5.50, 15.000),
(52, 'Pizza Reine', NULL, 0, NULL, 9, NULL, NULL, 9, 10.000, 20.000, 0, 0, 5.50, 15.000),
(53, 'Pizza Vege', NULL, 0, NULL, 9, NULL, NULL, 9, 10.000, 20.000, 0, 0, 5.50, 15.000),
(54, 'Pizzbolo', NULL, 0, NULL, 9, NULL, NULL, 9, 10.000, 20.000, 0, 0, 5.50, 15.000),
(55, 'Pop-corn Sale pot', NULL, 1, 14, 8, NULL, NULL, 8, 10.000, 20.000, 0, 0, 5.50, 15.000),
(56, 'Pop-corn Grand', NULL, 1, 14, 8, NULL, NULL, 8, 10.000, 20.000, 0, 0, 5.50, 15.000),
(57, 'Pop-corn Maxi', NULL, 1, 14, 8, NULL, NULL, 8, 10.000, 20.000, 0, 0, 5.50, 15.000),
(58, 'Pop-corn Moyen', NULL, 1, 14, 8, NULL, NULL, 8, 10.000, 20.000, 0, 0, 5.50, 15.000),
(59, 'Pop-corn Petit', NULL, 1, 14, 8, NULL, NULL, 8, 10.000, 20.000, 0, 0, 5.50, 15.000),
(60, 'Pur Jus de pomme', NULL, 1, 15, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(61, 'Recharge Maxi-Sceaux', NULL, 0, 14, 8, NULL, NULL, 8, 10.000, 20.000, 0, 0, 5.50, 15.000),
(62, 'Recharge Grand-Sceaux', NULL, 0, 14, 8, NULL, NULL, 8, 10.000, 20.000, 0, 0, 5.50, 15.000),
(63, 'Redbull', '25', 1, 16, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(64, 'San Pelligrino petillant', '50', 1, 17, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(65, 'Schtroumpf', '120', 1, 6, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(66, 'Seau Captain America', NULL, 1, 14, 7, NULL, NULL, 7, 10.000, 20.000, 0, 0, 5.50, 15.000),
(67, 'Seau Stitch', NULL, 1, 14, 7, NULL, NULL, 7, 10.000, 20.000, 0, 0, 5.50, 15.000),
(68, 'Seau Gladiator', NULL, 1, 14, 7, NULL, NULL, 7, 10.000, 20.000, 0, 0, 5.50, 15.000),
(69, 'Skittles', '45', 1, 20, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(70, 'Skittles ', '174', 1, 20, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(71, 'Skittles', '318', 1, 20, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(72, 'Snickers', NULL, 1, 21, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(73, 'Sprite', '33', 1, 22, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(74, 'Sprite', '50', 1, 22, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(75, 'Sucette', NULL, 1, NULL, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(76, 'Super Frite', '120', 1, NULL, 9, NULL, NULL, 9, 10.000, 20.000, 0, 0, 5.50, 15.000),
(77, 'thé pêche blanche ', NULL, 1, 25, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(78, 'thé rafraichissante', NULL, 1, NULL, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(79, 'Tourtel Twist', NULL, 1, 26, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(80, 'tropico tropical', NULL, 1, 27, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(81, 'Twix', NULL, 1, 28, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(82, 'Vittel Kids', NULL, 1, 29, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(83, 'Smarties Popup', NULL, 1, 30, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(84, 'Glace Vanille', NULL, 1, 11, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(85, 'Glace Vanille Fraise', NULL, 1, 11, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(86, 'Glace Magnum Amandes', NULL, 1, 13, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(87, 'Glace Magnum Blanc', NULL, 1, 13, 6, NULL, NULL, 6, 10.000, 20.000, 0, 0, 5.50, 15.000),
(88, 'Happy cola', '120', 1, 6, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(89, 'Happy Life', '120', 1, 6, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(90, 'Hari Croco', '120', 1, 6, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(91, 'Hari Croco Pik', NULL, 1, 6, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(92, 'Lipton Ice Tea', NULL, 1, 31, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(93, 'Lipton Ice tea', '33', 1, 31, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(94, 'Jus Abricot', NULL, 1, 32, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(95, 'Jus Ananas', NULL, 1, 32, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(96, 'Jus Orange', NULL, 1, 32, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(97, 'Kinder Bueno ', NULL, 1, 33, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(98, 'Kit-Kat Ball Moyen', NULL, 1, 34, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(99, 'Kit-Kat Ball Grand', NULL, 1, 34, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(100, 'Kit-Kat Ball Petit', NULL, 1, 34, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(101, 'Kit-Kat Barre', NULL, 1, 34, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(102, 'Lion', NULL, 1, 35, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(103, 'M&M\'S', '82', 1, 36, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(104, 'M&M\'S', '200', 1, 36, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(105, 'M&M\'S', '45', 1, 36, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(106, 'M&M\'S Crispy', NULL, 1, 36, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(107, 'Maltesers', NULL, 1, 37, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(108, 'Mentos Fruits', NULL, 1, 38, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(109, 'Mentos Menthe', NULL, 1, 38, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(110, 'Mini M&M\'S', NULL, 1, 36, 2, NULL, NULL, 2, 10.000, 20.000, 0, 0, 5.50, 15.000),
(111, 'Monster Energy ', NULL, 1, 39, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(112, 'Monster Ultra Peachy ', NULL, 1, 39, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(113, 'Oasis Mini', NULL, 1, 40, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(114, 'Oasis Tropical BTE', '33', 1, 40, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(115, 'Oasis Mini', NULL, 1, 40, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(116, 'Oasis Tropical BTE', '33', 1, 40, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(117, 'Oasis Tropical', '33', 1, 40, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(118, 'Orangina', '33', 1, 41, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(119, 'Orangina Pik', NULL, 1, 41, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(120, 'Pecheresse', NULL, 1, 42, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(121, 'Perrier', '33', 1, 43, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(122, 'Petillant Pomme', NULL, 1, 44, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(123, 'Petillant Pomme-Citron', NULL, 1, 44, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(124, 'Petillant Pomme-Fruit_Rouge', NULL, 1, 44, 4, NULL, NULL, 4, 10.000, 20.000, 0, 0, 5.50, 15.000),
(126, 'Verre ', '50cl', 1, NULL, 3, NULL, 40, 60, 5.000, 2.000, 0, 0, 5.50, 2.000);

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `idRole` int(11) NOT NULL,
  `NomRole` varchar(255) NOT NULL,
  `Description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`idRole`, `NomRole`, `Description`) VALUES
(1, 'Super Administrateur', 'Utilisateur Unique'),
(2, 'Administrateur', 'il est administrateur'),
(3, 'Utilisateur', 'il ne peut pas accéder à tout');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Categorie`
--
ALTER TABLE `Categorie`
  ADD PRIMARY KEY (`id_categorie`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `Commande`
--
ALTER TABLE `Commande`
  ADD PRIMARY KEY (`id_commande`),
  ADD KEY `fk_commande_comptes` (`idUtilisateur`);

--
-- Index pour la table `comptes`
--
ALTER TABLE `comptes`
  ADD PRIMARY KEY (`idUtilisateur`);

--
-- Index pour la table `disposer`
--
ALTER TABLE `disposer`
  ADD PRIMARY KEY (`idRole`,`idUtilisateur`),
  ADD KEY `idUtilisateur` (`idUtilisateur`);

--
-- Index pour la table `Fournisseur`
--
ALTER TABLE `Fournisseur`
  ADD PRIMARY KEY (`id_fournisseur`);

--
-- Index pour la table `Groupe`
--
ALTER TABLE `Groupe`
  ADD PRIMARY KEY (`id_pack`);

--
-- Index pour la table `GrPR`
--
ALTER TABLE `GrPR`
  ADD KEY `GrPR_ibfk_1` (`id_pack`),
  ADD KEY `GrPR_ibfk_2` (`id_produit`);

--
-- Index pour la table `Ligne_commande`
--
ALTER TABLE `Ligne_commande`
  ADD PRIMARY KEY (`id_ligne`),
  ADD KEY `id_commande` (`id_commande`),
  ADD KEY `id_produit` (`id_produit`);

--
-- Index pour la table `Marque`
--
ALTER TABLE `Marque`
  ADD PRIMARY KEY (`id_marque`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `Mouvement`
--
ALTER TABLE `Mouvement`
  ADD PRIMARY KEY (`id_mouvement`),
  ADD KEY `id_produit` (`id_produit`);

--
-- Index pour la table `Produit`
--
ALTER TABLE `Produit`
  ADD PRIMARY KEY (`id_produit`),
  ADD KEY `categoriefk` (`id_categorie`),
  ADD KEY `fournisseur_ibfk_1` (`id_fournisseur`),
  ADD KEY `marquefk` (`id_marque`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`idRole`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Categorie`
--
ALTER TABLE `Categorie`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `Commande`
--
ALTER TABLE `Commande`
  MODIFY `id_commande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `comptes`
--
ALTER TABLE `comptes`
  MODIFY `idUtilisateur` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `Fournisseur`
--
ALTER TABLE `Fournisseur`
  MODIFY `id_fournisseur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `Groupe`
--
ALTER TABLE `Groupe`
  MODIFY `id_pack` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `Ligne_commande`
--
ALTER TABLE `Ligne_commande`
  MODIFY `id_ligne` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `Marque`
--
ALTER TABLE `Marque`
  MODIFY `id_marque` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT pour la table `Mouvement`
--
ALTER TABLE `Mouvement`
  MODIFY `id_mouvement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `Produit`
--
ALTER TABLE `Produit`
  MODIFY `id_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `idRole` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Commande`
--
ALTER TABLE `Commande`
  ADD CONSTRAINT `fk_commande_comptes` FOREIGN KEY (`idUtilisateur`) REFERENCES `comptes` (`idUtilisateur`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `disposer`
--
ALTER TABLE `disposer`
  ADD CONSTRAINT `disposer_ibfk_1` FOREIGN KEY (`idRole`) REFERENCES `roles` (`idRole`),
  ADD CONSTRAINT `disposer_ibfk_2` FOREIGN KEY (`idUtilisateur`) REFERENCES `comptes` (`idUtilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `GrPR`
--
ALTER TABLE `GrPR`
  ADD CONSTRAINT `GrPR_ibfk_1` FOREIGN KEY (`id_pack`) REFERENCES `Groupe` (`id_pack`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `GrPR_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `Produit` (`id_produit`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Ligne_commande`
--
ALTER TABLE `Ligne_commande`
  ADD CONSTRAINT `Ligne_commande_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `Commande` (`id_commande`);

--
-- Contraintes pour la table `Mouvement`
--
ALTER TABLE `Mouvement`
  ADD CONSTRAINT `Mouvement_ibfk_1` FOREIGN KEY (`id_produit`) REFERENCES `Produit` (`id_produit`);

--
-- Contraintes pour la table `Produit`
--
ALTER TABLE `Produit`
  ADD CONSTRAINT `categoriefk` FOREIGN KEY (`id_categorie`) REFERENCES `Categorie` (`id_categorie`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fournisseur_ibfk_1` FOREIGN KEY (`id_fournisseur`) REFERENCES `Fournisseur` (`id_fournisseur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `marquefk` FOREIGN KEY (`id_marque`) REFERENCES `Marque` (`id_marque`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
