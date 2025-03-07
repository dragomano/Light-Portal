---
description: Korte beschrijving van de plugin creatie interface
order: 2
---

# Plug-in toevoegen

Plugins zijn de extensies die de mogelijkheden van de Light Portaal uitbreiden. Volg de onderstaande instructies om je eigen plugin aan te maken.

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

```php:line-numbers {17}
<?php declare(strict_types=1);

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
        echo 'Hello world!';
    }

    // Hookable and custom methods
}

```

## SSI gebruiken

Als de plugin gegevens moet ophalen met behulp van SSI-functies, gebruik dan de ingebouwde `getFromSsi(string $function, ...$params)` methode. Als parameter `$functionmoet u de naam van een van de functies invullen in het bestand **SSI.php**, zonder prefix `ssi_\`. Bijvoorbeeld:

```php
$data = $this->getFromSSI('topTopics', 'views', 10, 'array');
```

## Gebruik componist

Uw plugin kan gebruik maken van bibliotheken van derden die zijn geïnstalleerd via Composer. Zorg ervoor dat het `composer.json` bestand, dat de benodigde afhankelijkheden bevat, zich in de plugin map bevindt. Voordat u de plugin publiceert, open de plugin map in de command line en voer de opdracht uit `composer install --no-dev -o`. Daarna kan de gehele inhoud van de plugin directory worden verpakt als een afzonderlijke wijziging voor SMF (bijvoorbeeld zie **PluginMaker** pakket).
