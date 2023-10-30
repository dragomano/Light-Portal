<?php declare(strict_types=1);

/**
 * AdminSelect.php
 *
 * @package CrowdinContext (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 30.10.23
 */

namespace Bugo\LightPortal\Addons\CrowdinContext;

use Bugo\LightPortal\Partials\AbstractPartial;

final class AdminSelect extends AbstractPartial
{
	public function __invoke(array $params = []): string
	{
		$params['id'] ??= 'admins';
		$params['multiple'] ??= true;

		$data = $items = [];
		foreach ($params['data'] as $id => $name) {
			$data[] = [
				'label' => $name,
				'value' => $id
			];

			if (in_array($id, explode(',', $params['value']))) {
				$items[] = $id;
			}
		}

		return /** @lang text */ '
		<label for="admins">' . $this->txt['lp_crowdin_context']['admins'] . '</label>
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				multiple: '. ($params['multiple'] ? 'true' : 'false') . ',
				search: true,
				markSearchResults: true,
				showSelectedOptionsFirst: true,
				placeholder: "' . ($params['hint'] ?? $this->txt['lp_crowdin_context']['admins_select']) . '",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				allOptionsSelectedText: "' . $this->txt['all'] . '",
				clearButtonText: "' . $this->txt['remove'] . '",
				showValueAsTags: true,
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: [' . implode(',', $items) . ']
			});
		</script>';
	}
}
