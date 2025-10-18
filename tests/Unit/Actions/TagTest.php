<?php

declare(strict_types=1);

use LightPortal\Actions\Tag;
use LightPortal\Actions\PageListInterface;

arch()
    ->expect(Tag::class)
    ->toImplement(PageListInterface::class);
