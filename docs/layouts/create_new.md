# Creating custom homepage layout
In addition to existing layouts, you can always add your own.

To do this, create a file `CustomFrontPage.template.php` in the `/Themes/default` directory:

```php
<?php

/**
 * Custom template layout
 *
 * @return void
 */
function template_show_articles_custom() // Do not forget change custom name *custom* for your layout
{
	global $context;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_custom">'; // Do not forget change custom class *article_custom* for your layout

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_num_columns'], ' col-xl-', $context['lp_frontpage_num_columns'], '">';

		// Just outputs the $article data as a hint for you
		echo '<figure class="noticebox">' . parse_bbc('[code]' . print_r($article, true) . '[/code]') . '</figure>';

		// Your code

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

After that you will see a new frontpage layout - `Custom` - on the portal settings. You can create as many such layouts as you want.

To customize stylesheets, create a file `custom_frontpage.css` in the `/Themes/default/css` directory:

```css
/* Custom layout */
.article_custom {
	/* Your rules */
}
```

The advantage of this method is that if you delete or update the portal, the files you created will remain intact.