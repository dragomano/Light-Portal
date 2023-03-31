---
sidebar_position: 2
---

# Tilføj plugin
Plugins er de udvidelser, der udvider funktionerne i Lysportalen. For at oprette dit eget plugin, bare følg instruktionerne nedenfor.

:::info

Siden version 1.9 er funktionaliteten til at skabe plugins blevet flyttet til et separat plugin — **PluginMaker**. Download og aktiver det på siden _Admin -> Portal indstillinger -> Plugins_.

:::

## Valg af type af plugin
I øjeblikket er følgende typer af plugins tilgængelige:

* `blok` — plugins, der tilføjer en ny type blokke til portalen
* `ssi` — plugins (normalt blokke), der bruger SSI-funktioner til at hente data
* `editor` — plugins, der tilføjer en tredjeparts-editor til forskellige typer indhold
* `comment` — plugins, der tilføjer en tredjeparts kommentar widget i stedet for den indbyggede
* `parser` — plugins, der implementerer parseren for indholdet af sider og blokke
* `article` — plugins til behandling af indholdet af artikelkort på forsiden
* `frontside` — plugins til ændring af portalens hovedside
* `impex` — plugins til import og eksport af forskellige portalelementer
* `block_options` og `page_options` — plugins, der tilføjer yderligere parametre for den tilsvarende enhed (blok eller side)
* `ikoner` — plugins, der tilføjer nye ikonbiblioteker til at erstatte grænsefladeelementer eller til brug i blokoverskrifter
* `seo` — plugins, der på en eller anden måde påvirker synligheden af forummet på netværket
* `andre` — plugins, der ikke er relateret til nogen af ovennævnte kategorier

## Opretter en plugin mappe
Opret en separat mappe til dine plugin-filer, inde i `/Sources/LightPortal/Addons`. For eksempel, hvis dit plugin kaldes `HelloWorld`, skal mappestrukturen se sådan ud:

```
...(Addons)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Fil `index.php` kan kopieres fra mapper af andre plugins. Filen `HelloWorld.php` indeholder plugin-logiken:

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

## Brug af SSI
Hvis plugin'et skal hente data ved hjælp af SSI-funktioner, skal du bruge den indbyggede `getFromSsi(streng $function, ...$params)` -metode. Som parameter `$function` skal du bestå navnet på en af funktionerne i filen **SSI. hp**, uden præfiks `ssi_`. For eksempel:

```php
<?php

    // Se ssi_topTopics-funktionen i SSI.php-filen
    $data = $this->getFromSsi('topTopics', 'views', 10, 'array');
```

:::caution

Uden SSI.php fil, vil ovenstående metode ikke fungere.

:::

## Brug Af Komponist
Dit plugin kan bruge tredjepartsbiblioteker installeret via Composer. Sørg for, at `composer.json` -filen er placeret i plugin-mappen, som indeholder de nødvendige afhængigheder. Før du publicerer dit plugin, skal du åbne plugin-mappen i kommandolinjen og køre kommandoen: `composer install --no-dev -o`. Derefter kan hele indholdet af plugin mappen pakkes som en separat ændring for SMF (for eksempel se **PluginMaker** pakke).
