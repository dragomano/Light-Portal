---
description: Breve descrizione dell'interfaccia di creazione del plugin
order: 2
---

# Aggiungi Plugin

I plugin sono le estensioni che espandono le capacità del Light Portal. Per creare il tuo plugin, basta seguire le istruzioni seguenti.

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

:::info Note

Puoi utilizzare **PluginMaker** come assistente per creare i tuoi plugin. Scaricalo e abilitalo nella pagina _Amministrazione -> Portale -> Plugins_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Scelta del tipo di plugin

Scelta del tipo di plugin

| Tipo                            |                                                                                                                               Descrizione |
| ------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------: |
| `block`                         |                                                               Plugin che aggiungono nuovi tipi di blocchi per il portale. |
| `ssi`                           |                       Plugin (solitamente blocchi) che utilizzano le funzioni SSI per recuperare dati. |
| `editor`                        |                                             Plugin che aggiungono un editor di terze parti per diversi tipi di contenuti. |
| `comment`                       |                                               Plugin che aggiungono un widget di terze parti invece del widget integrato. |
| `parser`                        |                                                      Plugin che implementano l'analisi del contenuto di pagine e blocchi. |
| `article`                       |                              Plugin per l'elaborazione del contenuto delle schede degli articoli nella pagina principale. |
| `frontpage`                     |                                                                   Plugin per modificare la pagina principale del portale. |
| `impex`                         |                                                              Plugin per importare ed esportare vari elementi del portale. |
| `block_options`, `page_options` |              Plugin che aggiungono parametri aggiuntivi per l'entità corrispondente (blocco o pagina). |
| `icons`                         | Plugin che aggiungono nuove librerie di icone per sostituire gli elementi dell'interfaccia o da utilizzare nelle intestazioni dei blocchi |
| `seo`                           |                                                   Plugin che in qualche modo influenzano la visibilità del forum in rete. |
| `other`                         |                                                   Plugin che non sono correlati a nessuna delle categorie sopra indicate. |
| `games`                         |                                                                Plugins that typically add a block with some kind of game. |

## Creazione della cartella del plugin

Crea una cartella separata per i file dei plugin, all'interno di `/Sources/LightPortal/Plugins`. Ad esempio, se il tuo plugin si chiama "HelloWorld", la struttura delle cartelle dovrebbe assomigliare a questa:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Il file `index.php` può essere copiato da cartelle di altri plugin. Il file `HelloWorld.php` contiene la logica del plugin:

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

Se il plugin deve recuperare dati utilizzando le funzioni SSI, utilizzare il metodo integrato `getFromSsi(string $function, ...$params)`. Come parametro `$function` bisogna passare il nome di una delle funzioni contenute nel file **SSI.php**, senza prefisso `ssi_`. Ad esempio:

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

Your plugin can use a template with Blade markup. Ad esempio:

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

Il tuo plugin può utilizzare librerie di terze parti installate tramite Composer. Assicurati che il file `composer.json`, che contiene le dipendenze necessarie, si trovi nella cartella del plugin. Prima di pubblicare il tuo plugin, apri la cartella dei plugin con il terminale ed esegui il comando: `composer install --no-dev -o`. Successivamente, l'intero contenuto della cartella dei plugin può essere impacchettato come una modifica separata per SMF (vedi ad esempio il pacchetto **PluginMaker**).

Ad esempio:

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
