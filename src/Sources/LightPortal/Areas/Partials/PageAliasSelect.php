<?php declare(strict_types=1);

/**
 * PageAliasSelect.php
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

use Bugo\Compat\{Config, Lang, Utils};

final class PageAliasSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'lp_frontpage_alias';
		$params['value'] ??= Config::$modSettings['lp_frontpage_alias'] ?? '';
		$params['data'] ??= $this->getEntityData('page');

		$data = [];
		foreach ($params['data'] as $page) {
			$data[] = [
				'label' => $page['title'],
				'value' => $page['alias']
			];
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				search: true,
				maxWidth: "100%",
				placeholder: "' . ($params['hint'] ?? Lang::$txt['no']) . '",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				noOptionsText: "' . Lang::$txt['lp_frontpage_pages_no_items'] . '",
				options: ' . json_encode($data) . ',
				selectedValue: "' . $params['value'] . '"
			});
		</script>';
	}
}
