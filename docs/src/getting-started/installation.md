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
- PHP 8.1 or higher
- PHP extension `intl` to localize some language strings properly
- PHP extensions `dom` and `simplexml` to export/import pages and blocks
- PHP extension `zip` to export/import plugins

:::info Note

It is enough to download the package with the portal files from the [official catalog](https://custom.simplemachines.org/mods/index.php?mod=4244) and upload via the package manager on your forum.

:::

## Troubleshooting

If your hosting is too "smart" with permissions and the portal files were not unpacked during installation, you need to manually extract the directories `Themes` and `Sources` from the modification archive into your forum folder (where the same Themes and Sources folders are already located, as well as files `cron.php`, `SSI.php`, `Settings.php`, etc) and set the appropriate permissions. Most often it is `644`, `664` or `666` for files, and `755`, `775` or `777` for folders.

Also you need to unpack the file `database.php` from modification archive to the root of your forum, set execution rights for it (`666`) and access it through the browser (you must be logged in as a forum administrator). This file contains instructions for creating the tables used by the portal.
