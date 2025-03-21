---
description: Instrucciones para crear sus propios diseños de portal
---

# Crear diseño propio de la página principal

:::info Nota

Desde la versión 2.6 usamos [BladeOne](https://github.com/EFTEC/BladeOne) para renderizar diseños de portal.

:::

Además de los diseños existentes, siempre puedes añadir los tuyos.

Para hacer esto, crea un archivo `custom.blade.php` en el directorio `/Themes/default/portal_layouts`:

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

Después, verás un nuevo diseño de página principal - `Personalizado` - en la configuración del portal:

![Select custom template](set_custom_template.png)

Puede crear tantos diseños como desee. Usa `debug.blade.php` y otros diseños en el directorio `/Themes/default/LightPortal/layouts` como ejemplos.

Para personalizar las hojas de estilo, crea un archivo `portal_custom.css` en el directorio `/Themes/default/css`:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip Consejo

Si has creado tu propia plantilla de portada y quieres compartirla con el desarrollador y otros usuarios, usa https://codepen.io/pen/ u otros recursos similares.

:::
