---
sidebar_position: 1
---

# Eigenes Hauptseiten-Layout erzeugen

:::info

Seit Version 2.2.0 verwenden wir [Latte](https://latte.nette.org/syntax) um Hauptseiten-Layouts zu rendern.

:::

Zusätzlich zu existierenden Layouts können Sie stets eigene hinzufügen.

Um dies zu tun, erzeugen Sie eine Datei `custom.latte` im Verzeichnis `/Themes/default/portal_layouts`:

```latte
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

Anschließend werden Sie ein neues Hauptseiten-Layout – `benutzerdefiniert` – in den Portaleinstellungen sehen:

![Benutzerdefinierte Vorlage auswählen](set_custom_template.png)

Sie können so viele dieser Layouts erzeugen wie sie möchten. Verwenden Sie `debug.latte` und andere Layouts im Verzeichnis `/Themes/default/LightPortal/layouts` als Beispiele.

Um Stylesheets anzupassen, erzeugen Sie eine Datei `portal_custom.css` im Verzeichnis `/Themes/default/css`:

```css {3}
/* Benutzerdefiniertes Layout */
.article_custom_class {
    /* Ihre Regeln */
}
```

:::tip

Falls Sie Ihre eigene Hauptseitenvorlage erzeugt haben und diese mit dem Entwickler und anderen Benutzern teilen möchten, verwenden Sie https://codepen.io/pen/ oder ähnliche Ressourcen.

:::
