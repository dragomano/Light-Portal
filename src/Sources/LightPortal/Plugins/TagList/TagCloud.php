<?php declare(strict_types=1);

/**
 * @package TagList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 23.04.25
 */

namespace LightPortal\Plugins\TagList;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Tag\Cloud;

class TagCloud extends Cloud
{
	public function __construct($options = null)
	{
		parent::__construct($options);

		$this->decorators = new DecoratorPluginManager(new ServiceManager());
	}
}
