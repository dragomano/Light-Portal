---
description: Всеобъемлющее руководство по системе шаблонов Light Portal, шаблонизатору Blade, макетам и темам
---

# Создание своих макетов

Light Portal использует гибкую систему шаблонов, основанную на [BladeOne](https://github.com/EFTEC/BladeOne) — автономной реализации шаблонизатора Blade от Laravel. Эта система позволяет настраивать внешний вид и структуру вашего портала с помощью макетов, тем и переиспользуемых компонентов.

## Система шаблонов

### Шаблонизатор Blade

Blade — это мощный шаблонизатор, который предоставляет чистый, удобочитаемый синтаксис для совмещения PHP и HTML. Основные возможности:

- **Наследование шаблонов**: Используйте директивы `@extends` и `@section` для создания иерархий макетов.
- **Включения**: Повторное использование компонентов с помощью директив `@include`
- **Управляющие конструкции**: PHP-подобный синтаксис с `@if`, `@foreach`, `@while` и т. д.

Подробную информацию о разметке Blade смотрите [здесь](https://github.com/EFTEC/BladeOne/wiki/Template-variables).

### Макеты

Макеты определяют общую структуру главной страницы. Расположенные в `/Themes/default/LightPortal/layouts/`, они определяют, как размещаются статьи на главной странице. Примеры:

- `default.blade.php` - Стандартный сеточный макет
- `simple.blade.php` - Минималистичный дизайн
- `modern.blade.php` - Современный стиль
- `featured_grid.blade.php` - Сетка с выделенным контентом

### Фрагменты

Повторно используемые компоненты шаблонов, хранящиеся в `/Themes/default/LightPortal/layouts/partials/`:

- `base.blade.php` - Основная обёртка макета
- `card.blade.php` - Шаблон карточки статьи
- `pagination.blade.php` - Пагинация
- `image.blade.php` - Блок вывода изображения

### Темы и ресурсы

- `/Themes/default/LightPortal`: Файлы шаблонов портала
- `/languages/LightPortal`: Файлы локализации
- `/css/light_portal`: Файлы CSS стилей
- `/scripts/light_portal`: Файлы JavaScript

## Пример макета

Помимо существующих макетов главной страницы, вы всегда можете добавить свой собственный.

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

<style>
.article_custom {
	// Your CSS
}
</style>
```

После этого в настройках портала появится макет главной страницы под названием `Custom`:

![Выбираем кастомный макет](set_custom_template.png)

Вы можете создать столько макетов, сколько захотите. Используйте `debug.blade.php` и другие макеты в директории `/Themes/default/LightPortal/layouts` в качестве примеров.

## Кастомизация CSS

Вы можете легко изменить внешний вид чего угодно, добавив свои стили. Просто создайте новый файл под названием `portal_custom.css` в директории `Themes/default/css` и поместите туда свой CSS.

:::tip Совет

Если вы создали свой шаблон главной страницы и хотите поделиться им с разработчиком и другими пользователями, воспользуйтесь https://codepen.io/pen/ или другими подобными ресурсами.

:::
