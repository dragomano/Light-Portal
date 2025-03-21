---
description: Instructions pour créer vos propres mises en page de portail
---

# Créer sa propre mise en page

:::info Note

Depuis la version 2.6, nous utilisons [BladeOne](https://github.com/EFTEC/BladeOne) pour afficher les mises en page frontpage.

:::

En plus des mises en page existantes, vous pouvez toujours ajouter les vôtres.

Pour cela, créez un fichier `custom.blade.php` dans le répertoire `/Themes/default/portal_layouts` :

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

Après cela, vous verrez une nouvelle mise en page - `Custom` - dans les paramètres du portail:

![Select custom template](set_custom_template.png)

Vous pouvez créer autant de mises en page que vous le souhaitez. Utilisez `debug.blade.php` et d'autres layouts dans le répertoire `/Themes/default/LightPortal/layouts` comme exemple.

Pour personnaliser les feuilles de style, créez un fichier `portal_custom.css` dans le répertoire `/Themes/default/css` :

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip Conseil

Si vous avez créé votre propre modèle de page d'accueil et que vous voulez le partager avec le développeur et d'autres utilisateurs, utilisez https://codepen.io/pen/ ou d'autres ressources similaires.

:::
