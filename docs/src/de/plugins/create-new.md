---
description: Kurze Beschreibung der Plugin-Erstellungsschnittstelle
order: 2
---

# Plugin hinzufügen

Plugins sind die Erweiterungen, die die Möglichkeiten des Light Portals erweitern. Um Ihr eigenes Plugin zu erstellen, folgen Sie einfach den Anweisungen unten.

:::info Hinweis

Du kannst den **PluginMaker** als Helfer verwenden, um deine eigenen Plugins zu erstellen. Laden Sie es herunter und aktivieren Sie es auf der Seite _Admin -> Portal Einstellungen -> Plugins_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Wählen Sie den Typ des Plugins

Zur Zeit sind folgende Plug-In-Typen verfügbar:

### `block`

Plugins, die eine neue Art von Blöcken für das Portal hinzufügen.

### `ssi`

Plugins (normalerweise Blöcke), die SSI-Funktionen verwenden, um Daten abzurufen.

### `editor`

Plugins, die einen Drittanbieter-Editor für verschiedene Arten von Inhalten hinzufügen.

### `comment`

Plugins, die ein Drittanbieter-Kommentar-Widget anstelle des eingebauten Plugins hinzufügen.

### `parser`

Plugins, die den Parser für den Inhalt von Seiten und Blöcken implementieren.

### `article`

Plugins zur Bearbeitung des Inhalts von Artikelkarten auf der Hauptseite.

### `frontpage`

Plugins zum Ändern der Hauptseite des Portals.

### `impex`

Plugins zum Importieren und Exportieren verschiedener Portal-Elemente.

### `block_options` | `page_options`

Plugins, die zusätzliche Parameter für die entsprechende Entität (Block oder .page) hinzufügen.

### `icons`

Plugins, die neue Icon-Bibliotheken zur Ersetzung von Interface-Elementen oder zur Verwendung in Block-Headern hinzufügen

### `seo`

Plugins, die irgendwie die Sichtbarkeit des Forums im Netzwerk beeinflussen.

### `other`

Plugins, die sich nicht auf eine der oben genannten Kategorien beziehen.

## Plugin-Verzeichnis erstellen

Erstellen Sie einen separaten Ordner für Ihre Plugin-Dateien im Verzeichnis `/Sources/LightPortal/Plugins`. Wenn z. B. dein Plugin `HelloWorld` genannt wird, sollte die Ordnerstruktur so aussehen:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Datei `index.php` kann aus Ordnern anderer Plugins kopiert werden. Die Datei `HelloWorld.php` enthält die Plugin-Logik:

```php:line-numbers
<?php

namespace Bugo\LightPortal\Plugins\HelloWorld;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class HelloWorld extends Plugin
{
    // FA icon (for blocks only)
    public string $icon = 'fas fa-globe';

    // Your plugin's type
    public string $type = 'other';

    // Optional init method
    public function init(): void
    {
        // Access to global variables: Utils::$context['user'], Config::$modSettings['variable'], etc.
        // Access to language variables: Lang::$txt['lp_hello_world']['variable_name']
    }

    // Custom properties and methods
}

```

## SSI verwenden

Wenn das Plugin Daten über SSI-Funktionen abrufen muss, verwenden Sie die eingebaute Methode `getFromSsi(string $function, ...$params)`. Als Parameter `$function` musst du den Namen einer der Funktionen übergeben, die in der Datei **SSI.php** enthalten sind, ohne das Präfix `ssi_`. Zum Beispiel:

```php
<?php

// See ssi_topTopics function in the SSI.php file
$data = $this->getFromSsi('topTopics', 'views', 10, 'array');
```

## Komponist verwenden

Ihr Plugin kann Drittanbieter-Bibliotheken verwenden, die über Composer installiert sind. Stelle sicher, dass sich die Datei `composer.json` mit den notwendigen Abhängigkeiten im Plugin-Verzeichnis befindet. Bevor Sie Ihr Plugin veröffentlichen, öffnen Sie das Plugin-Verzeichnis in der Kommandozeile und führen Sie den Befehl `composer install --no-dev -o` aus. Danach kann der gesamte Inhalt des Plugin-Verzeichnisses als separate Modifikation für SMF paketiert werden (zum Beispiel **PluginMaker** Paket).
