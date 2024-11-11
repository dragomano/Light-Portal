---
description: Anleitung zur Erstellung eigener Portallayouts
---

# Eigenes Layout der Startseite erstellen

:::info Hinweis

Seit Version 2.6 verwenden wir [BladeOne](https://github.com/EFTEC/BladeOne), um Frontpage Layouts zu rendern.

:::

Zusätzlich zu bestehenden Layouts können Sie immer eigene hinzufügen.

Erstelle dazu eine Datei `custom.blade.php` im `/Themes/default/portal_layouts` Verzeichnis:

```php:line-numbers {9}
@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty
	<!-- <div> @dump($context['user']) </div> -->

	<div class="lp_frontpage_articles article_custom">
		{{ show_pagination() }}

		@foreach ($context['lp_frontpage_articles'] as $article)
		<div class="
			col-xs-12 col-sm-6 col-md-4
			col-lg-{{ $context['lp_frontpage_num_columns'] }}
			col-xl-{{ $context['lp_frontpage_num_columns'] }}
		">
			<figure class="noticebox">
				{!! parse_bbc('[code]' . print_r($article, true) . '[/code]') !!}
			</figure>
		</div>
		@endforeach

		{{ show_pagination('bottom') }}
	</div>

@empty ($context['lp_active_blocks'])
</div>
@endempty
```

Danach siehst du ein neues Frontpage Layout - `Custom` - in den Portaleinstellungen:

![Select custom template](set_custom_template.png)

Sie können so viele Layouts erstellen, wie Sie wollen. Benutze `debug.blade.php` und andere Layouts im `/Themes/default/LightPortal/layouts` Verzeichnis als Beispiele.

Um Stylesheets anzupassen, erstelle eine Datei `portal_custom.css` im `/Themes/default/css` Verzeichnis:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip Hinweis

Wenn du dein eigenes Frontpage Template erstellt hast und es mit dem Entwickler und anderen Benutzern teilen möchtest, benutze https://codepen.io/pen/ oder andere ähnliche Ressourcen.

:::
