---
description: Istruzioni per creare i propri layout del portale
---

# Creare il proprio layout per il frontpage

:::info

Dalla versione 2.2.0 viene utilizzato [Latte](https://latte.nette.org/syntax) per eseguire il rendering dei layout del frontpage.

:::

Oltre ai layout esistenti, puoi sempre aggiungerne di tuoi.

Per farlo, crea un file `custom.latte` nella cartella `/Themes/default/portal_layouts`:

```php:line-numbers {9}
{varType array $txt}
{varType array $context}
{varType array $modSettings}

{if empty($context[lp_active_blocks])}
<div class="col-xs">
{/if}

    <div class="lp_frontpage_articles article_custom">
        {do show_pagination()}

            <div
                n:foreach="$context[lp_frontpage_articles] as $article"
                class="col-xs-12 col-sm-6 col-md-4 col-lg-{$context[lp_frontpage_num_columns]}"
            >
                <div n:if="!empty($article[image])">
                    <img src="{$article[image]}" alt="{$article[title]}">
                </div>
                <h3>
                    <a href="{$article[msg_link]}">{$article[title]}</a>
                </h3>
                <p n:if="!empty($article[teaser])">
                    {teaser($article[teaser])}
                </p>
            </div>

        {do show_pagination(bottom)}
    </div>

{if empty($context[lp_active_blocks])}
</div>
{/if}
```

Successivamente vedrai un nuovo layout del frontpage - "Custom" - nelle impostazioni del portale:

![Select custom template](set_custom_template.png)

Puoi creare tutti i layout che desideri. Utilizza `debug.latte` e altri layout nella cartella `/Themes/default/LightPortal/layouts` come esempi.

Per personalizzare i fogli di stile, crea un file `portal_custom.css` nella cartella `/Themes/default/css`:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip

Se hai creato il tuo modello del frontpage e desideri condividerlo con lo sviluppatore ed altri utenti, utilizza https\://codepen.io/pen/ o altre risorse simili.

:::
