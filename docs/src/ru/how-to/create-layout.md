---
description: Инструкции по созданию собственных макетов портала
---

# Создание макета главной страницы

:::info Примечание

Начиная с версии 2.6 мы используем [BladeOne](https://github.com/EFTEC/BladeOne) для отрисовки макетов главной страницы.

:::

В дополнение к уже имеющимся макетам всегда можно добавить собственные.

Для этого создайте файл `custom.blade.php` в директории `/Themes/default/portal_layouts`:

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

После этого в настройках портала появится макет главной страницы под названием `Custom`:

![Выбираем кастомный макет](set_custom_template.png)

Вы можете создать столько макетов, сколько захотите. Используйте `debug.blade.php` и другие макеты в директории `/Themes/default/LightPortal/layouts` в качестве примеров.

Для кастомизации таблиц стилей создайте файл `portal_custom.css` в директории `/Themes/default/css`:

```css {3}
/* Ваш макет */
.article_custom {
  /* Ваши правила */
}
```

:::tip Совет

Если вы создали свой шаблон главной страницы и хотите поделиться им с разработчиком и другими пользователями, воспользуйтесь https://codepen.io/pen/ или другими подобными ресурсами.

:::
