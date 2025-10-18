<?php

declare(strict_types=1);

use LightPortal\Articles\ChosenPageArticle;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\PageArticle;

arch()
    ->expect(ChosenPageArticle::class)
    ->toExtend(PageArticle::class)
    ->toImplement(ArticleInterface::class);
