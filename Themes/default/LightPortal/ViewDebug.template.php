<?php

function template_docs_above()
{
	global $txt;

	echo '
	<div class="noticebox"><a class="bbc_link" href="https://dragomano.github.io/Light-Portal/" target="_blank" rel="noopener">', $txt['admin_search_type_online'], '</a></div>';
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
