---
description: Liste der Anforderungen für die Portal-Installation sowie Lösungen für mögliche Probleme
order: 1
---

# Installation

Hier sind keine Besonderheiten zu beachten. Light Portal kann wie jede andere Modifikation für SMF installiert werden – durch die Paketverwaltung.

## Anforderungen

- [SMF 2.1.x](https://download.simplemachines.org)
- Moderner Browser mit aktiviertem JavaScript
- Internet (das Portal und viele Plugins laden Scripts und Styles aus CDNs)
- PHP 8.2 or higher
- PHP-Erweiterung `intl` um einige Sprachen-Strings korrekt zu lokalisieren
- PHP-Erweiterungen `dom` und `simplexml`, um Seiten und Blöcke zu exportieren/importieren
- PHP-Erweiterung `zip` um Plugins zu exportieren/importieren
- MySQL 5.7+ / MariaDB 10.5+ / PostgreSQL 12+

:::info Hinweis

Es reicht aus, das Paket mit den Portaldateien aus dem [offiziellen Katalog](https://custom.simplemachines.org/mods/index.php?mod=4244) herunterzuladen und über den Paketmanager in deinem Forum hochzuladen.

:::

## Testing

You can try our [Docker files](https://github.com/dragomano/Light-Portal/tree/d1074c8486ed9eb2f9e89e3afebce2b914d4d570/_docker) or your preffered LAMP/WAMP/MAMP app.
