<?php declare(strict_types=1);

/**
 * LanguageSelect.php
 *
 * @package CustomTranslate (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 20.05.23
 */

namespace Bugo\LightPortal\Addons\CustomTranslate;

use Bugo\LightPortal\Partials\AbstractPartial;

final class LanguageSelect extends AbstractPartial
{
	public function __invoke(array $params = []): string
	{
		$params['id'] ??= 'languages';
		$params['multiple'] ??= true;

		$data = $items = [];
		foreach ($params['data'] as $code => $lang) {
			$data[] = [
				'label' => $lang,
				'value' => $code
			];

			if (in_array($code, explode(',', $params['value']))) {
				$items[] = $this->jsEscape($code);
			}
		}

		return /** @lang text */ '
		<label for="languages">' . $this->txt['lp_custom_translate']['languages'] . '</label>
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
				placeholder: "' . ($params['hint'] ?? $this->txt['lp_custom_translate']['languages_select']) . '",
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
