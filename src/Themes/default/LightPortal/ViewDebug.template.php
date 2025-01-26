<?php declare(strict_types=1);

use Bugo\Compat\{Lang, Utils};

function template_docs_above(): void
{
	$lang = in_array(
		Lang::$txt['lang_dictionary'],
		['ru', 'el', 'it', 'ar', 'es', 'de', 'nl', 'pl', 'uk', 'fr', 'tr']
	) ? Lang::$txt['lang_dictionary'] : '';

	echo '
	<div class="noticebox">
		<a class="bbc_link" href="https://dragomano.github.io/Light-Portal/' . $lang . '" target="_blank" rel="noopener">
			' . Lang::$txt['admin_search_type_online'] . '
		</a>
	</div>';
}

function template_docs_below()
{
}

function template_debug_above()
{
}

function template_debug_below(): void
{
	echo '
	<div class="centertext clear noticebox smalltext" style="margin-top: 2px">
		', Utils::$context['lp_load_page_stats'], '
	</div>';
}
