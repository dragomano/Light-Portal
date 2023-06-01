<?php

function template_docs_above()
{
	global $txt;

	$lang = in_array($txt['lang_dictionary'], ['cs', 'da', 'el', 'nl', 'no', 'ru', 'sv', 'es']) ? $txt['lang_dictionary'] : '';

	echo '
	<div class="noticebox"><a class="bbc_link" href="https://dragomano.github.io/Light-Portal/' . $lang . '" target="_blank" rel="noopener">', $txt['admin_search_type_online'], '</a></div>';
}

function template_docs_below()
{
}

function template_debug_above()
{
}

function template_debug_below()
{
	global $context;

	echo '
	<div class="centertext clear noticebox smalltext" style="margin-top: 2px">', $context['lp_load_page_stats'], '</div>';
}
