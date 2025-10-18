<?php

declare(strict_types=1);

use LightPortal\Repositories\AbstractRepository;
use LightPortal\Repositories\CommentRepository;
use LightPortal\Repositories\CommentRepositoryInterface;

arch()
    ->expect(CommentRepository::class)
    ->toImplement(CommentRepositoryInterface::class)
    ->toExtend(AbstractRepository::class);
