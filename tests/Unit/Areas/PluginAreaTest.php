<?php declare(strict_types=1);

use Bugo\LightPortal\Areas\PluginArea;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Utils\Traits\HasCache;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasResponse;

arch()
	->expect(PluginArea::class)
	->toUseTraits([HasCache::class, HasEvents::class, HasRequest::class, HasResponse::class]);
