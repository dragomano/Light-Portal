<?php

declare(strict_types=1);

use LightPortal\Actions\Comment;
use LightPortal\Actions\ActionInterface;

arch()
    ->expect(Comment::class)
    ->toImplement(ActionInterface::class);
