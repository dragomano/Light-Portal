---
sidebar_position: 2
---

# Přidat plugin
Pluginy jsou rozšíření, která rozšiřují možnosti lehkého portálu. Chcete-li vytvořit svůj vlastní plugin, stačí dodržovat pokyny níže.

:::info

Od verze 1.9 byly funkce pro vytváření pluginů přesunuty do samostatného pluginu – **PluginMaker**. Stáhněte a povolte to na stránce _Admin -> Nastavení portálu -> Pluginy_.

:::

## Výběr typu pluginu
V současné době jsou k dispozici následující typy pluginů:

* `blok` — pluginy, které přidávají nový typ bloků portálu
* `ssi` – pluginy (obvykle blokují), které používají funkce SSI k načítání dat
* `editor` – pluginy, které přidávají editor třetích stran pro různé typy obsahu
* `komentář` — pluginy, které přidávají komentář třetí strany namísto vestavěného
* `parser` – pluginy, které implementují parser pro obsah stránek a bloků
* `článek` – zásuvné moduly pro zpracování obsahu karet článku na hlavní stránce
* `frontpage` – zásuvné moduly pro změnu hlavní stránky portálu
* `impex` – pluginy pro import a export různých prvků portálu
* `block_options` a `page_options` – pluginy, které přidávají další parametry odpovídající entity (blok nebo stránka)
* `ikony` – pluginy, které přidávají nové knihovny ikon k nahrazení prvků rozhraní nebo pro použití v záhlaví bloku
* `seo` — pluginy, které nějakým způsobem ovlivňují viditelnost fóra v síti
* `jiný` – pluginy, které nesouvisejí s žádnou z výše uvedených kategorií

## Vytváření adresáře pluginů
Vytvořte samostatnou složku pro vaše soubory pluginu uvnitř `/Sources/LightPortal/Addons`. Například, pokud se váš plugin nazývá `HelloWorld`, struktura složek by měla vypadat takto:

```
...(Addons)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Soubor `index.php` může být zkopírován ze složek jiných pluginů. Soubor `HelloWorld.php` obsahuje logiku pluginu:

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

## Použití SSI
Pokud plugin potřebuje načíst data pomocí funkcí SSI, použijte vestavěnou metodu `getFromSsi(string $function, ...$params`. Jako parametr `$function` musíte zadat název jedné z funkcí obsažených v souboru **SSI. hp**, bez prefixu `ssi_`. Například:

```php
<?php

    // Viz funkce ssi_topTopics v souboru SSI.php
    $data = $this->getFromSsi('topics', 'views', 10, 'array');
```

:::caution

Bez souboru SSI.php nebude výše uvedená metoda fungovat.

:::

## Používá se editor
Plugin může používat knihovny třetích stran nainstalované prostřednictvím Composer. Ujistěte se, že soubor `composer.json` je umístěn v adresáři plugin, který obsahuje potřebné závislosti. Před zveřejněním pluginu otevřete adresář pluginu v příkazovém řádku a spusťte příkaz: `composer install --no-dev -o`. Poté může být celý obsah adresáře pluginů zabalen jako samostatná úprava SMF (například viz balíček **PluginMaker**).
