<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\{ItemList as BaseItemList, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class ItemList extends BaseItemList
{
	public function __construct(array $listOptions)
	{
		parent::__construct($listOptions);

		Utils::$context['sub_template'] = 'show_list';
		Utils::$context['default_list'] = $listOptions['id'];
	}
}
