---
sidebar_position: 2
---

# Add plugin
Plugins are the extensions that expand the capabilities of the Light Portal. To create your own plugin, just follow the instructions below.

:::info

Since version 1.9, the functionality for creating plugins has been moved to a separate plugin — **PluginMaker**. Download and enable it on the page _Admin -> Portal settings -> Plugins_.

:::

## Choosing the type of plugin
Currently, the following types of plugins are available:

* `block` — plugins that add a new type of blocks for the portal
* `ssi` — plugins (usually blocks) that use SSI functions to retrieve data
* `editor` — plugins that add a third-party editor for different types of content
* `comment` — plugins that add a third-party comment widget instead of the built-in
* `parser` — plugins that implement the parser for the content of pages and blocks
* `article` — plugins for processing the content of article cards on the main page
* `frontpage` — plugins for changing the main page of the portal
* `impex` — plugins for importing and exporting various portal elements
* `block_options` and `page_options` — plugins that add additional parameters for the corresponding entity (block or page)
* `icons` — plugins that add new icon libraries to replace interface elements or for use in block headers
* `seo` — plugins that somehow affect the visibility of the forum on the network
* `other` — plugins that are not related to any of the categories above

## Creating a plugin directory
Create a separate folder for your plugin files, inside `/Sources/LightPortal/Addons`. For example, if your plugin is called `HelloWorld`, the folder structure should look like this:

```
...(Addons)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

File `index.php` can be copied from folders of other plugins. The file `HelloWorld.php` contains the plugin logic:

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

## Using SSI
If the plugin needs to retrieve any data using SSI functions, use the built-in `getFromSsi(string $function, ...$params)` method. As parameter `$function` you must pass the name of one of the functions contained in file **SSI.php**, without prefix `ssi_`. For example:

```php
<?php

    // See ssi_topTopics function in the SSI.php file
    $data = $this->getFromSsi('topTopics', 'views', 10, 'array');
```

:::caution

Without SSI.php file, the above method will not work.

:::

## Using Composer
Your plugin can use third-party libraries installed through Composer. Make sure that the `composer.json` file is located in the plugin directory, which contains the necessary dependencies. Before publishing your plugin, open the plugin directory in the command line and run the command: `composer install --no-dev -o`. After that, the entire contents of the plugin directory can be packaged as a separate modification for SMF (for example see **PluginMaker** package).
