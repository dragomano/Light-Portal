---
description: Brief description of the plugin creation interface
order: 2
---

# Add plugin

Plugins are the extensions that expand the capabilities of the Light Portal. To create your own plugin, just follow the instructions below.

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

You can use the **PluginMaker** as a helper to create your own plugins. Download and enable it on the page _Admin -> Portal settings -> Plugins_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Choosing the type of plugin

Currently, the following types of plugins are available:

| Type                            |                                                                                   Description |
| ------------------------------- | --------------------------------------------------------------------------------------------: |
| `block`                         |                                         Plugins that add a new type of blocks for the portal. |
| `ssi`                           |                             Plugins (usually blocks) that use SSI functions to retrieve data. |
| `editor`                        |                         Plugins that add a third-party editor for different types of content. |
| `comment`                       |                        Plugins that add a third-party comment widget instead of the built-in. |
| `parser`                        |                        Plugins that implement the parser for the content of pages and blocks. |
| `article`                       |                         Plugins for processing the content of article cards on the main page. |
| `frontpage`                     |                                             Plugins for changing the main page of the portal. |
| `impex`                         |                                  Plugins for importing and exporting various portal elements. |
| `block_options`, `page_options` |          Plugins that add additional parameters for the corresponding entity (block or page). |
| `icons`                         | Plugins that add new icon libraries to replace interface elements or for use in block headers |
| `seo`                           |                       Plugins that somehow affect the visibility of the forum on the network. |
| `other`                         |                                  Plugins that are not related to any of the categories above. |
| `games`                         |                                    Plugins that typically add a block with some kind of game. |

## Creating a plugin directory

Create a separate folder for your plugin files, inside `/Sources/LightPortal/Plugins`. For example, if your plugin is called `HelloWorld`, the folder structure should look like this:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

File `index.php` can be copied from folders of other plugins. The file `HelloWorld.php` contains the plugin logic:

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

If the plugin needs to retrieve any data using SSI functions, use the built-in `getFromSsi(string $function, ...$params)` method. As parameter `$function` you must pass the name of one of the functions contained in file **SSI.php**, without prefix `ssi_`. For example:

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

Your plugin can use a template with Blade markup. For example:

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

Your plugin can use third-party libraries installed through Composer. Make sure that the `composer.json` file, which contains the necessary dependencies, is located in the plugin directory. Before publishing your plugin, open the plugin directory in the command line and run the command: `composer install --no-dev -o`. After that, the entire contents of the plugin directory can be packaged as a separate modification for SMF (for example see **PluginMaker** package).

For example:

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
