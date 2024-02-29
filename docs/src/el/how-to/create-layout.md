---
description: Οδηγίες για τη δημιουργία δικής σας εμφάνισης της πύλης
---

# Δημιουργήστε τη δική σας διάταξη πρώτης σελίδας

:::info

Από την έκδοση 2.6 χρησιμοποιούμε το [BladeOne](https://github.com/EFTEC/BladeOne) για την απόδοση των διατάξεων πρώτης σελίδας.

:::

Εκτός από τις υπάρχουσες διατάξεις, μπορείτε πάντα να προσθέσετε τις δικές σας.

Για να το κάνετε αυτό, δημιουργήστε ένα αρχείο «custom.blade.php» στον κατάλογο «/Themes/default/portal_layouts»:

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

Μετά από αυτό, θα δείτε μια νέα διάταξη πρώτης σελίδας - "Προσαρμοσμένη" - στις ρυθμίσεις της πύλης:

![Select custom template](set_custom_template.png)

Μπορείτε να δημιουργήσετε όσες τέτοιες διατάξεις θέλετε. Χρησιμοποιήστε το "debug.blade.php" και άλλες διατάξεις στον κατάλογο \`/Themes/default/LightPortal/layouts ως παραδείγματα.

Για να προσαρμόσετε τα φύλλα στυλ, δημιουργήστε ένα αρχείο «portal_custom.css» στον κατάλογο «/Themes/default/css»:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip

Εάν έχετε δημιουργήσει το δικό σας πρότυπο αρχικής σελίδας και θέλετε να το μοιραστείτε με τον προγραμματιστή και άλλους χρήστες, χρησιμοποιήστε τη διεύθυνση https\://codepen.io/pen/ ή άλλους παρόμοιους πόρους.

:::
