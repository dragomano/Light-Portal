<?php declare(strict_types=1);

namespace Bugo\LightPortal\Events;

trait HasEvents
{
	public function events(array $plugins = []): EventManager
	{
		return app(EventManagerFactory::class)($plugins);
	}
}
