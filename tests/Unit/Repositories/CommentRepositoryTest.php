<?php

declare(strict_types=1);

use Bugo\LightPortal\Repositories\AbstractRepository;
use Bugo\LightPortal\Repositories\CommentRepository;
use Bugo\LightPortal\Repositories\CommentRepositoryInterface;

arch()
    ->expect(CommentRepository::class)
    ->toImplement(CommentRepositoryInterface::class)
    ->toExtend(AbstractRepository::class);
