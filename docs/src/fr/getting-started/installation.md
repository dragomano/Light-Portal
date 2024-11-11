---
description: Liste des exigences pour l'installation du portail, ainsi que des solutions aux éventuels problèmes
order: 1
---

# Installation

Il n'y a pas de subtilités ici. Light Portal peut être installé comme toute autre modification de SMF - via le gestionnaire de paquets.

## Exigences

- [SMF 2.1.x](https://download.simplemachines.org)
- Navigateur moderne avec JavaScript activé
- Internet (le portail et de nombreux plugins chargent des scripts et des styles à partir du CDN)
- PHP 8.1 ou supérieur
- L'extension PHP `intl` pour localiser correctement certaines chaînes de langage
- Extensions PHP `dom` et `simplexml` pour exporter/importer des pages et des blocs
- Extension PHP `zip` pour exporter/importer des plugins

:::info Note

Il suffit de télécharger le package contenant les fichiers du portail depuis le [catalogue officiel](https://custom.simplemachines.org/mods/index.php?mod=4244) et de le télécharger via le gestionnaire de packages de votre forum.

:::

## Dépannage

Si votre hébergement est trop "intelligent" avec les permissions et que les fichiers du portail n'ont pas été décompressés pendant l'installation, vous devez extraire manuellement les répertoires `Themes` et `Sources` de l'archive de modification dans votre dossier de forum (où les mêmes dossiers Thèmes et Sources sont déjà localisés, ainsi que les fichiers `cron.php`, `SSI.php`, `Settings.php`, etc.) et définissez les permissions appropriées. Le plus souvent, c'est `644`, `664` ou `666` pour les fichiers, et `755`, `775` ou `777` pour les dossiers.

Vous devez également décompresser le fichier `database.php` de l'archive de modification à la racine de votre forum, définir les droits d'exécution (`666`) et y accéder via le navigateur (vous devez être connecté en tant qu'administrateur du forum). Ce fichier contient des instructions pour créer les tables utilisées par le portail.
