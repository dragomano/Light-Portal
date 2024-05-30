<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Events\Listeners;

use Bugo\LightPortal\Events\Event;
use Bugo\LightPortal\Utils\SMFTrait;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;

final class HitchSmfListener extends AbstractListenerAggregate
{
	public function attach(EventManagerInterface $events, $priority = 1)
	{
		$this->listeners[] = $events->attach(
			Event::HitchSMF->value,
			[$this, 'onHitchSmf'],
			$priority
		);
	}

	public function onHitchSmf(EventInterface $event)
	{
		$integration = $event->getTarget();
		$integration->init();
	}
}
