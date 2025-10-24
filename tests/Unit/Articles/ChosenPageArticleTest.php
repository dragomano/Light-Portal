<?php

declare(strict_types=1);

use LightPortal\Articles\AbstractArticle;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\ChosenPageArticle;

arch()
    ->expect(ChosenPageArticle::class)
    ->toExtend(AbstractArticle::class)
    ->toImplement(ArticleInterface::class);
