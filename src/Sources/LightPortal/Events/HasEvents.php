<?php declare(strict_types=1);

namespace Bugo\LightPortal\Events;

use function Bugo\LightPortal\app;

trait HasEvents
{
	public function events(array $plugins = []): EventManager
	{
		return app(EventManagerFactory::class)($plugins);
	}
}
