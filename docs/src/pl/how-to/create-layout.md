---
description: Kompleksowy przewodnik po systemie szablonów serwisu Light Portal, szablonach, układach i motywach
---

# Tworzenie niestandardowych układów

Light Portal wykorzystuje elastyczny system szablonów oparty na [BladeOne](https://github.com/EFTEC/BladeOne), czyli samodzielnej implementacji silnika szablonów Blade z frameworka Laravel. System ten umożliwia dostosowanie wyglądu i struktury portalu za pomocą układów, motywów oraz komponentów wielokrotnego użytku.

## System szablonów

### Silnik szablonów Blade

Blade to potężny silnik szablonów, który oferuje przejrzystą i czytelna składnię umożliwiającą łączenie kodu PHP z HTML. Najważniejsze cechy:

- **Dziedziczenie szablonów**: Użyj dyrektyw `@extends` i `@section`, aby tworzyć hierarchie układów
- **Zawiera**: Ponowne wykorzystanie komponentów za pomocą dyrektyw `@include`
- **Struktury kontrolne**: składnia podobna do PHP z `@if`, `@foreach`, `@while` itp.

Szczegółowe informacje na temat znaczników Blade można znaleźć [tutaj](https://github.com/EFTEC/BladeOne/wiki/Template-variables).

### Układ strony

Układy określają ogólną strukturę strony głównej. Znajdują się w katalogu `/Themes/default/LightPortal/layouts/` i określają sposób rozmieszczenia artykułów na stronie głównej. Przykłady obejmują:

- `default.blade.php` – Standardowy układ siatki
- `simple.blade.php` – Minimalistyczny projekt
- `modern.blade.php` – Współczesna stylistyka
- `featured_grid.blade.php` – Siatka z wyróżnionymi treściami

### Elementy częściowe

Elementy szablonów wielokrotnego użytku przechowywane w katalogu `/Themes/default/LightPortal/layouts/partials/`:

- `base.blade.php` – Główny element otaczający układ strony
- `card.blade.php` – szablon karty artykułu
- `pagination.blade.php` – Nawigacja po stronach
- `image.blade.php` – komponent do wyświetlania obrazów

### Motywy i elementy graficzne

- `/Themes/default/LightPortal`: Pliki szablonów portalu
- `/languages/LightPortal`: Pliki językowe
- `/css/light_portal`: ulepszenia CSS
- `/scripts/light_portal`: ulepszenia w JavaScript

## Przykładowy układ

Oprócz istniejących układów strony głównej zawsze możesz dodać własny.

Aby to zrobić, utwórz plik "custom.blade.php" w katalogu "/Themes/default/portal_layouts":

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

Następnie w ustawieniach portalu pojawi się nowy układ strony głównej — `Custom`:

![Select custom template](set_custom_template.png)

Możesz stworzyć tyle takich układów, ile chcesz. Użyj "debug.blade.php" i innych układów w katalogu "/Themes/default/LightPortal/layouts" jako przykładów.

## Dostosowywanie CSS

Możesz z łatwością zmienić wygląd dowolnego elementu, dodając własne style. Wystarczy utworzyć nowy plik o nazwie `portal_custom.css` w katalogu `Themes/default/css` i umieścić w nim swój kod CSS.

:::tip Porada

Jeśli stworzyłeś własny szablon strony głównej i chcesz udostępnić go programiście oraz innym użytkownikom, skorzystaj z serwisu https://codepen.io/pen/ lub innych podobnych zasobów.

:::
