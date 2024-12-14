<?php declare(strict_types=1);

/**
 * PageList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Repositories\BlockRepository;
use Bugo\LightPortal\Utils\Weaver;

class ActiveBlockList implements ListInterface
{
	public function __invoke(): array
	{
		return (new Weaver())(static fn() => (new BlockRepository())->getActive());
	}
}
