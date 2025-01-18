<?php declare(strict_types=1);

use Bugo\Compat\Utils;

function template_manage_plugins(): void
{
	if (! empty(Utils::$context['lp_addon_chart'])) {
		echo '
	<canvas id="addon_chart"></canvas>';
	}

	echo /** @lang text */ '
	<div id="svelte_plugins"></div>
	<script type="module">
		usePortalApi("', Utils::$context['lp_plugins_api_endpoint'], '", "bundle_plugins.js")
	</script>';
}
