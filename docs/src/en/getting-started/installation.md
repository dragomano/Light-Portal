---
description: List of requirements for portal installation, as well as solutions to possible problems
order: 1
---

# Installation

There are no subtleties here. Light Portal can be installed like any other modification for SMF - through the package manager.

## Requirements

- [SMF 2.1.x](https://download.simplemachines.org)
- Modern browser with JavaScript enabled
- Internet (the portal and many plugins load scripts and styles from CDN)
- PHP 8.2 or higher
- PHP extension `intl` to localize some language strings properly
- PHP extensions `dom` and `simplexml` to export/import pages and blocks
- PHP extension `zip` to export/import plugins
- MySQL 8.0+ / MariaDB 10.5+ / PostgreSQL 12+ / SQLite 3.35.0+ (for testing only, does not support in SMF)

:::info Note

It is enough to download the package with the portal files from the [official catalog](https://custom.simplemachines.org/mods/index.php?mod=4244) and upload via the package manager on your forum.

:::

## Testing

You can try our [Docker files](https://github.com/dragomano/Light-Portal/tree/v3.0/_docker) or your preffered LAMP/WAMP/MAMP app.
