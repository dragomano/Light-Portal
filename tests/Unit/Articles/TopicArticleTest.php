<?php

declare(strict_types=1);

use LightPortal\Articles\TopicArticle;
use LightPortal\Articles\ArticleInterface;

arch()
    ->expect(TopicArticle::class)
    ->toImplement(ArticleInterface::class);
