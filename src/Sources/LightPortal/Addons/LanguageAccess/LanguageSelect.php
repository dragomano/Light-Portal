<?php declare(strict_types=1);

/**
 * @package LanguageAccess (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.05.24
 */

namespace Bugo\LightPortal\Addons\LanguageAccess;

use Bugo\Compat\{Lang, Utils};
use Bugo\LightPortal\Areas\Partials\AbstractPartial;

final class LanguageSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$currentLanguages = Utils::$context['lp_block']['options']['allowed_languages'] ?? [];
		$currentLanguages = is_array($currentLanguages) ? $currentLanguages : explode(',', (string) $currentLanguages);

		$data = $items = [];

		foreach (Utils::$context['lp_languages'] as $lang) {
			$data[] = '{label: "' . $lang['name'] . '", value: "' . $lang['filename'] . '"}';

			if (in_array($lang['filename'], $currentLanguages)) {
				$items[] = Utils::escapeJavaScript($lang['filename']);
			}
		}

		return /** @lang text */ '
		<div id="allowed_languages" name="allowed_languages"></div>
		<script>
			VirtualSelect.init({
				ele: "#allowed_languages",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				showValueAsTags: true,
				placeholder: "' . Lang::$txt['lp_language_access']['allowed_languages_subtext'] . '",
				clearButtonText: "' . Lang::$txt['remove'] . '",
				selectAllText: "' . Lang::$txt['check_all'] . '",
				multiple: true,
				search: false,
				options: [' . implode(',', $data) . '],
				selectedValue: [' . implode(',', $items) . ']
			});
		</script>';
	}
}
