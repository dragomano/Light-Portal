<?php

declare(strict_types=1);

use Bugo\LightPortal\Articles\PageArticle;
use Bugo\LightPortal\Articles\ArticleInterface;

arch()
    ->expect(PageArticle::class)
    ->toImplement(ArticleInterface::class);
