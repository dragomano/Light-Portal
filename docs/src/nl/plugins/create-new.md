---
description: Korte beschrijving van de plugin creatie interface
order: 2
---

# Plug-in toevoegen

Plugins zijn de extensies die de mogelijkheden van de Light Portaal uitbreiden. Volg de onderstaande instructies om je eigen plugin aan te maken.

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

:::info Notitie

U kunt de **PluginMaker** gebruiken als helper om uw eigen plugins te maken. Download en schakel het in op de pagina _Admin -> Portal instellingen -> Plugins_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Het type plug-in kiezen

Momenteel zijn de volgende plugins beschikbaar:

| Type                            |                                                                                                                              Description |
| ------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------: |
| `block`                         |                                                            Plugins die een nieuw type blokken toevoegen aan het portaal. |
| `ssi`                           |                         Plugins (meestal blokken) die SSI-functies gebruiken om gegevens op te halen. |
| `editor`                        |                                         Plugins die een derde partij editor voor verschillende soorten inhoud toevoegen. |
| `comment`                       |                                                       Plugins die een commentaar van derden toevoegen in plaats van de ingebouwde widget |
| `parser`                        |                                              Plugins die de parser implementeren voor de inhoud van pagina's en blokken. |
| `article`                       |                                        Plugins voor het verwerken van de inhoud van de artikelkaarten op de hoofdpagina. |
| `frontpage`                     |                                                              Plugins voor het wijzigen van de hoofdpagina van de portal. |
| `impex`                         |                                             Plugins voor het importeren en exporteren van verschillende portalelementen. |
| `block_options`, `page_options` | Plugins die extra parameters voor de bijbehorende entiteit toevoegen (blok of .page). |
| `icons`                         |                    Plugins die nieuwe icoonbibliotheken toevoegen om interface elementen te vervangen of om te gebruiken in blok headers |
| `seo`                           |                        Plugins die de zichtbaarheid van het forum op het netwerk op de een of andere manier beïnvloeden. |
| `other`                         |                                               Plugins die niet gerelateerd zijn aan een van de bovenstaande categorieën. |
| `games`                         |                                                               Plugins that typically add a block with some kind of game. |

## Aanmaken van een plugin map

Maak een aparte map voor je pluginbestanden, binnen `/Sources/LightPortal/Plugins`. Bijvoorbeeld, als uw plugin `HelloWorld` heet, zou de mapstructuur er als volgt uit moeten zien:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Bestand `index.php` kan worden gekopieerd uit mappen van andere plugins. Het bestand `HelloWorld.php` bevat de plugin logica:

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

Als de plugin gegevens moet ophalen met behulp van SSI-functies, gebruik dan de ingebouwde `getFromSsi(string $function, ...$params)` methode. Als parameter `$functionmoet u de naam van een van de functies invullen in het bestand **SSI.php**, zonder prefix `ssi_\`. Bijvoorbeeld:

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

Your plugin can use a template with Blade markup. Bijvoorbeeld:

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

Uw plugin kan gebruik maken van bibliotheken van derden die zijn geïnstalleerd via Composer. Zorg ervoor dat het `composer.json` bestand, dat de benodigde afhankelijkheden bevat, zich in de plugin map bevindt. Voordat u de plugin publiceert, open de plugin map in de command line en voer de opdracht uit `composer install --no-dev -o`. Daarna kan de gehele inhoud van de plugin directory worden verpakt als een afzonderlijke wijziging voor SMF (bijvoorbeeld zie **PluginMaker** pakket).

Bijvoorbeeld:

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
