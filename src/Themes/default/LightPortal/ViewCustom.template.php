<?php declare(strict_types=1);

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Events\EventManagerFactory;

use function Bugo\LightPortal\app;

/**
 * @layer custom
 * @see Utils::$context['template_layers']
 */
function template_custom_above(): void
{
	app(EventManagerFactory::class)()->dispatch(PortalHook::addLayerAbove);
}

/**
 * @template custom
 * @see Utils::$context['sub_template']
 */
function template_custom(): void
{
	echo Utils::$context['lp_custom_content'] ?? '';
}

/**
 * @layer custom
 * @see Utils::$context['template_layers']
 */
function template_custom_below(): void
{
	app(EventManagerFactory::class)()->dispatch(PortalHook::addLayerBelow);
}
