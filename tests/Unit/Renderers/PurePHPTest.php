<?php

declare(strict_types=1);

use LightPortal\Renderers\PurePHP;
use LightPortal\Renderers\RendererInterface;

arch()
    ->expect(PurePHP::class)
    ->toImplement(RendererInterface::class);
