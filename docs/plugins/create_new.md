# Add plugin
Plugins, or addons - add-ons that expand the capabilities of the Light Portal. To create your own addon, just follow the instructions below.

!> Since version 1.9, the functionality for creating plugins has been moved to a separate plugin — **PluginMaker**. Download and enable it on the page _Admin -> Portal settings -> Plugins_.

## Choosing the type of addon
Currently, the following types of addons are available:

* 'block' — addons that add a new type of blocks for the portal
* 'editor' — addons that add a third-party editor for different types of content
* 'comment' — addons that add a third-party comment widget instead of the built-in
* 'parser' — addons that implement the parser for the content of pages and blocks
* 'article' — addons for processing the content of article cards on the main page
* 'frontpage' — addons for changing the main page of the portal
* 'impex' — addons for importing and exporting various portal elements
* 'other' — addons that are not related to any of the categories above
* 'block_options' and 'page_options' — addons that add additional parameters for the corresponding entity (block or page)

## Creating an addon directory
Create a separate folder for your addon files, inside `/Sources/LightPortal/Addons`. For example, if your addon is called `HelloWorld`, the folder structure should look like this:

```php
    ...(Addons)
        HelloWorld\
            langs\
                 english.php
                 index.php
            index.php
            HelloWorld.php
```

File `index.php` can be copied from folders of other addons. The file `HelloWorld.php` contains addon logic:

```php
<?php

/**
 * HelloWorld.php (Name of the current file)
 *
 * @package HelloWorld (Light Portal) (name of the addon and name of the portal)
 * @link https://dragomano.ru/mods/light-portal (link to the portal page, or to the page of your addon, if it is not included with the portal)
 * @author Your nickname and email address
 * @copyright Year of creation of the addon and your nickname (again)
 * @license Link to the license under which your addon is distributed and the name of the license
 *
 * @category addon
 * @version 08.01.22 (date when the source code of the addon was created or last updated, in the format dd.mm.yy)
 */

namespace Bugo\LightPortal\Addons\HelloWorld;

use Bugo\LightPortal\Addons\Plugin;

class HelloWorld extends Plugin
{
    // Used properties and methods
    // Access to global variables: $this->context['user'], $this->modSettings['variable'], etc.
    // Access to language variables: $this->txt['lp_hello_world']['variable_name']
}

```

## Using Composer
Your plugin can use third-party libraries installed through Composer.
Make sure that the `composer.json` file is located in the plugin directory, which contains the necessary dependencies. The `src` directory may contain key files of the scripts you use.
Before publishing your plugin, open the plugin directory in the command line and run the command: `composer install --no-dev -o`. After that, the entire contents of the plugin directory can be packaged as a separate modification for SMF (for example see **PluginMaker** package).
