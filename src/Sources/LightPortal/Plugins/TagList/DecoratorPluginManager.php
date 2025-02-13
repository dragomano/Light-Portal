<?php declare(strict_types=1);

/**
 * @package TagList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 12.02.25
 */

namespace Bugo\LightPortal\Plugins\TagList;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\Tag\Cloud\Decorator;
use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class DecoratorPluginManager extends AbstractPluginManager
{
	protected array $aliases = [
		'htmlcloud' => Decorator\HtmlCloud::class,
		'htmlCloud' => Decorator\HtmlCloud::class,
		'Htmlcloud' => Decorator\HtmlCloud::class,
		'HtmlCloud' => Decorator\HtmlCloud::class,
		'htmltag'   => Decorator\HtmlTag::class,
		'htmlTag'   => Decorator\HtmlTag::class,
		'Htmltag'   => Decorator\HtmlTag::class,
		'HtmlTag'   => Decorator\HtmlTag::class,
		'tag'       => Decorator\HtmlTag::class,
		'Tag'       => Decorator\HtmlTag::class,
	];

	protected array $factories = [];

	protected string $instanceOf = Decorator\DecoratorInterface::class;

	public function validate(mixed $instance): void
	{
		if (! $instance instanceof $this->instanceOf) {
			throw new InvalidServiceException(sprintf(
				'%s can only create instances of %s; %s is invalid',
				static::class,
				$this->instanceOf,
				is_object($instance) ? $instance::class : gettype($instance)
			));
		}
	}

	public function validatePlugin(mixed $instance): void
	{
		try {
			$this->validate($instance);
		} catch (InvalidServiceException $e) {
			throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
		} catch (ContainerExceptionInterface) {
		}
	}
}
