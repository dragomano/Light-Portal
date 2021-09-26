# Add plugin
Plugins, or addons - add-ons that expand the capabilities of the Light Portal. To create your own addon, just follow the instructions below.

!> Since version 1.5, Light Portal has built-in functionality for creating plugin skeletons. To use it, go to the section _Admin -> Portal settings -> Plugins -> Add plugin_.

!> Since version 1.9, the functionality for creating plugins has been moved to a separate plugin — **PluginMaker**. Download and enable it on the page _Admin -> Portal settings -> Plugins_.

## Choosing the type of addon
Currently, the following types of addons are available:

* 'block' — addons that add a new type of blocks for the portal
* 'editor' — addons that add a third-party editor for different types of content
* 'comment' — addons that add a third-party comment widget instead of the built-in
* 'parser' — addons that implement the parser for the content of pages or blocks
* 'article' — addons for processing the content of article cards on the main page
* 'frontpage' — addons for changing the main page of the portal
* 'impex' — addons for importing and exporting various portal elements
* 'other' — addons that are not related to any of the categories above

## Creating an addon directory
Create a separate folder for your addon files, inside `/Sources/LightPortal/addons`. For example, if your addon is called `MyAddon`, the folder structure should look like this:

```php
    ...(addons)
        MyAddon\
            langs\
                 english.php
                 index.php
            index.php
            MyAddon.php
```

The `langs` directory is optional and is intended for language files if they are used in your addon. File `index.php` can be copied from folders of other addons. The file `MyAddon.php` contains addon logic:

```php
<?php

/**
 * MyAddon
 *
 * @package Light Portal (the portal name, do not change)
 * @link https://dragomano.ru/mods/light-portal (link to the portal page, or to the page of your addon, if it is not included with the portal)
 * @author Your nickname and email address
 * @copyright Year of creation of the addon and your nickname (again)
 * @license Link to the license under which your addon is distributed and the name of the license
 *
 * @version 1.9 (version of the portal where your addon was developed and tested)
 */

namespace Bugo\LightPortal\Addons\MyAddon;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers; // Leave it if you use portal helpers in your code

class MyAddon extends Plugin
{
    // Used properties and methods
}

```
