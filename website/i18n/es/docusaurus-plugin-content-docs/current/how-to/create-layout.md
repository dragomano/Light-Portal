---
sidebar_position: 1
---

# Crear un diseño propio para la portada

Además de los diseños existentes, siempre puede agregar el suyo propio.

Para ello, cree un archivo `CustomFrontPage.template.php` en el directorio `/Themes/default`:

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

Después de eso, verá un nuevo diseño de portada - `Personalizado` - en la configuración del portal. Puede crear tantos diseños como desee (`template_show_articles_custom1()`, `template_show_articles_custom2()`, etc.).

![Seleccionar plantilla personalizada](set_custom_template.png)

Para personalizar las hojas de estilo, cree un archivo `custom_frontpage.css` en el directorio `/Themes/default/css`:

```css {3}
/* Custom layout */
.article_custom {
    /* Your rules */
}
```

La ventaja de este método es que si elimina o actualiza el portal, los archivos que creó permanecerán intactos.

:::tip

Si ha creado su propia plantilla de portada y desea compartirla con el desarrollador y otros usuarios, use https://codepen.io/pen/ u otros recursos similares.

:::
