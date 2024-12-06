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

### `block`

Plugins that add a new type of blocks for the portal.

### `ssi`

Plugins (usually blocks) that use SSI functions to retrieve data.

### `editor`

Plugins that add a third-party editor for different types of content.

### `comment`

Plugins that add a third-party comment widget instead of the built-in.

### `parser`

Plugins that implement the parser for the content of pages and blocks.

### `article`

Plugins for processing the content of article cards on the main page.

### `frontpage`

Plugins for changing the main page of the portal.

### `impex`

Plugins for importing and exporting various portal elements.

### `block_options` | `page_options`

Plugins that add additional parameters for the corresponding entity (block or page).

### `icons`

Plugins that add new icon libraries to replace interface elements or for use in block headers

### `seo`

Plugins that somehow affect the visibility of the forum on the network.

### `other`

Plugins that are not related to any of the categories above.

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

## Using SSI

If the plugin needs to retrieve any data using SSI functions, use the built-in `getFromSsi(string $function, ...$params)` method. As parameter `$function` you must pass the name of one of the functions contained in file **SSI.php**, without prefix `ssi_`. For example:

```php
<?php

// See ssi_topTopics function in the SSI.php file
$data = $this->getFromSSI('topTopics', 'views', 10, 'array');
```

## Using Composer

Your plugin can use third-party libraries installed through Composer. Make sure that the `composer.json` file, which contains the necessary dependencies, is located in the plugin directory. Before publishing your plugin, open the plugin directory in the command line and run the command: `composer install --no-dev -o`. After that, the entire contents of the plugin directory can be packaged as a separate modification for SMF (for example see **PluginMaker** package).
