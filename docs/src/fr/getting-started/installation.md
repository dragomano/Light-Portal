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
- PHP 8.2 or higher
- L'extension PHP `intl` pour localiser correctement certaines chaînes de langage
- Extensions PHP `dom` et `simplexml` pour exporter/importer des pages et des blocs
- Extension PHP `zip` pour exporter/importer des plugins
- MySQL 5.7+ / MariaDB 10.5+ / PostgreSQL 12+

:::info Note

Il suffit de télécharger le package contenant les fichiers du portail depuis le [catalogue officiel](https://custom.simplemachines.org/mods/index.php?mod=4244) et de le télécharger via le gestionnaire de packages de votre forum.

:::

## Testing

You can try our [Docker files](https://github.com/dragomano/Light-Portal/tree/d1074c8486ed9eb2f9e89e3afebce2b914d4d570/_docker) or your preffered LAMP/WAMP/MAMP app.
