---
sidebar_position: 1
---

# Maak eigen lay-out voor de voorpagina

Naast de bestaande lay-outs kunt u altijd uw eigen lay-outs toevoegen.

Om dit te doen maak je een bestand aan `CustomFrontPage.template.php` in de `/Themes/default` directory:

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

Daarna ziet u een nieuwe voorpagina lay-out - `Aangepast` - in de portalinstellingen. Je kunt zoveel van dergelijke lay-outs maken als je wilt (`template_show_articles_custom1()`, `template_show_articles_custom2()`, etc.).

![Selecteer aangepaste sjabloon](set_custom_template.png)

Om stylesheets aan te passen maak je een bestand `custom_frontpage.css` in de `/Themes/default/css` directory:

```css {3}
/* Custom layout */
.article_custom {
    /* Your rules */
}
```

Het voordeel van deze methode is dat als u de portal verwijdert of bijwerkt, de bestanden die u hebt gemaakt intact blijven.

:::tip

Als u uw eigen frontpagina template hebt gemaakt en deze wilt delen met de ontwikkelaar en andere gebruikers, gebruik dan https://coafhank.io/pen/ of andere vergelijkbare bronnen.

:::
