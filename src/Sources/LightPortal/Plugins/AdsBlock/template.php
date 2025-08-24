<?php

use Bugo\Compat\Config;
use Bugo\Compat\Utils;

function template_ads_block_form_above() {}

function template_ads_block_form_below(): void
{
	echo '
	<form
		name="ads_block_form"
		action="', Config::$scripturl, '?action=admin;area=lp_blocks;sa=add"
		method="post"
		accept-charset="', Utils::$context['character_set'], /** @lang text */ '"
		style="display: none"
	>
		<input type="hidden" name="add_block" value="ads_block">
		<input type="hidden" name="placement" value="ads">
	</form>
	<script>
		const addButton = document.querySelector(\'h3 a[href$="placement=ads"]\');
		if (addButton) {
			addButton.removeAttribute("href");
			addButton.addEventListener("click", () => document.forms.ads_block_form.submit());
		}
	</script>';
}
