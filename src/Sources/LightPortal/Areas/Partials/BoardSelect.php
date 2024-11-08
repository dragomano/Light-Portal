<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Areas\Partials;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Utils\MessageIndex;

use function func_get_args;
use function json_encode;

final class BoardSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'lp_frontpage_boards';
		$params['value'] ??= Config::$modSettings['lp_frontpage_boards'] ?? '';
		$params['data'] ??= MessageIndex::getBoardList();

		$data = [];
		foreach ($params['data'] as $cat) {
			$options = [];
			foreach ($cat['boards'] as $id_board => $board) {
				$options[] = [
					'label' => $board['name'],
					'value' => $id_board
				];
			}

			$data[] = [
				'label'   => $cat['name'],
				'options' => $options
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
				placeholder: "' . ($params['hint'] ?? Lang::$txt['lp_frontpage_boards_select']) . '",
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
