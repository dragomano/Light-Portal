<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Plugins;

use Bugo\Compat\ServerSideIncludes;
use Bugo\LightPortal\Enums\PluginType;

#[PluginAttribute(type: [PluginType::BLOCK, PluginType::SSI])]
abstract class SsiBlock extends Block
{
	abstract public function prepareBlockFields(Event $e): void;

	abstract public function prepareContent(Event $e): void;

	public function getFromSSI(string $function, ...$params)
	{
		require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'SSI.php';

		return ServerSideIncludes::{$function}(...$params);
	}
}
