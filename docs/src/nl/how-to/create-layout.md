---
description: Instructies voor het maken van uw eigen portallay-outs
---

# Maak eigen voorpagina lay-out

:::info Opmerking

Sinds versie 2.6 gebruiken we [BladeOne](https://github.com/EFTEC/BladeOne) om frontpage lay-outs weer te geven.

:::

Naast de bestaande lay-outs kunt u altijd uw eigen lay-outs toevoegen.

Om dit te doen, maak een bestand `custom.blade.php` aan in de `/Themes/default/portal_layouts` map:

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

Daarna ziet u een nieuwe voorpagina-layout - `Custom` - in de portal-instellingen:

![Select custom template](set_custom_template.png)

Je kunt zoveel van dergelijke lay-outs maken als je wilt. Gebruik `debug.blade.php` en andere lay-outs in `/Themes/default/LightPortal/layouts` als voorbeelden.

Om stylesheets aan te passen, maak een bestand `portal_custom.css` aan in de `/Themes/default/css` map:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip Advies

Als u uw eigen frontpagina template hebt gemaakt en deze wilt delen met de ontwikkelaar en andere gebruikers, gebruik dan https://coafhank.io/pen/ of andere vergelijkbare bronnen.

:::
