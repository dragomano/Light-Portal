---
sidebar_position: 1
---

# Opret eget layout til forsiden

Ud over eksisterende layouts, kan du altid tilføje dine egne.

For at gøre dette skal du oprette en fil `CustomFrontPage.template.php` i mappen `/Themes/default`:

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

Derefter vil du se en ny forside layout - `Brugerdefineret` - i portalens indstillinger. Du kan oprette så mange layouts som du ønsker (`template_show_articles_custom1()`, `template_show_articles_custom2()`, etc.).

![Vælg brugerdefineret skabelon](set_custom_template.png)

For at tilpasse stilark, opret en fil `custom_frontpage.css` i mappen `/Themes/default/css`:

```css {3}
/* Custom layout */
.article_custom {
    /* Your rules */
}
```

Fordelen ved denne metode er, at hvis du sletter eller opdaterer portalen, vil de filer, du har oprettet, forblive intakt.

:::tip

Hvis du har oprettet din egen frontside-skabelon og ønsker at dele den med udvikleren og andre brugere, skal du bruge https://codepen.io/pen/ eller andre lignende ressourcer.

:::
