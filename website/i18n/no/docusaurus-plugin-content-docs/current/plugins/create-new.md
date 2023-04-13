---
sidebar_position: 2
---

# Legg til tilleggsprogram
Plugins er utvidelsen som utvider funksjonaliteten i lett portalen. For å lage din egen plugin, følg instruksene nedenfor.

:::info

Siden versjon 1.9, har funksjonaliteten for å opprette plugins blitt flyttet til en egen plugin — **PluginMaker**. Last ned og aktiver den på siden _Admin -> Portalinnstillinger -> Plugins_.

:::

## Velger type plugin
For øyeblikket er følgende typer plugins tilgjengelige:

* `block` – plugins som legger til en ny type blokker for portalen
* `ssi` — plugins (vanligvis blokker) som bruker SSI-funksjoner for å hente data
* `editor` - programtillegg som legger til en tredjeparts-editor for forskjellige typer innhold
* `comment` — plugins som legger til en tredjeparts kommentar widget i stedet for innebygget
* `parser` — programtillegg som implementerer tolker for innhold på sider og blokker
* `article` — programtillegg for behandling av innhold i artikkelkort på hovedsiden
* `frontpage` — programtillegg for å endre hovedsiden i portalen
* `impex` — plugins for import og eksport av ulike portalelementer
* `block_options` and `page_options` — plugins som legger til ekstra parametere for tilsvarende enhet (blokk eller side)
* `icons` — plugins som legger til nye ikon biblioteker for å erstatte grensesnittelementer eller for bruk i blokkoverskrifter
* `seo` — plugins som påvirker synligheten til forumet på nettverket
* `other` — plugins som ikke er relatert til noen av kategoriene ovenfor

## Oppretter en plugin-mappe
Opprett en egen mappe for plugin-filene, i `/Sources/LightPortal/Addons`. For eksempel, hvis pluginen din heter `HalloWorld`, skal mappestrukturen se slik ut:

```
...(Addons)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Fil `index.php` kan kopieres fra andre mapper på programtillegg. Filen `HelloWorld.php` inneholder logoen til pluginen:

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

## Bruker SSI
Hvis utvidelsen må hente noen data ved hjelp av SSI-funksjoner, bruk innebygget `getFromSsi(streng $function, ...$params)` metode. Som parameter `$function` må du bestå navnet på en av funksjonene i filen **SSI. hp**, uten prefiks `ssi_`. For eksempel:

```php
<?php

    // Se ssi_topics funksjonen i filen SSI.php
    $data = $this->getFromSsi('toptopTopics', 'views', 10, 'array');
```

## Bruke komponist
Plugin kan bruke tredjeparts biblioteker installert gjennom komponist. Pass på at filen `composer.json` ligger i plugin mappen som inneholder de nødvendige avhengighetene. Før du publiserer din plugin, åpne plugin mappen i kommandolinjen og kjør kommandoen: `composer install --no-dev -o`. Deretter kan hele innholdet i plugin mappen pakkes som en separat modifikasjon for SMF (for eksempel se **PluginMaker** pakke).
