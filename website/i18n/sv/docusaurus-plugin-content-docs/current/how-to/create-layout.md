---
sidebar_position: 1
---

# Skapa egen layout för startsidan

Förutom befintliga layouter kan du alltid lägga till dina egna.

För att göra detta, skapa en fil `CustomFrontPage.template.php` i katalogen `/Themes/default`:

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

Efter det kommer du att se en ny frontpage layout - `Anpassad` - på portalinställningarna. Du kan skapa så många sådana layouter som du vill ha (`template_show_articles_custom1()`, `template_show_articles_custom2()`, etc.).

![Välj anpassad mall](set_custom_template.png)

Skapa en fil `custom_frontpage.css` i katalogen `/Themes/default/css` för att anpassa stilmallar:

```css {3}
/* Custom layout */
.article_custom {
    /* Your rules */
}
```

Fördelen med denna metod är att om du tar bort eller uppdaterar portalen, kommer de filer du skapade att förbli intakta.

:::tip

Om du har skapat din egen frontpage mall och vill dela den med utvecklaren och andra användare, använd https://codepen.io/pen/ eller andra liknande resurser.

:::
