<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Areas\Partials;

use Bugo\Compat\{Config, Lang, Utils};

use function explode;
use function func_get_args;
use function implode;
use function json_encode;

final class ActionSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'lp_disabled_actions';
		$params['data'] ??= (empty(Config::$modSettings['lp_disabled_actions'])
			? []
			: explode(',', (string) Config::$modSettings['lp_disabled_actions'])
		);
		$params['value'] = [];

		$data = [];
		foreach ($params['data'] as $value) {
			$data[] = [
				'label' => $value,
				'value' => Utils::escapeJavaScript($value)
			];

			$params['value'][] = Utils::escapeJavaScript($value);
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
