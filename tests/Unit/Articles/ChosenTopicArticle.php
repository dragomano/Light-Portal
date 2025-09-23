<?php

declare(strict_types=1);

use Bugo\LightPortal\Articles\ChosenTopicArticle;
use Bugo\LightPortal\Articles\ArticleInterface;
use Bugo\LightPortal\Articles\TopicArticle;

arch()
    ->expect(ChosenTopicArticle::class)
    ->toExtend(TopicArticle::class)
    ->toImplement(ArticleInterface::class);
