<?php declare(strict_types=1);

use Bugo\LightPortal\Areas\ConfigArea;
use Bugo\LightPortal\Utils\Traits\HasCache;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasForumHooks;
use Bugo\LightPortal\Events\HasEvents;

arch()
	->expect(ConfigArea::class)
	->toBeInvokable()
	->toUseTraits([HasCache::class, HasEvents::class, HasForumHooks::class, HasRequest::class]);
