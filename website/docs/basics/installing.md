---
sidebar_position: 1
---

# Installing
There are no subtleties here. Light Portal can be installed like any other modification for SMF - through the package manager.

:::info

It is enough to download the archive with the portal files (in SMF this is called a package) from the [official catalog](https://custom.simplemachines.org/mods/index.php?mod=4244) and upload via the package manager on your forum.

:::

## Troubleshooting
If your hosting is too "smart" with permissions and the portal files were not unpacked during installation, you need to manually extract the directories `Themes` and `Sources` from the modification archive into your forum folder (where the same Themes and Sources folders are already located, as well as files `cron.php`, `SSI.php`, `Settings.php`, etc) and set the appropriate permisssions. Most often it is `644`, `664` or `666` for files, and `755`, `775` or `777` for folders.

Also you need to unpack the file `database.php` from modification archive to the root of your forum, set execution rights for it (`666`) and access it through the browser (you must be logged in as a forum administrator). This file contains instructions for creating the tables used by the portal. The message `Database changes are complete! Please wait...` will confirm the successful execution of the script.

If, after completing all the above steps, you still do not see the section with the portal settings in the admin panel, check for the line `$sourcedir/LightPortal/app.php` (variable `integrate_pre_include`) in the table `settings` of your database. To do this, use the built-in search of the phpMyAdmin or another similar utility.
