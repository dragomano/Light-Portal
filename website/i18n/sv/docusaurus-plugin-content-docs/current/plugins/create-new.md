---
sidebar_position: 2
---

# Lägg till plugin
Plugins är de tillägg som expanderar funktionerna i Light Portal. För att skapa din egen plugin, följ instruktionerna nedan.

:::info

You can use the **PluginMaker** as a helper to create your own plugins. Ladda ner och aktivera den på sidan _Admin -> Portalinställningar -> Plugins_.

:::

## Välja typ av plugin
För närvarande är följande typer av plugins tillgängliga:

* `block` — plugins som lägger till en ny typ av block för portalen
* `ssi` — plugins (vanligtvis block) som använder SSI-funktioner för att hämta data
* `editor` — plugins som lägger till en tredjepartsredigerare för olika typer av innehåll
* `comment` — plugins som lägger till en kommentar från tredje part widget istället för den inbyggda
* `parser` — plugins som implementerar tolken för innehållet i sidor och block
* `article` — plugins för att behandla innehållet i artikelkort på huvudsidan
* `frontpage` — plugins för att ändra huvudsidan för portalen
* `impex` — plugins för import och export av olika portalelement
* `block_options` och `page_options` — plugins som lägger till ytterligare parametrar för motsvarande enhet (block eller sida)
* `icons` — plugins som lägger till nya ikonbibliotek för att ersätta gränssnittselement eller för användning i blockrubriker
* `seo` — plugins som på något sätt påverkar synligheten av forumet på nätverket
* `other` — plugins som inte är relaterade till någon av kategorierna ovan

## Skapa en plugin-katalog
Skapa en separat mapp för dina plugin-filer, inuti `/Källor/LightPortal/Addons`. Till exempel, om din plugin heter `HelloWorld`, mappstrukturen bör se ut så här:

```
...(Addons)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Filen `index.php` kan kopieras från mappar med andra plugins. Filen `HelloWorld.php` innehåller plugin logik:

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

## Använda SSI
Om pluginen behöver hämta data med SSI-funktioner, använd den inbyggda `getFromSsi(sträng $function, ...$params)` metoden. Som parameter `$function` måste du skicka namnet på en av funktionerna i filen **SSI. hk**, utan prefix `ssi_`. Till exempel:

```php
<?php

    // Se funktionen ssi_topics i SSI.php-filen
    $data = $this->getFromSsi('topics', 'views', 10, 'array');
```

## Använder Composer
Din plugin kan använda tredjepartsbibliotek installerade via Composer. Se till att filen `composer.json` finns i plugin-katalogen, som innehåller nödvändiga beroenden. Innan du publicerar din plugin, öppna plugin-katalogen i kommandoraden och kör kommandot: `composer install --no-dev -o`. Efter det kan hela innehållet i plugin-katalogen paketeras som en separat ändring för SMF (till exempel se **PluginMaker** -paketet).
