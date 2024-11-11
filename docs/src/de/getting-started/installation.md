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
- PHP 8.1 oder höher
- PHP-Erweiterung `intl` um einige Sprachen-Strings korrekt zu lokalisieren
- PHP-Erweiterungen `dom` und `simplexml`, um Seiten und Blöcke zu exportieren/importieren
- PHP-Erweiterung `zip` um Plugins zu exportieren/importieren

:::info Hinweis

Es reicht aus, das Paket mit den Portaldateien aus dem [offiziellen Katalog](https://custom.simplemachines.org/mods/index.php?mod=4244) herunterzuladen und über den Paketmanager in deinem Forum hochzuladen.

:::

## Problembehebung

Falls Ihr Hosting-Provider zu „klug“ mit Berechtigungen umgeht und die Portaldateien während der Installation nicht entpackt wurden, müssen Sie manuell die Verzeichnisse `Themes` und `Sources` aus dem Modifikations-Archiv in Ihr Forumsverzeichnis (wo dieselben `Themes`- und `Sources`-Verzeichnisse bereits existieren und ebenso die Dateien `cron.php`, `SSI.php`, `Settings.php`, etc.) entpacken und die passenden Berechtigungen setzen. Meistens ist es `644`, `664` oder `666` für Dateien, und `755`, `775` oder `777` für Ordner.

Außerdem müssen Sie die Datei `database.php` aus dem Modifikationsarchiv in das Wurzelverzeichnis Ihres Forums entpacken, Ausführungsberechtigungen (`666`) für die Datei setzen und mit dem Browser darauf zugreifen (Sie müssen als Forumsadministrator eingeloggt sein). Diese Datei enthält Anweisungen zur Erzeugung der vom Portal verwendeten Tabellen.
