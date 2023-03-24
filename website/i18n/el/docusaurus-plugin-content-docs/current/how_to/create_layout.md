---
sidebar_position: 1
---

# Δημιουργήστε τη δική σας διάταξη για την πρώτη σελίδα

Εκτός από τις υπάρχουσες διατάξεις, μπορείτε πάντα να προσθέσετε τις δικές σας.

Για να το κάνετε αυτό, δημιουργήστε ένα αρχείο `CustomFrontPage.template.php` στον κατάλογο `/Themes/default`:

```php {8,17}
<?php

/**
 * Custom template layout
 *
 * @return void
 */
function template_show_articles_custom() // Do not forget change custom name *custom* for your layout
{
    global $context;

    if (empty($context['lp_active_blocks']))
        echo '
    <div class="col-xs">';

    echo '
    <div class="lp_frontpage_articles article_custom">'; // Do not forget change custom class *article_custom* for your layout

    show_pagination();

    foreach ($context['lp_frontpage_articles'] as $article) {
        echo '
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_num_columns'], ' col-xl-', $context['lp_frontpage_num_columns'], '">';

        // Just outputs the $article data as a hint for you
        echo '<figure class="noticebox">' . parse_bbc('[code]' . print_r($article, true) . '[/code]') . '</figure>';

        // Your code

        echo '
        </div>';
    }

    show_pagination('bottom');

    echo '
    </div>';

    if (empty($context['lp_active_blocks']))
        echo '
    </div>';
}

```

Μετά από αυτό, θα δείτε μια νέα διάταξη πρώτης σελίδας - `Προσαρμοσμένη` - στις ρυθμίσεις της πύλης. Μπορείτε να δημιουργήσετε όσες τέτοιες διατάξεις θέλετε (`template_show_articles_custom1()`, `template_show_articles_custom2()`, κ.λπ.).

![Επιλέξτε προσαρμοσμένο πρότυπο](https://user-images.githubusercontent.com/229402/136643076-765289c2-342f-43c5-865a-1aca948beafe.png)

Για να προσαρμόσετε τα φύλλα στυλ, δημιουργήστε ένα αρχείο `custom_frontpage.css` στον κατάλογο `/Themes/default/css`:

```css {3}
/* Custom layout */
.article_custom {
    /* Your rules */
}
```

Το πλεονέκτημα αυτής της μεθόδου είναι ότι εάν διαγράψετε ή ενημερώσετε την πύλη, τα αρχεία που δημιουργήσατε θα παραμείνουν ανέπαφα.