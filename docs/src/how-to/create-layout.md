---
description: Instructions for creating your own portal layouts
---

# Create own frontpage layout

:::info

Since version 2.2.0 we use [Latte](https://latte.nette.org/syntax) to render frontpage layouts.

:::

In addition to existing layouts, you can always add your own.

To do this, create a file `custom.latte` in the `/Themes/default/portal_layouts` directory:

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

After that you will see a new frontpage layout - `Custom` - on the portal settings:

![Select custom template](set_custom_template.png)

You can create as many such layouts as you want. Use `debug.latte` and other layouts in `/Themes/default/LightPortal/layouts` directory as examples.

To customize stylesheets, create a file `portal_custom.css` in the `/Themes/default/css` directory:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip

If you have created your own frontpage template and want to share it with the developer and other users, use https://codepen.io/pen/ or other similar resources.

:::
