---
description: Brief description of the plugin creation interface
order: 2
---

# Προσθήκη πρόσθετου

Plugins are the extensions that expand the capabilities of the Light Portal. To create your own plugin, just follow the instructions below.

:::info

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

Plugins that add additional parameters for the corresponding entity (block or .page).

### `icons`

Plugins that add new icon libraries to replace interface elements or for use in block headers

### `seo`

Plugins that somehow affect the visibility of the forum on the network.

### `other`

Plugins that are not related to any of the categories above.

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

File `index.php` can be copied from folders of other plugins. Το αρχείο `HelloWorld.php` περιέχει τη λογική της προσθήκης:

```php:line-numbers
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

## Χρήση SSI

Εάν το πρόσθετο χρειάζεται να ανακτήσει δεδομένα χρησιμοποιώντας συναρτήσεις SSI, χρησιμοποιήστε την ενσωματωμένη μέθοδο «getFromSsi(string $function, ...$params)». Ως παράμετρος `$function` πρέπει να μεταβιβάσετε το όνομα μιας από τις συναρτήσεις που περιέχονται στο αρχείο **SSI.php**, χωρίς πρόθεμα `ssi_`. Για παράδειγμα:

```php
<?php

// See ssi_topTopics function in the SSI.php file
$data = $this->getFromSsi('topTopics', 'views', 10, 'array');
```

## Χρήση του Composer

Η προσθήκη σας μπορεί να χρησιμοποιεί βιβλιοθήκες τρίτων που έχουν εγκατασταθεί μέσω του Composer. Βεβαιωθείτε ότι το αρχείο «composer.json», το οποίο περιέχει τις απαραίτητες εξαρτήσεις, βρίσκεται στον κατάλογο των προσθηκών. Πριν δημοσιεύσετε την προσθήκη, ανοίξτε τον κατάλογο της προσθήκης στη γραμμή εντολών και εκτελέστε την εντολή: "composer install --no-dev -o". Μετά από αυτό, ολόκληρο το περιεχόμενο του καταλόγου πρόσθετων μπορεί να συσκευαστεί ως ξεχωριστή τροποποίηση για SMF (για παράδειγμα, δείτε το πακέτο **PluginMaker**).