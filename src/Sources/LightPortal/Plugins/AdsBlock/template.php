<?php

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\AdsBlock\Placement;

function template_ads_placement_board_above(): void
{
	lp_show_blocks(Placement::BOARD_TOP->name());
}

function template_ads_placement_board_below(): void
{
	lp_show_blocks(Placement::BOARD_BOTTOM->name());
}

function template_ads_placement_topic_above(): void
{
	lp_show_blocks(Placement::TOPIC_TOP->name());
}

function template_ads_placement_topic_below(): void
{
	lp_show_blocks(Placement::TOPIC_BOTTOM->name());
}

function template_ads_placement_page_above(): void
{
	lp_show_blocks(Placement::PAGE_TOP->name());
}

function template_ads_placement_page_below(): void
{
	lp_show_blocks(Placement::PAGE_BOTTOM->name());
}

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
