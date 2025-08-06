<?php declare(strict_types=1);

use Bugo\LightPortal\Articles\TopicArticle;
use Bugo\LightPortal\Articles\ArticleInterface;

arch()
	->expect(TopicArticle::class)
	->toImplement(ArticleInterface::class);
