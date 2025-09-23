<?php

declare(strict_types=1);

use Bugo\LightPortal\Actions\Comment;
use Bugo\LightPortal\Actions\ActionInterface;

arch()
    ->expect(Comment::class)
    ->toImplement(ActionInterface::class);
