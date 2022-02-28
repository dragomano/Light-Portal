---
sidebar_position: 1
---

# Создание макета главной страницы

В дополнение к уже имеющимся макетам всегда можно добавить собственные.

Для этого в директории `/Themes/default` создайте файл `CustomFrontPage.template.php`:

```php {8,17}
<?php

/**
 * Custom template layout
 *
 * @return void
 */
function template_show_articles_custom() // Не забудьте поменять *_custom* на что-нибудь другое, для уникализации
{
	global $context;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_custom">'; // Не забудьте поменять *article_custom* на что-нибудь другое, для уникализации

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_num_columns'], ' col-xl-', $context['lp_frontpage_num_columns'], '">';

		// Отображение содержимого переменной $article, в качестве подсказки
		echo '<figure class="noticebox">' . parse_bbc('[code]' . print_r($article, true) . '[/code]') . '</figure>';

		// Ваш код

		echo '
		</div>';
	}

	show_pagination('bottom');

	echo '
	</div>';

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}

```

После этого в настройках портала появится макет главной страницы под названием `Custom`. При желании в этом же файле можно добавить дополнительные макеты (`template_show_articles_custom1()`, `template_show_articles_custom2()` и т. д.).

Для кастомизации таблиц стилей в директории `/Themes/default/css` создайте файл `custom_frontpage.css`:

```css {3}
/* Custom layout */
.article_custom {
	/* Ваши правила */
}
```

Преимущество этого способа в том, что при удалении или обновлении портала созданные вами файлы останутся нетронутыми.
