---
description: Kratek opis vmesnika za ustvarjanje vtičnikov
order: 2
---

# Dodaj vtičnik

Vtičniki so razširitve, ki povečajo zmogljivosti Light Portala. Za ustvarjanje lastnega vtičnika preprosto sledi spodnjim navodilom.

:::info Opomba

Za lažje ustvarjanje lastnih vtičnikov lahko uporabiš **PluginMaker**. Prenesi in omogoči na strani _Admin -> Nastavitve portala -> Vtičniki_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Izbira vrste vtičnika

Trenutno so na voljo naslednje vrste vtičnikov:

### `block`

Vtičniki, ki dodajo nov tip blokov za portal.

### `ssi`

Vtičniki (ponavadi bloki), ki uporabljajo SSI funkcije za pridobivanje podatkov.

### `editor`

Vtičniki, ki dodajo urejevalnik tretjih strani za različne vrste vsebin.

### `comment`

Vtičniki, ki namesto vgrajenega dodajo urejevalnik komentarjev

### `parser`

Vtičniki, ki implementirajo parser za vsebino strani in blokov.

### `article`

Vtičniki za obdelavo vsebine kartic člankov na glavni strani.

### `frontpage`

Vtičniki za prilagoditev glavne strani portala.

### `impex`

Vtičniki za uvoz in izvoz različnih elementov portala.

### `block_options` | `page_options`

Vtičniki, ki dodajo dodatne parametre za ustrezno entiteto (blok ali stran).

### `icons`

Vtičniki, ki dodajo nove knjižnice ikon za zamenjavo elementov vmesnika ali za uporabo v naslovih blokov.

### `seo`

Vtičniki, ki na nek način vplivajo na vidnost foruma na omrežju.

### `other`

Vtičniki, ki niso povezani z nobeno od zgoraj navedenih kategorij.

## Ustvarjanje mape za vtičnike

Ustvari ločeno mapo za datoteke svojega vtičnika znotraj `/Sources/LightPortal/Plugins`. Na primer, če se tvoj vtičnik imenuje `HelloWorld`, bi morala struktura map izgledati takole:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Datoteko `index.php` je mogoče kopirati iz map drugih vtičnikov. Datoteka `HelloWorld.php` vsebuje logiko vtičnika:

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

## Uporaba SSI

Če vtičnik potrebuje pridobivanje podatkov z uporabo SSI funkcij, uporabi vgrajeno metodo `getFromSsi(string $function, ...$params)`. Kot parameter `$function` moraš posredovati ime ene izmed funkcij, ki so vsebovane v datoteki **SSI.php**, brez predpone `ssi_`. Na primer:

```php
<?php

// See ssi_topTopics function in the SSI.php file
$data = $this->getFromSSI('topTopics', 'views', 10, 'array');
```

## Uporaba Composerja

Tvoj vtičnik lahko uporablja zunanje knjižnice, nameščene preko Composerja. Prepričaj se, da je datoteka `composer.json`, ki vsebuje potrebne odvisnosti, nameščena v mapi vtičnika. Preden objaviš svoj vtičnik, odpri mapo vtičnika v ukazni vrstici in zaženi ukaz: `composer install --no-dev -o`. Po tem lahko celotno vsebino mape vtičnika pakiraš kot ločeno modifikacijo za SMF (na primer glej paket **PluginMaker**).
