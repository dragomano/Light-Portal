<?php

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;

function template_custom_translate_above() {}

function template_custom_translate_below(): void
{
	echo '
	<div class="lang lang_fixed">
		<div id="ytWidget" style="display: none"></div>
		<div class="lang__link lang__link_select" data-lang-active="">
			<div class="lang__code lang_', Lang::$txt['lang_dictionary'], '"></div>
		</div>
		<div class="lang__list" data-lang-list="" translate="no">';

	foreach (Utils::$context['ctw_languages'] as $lang) {
		echo '
			<a class="lang__link lang__link_sub" data-ya-lang="', $lang, '" title="', Utils::$context['ctw_lang_titles'][$lang], '">
				<div class="lang__code lang_', $lang, '"></div>
			</a>';
	}

	echo '
		</div>
	</div>';
}
