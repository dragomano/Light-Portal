<?php

declare(strict_types=1);

use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\ChosenTopicArticle;
use LightPortal\Articles\TopicArticle;

arch()
    ->expect(ChosenTopicArticle::class)
    ->toExtend(TopicArticle::class)
    ->toImplement(ArticleInterface::class);
