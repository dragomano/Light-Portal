<?php declare(strict_types=1);

use Bugo\LightPortal\Utils\Content;

arch()
	->expect(Content::class)
	->toHaveMethods(['prepare', 'parse']);
