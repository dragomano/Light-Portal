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

namespace Bugo\LightPortal\Plugins\TagList;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\Tag\Cloud\Decorator\DecoratorInterface;

class DecoratorPluginManager extends AbstractPluginManager
{
	protected string $instanceOf = DecoratorInterface::class;

	public function validate(mixed $instance): void
	{
		if (! $instance instanceof $this->instanceOf) {
			throw new InvalidServiceException(sprintf(
				'%s can only create instances of %s; %s is invalid',
				static::class,
				$this->instanceOf,
				get_debug_type($instance)
			));
		}
	}
}
