<?php

declare(strict_types=1);

use LightPortal\Articles\AbstractArticle;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\ChosenTopicArticle;

arch()
    ->expect(ChosenTopicArticle::class)
    ->toExtend(AbstractArticle::class)
    ->toImplement(ArticleInterface::class);
