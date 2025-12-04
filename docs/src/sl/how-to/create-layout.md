---
description: Podroben vodič po Light Portal sistemu predlog, Blade predlogah, layoutih in temah
---

# Naredi prilagojene layout-e

Light Portal uporablja prilagodljiv sistem predlog, ki temelji na [BladeOne](https://github.com/EFTEC/BladeOne), samostojni implementaciji Blade predlogovnega sistema iz Laravel-a. Ta sistem omogoča prilagajanje videza in strukture portala preko postavitev, tem in ponovno uporabnih komponent.

## Sistem predlog

### Blade pogon za predloge

Blade je zmogljiv predlogovni pogon, ki omogoča čisto in berljivo sintakso za združevanje PHP-ja z HTML-jem. Glavne funkcije:

- **Dedovanje predlog**: uporabi direktive `@extends` in `@section` za ustvarjanje hierarhij postavitev
- **Vključitve**: ponovno uporabi komponente z direktivami `@include`
- **Kontrolne strukture**: sintaksa podobna PHP-ju z `@if`, `@foreach`, `@while` itd.

Za več podrobnosti o Blade oznakah obišči to [povezavo](https://github.com/EFTEC/BladeOne/wiki/Template-variables).
.

### Postavitev

Postavitve določajo splošno strukturo tvoje začetne strani. Nahajajo se v `/Themes/default/LightPortal/layouts/` in določajo, kako so članki na začetni strani razporejeni. Primeri vključujejo:

- `default.blade.php` - standardna mrežna postavitev
- `simple.blade.php` - minimalističen dizajn
- `modern.blade.php` - sodoben slog
- `featured_grid.blade.php` - mreža z izpostavljeno vsebino

### Podpredloge

Ponovno uporabne komponente predlog, shranjene v `/Themes/default/LightPortal/layouts/partials/`:

- `base.blade.php` - glavni ovoj postavitve
- `card.blade.php` - predloga kartice članka
- `pagination.blade.php` - navigacija po straneh
- `image.blade.php` - komponenta za prikaz slik

### Teme in sredstva

- `/Themes/default/LightPortal`: datoteke predlog portala
- `/languages/LightPortal`: datoteke za lokalizacijo
- `/css/light_portal`: izboljšave CSS
- `/scripts/light_portal`: izboljšave JavaScript

## Primer postavitve

Poleg obstoječih postavitev začetne strani lahko vedno dodaš svoje.

Da to storiš, ustvari datoteko `custom.blade.php` v mapi `/Themes/default/portal_layouts`:

```php:line-numbers {6,16}
@extends('partials.base')

@section('content')
	<!-- <div> @dump($context['user']) </div> -->

	<div class="lp_frontpage_articles article_custom">
		@include('partials.pagination')

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

		@include('partials.pagination', ['position' => 'bottom'])
	</div>
@endsection

<style>
.article_custom {
	// Your CSS
}
</style>
```

Zatem boš v nastavitvah portala videl novo postavitev začetne strani – `Custom`.

![Select custom template](set_custom_template.png)

Lahko ustvariš poljubno število takšnih postavitev. Uporabi `debug.blade.php` in druge postavitve v mapi `/Themes/default/LightPortal/layouts` kot primere.

## Prilagajanje CSS

S preprostimi CSS pravili lahko spremeniš videz kateregakoli elementa. Preprosto ustvari novo datoteko z imenom `portal_custom.css` v mapi `Themes/default/css` in vanjo dodaj svoje CSS.

:::tip Nasvet



:::
