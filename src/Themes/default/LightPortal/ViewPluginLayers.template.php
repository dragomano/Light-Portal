<?php declare(strict_types=1);

use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Events\EventManagerFactory;

use function Bugo\LightPortal\app;

/**
 * @layer lp_plugins
 * @see Utils::$context['template_layers']
 */
function template_lp_plugins_above(): void
{
	app(EventManagerFactory::class)()->dispatch(PortalHook::addLayerAbove);
}

/**
 * @layer lp_plugins
 * @see Utils::$context['template_layers']
 */
function template_lp_plugins_below(): void
{
	app(EventManagerFactory::class)()->dispatch(PortalHook::addLayerBelow);
}
