---
description: Navodila za ustvarjanje lastnih zasnov portala
---

# Ustvari lastno postavitev začetne strani

:::info Opomba

Od različice 2.6 uporabljamo [BladeOne](https://github.com/EFTEC/BladeOne) za upodabljanje postavitev začetne strani.

:::

Poleg obstoječih postavitev lahko vedno dodaš svoje.

Da to storiš, ustvari datoteko `custom.blade.php` v mapi `/Themes/default/portal_layouts`:

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

Po tem se bo v nastavitvah portala prikazala nova postavitev začetne strani – `Custom`:

![Select custom template](set_custom_template.png)

Lahko ustvariš poljubno število takšnih postavitev. Uporabi `debug.blade.php` in druge postavitve v mapi `/Themes/default/LightPortal/layouts` kot primere.

Za prilagoditev slogovnih predlog ustvari datoteko `portal_custom.css` v mapi `/Themes/default/css`:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip Nasvet

Če si ustvaril svojo predlogo začetne strani in jo želiš deliti z razvijalcem in drugimi uporabniki, uporabi https://codepen.io/pen/ ali druge podobne vire.

:::
