---
description: Інструкції щодо створення власного порталу макетів
---

# Створення власної допоміжної сторінки

:::info Примітка

Починаючи з версії 2.6, ми використовуємо [BladeOne](https://github.com/EFTEC/BladeOne) для візуалізації схем фронтальної сторінки.

:::

Окрім наявних макетів, ви завжди можете додати власну.

Щоб зробити це, створіть файл `custom.blade.php` у каталозі `/Themes/default/portal_layouts`:

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

Після цього ви побачите нову схему фронтальної сторінки - `Custom` - на порталі налаштувань:

![Select custom template](set_custom_template.png)

Ви можете створити стільки таких макетів, скільки захочете. Використовуйте `debug.blade.php` та інші макети в `/Themes/default/LightPortal/layouts` як приклади.

Щоб налаштувати таблиці стилів, створіть файл `portal_custom.css` в каталозі `/Themes/default/css`:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip Порада

Якщо ви створили свій власний шаблон головної сторінки і хочете поділитися ним з розробником та іншими користувачами, використовуйте https://codepen.io/pen/ або інші подібні ресурси.

:::
