<?php

declare(strict_types=1);

use LightPortal\Articles\BoardArticle;
use LightPortal\Articles\ArticleInterface;

arch()
    ->expect(BoardArticle::class)
    ->toImplement(ArticleInterface::class);
