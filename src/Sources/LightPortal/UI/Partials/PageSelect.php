<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;

use function func_get_args;
use function json_encode;

final class PageSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'lp_frontpage_pages';
		$params['value'] ??= Config::$modSettings['lp_frontpage_pages'] ?? '';
		$params['data'] ??= app('page_list');

		$data = [];
		foreach ($params['data'] as $id => $page) {
			$data[] = [
				'label' => $page['title'],
				'value' => $id
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
				placeholder: "' . ($params['hint'] ?? Lang::$txt['lp_frontpage_pages_select']) . '",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				allOptionsSelectedText: "' . Lang::$txt['all'] . '",
				noOptionsText: "' . Lang::$txt['lp_frontpage_pages_no_items'] . '",
				moreText: "' . Lang::$txt['post_options'] . '",
				showValueAsTags: true,
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: [' . $params['value'] . ']
			});
		</script>';
	}
}
