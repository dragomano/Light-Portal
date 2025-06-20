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

namespace Bugo\LightPortal\UI\Tables;

use Bugo\Bricks\Renderers\RendererInterface;
use Bugo\Compat\ItemList;
use Bugo\Compat\Utils;

class TableRenderer implements RendererInterface
{
	public function render(array $data): void
	{
		new ItemList($data);

		Utils::$context['sub_template'] = 'show_list';
		Utils::$context['default_list'] = $data['id'];
	}
}
