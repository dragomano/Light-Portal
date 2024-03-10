<?php

use Bugo\Compat\{Lang, Utils};

function template_docs_above(): void
{
	$lang = in_array(Lang::$txt['lang_dictionary'], ['ru', 'el', 'it']) ? Lang::$txt['lang_dictionary'] : '';

	echo '
	<div class="noticebox">
		<a class="bbc_link" href="https://dragomano.github.io/Light-Portal/' . $lang . '" target="_blank" rel="noopener">
			', Lang::$txt['admin_search_type_online'], '
		</a>
	</div>';

	echo '
	<script defer src="https://www.unpkg.com/@feelback/js/dist/browser-auto.js"></script>
	<div class="infobox">
		<div class="feelback-container" data-feelback-set="e2b51143-5455-41d9-9609-d80081496dba" data-feelback-key="', Utils::$context['page_title'], '">
			<span>', Lang::$txt['lp_feedback_question'], '</span>
			<button class="button floatnone" data-feelback-value="y">', Lang::$txt['yes'], '</button>
			<button class="button floatnone" data-feelback-value="n">', Lang::$txt['no'], '</button>
		</div>
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
