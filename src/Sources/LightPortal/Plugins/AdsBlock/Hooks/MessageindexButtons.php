<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 06.11.24
 */

namespace Bugo\LightPortal\Plugins\AdsBlock\Hooks;

use Bugo\Compat\Utils;

use function dirname;

class MessageindexButtons
{
	public function __invoke(): void
	{
		require_once dirname(__DIR__) . '/template.php';

		Utils::$context['template_layers'][] = 'ads_placement_board';
	}
}
