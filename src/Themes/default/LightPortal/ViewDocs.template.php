<?php declare(strict_types=1);

use Bugo\Compat\Lang;

/**
 * @layer lp_docs
 * @see Utils::$context['template_layers']
 */
function template_lp_docs_above(): void
{
	$lang = in_array(
		Lang::$txt['lang_dictionary'],
		['ru', 'el', 'it', 'ar', 'es', 'de', 'nl', 'pl', 'uk', 'fr', 'tr', 'sl']
	) ? Lang::$txt['lang_dictionary'] : '';

	echo '
	<div class="noticebox">
		<a class="bbc_link" href="https://dragomano.github.io/Light-Portal/' . $lang . '" target="_blank" rel="noopener">
			' . Lang::$txt['admin_search_type_online'] . '
		</a>
	</div>';
}

/**
 * @layer lp_docs
 * @see Utils::$context['template_layers']
 */
function template_lp_docs_below()
{
}
