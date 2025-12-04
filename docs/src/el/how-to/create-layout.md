---
description: Πλήρης οδηγός για το σύστημα προτύπων του Light Portal, τη δημιουργία προτύπων Blade, τις διατάξεις και τα θέματα
---

# Δημιουργήστε προσαρμοσμένες διατάξεις

Το Light Portal χρησιμοποιεί ένα ευέλικτο σύστημα προτύπων που βασίζεται στο [BladeOne](https://github.com/EFTEC/BladeOne), μια αυτόνομη υλοποίηση της μηχανής δημιουργίας προτύπων Blade του Laravel. Αυτό το σύστημα σάς επιτρέπει να προσαρμόσετε την εμφάνιση και τη δομή της πύλης σας μέσω διατάξεων, θεμάτων και επαναχρησιμοποιήσιμων στοιχείων.

## Σύστημα προτύπων

### Μηχανή δημιουργίας προτύπων Blade

Το Blade είναι μια ισχυρή μηχανή δημιουργίας προτύπων που παρέχει καθαρή, ευανάγνωστη σύνταξη για την ανάμειξη PHP με HTML. Βασικά χαρακτηριστικά:

- **Κληρονομικότητα προτύπου**: Χρησιμοποιήστε τις οδηγίες `@extends` και `@section` για να δημιουργήσετε ιεραρχίες διάταξης
- **Περιλαμβάνει**: Επαναχρησιμοποίηση στοιχείων με οδηγίες `@include`
- **Control Structures**: PHP-like syntax with `@if`, `@foreach`, `@while`, etc.

See detailed information about Blade markup [here](https://github.com/EFTEC/BladeOne/wiki/Template-variables).

### Layouts

Layouts define the overall structure of your front page. Located in `/Themes/default/LightPortal/layouts/`, they determine how front page articles are arranged. Examples include:

- `default.blade.php` - Standard grid layout
- `simple.blade.php` - Minimalist design
- `modern.blade.php` - Contemporary styling
- `featured_grid.blade.php` - Highlighted content grid

### Partials

Reusable template components stored in `/Themes/default/LightPortal/layouts/partials/`:

- `base.blade.php` - Main layout wrapper
- `card.blade.php` - Article card template
- `pagination.blade.php` - Page navigation
- `image.blade.php` - Image display component

### Themes and assets

- `/Themes/default/LightPortal`: Portal templates files
- `/languages/LightPortal`: Localization files
- `/css/light_portal`: CSS enhancements
- `/scripts/light_portal`: JavaScript enhancements

## Layout example

In addition to existing front page layouts, you can always add your own.

Για να το κάνετε αυτό, δημιουργήστε ένα αρχείο «custom.blade.php» στον κατάλογο «/Themes/default/portal_layouts»:

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

After that you will see a new front page layout - `Custom` - on the portal settings:

![Select custom template](set_custom_template.png)

Μπορείτε να δημιουργήσετε όσες τέτοιες διατάξεις θέλετε. Χρησιμοποιήστε το "debug.blade.php" και άλλες διατάξεις στον κατάλογο \`/Themes/default/LightPortal/layouts ως παραδείγματα.

## CSS customizing

You can easily change the look of anything by adding your own styles. Just create a new file called `portal_custom.css` in the `Themes/default/css` directory and put your CSS there.

:::tip Συμβουλή

If you have created your own front page template and want to share it with the developer and other users, use https://codepen.io/pen/ or other similar resources.

:::
