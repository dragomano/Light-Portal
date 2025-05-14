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

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ContentClass;

use function func_get_args;
use function json_encode;
use function sprintf;

final class ContentClassSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'content_class';
		$params['wide'] ??= false;
		$params['data'] ??= ContentClass::values() ?? [];
		$params['value'] ??= Utils::$context['lp_block']['content_class'] ?? '';

		$data = [];
		foreach ($params['data'] as $key => $template) {
			$data[] = [
				'label' => sprintf($template, empty($key) ? Lang::$txt['no'] : $key),
				'value' => $key,
			];
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				showSelectedOptionsFirst: true,
				optionHeight: "60px",
				placeholder: "' . Lang::$txt['no'] . '",
				options: ' . json_encode($data) . ',' . ($params['wide'] ? '
				maxWidth: "100%",' : '') . '
				selectedValue: "' . $params['value'] . '",
				labelRenderer: function (data) {
					return `<div>${data.label}</div>`;
				}
			});
		</script>';
	}
}
