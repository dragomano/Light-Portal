---
description: Instructions for creating your own portal layouts
---

# Create own frontpage layout

:::info Note

Since version 2.6 we use [BladeOne](https://github.com/EFTEC/BladeOne) to render frontpage layouts.

:::

In addition to existing layouts, you can always add your own.

To do this, create a file `custom.blade.php` in the `/Themes/default/portal_layouts` directory:

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
```

After that you will see a new frontpage layout - `Custom` - on the portal settings:

![Select custom template](set_custom_template.png)

You can create as many such layouts as you want. Use `debug.blade.php` and other layouts in `/Themes/default/LightPortal/layouts` directory as examples.

To customize stylesheets, create a file `portal_custom.css` in the `/Themes/default/css` directory:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip Tip

If you have created your own frontpage template and want to share it with the developer and other users, use https://codepen.io/pen/ or other similar resources.

:::
