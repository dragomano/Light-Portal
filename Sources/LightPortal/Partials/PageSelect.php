<?php declare(strict_types=1);

/**
 * PageSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Partials;

final class PageSelect extends AbstractPartial
{
	public function __invoke(array $params = []): string
	{
		$params['id'] ??= 'lp_frontpage_pages';
		$params['value'] ??= $this->modSettings['lp_frontpage_pages'] ?? '';
		$params['data'] ??= $this->getEntityList('page');

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
				ele: "#' . $params['id'] . '",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				multiple: true,
				search: true,
				markSearchResults: true,
				placeholder: "' . ($params['hint'] ?? $this->txt['lp_frontpage_pages_select']) . '",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				allOptionsSelectedText: "' . $this->txt['all'] . '",
				noOptionsText: "' . $this->txt['lp_frontpage_pages_no_items'] . '",
				moreText: "' . $this->txt['post_options'] . '",
				showValueAsTags: true,
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: [' . $params['value'] . ']
			});
		</script>';
	}
}
