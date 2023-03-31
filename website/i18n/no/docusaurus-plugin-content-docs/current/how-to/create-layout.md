---
sidebar_position: 1
---

# Opprett eget oppsett for forsiden

I tillegg til eksisterende visninger kan du alltid legge til din egen.

For å gjøre dette må du opprette en fil `CustomFrontPage.template.php` i `/Themes/standard` mappen:

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

After that you will see a new frontpage layout - `Custom` - on the portal settings. Du kan opprette så mange slike oppsett som du ønsker (`template_show_articles_custom1()`, `template_show_articles_custom2()`etc.).

![Velg egendefinert mal](set_custom_template.png)

For å tilpasse stilheets, opprett en fil `custom_frontpage.css` i `/Themes/default/css` mappen.

```css {3}
/* Custom layout */
.article_custom {
    /* Your rules */
}
```

Fordelen med denne metoden er at hvis du sletter eller oppdaterer portalen vil filene du opprettet forbli intakte.

:::tip

Hvis du har opprettet din egen forsidemal og ønsker å dele den med utvikleren og andre brukere, kan du bruke https://codepen.io/pen/ eller andre lignende ressurser.

:::
