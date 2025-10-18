<?php

declare(strict_types=1);

use LightPortal\Areas\ConfigArea;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasForumHooks;
use LightPortal\Events\HasEvents;

arch()
    ->expect(ConfigArea::class)
    ->toBeInvokable()
    ->toUseTraits([HasCache::class, HasEvents::class, HasForumHooks::class, HasRequest::class]);
