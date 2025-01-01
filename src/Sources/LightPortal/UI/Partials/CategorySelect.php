<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Icon;

use function count;
use function func_get_args;
use function json_encode;

final class CategorySelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'lp_frontpage_categories';
		$params['multiple'] ??= true;
		$params['wide'] ??= true;
		$params['hint'] ??= Lang::$txt['lp_frontpage_categories_select'];
		$params['data'] ??= app('category_list');
		$params['value'] ??= Config::$modSettings['lp_frontpage_categories'] ?? '';

		$data = [];
		foreach ($params['data'] as $id => $cat) {
			$data[] = [
				'label' => Icon::parse($cat['icon']) . $cat['title'],
				'value' => $id,
			];
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				multiple: '. ($params['multiple'] ? 'true' : 'false') . ',
				search: true,' . (count($params['data']) < 2 ? '
				disabled: true,' : '') . '
				markSearchResults: true,
				placeholder: "' . $params['hint'] . '",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				allOptionsSelectedText: "' . Lang::$txt['all'] . '",' . ($params['multiple'] ? '
				showValueAsTags: true,' : '') . ($params['wide'] ? '
				maxWidth: "100%",' : '') . '
				options: ' . json_encode($data) . ',
				selectedValue: [' . $params['value'] . ']
			});
		</script>';
	}
}
