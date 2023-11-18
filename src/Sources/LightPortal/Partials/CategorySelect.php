<?php declare(strict_types=1);

/**
 * CategorySelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Partials;

final class CategorySelect extends AbstractPartial
{
	public function __invoke(array $params = []): string
	{
		$params['id'] ??= 'lp_frontpage_categories';
		$params['multiple'] ??= true;
		$params['full_width'] ??= true;
		$params['data'] ??= $this->getEntityList('category');
		$params['value'] ??= $this->modSettings['lp_frontpage_categories'] ?? '';

		$data = [];
		foreach ($params['data'] as $id => $cat) {
			$data[] = [
				'label' => $cat['name'],
				'value' => $id
			];
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				multiple: '. ($params['multiple'] ? 'true' : 'false') . ',
				search: true,' . (count($params['data']) < 2 ? '
				disabled: true,' : '') . '
				markSearchResults: true,
				placeholder: "' . ($params['hint'] ?? $this->txt['lp_frontpage_categories_select']) . '",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				allOptionsSelectedText: "' . $this->txt['all'] . '",
				showValueAsTags: true,' . ($params['full_width'] ? '
				maxWidth: "100%",' : '') . '
				options: ' . json_encode($data) . ',
				selectedValue: [' . $params['value'] . ']
			});
		</script>';
	}
}
