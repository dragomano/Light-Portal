<?php

declare(strict_types=1);

use Bugo\LightPortal\Actions\Tag;
use Bugo\LightPortal\Actions\PageListInterface;

arch()
    ->expect(Tag::class)
    ->toImplement(PageListInterface::class);
