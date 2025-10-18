<?php

declare(strict_types=1);

use LightPortal\Areas\PluginArea;
use LightPortal\Events\HasEvents;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasResponse;

arch()
    ->expect(PluginArea::class)
    ->toUseTraits([HasCache::class, HasEvents::class, HasRequest::class, HasResponse::class]);
