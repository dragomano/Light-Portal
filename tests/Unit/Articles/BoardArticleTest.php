<?php

declare(strict_types=1);

use Bugo\LightPortal\Articles\BoardArticle;
use Bugo\LightPortal\Articles\ArticleInterface;

arch()
    ->expect(BoardArticle::class)
    ->toImplement(ArticleInterface::class);
