---
sidebar_position: 2
---

# Plugin hinzufügen

Plugins sind die Erweiterungen, die die Fähigkeiten von Light Portal erweitern. Um Ihr eigenes Plugin zu erzeugen, folgen Sie einfach den Anweisungen unterhalb.

:::info

Sie können **PluginMaker** bei der Erstellung Ihrer eigenen Plugins zu Hilfe nehmen. Laden Sie es herunter und aktivieren Sie es auf der Seite _Administration -> Portaleinstellungen -> Plugins_.

:::

## Wählen Sie die Art von Plugin

Aktuell sind die folgenden Arten von Plugins verfügbar:

### `Block`

Plugins that add a new type of blocks for the portal.

### `ssi`

Plugins (usually blocks) that use SSI functions to retrieve data.

### `editor`

Plugins that add a third-party editor for different types of content.

### `comment`

Plugins that add a third-party comment widget instead of the built-in.

### `parser`

Plugins that implement the parser for the content of pages and blocks.

### `article`

Plugins for processing the content of article cards on the main page.

### `frontpage`

Plugins for changing the main page of the portal.

### `impex`

Plugins for importing and exporting various portal elements.

### `block_options` | `page_options`

Plugins that add additional parameters for the corresponding entity (block or .page).

### `icons`

Plugins that add new icon libraries to replace interface elements or for use in block headers

### `seo`

Plugins that somehow affect the visibility of the forum on the network.

### `other`

Plugins that are not related to any of the categories above.

## Ein Plugin-Verzeichnis erzeugen

Erzeugen Sie ein separates Verzeichnis für Ihre Plugin-Dateien, innerhalb von `/Sources/LightPortal/Addons`. Falls Ihr Plugin zum Beispiel `HelloWorld` heißt, sollte die Verzeichnisstruktur wie folgt aussehen:

```
...(Addons)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Die Datei `index.php` kann aus dem Verzeichnis eines anderen Plugins kopiert werden. Die Datei `HelloWorld.php` enthält die Plugin-Logik:

```php
<?php

/**
 * HelloWorld.php
 *
 * @package HelloWorld (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Nickname <email>
 * @copyright 2023 Nickname
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 23.03.23 (date when the source code of the plugin was created or last updated, in the format dd.mm.yy)
 */

namespace Bugo\LightPortal\Addons\HelloWorld;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
    die('No direct access...');

class HelloWorld extends Plugin
{
    // Used properties and methods
    // Access to global variables: $this->context['user'], $this->modSettings['variable'], etc.
    // Access to language variables: $this->txt['lp_hello_world']['variable_name']
}

```

## Verwendung von SSI

Falls das Plugin Daten über SSI-Funktionen abrufen muss, verwenden Sie die eingebaute `getFromSsi(string $function, ...$params)`-Methode. Als Parameter `$function` müssen Sie den Namen einer der Funktionen übergeben, die in der Datei **SSI.php** enthalten sind, ohne Präfix `ssi_`. Zum Beispiel:

```php
<?php

// See ssi_topTopics function in the SSI.php file
$data = $this->getFromSsi('topTopics', 'views', 10, 'array');
```

## Verwendung von Composer

Ihr Plugin kann Drittanbieter-Bibliotheken verwenden, die mit Composer installiert wurden. Stellen Sie sicher, dass die Datei `composer.json`, die die notwendigen Abhängigkeiten enthält, im Plugins-Verzeichnis liegt. Bevor Sie Ihr Plugin veröffentlichen, öffnen Sie das Plugin-Verzeichnis auf der Kommandozeile und führen diesen Befehl aus: `composer install --no-dev -o`. Anschließend kann der gesamte Inhalt des Plugin-Verzeichnisses als separate SMF-Modifikation verpackt werden (sehen Sie sich zum Beispiel das **PluginMaker**-Paket an).
