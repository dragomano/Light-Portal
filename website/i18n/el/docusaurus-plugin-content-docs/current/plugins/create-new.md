---
sidebar_position: 2
---

# Προσθήκη πρόσθετου
Τα πρόσθετα είναι οι επεκτάσεις που επεκτείνουν τις δυνατότητες του Light Portal. Για να δημιουργήσετε το δικό σας πρόσθετο, απλώς ακολουθήστε τις παρακάτω οδηγίες.

:::info

Από την έκδοση 1.9, η λειτουργικότητα για τη δημιουργία προσθηκών έχει μετακινηθεί σε ξεχωριστή προσθήκη — **Δημιουργός προσθηκών**. Κάντε λήψη και ενεργοποιήστε το στη σελίδα _Διαχειριστής -> Ρυθμίσεις πύλης -> Πρόσθετα_.

:::

## Επιλογή του τύπου προσθήκης
Επί του παρόντος, είναι διαθέσιμοι οι ακόλουθοι τύποι προσθηκών:

* `block` — προσθήκες που προσθέτουν έναν νέο τύπο μπλοκ για την πύλη
* `ssi` — προσθήκες (συνήθως μπλοκ) που χρησιμοποιούν λειτουργίες SSI για την ανάκτηση δεδομένων
* `editor` — προσθήκες που προσθέτουν ένα πρόγραμμα επεξεργασίας τρίτου μέρους για διαφορετικούς τύπους περιεχομένου
* `comment` — προσθήκες που προσθέτουν ένα γραφικό στοιχείο σχολίων τρίτου μέρους αντί για το ενσωματωμένο
* `parser` — προσθήκες που υλοποιούν τον αναλυτή για το περιεχόμενο σελίδων και μπλοκ
* `article` — προσθήκες για την επεξεργασία του περιεχομένου καρτών άρθρων στην κύρια σελίδα
* `frontpage` — πρόσθετα για την αλλαγή της κύριας σελίδας της πύλης
* `impex` — πρόσθετα για εισαγωγή και εξαγωγή διαφόρων στοιχείων πύλης
* `block_options` και `page_options` — προσθήκες που προσθέτουν πρόσθετες παραμέτρους για την αντίστοιχη οντότητα (μπλοκ ή σελίδα)
* `icons` — προσθήκες που προσθέτουν νέες βιβλιοθήκες εικονιδίων για να αντικαταστήσουν στοιχεία διεπαφής ή για χρήση σε κεφαλίδες μπλοκ
* `seo` — προσθήκες που επηρεάζουν κατά κάποιο τρόπο την ορατότητα του φόρουμ στο δίκτυο
* `other` — προσθήκες που δεν σχετίζονται με καμία από τις παραπάνω κατηγορίες

## Δημιουργία καταλόγου προσθηκών
Δημιουργήστε έναν ξεχωριστό φάκελο για τα αρχεία των προσθηκών σας, μέσα στο `/Sources/LightPortal/Addons`. Για παράδειγμα, εάν η προσθήκη σας ονομάζεται `HelloWorld`, η δομή του φακέλου θα πρέπει να μοιάζει με αυτό:

```
...(Addons)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Το αρχείο `index.php` μπορεί να αντιγραφεί από φακέλους άλλων προσθηκών. Το αρχείο `HelloWorld.php` περιέχει τη λογική της προσθήκης:

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

## Χρήση SSI
Εάν η προσθήκη χρειάζεται να ανακτήσει δεδομένα χρησιμοποιώντας συναρτήσεις SSI, χρησιμοποιήστε την ενσωματωμένη μέθοδο `getFromSsi(string $function, ...$params)`. Ως παράμετρος `$function` πρέπει να μεταβιβάσετε το όνομα μιας από τις συναρτήσεις που περιέχονται στο αρχείο **SSI.php**, χωρίς πρόθεμα `ssi_`. Για παράδειγμα:

```php
<?php

    // See ssi_topTopics function in the SSI.php file
    $data = $this->getFromSsi('topTopics', 'views', 10, 'array');
```

:::caution

Χωρίς αρχείο SSI.php, η παραπάνω μέθοδος δεν θα λειτουργήσει.

:::

## Χρήση του Composer
Η προσθήκη σας μπορεί να χρησιμοποιεί βιβλιοθήκες τρίτων που έχουν εγκατασταθεί μέσω του Composer. Βεβαιωθείτε ότι το αρχείο `composer.json` βρίσκεται στον κατάλογο των προσθηκών, ο οποίος περιέχει τις απαραίτητες εξαρτήσεις. Πριν δημοσιεύσετε την προσθήκη, ανοίξτε τον κατάλογο της προσθήκης στη γραμμή εντολών και εκτελέστε την εντολή: `composer install --no-dev -o`. Μετά από αυτό, ολόκληρο το περιεχόμενο του καταλόγου προσθηκών μπορεί να συσκευαστεί ως ξεχωριστή τροποποίηση για SMF (για παράδειγμα, δείτε το πακέτο **PluginMaker**).