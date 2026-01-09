<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\UI\Partials;

use Bugo\Compat\Utils;
use LightPortal\Utils\Icon;

if (! defined('SMF'))
	die('No direct access...');

final class IconSelect extends AbstractSelect
{
	protected string $template = 'icon_select';

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
		$icon = $this->params['icon'] ?? Utils::$context['lp_block']['icon'] ?? '';
		$type = $this->params['type'] ?? Utils::$context['lp_block']['type'] ?? '';

		return [
			'id'         => 'icon',
			'multiple'   => false,
			'wide'       => false,
			'hint'       => 'cheese',
			'value'      => $icon,
			'block_type' => $type,
		];
	}
}
