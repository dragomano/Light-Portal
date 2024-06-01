<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Events;

enum Event: string
{
	case InitAddons  = 'addon.init';
	case RunAddon    = 'addon.run';
	case SmfHook    = 'smf.hook';
	case PortalHook = 'portal.hook';
}
