---
description: Brève description de l'interface de création de plugin
order: 2
---

# Ajouter un plugin

Les plugins sont les extensions qui étendent les capacités du portail Lumière. Pour créer votre propre plugin, suivez les instructions ci-dessous.

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

Tu peux utiliser le **PluginMaker** comme aide pour créer tes propres plugins. Téléchargez et activez-le sur la page _Admin -> Paramètres du portail -> Plugins_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Choix du type de plugin

Actuellement, les types de plugins suivants sont disponibles :

| Type                            |                                                                                                                                          Description |
| ------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------: |
| `block`                         |                                                                       Plugins qui ajoutent un nouveau type de blocs pour le portail. |
| `ssi`                           |                  Les plugins (généralement les blocs) qui utilisent les fonctions SSI pour récupérer des données. |
| `editor`                        |                                                              Plugins qui ajoutent un éditeur tiers pour différents types de contenu. |
| `comment`                       |                                                   Les plugins qui ajoutent un widget de commentaire tiers au lieu du widget intégré. |
| `parser`                        |                                                         Plugins qui implémentent l'analyseur pour le contenu des pages et des blocs. |
| `article`                       |                                                   Plugins pour le traitement du contenu des cartes d'article sur la page principale. |
| `frontpage`                     |                                                                                  Plugins pour changer la page principale du portail. |
| `impex`                         |                                                                        Plugins pour importer et exporter divers éléments de portail. |
| `block_options`, `page_options` | Plugins qui ajoutent des paramètres supplémentaires pour l'entité correspondante (bloc ou .page). |
| `icons`                         |                    Plugins qui ajoutent de nouvelles bibliothèques d'icônes pour remplacer les éléments de l'interface ou pour les en-têtes de blocs |
| `seo`                           |                                             Plugins qui affectent d'une manière ou d'une autre la visibilité du forum sur le réseau. |
| `other`                         |                                                                      Les plugins qui ne sont liés à aucune des catégories ci-dessus. |
| `games`                         |                                                                           Plugins that typically add a block with some kind of game. |

## Création d'un répertoire de plugins

Créez un dossier séparé pour vos fichiers de plugin, à l'intérieur de `/Sources/LightPortal/Plugins`. Par exemple, si votre plugin est appelé `HelloWorld`, la structure des dossiers devrait ressembler à ceci :

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Le fichier `index.php` peut être copié à partir de dossiers d'autres plugins. Le fichier `HelloWorld.php` contient la logique du plugin :

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

Si le plugin a besoin de récupérer des données en utilisant des fonctions SSI, utilisez la méthode intégrée `getFromSsi(string $function, ...$params)`. En tant que paramètre `$function` vous devez passer le nom d'une des fonctions contenues dans le fichier **SSI.php**, sans préfixe `ssi_`. Par exemple :

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

Your plugin can use a template with Blade markup. Par exemple :

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

Votre plugin peut utiliser des bibliothèques tierces installées via Composer. Assurez-vous que le fichier `composer.json` qui contient les dépendances nécessaires, se trouve dans le répertoire des plugins. Avant de publier votre plugin, ouvrez le répertoire des plugins en ligne de commande et exécutez la commande : `composer install --no-dev -o`. Après cela, tout le contenu du répertoire de plugins peut être empaqueté comme une modification séparée pour SMF (par exemple voir **PluginMaker**).

Par exemple :

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
