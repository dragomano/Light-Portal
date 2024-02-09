<?php declare(strict_types=1);

namespace Tests\Actions;

use Bugo\LightPortal\Actions\Comment;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('has method', function () {
	Assert::true(method_exists(Comment::class, 'show'));
});
