---
description: Kratek opis vmesnika za ustvarjanje vtičnikov
order: 2
---

# Dodaj vtičnik

Vtičniki so razširitve, ki povečajo zmogljivosti Light Portala. Za ustvarjanje lastnega vtičnika preprosto sledi spodnjim navodilom.

## PluginType enum

Za večjo varnost tipov in podporo v IDE-ju lahko za parameter `type ` uporabiš `PluginType ` enum namesto nizov (string vrednosti):

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

Razpoložljive vrednosti PluginType:

- PluginType::ARTICLE - za obdelavo vsebine člankov
- PluginType::BLOCK - za bloke
- PluginType::BLOCK_OPTIONS - za možnosti blokov
- PluginType::COMMENT - za sistem komentarjev
- PluginType::EDITOR - za urejevalnike
- PluginType::FRONTPAGE - za spremembe začetne strani
- PluginType::GAMES - za igre
- PluginType::ICONS - za knjižnice ikon
- PluginType::IMPEX - za uvoz/izvoz
- PluginType::OTHER - privzeti tip (lahko se izpusti)
- PluginType::PAGE_OPTIONS - za možnosti strani
- PluginType::PARSER - za parserje
- `PluginType::SEO` - Za SEO
- `PluginType::SSI` - Za bloke s SSI funkcijami

Za vtičnike, ki razširjajo razrede `Block`, `Editor`, `GameBlock` ali `SSIBlock`, se tip samodejno deduje in ga ni treba izrecno določiti.

:::info Opomba

Za lažje ustvarjanje lastnih vtičnikov lahko uporabiš **PluginMaker**. Prenesi in omogoči na strani _Admin -> Nastavitve portala -> Vtičniki_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Izbira vrste vtičnika

Trenutno so na voljo naslednje vrste vtičnikov:

| Vrsta                           |                                                                                                                      Opis |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------------: |
| `block`                         |                                                             Vtičniki, ki dodajo nov tip blokov za portal. |
| `ssi`                           |       Vtičniki (ponavadi bloki), ki uporabljajo SSI funkcije za pridobivanje podatkov. |
| `editor`                        |                                  Vtičniki, ki dodajo urejevalnik tretjih strani za različne vrste vsebin. |
| `comment`                       |                                                            Vtičniki, ki namesto vgrajenega dodajo urejevalnik komentarjev |
| `parser`                        |                                           Vtičniki, ki implementirajo parser za vsebino strani in blokov. |
| `article`                       |                                             Vtičniki za obdelavo vsebine kartic člankov na glavni strani. |
| `frontpage`                     |                                                           Vtičniki za prilagoditev glavne strani portala. |
| `impex`                         |                                                    Vtičniki za uvoz in izvoz različnih elementov portala. |
| `block_options`, `page_options` |           Vtičniki, ki dodajo dodatne parametre za ustrezno entiteto (blok ali stran). |
| `icons`                         | Vtičniki, ki dodajo nove knjižnice ikon za zamenjavo elementov vmesnika ali za uporabo v naslovih blokov. |
| `seo`                           |                                          Vtičniki, ki na nek način vplivajo na vidnost foruma na omrežju. |
| `other`                         |                                        Vtičniki, ki niso povezani z nobeno od zgoraj navedenih kategorij. |
| `games`                         |                                                      Vtičniki, ki običajno dodajo blok z neko vrsto igre. |

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

Če vtičnik potrebuje pridobivanje podatkov z uporabo SSI funkcij, uporabi vgrajeno metodo `getFromSsi(string $function, ...$params)`. Kot parameter `$function` moraš posredovati ime ene izmed funkcij, ki so vsebovane v datoteki **SSI.php**, brez predpone `ssi_`. Na primer:

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

## Blade predloge

Tvoj vtičnik lahko uporabi predlogo z Blade markup. Na primer:

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

**Navodila:**

1. Ustvari podmapo `views` svoji mapi vtičnika, če še ne obstaja.
2. Ustvari datoteko `default.blade.php` z naslednjo vsebino:

```blade
<div class="some-class-{{ $id }}">
    {{-- Tvoj Blade markup --}}
</div>

<style>
// Tvoja CSS-koda
</style>

<script>
// Tvoja JS-koda
</script>
```

## Composer

Tvoj vtičnik lahko uporablja zunanje knjižnice, nameščene preko Composerja. Prepričaj se, da je datoteka `composer.json`, ki vsebuje potrebne odvisnosti, nameščena v mapi vtičnika. Preden objaviš svoj vtičnik, odpri mapo vtičnika v ukazni vrstici in zaženi ukaz: `composer install --no-dev -o`. Po tem lahko celotno vsebino mape vtičnika pakiraš kot ločeno modifikacijo za SMF (na primer glej paket **PluginMaker**).

Na primer:

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
