<?php declare(strict_types=1);

/**
 * ActionSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Partials;

use Bugo\LightPortal\Utils\{Config, Lang, Utils};

final class ActionSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'lp_disabled_actions';
		$params['data'] ??= (empty(Config::$modSettings['lp_disabled_actions']) ? [] : explode(',', Config::$modSettings['lp_disabled_actions']));
		$params['value'] = [];

		$data = [];
		foreach ($params['data'] as $value) {
			$data[] = [
				'label' => $value,
				'value' => Utils::JavaScriptEscape($value)
			];

			$params['value'][] = Utils::JavaScriptEscape($value);
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
				placeholder: "' . Lang::$txt['lp_example'] . 'mlist, calendar",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				allOptionsSelectedText: "' . Lang::$txt['all'] . '",
				noOptionsText: "' . Lang::$txt['no'] . '",
				showValueAsTags: true,
				allowNewOption: true,
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: [' . implode(',', $params['value']) . ']
			});
		</script>';
	}
}
