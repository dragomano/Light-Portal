---
description: Kurze Beschreibung der Plugin-Erstellungsschnittstelle
order: 2
---

# Plugin hinzufügen

Plugins sind die Erweiterungen, die die Möglichkeiten des Light Portals erweitern. Um Ihr eigenes Plugin zu erstellen, folgen Sie einfach den Anweisungen unten.

## PluginType enum

For better type safety and IDE support, you can use the `PluginType` enum instead of string values for the `type` parameter:

```php
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\PluginAttribute;

// Instead of: #[PluginAttribute(type: 'editor')]
#[PluginAttribute(type: PluginType::EDITOR)]

// Instead of: #[PluginAttribute(type: 'block')]
#[PluginAttribute(type: PluginType::BLOCK)]

// Instead of: #[PluginAttribute(type: 'other')]
#[PluginAttribute(type: PluginType::OTHER)]

// Or simply omit the type parameter since OTHER is default:
#[PluginAttribute]
```

Available PluginType values:

- `PluginType::ARTICLE` - For processing article content
- `PluginType::BLOCK` - For blocks
- `PluginType::BLOCK_OPTIONS` - For block options
- `PluginType::COMMENT` - For comment systems
- `PluginType::EDITOR` - For editors
- `PluginType::FRONTPAGE` - For frontpage modifications
- `PluginType::GAMES` - For games
- `PluginType::ICONS` - For icon libraries
- `PluginType::IMPEX` - For import/export
- `PluginType::OTHER` - Default type (can be omitted)
- `PluginType::PAGE_OPTIONS` - For page options
- `PluginType::PARSER` - For parsers
- `PluginType::SEO` - For SEO
- `PluginType::SSI` - For blocks with SSI functions

For plugins extending `Block`, `Editor`, `GameBlock`, or `SSIBlock` classes, the type is automatically inherited and doesn't need to be specified explicitly.

:::info Hinweis

Du kannst den **PluginMaker** als Helfer verwenden, um deine eigenen Plugins zu erstellen. Laden Sie es herunter und aktivieren Sie es auf der Seite _Admin -> Portal Einstellungen -> Plugins_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Wählen Sie den Typ des Plugins

Zur Zeit sind folgende Plug-In-Typen verfügbar:

| Type                            |                                                                                                                                       Beschreibung |
| ------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------: |
| `block`                         |                                                                  Plugins, die eine neue Art von Blöcken für das Portal hinzufügen. |
| `ssi`                           |                               Plugins (normalerweise Blöcke), die SSI-Funktionen verwenden, um Daten abzurufen. |
| `editor`                        |                                            Plugins, die einen Drittanbieter-Editor für verschiedene Arten von Inhalten hinzufügen. |
| `comment`                       |                                       Plugins, die ein Drittanbieter-Kommentar-Widget anstelle des eingebauten Plugins hinzufügen. |
| `parser`                        |                                                      Plugins, die den Parser für den Inhalt von Seiten und Blöcken implementieren. |
| `article`                       |                                                          Plugins zur Bearbeitung des Inhalts von Artikelkarten auf der Hauptseite. |
| `frontpage`                     |                                                                                     Plugins zum Ändern der Hauptseite des Portals. |
| `impex`                         |                                                             Plugins zum Importieren und Exportieren verschiedener Portal-Elemente. |
| `block_options`, `page_options` | Plugins, die zusätzliche Parameter für die entsprechende Entität (Block oder .page) hinzufügen. |
| `icons`                         |                          Plugins, die neue Icon-Bibliotheken zur Ersetzung von Interface-Elementen oder zur Verwendung in Block-Headern hinzufügen |
| `seo`                           |                                                       Plugins, die irgendwie die Sichtbarkeit des Forums im Netzwerk beeinflussen. |
| `other`                         |                                                           Plugins, die sich nicht auf eine der oben genannten Kategorien beziehen. |
| `games`                         |                                                                         Plugins that typically add a block with some kind of game. |

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

```php:line-numbers {16}
<?php declare(strict_types=1);

namespace LightPortal\Plugins\HelloWorld;

use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;

if (! defined('LP_NAME'))
    die('No direct access...');

#[PluginAttribute(icon: 'fas fa-globe')]
class HelloWorld extends Plugin
{
    public function init(): void
    {
        echo 'Hello world!';
    }

    // Other hooks and custom methods
}

```

## SSI

Wenn das Plugin Daten über SSI-Funktionen abrufen muss, verwenden Sie die eingebaute Methode `getFromSsi(string $function, ...$params)`. Als Parameter `$function` musst du den Namen einer der Funktionen übergeben, die in der Datei **SSI.php** enthalten sind, ohne das Präfix `ssi_`. Zum Beispiel:

```php:line-numbers {17}
<?php declare(strict_types=1);

namespace LightPortal\Plugins\TopTopics;

use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\SsiBlock;

if (! defined('LP_NAME'))
    die('No direct access...');

#[PluginAttribute(icon: 'fas fa-star')]
class TopTopics extends SsiBlock
{
    public function prepareContent(Event $e): void
    {
        $data = $this->getFromSSI('topTopics', 'views', 10, 'array');

        if ($data) {
            var_dump($data);
        } else {
            echo '<p>No top topics found.</p>';
        }
    }
}
```

## Blade templates

Your plugin can use a template with Blade markup. Zum Beispiel:

```php:line-numbers {16,20}
<?php declare(strict_types=1);

namespace LightPortal\Plugins\Calculator;

use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\Block;
use LightPortal\Utils\Traits\HasView;

if (! defined('LP_NAME'))
    die('No direct access...');

#[PluginAttribute(icon: 'fas fa-calculator')]
class Calculator extends Block
{
    use HasView;

    public function prepareContent(Event $e): void
    {
        echo $this->view(params: ['id' => $e->args->id]);
    }
}
```

**Instructions:**

1. Create the `views` subdirectory inside your plugin directory if it doesn't exist.
2. Create the file `default.blade.php` with the following content:

```blade
<div class="some-class-{{ $id }}">
    {{-- Your blade markup --}}
</div>

<style>
// Your CSS
</style>

<script>
// Your JS
</script>
```

## Composer

Ihr Plugin kann Drittanbieter-Bibliotheken verwenden, die über Composer installiert sind. Stelle sicher, dass sich die Datei `composer.json` mit den notwendigen Abhängigkeiten im Plugin-Verzeichnis befindet. Bevor Sie Ihr Plugin veröffentlichen, öffnen Sie das Plugin-Verzeichnis in der Kommandozeile und führen Sie den Befehl `composer install --no-dev -o` aus. Danach kann der gesamte Inhalt des Plugin-Verzeichnisses als separate Modifikation für SMF paketiert werden (zum Beispiel **PluginMaker** Paket).

Zum Beispiel:

::: code-group

```php:line-numbers {15} [CarbonDate.php]
<?php declare(strict_types=1);

namespace LightPortal\Plugins\CarbonDate;

use Carbon\Carbon;
use LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
    die('No direct access...');

class CarbonDate extends Plugin
{
    public function init(): void
    {
        require_once __DIR__ . '/vendor/autoload.php';

        $date = Carbon::now()->format('l, F j, Y \a\t g:i A');

        echo 'Current date and time: ' . $date;
    }
}
```

```json [composer.json]
{
    "require": {
      "nesbot/carbon": "^3.0"
    },
    "config": {
      "optimize-autoloader": true
    }
}
```

:::
