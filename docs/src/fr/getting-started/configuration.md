---
description: Un bref résumé des paramètres du portail disponible
order: 3
outline:
  - 2
  - 3
---

# Paramètres du portail

Utilisez l'accès rapide via l'élément dans le menu principal du forum ou la section correspondante dans le panneau d'administration pour ouvrir les paramètres du portail.

Nous ne décrirons pas en détail chacun des paramètres disponibles, nous ne mentionnerons que les plus importants.

## Paramètres généraux

Dans cette section, vous pouvez entièrement personnaliser la page d'accueil du portail, activer le mode autonome et modifier les permissions des utilisateurs pour accéder aux éléments du portail.

### Paramètres de la page d'accueil et des articles

Pour modifier le contenu de la page d'accueil du portail, sélectionnez le mode "frontpage du portail" approprié :

- Désactivée
- Page spécifiée (seule la page sélectionnée sera affichée)
- Toutes les pages des catégories sélectionnées
- Pages sélectionnées
- Tous les sujets des tableaux sélectionnés
- Sujets sélectionnés
- Sections sélectionnées

### Mode autonome

This is a mode where you can specify your own home page, and remove unnecessary items from the main menu (user list, calendar, etc.). Voir `portal.php` à la racine du forum par exemple.

### Permissions

Ici, vous notez simplement OMS peut et que peut faire avec les différents éléments (blocs et pages) du portail.

## Pages et blocs

Dans cette section, vous pouvez modifier les paramètres généraux des pages et des blocs utilisés lors de leur création et de leur affichage.

## Panneaux

Dans cette section, vous pouvez modifier certains des paramètres des panneaux de portail existants et personnaliser la direction des blocs dans ces panneaux.

![Panels](panels.png)

## Divers

Dans cette section, vous pouvez modifier divers paramètres auxiliaires du portail, ce qui peut être utile pour les développeurs de modèles et de plugins.

### Mode de compatibilité

- La valeur du paramètre **action** du portail - vous pouvez modifier ce paramètre pour utiliser le Light Portal en conjonction avec d'autres modifications similaires. Ensuite, la page d'accueil s'ouvrira à l'adresse indiquée.
- Le paramètre **page** pour les pages du portail - voir ci-dessus. De même, pour les pages du portail, changez le paramètre et ils s'ouvriront avec différents URL.

### Entretien

- Optimisation hebdomadaire des tables de portails - activez cette option pour qu'une fois par semaine, les lignes avec des valeurs vides dans les tables de portails dans la base de données soient supprimées et les tables seront optimisées.
