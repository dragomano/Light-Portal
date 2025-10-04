<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Icon;

if (! defined('SMF'))
	die('No direct access...');

final class PageIconSelect extends AbstractSelect
{
	protected string $template = 'page_icon_select';

	public function getData(): array
	{
		$icon = $this->params['value'];
		$template = Icon::parse($icon) . $icon;

		return [
			[
				'label' => $template,
				'value' => $icon,
			]
		];
	}

	protected function getDefaultParams(): array
	{
		$icon = $this->params['icon'] ?? Utils::$context['lp_page']['options']['page_icon'] ?? '';

		return [
			'id'       => 'page_icon',
			'disabled' => ! Utils::$context['lp_page']['options']['show_in_menu'],
			'multiple' => false,
			'wide'     => false,
			'hint'     => 'cheese',
			'value'    => $icon,
		];
	}
}
