<?php

declare(strict_types=1);

use LightPortal\Renderers\Blade;
use LightPortal\Renderers\RendererInterface;

arch()
    ->expect(Blade::class)
    ->toImplement(RendererInterface::class);
