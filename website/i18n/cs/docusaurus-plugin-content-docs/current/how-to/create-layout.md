---
sidebar_position: 1
---

# Vytvořit vlastní rozložení pro stránku

Kromě existujících rozvržení můžete vždy přidat své vlastní.

Chcete-li to provést, vytvořte soubor `CustomFrontPage.template.php` v adresáři `/Themes/default`:

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

Poté uvidíte nové rozložení stránek - `Vlastní` - v nastavení portálu. Můžete vytvořit tolik takových rozložení, kolik chcete, (`template_show_articles_custom1()`, `template_show_articles_custom2()`, atd.).

![Vybrat vlastní šablonu](set_custom_template.png)

Pro přizpůsobení stylů vytvořte soubor `custom_frontpage.css` v adresáři `/Themes/default/css`:

```css {3}
/* Custom layout */
.article_custom {
    /* Your rules */
}
```

Výhodou této metody je, že pokud odstraníte nebo aktualizujete portál, vytvořené soubory zůstanou nedotčené.

:::tip

Pokud jste vytvořili vlastní šablonu frontpage a chcete ji sdílet s vývojářem a dalšími uživateli, použijte https://codepen.io/pen/ nebo jiné podobné zdroje.

:::
