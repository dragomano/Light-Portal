<?php declare(strict_types=1);

use Bugo\Compat\Utils;

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
