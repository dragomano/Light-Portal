---
sidebar_position: 2
---

# Plug-in toevoegen
Plugins zijn de extensies die de mogelijkheden van de Light Portaal uitbreiden. Volg de onderstaande instructies om je eigen plugin aan te maken.

:::info

Sinds versie 1.9 is de functionaliteit voor het maken van plugins verplaatst naar een aparte plugin — **PluginMaker**. Download en activeer het op de pagina _Beheer -> Portal instellingen -> Plugins_.

:::

## Het type plug-in kiezen
Momenteel zijn de volgende plugins beschikbaar:

* `block` - plugins die een nieuw type blokken toevoegen aan het portaal
* `ssi` - plugins (meestal blokken) die SSI-functies gebruiken om gegevens op te halen
* `editor` - plugins die een derde partij editor toevoegen voor verschillende soorten inhoud
* `comment` - plugins die een commentaar widget van derden toevoegen in plaats van de ingebouwde
* `parser` - plugins die de parser implementeren voor de inhoud van pagina's en blokken
* `article` – plugins voor het verwerken van de inhoud van de artikelkaarten op de hoofdpagina
* `frontpage` - plugins voor het wijzigen van de hoofdpagina van het portaal
* `impex` - plugins voor het importeren en exporteren van verschillende portaalelementen
* `block_options` and `page_options` — plugins die extra parameters voor de bijbehorende entiteit (blok of pagina) toevoegen
* `icons` - plugins die nieuwe icoonbibliotheken toevoegen om interface elementen te vervangen of om te gebruiken in block headers
* `seo` - plugins die de zichtbaarheid van het forum op het netwerk beïnvloeden
* `other` — plugins die niet gerelateerd zijn aan een van de bovenstaande categorieën

## Aanmaken van een plugin map
Maak een aparte map voor uw plugin bestanden, binnen `/Sources/LightPortal/Addons`. Bijvoorbeeld, als de plugin `HelloWorld`heet, zou de mapstructuur er als volgt uit moeten zien:

```
...(Addons)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Bestand `index.php` kan worden gekopieerd uit mappen van andere plugins. Het bestand `HelloWorld.php` bevat de plugin logic:

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

## SSI gebruiken
Als de plugin gegevens moet ophalen met behulp van SSI-functies, gebruik dan de ingebouwde `getFromSsi(string $function, ...$params)` methode. Als parameter `$function` moet u de naam opgeven van een van de functies van het bestand **SSI. hp**, zonder voorvoegsel `ssi_`. Bijvoorbeeld:

```php
<?php

    // Zie ssi_topTopTopics functie in het SSI.php bestand
    $data = $this->getFromSsi('topTopics', 'views', 10, 'array');
```

## Gebruik componist
Uw plugin kan gebruik maken van bibliotheken van derden die zijn geïnstalleerd via Composer. Zorg ervoor dat het bestand `composer.json` zich bevindt in de plugin directory, die de benodigde afhankelijkheden bevat. Voordat u de plugin publiceert, open de plugin map in de command line en voer de opdracht uit: `composer install --no-dev -o`. Daarna kan de gehele inhoud van de plugin directory worden verpakt als een afzonderlijke wijziging voor SMF (bijvoorbeeld zie **PluginMaker** pakket).
