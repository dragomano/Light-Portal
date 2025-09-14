---
description: Brief description of the plugin creation interface
order: 2
---

# Add plugin

Plugins are the extensions that expand the capabilities of the Light Portal. To create your own plugin, just follow the instructions below.

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

```php:line-numbers {17}
<?php declare(strict_types=1);

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
        echo 'Hello world!';
    }

    // Hookable and custom methods
}

```

## Using SSI

If the plugin needs to retrieve any data using SSI functions, use the built-in `getFromSsi(string $function, ...$params)` method. As parameter `$function` you must pass the name of one of the functions contained in file **SSI.php**, without prefix `ssi_`. For example:

```php
$data = $this->getFromSSI('topTopics', 'views', 10, 'array');
```

### Example: SSI Integration Plugin (TopTopics)

**Description:**

This plugin demonstrates how to use SSI functions to retrieve and display data from SMF. The TopTopics plugin fetches the top topics based on views and displays them in a list.

**Installation:**

1. Create a new plugin directory `/Sources/LightPortal/Plugins/TopTopics/`.
2. Create the required files as per the plugin structure guidelines.
3. Enable the plugin in the admin panel under _Admin -> Portal settings -> Plugins_.

**Usage:**

Add this plugin as a block type to display top topics on your portal pages.

**Code:**

```php:line-numbers {17}
<?php declare(strict_types=1);

namespace Bugo\LightPortal\Plugins\TopTopics;

use Bugo\LightPortal\Plugins\SSI;
use Bugo\LightPortal\Plugins\Event;

if (! defined('LP_NAME'))
    die('No direct access...');

class TopTopics extends SSI
{
    public string $icon = 'fas fa-star';

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

## Examples of built-in plugins

### Simple block plugin example (Calculator)

The Calculator plugin is a simple block that displays a calculator widget. Here's its main code:

```php:line-numbers {14,25}
<?php declare(strict_types=1);

namespace Bugo\LightPortal\Plugins\Calculator;

use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\Traits\HasView;

if (! defined('LP_NAME'))
    die('No direct access...');

class Calculator extends Block
{
    use HasView;

    public string $icon = 'fas fa-calculator';

    public function prepareBlockParams(Event $e): void
    {
        $e->args->params['no_content_class'] = true;
    }

    public function prepareContent(Event $e): void
    {
        echo $this->view(params: ['id' => $e->args->id]);
    }
}
```

This plugin uses a view template to render the calculator UI.

#### Creating the template file for Calculator

For the Calculator plugin to display the calculator interface, create a template file `default.blade.php` in the `/Sources/LightPortal/Plugins/Calculator/views/` directory. This file contains the HTML, CSS, and JavaScript needed for the calculator widget.

**Instructions:**

1. Create the `views` subdirectory inside your Calculator plugin directory if it doesn't exist.
2. Create the file `default.blade.php` with the following content:

```blade
<div class="calculator" id="calculator-{{ $id }}">
    {{-- Your blade markup --}}
</div>

<style>
// Your CSS
</style>

<script>
// Your JS
</script>
```

3. Save the file. The plugin will automatically use this template when rendering the block.

This template provides a basic calculator interface with buttons for numbers, operators, equals, and clear. The JavaScript uses `eval()` for calculation (in a real-world scenario, consider using a safer evaluation method).

## Using Composer

Your plugin can use third-party libraries installed through Composer. Make sure that the `composer.json` file, which contains the necessary dependencies, is located in the plugin directory. Before publishing your plugin, open the plugin directory in the command line and run the command: `composer install --no-dev -o`. After that, the entire contents of the plugin directory can be packaged as a separate modification for SMF (for example see **PluginMaker** package).

### Example: Composer Dependency Plugin (CarbonDate)

**Description:**

This plugin demonstrates how to use Composer for managing PHP dependencies. The CarbonDate plugin uses the popular Carbon library to display the current date and time in a formatted manner.

**Installation:**

1. Create a new plugin directory `/Sources/LightPortal/Plugins/CarbonDate/`.
2. Create a `composer.json` file in the plugin directory with the following content:

```json
{
    "require": {
      "nesbot/carbon": "^3.0"
    },
    "config": {
      "optimize-autoloader": true
    }
}
```

3. Open the plugin directory in the command line and run: `composer install --no-dev -o`.
4. Create the plugin files following the standard structure.
5. Enable the plugin in the admin panel.

**Usage:**

This plugin can be used in blocks or pages to display formatted dates. The `init` method can be called to output the current date.

**Code:**

```php:line-numbers {17}
<?php declare(strict_types=1);

namespace Bugo\LightPortal\Plugins\CarbonDate;

use Bugo\LightPortal\Plugins\Plugin;
use Carbon\Carbon;

if (! defined('LP_NAME'))
    die('No direct access...');

class CarbonDate extends Plugin
{
    public string $type = 'other';

    public function init(): void
    {
        require_once __DIR__ . '/vendor/autoload.php';

        $date = Carbon::now()->format('l, F j, Y \a\t g:i A');

        echo 'Current date and time: ' . $date;
    }
}
```
