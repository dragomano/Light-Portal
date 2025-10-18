<?php declare(strict_types=1);

namespace LightPortal\Events;

use function LightPortal\app;

trait HasEvents
{
	public function events(array $plugins = []): EventManager
	{
		return app(EventManagerFactory::class)($plugins);
	}
}
