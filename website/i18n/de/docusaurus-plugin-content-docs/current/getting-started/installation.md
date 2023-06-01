---
sidebar_position: 1
---

# Installation
Hier sind keine Besonderheiten zu beachten. Light Portal kann wie jede andere Modifikation für SMF installiert werden – durch die Paketverwaltung.

## Anforderungen
* Moderner Browser mit aktiviertem JavaScript
* Internet (das Portal und viele Plugins laden Scripts und Styles aus CDNs)
* PHP 8.0 oder höher
* PHP-Erweiterung `intl`, um einige Sprachen-Strings korrekt zu lokalisieren
* PHP-Erweiterungen `dom` und `simplexml`, um Seiten und Blöcke zu exportieren/importieren
* PHP-Erweiterung `zip`, um Plugins zu exportieren/importieren

:::info

Es reicht aus, das Archiv mit den Portaldateien (in SMF nennt sich das „Paket“) aus dem [offiziellen Katalog](https://custom.simplemachines.org/mods/index.php?mod=4244) herunterzuladen und über die Paketverwaltung in Ihr Forum hochzuladen.

:::

## Problembehebung
Falls Ihr Hosting-Provider zu „klug“ mit Berechtigungen umgeht und die Portaldateien während der Installation nicht entpackt wurden, müssen Sie manuell die Verzeichnisse `Themes` und `Sources` aus dem Modifikations-Archiv in Ihr Forumsverzeichnis (wo dieselben Themes- und Sources-Verzeichnisse bereits existieren und ebenso die Dateien `cron.php`, `SSI.php`, `Settings.php`, etc.) entpacken und die passenden Berechtigungen setzen. Diese sind meist `644`, `664` oder `666` für Dateien und `755`, `775` oder `777` für Verzeichnisse.

Außerdem müssen Sie die Datei `database.php` aus dem Modifikationsarchiv in das Wurzelverzeichnis Ihres Forums entpacken, Ausführungsberechtigungen (`666`) für die Datei setzen und mit dem Browser darauf zugreifen (Sie müssen als Forumsadministrator eingeloggt sein). Diese Datei enthält Anweisungen zur Erzeugung der vom Portal verwendeten Tabellen.

Falls Sie nach Abschluss aller obiger Schritte die Portaleinstellungen immer noch nicht im Adminbereich sehen, prüfen Sie die Zeile `$sourcedir/LightPortal/app.php` (Variable `integrale_pre_include`) in der Tabelle `<your_prefix>settings` Ihrer Datenbank. Nutzen Sie dazu die integrierte Suche von phpMyAdmin oder eines ähnlichen Werkzeugs.
