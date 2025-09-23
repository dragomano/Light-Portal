<?php

declare(strict_types=1);

use Bugo\LightPortal\Articles\ChosenPageArticle;
use Bugo\LightPortal\Articles\ArticleInterface;
use Bugo\LightPortal\Articles\PageArticle;

arch()
    ->expect(ChosenPageArticle::class)
    ->toExtend(PageArticle::class)
    ->toImplement(ArticleInterface::class);
