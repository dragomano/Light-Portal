<?php declare(strict_types=1);

/**
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 26.03.25
 */

namespace Bugo\LightPortal\Plugins\PluginMaker;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\UI\Partials\AbstractPartial;

use function func_get_args;
use function json_encode;

final class TypeSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'type';
		$params['value'] ??= Utils::escapeJavaScript(Utils::$context['lp_plugin']['type']);
		$params['data'] ??= Utils::$context['lp_plugin_types'];

		$data = [];
		foreach ($params['data'] as $key => $value) {
			$data[] = [
				'label' => $value,
				'value' => $key,
			];
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				multiple: true,
				search: true,
				markSearchResults: true,
				placeholder: "' . ($params['hint'] ?? Lang::$txt['lp_plugin_maker']['type_select']) . '",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				allOptionsSelectedText: "' . Lang::$txt['all'] . '",
				showValueAsTags: true,
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: [' . $params['value'] . ']
			});
		</script>';
	}
}
