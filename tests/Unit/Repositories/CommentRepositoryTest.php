<?php declare(strict_types=1);

use Bugo\LightPortal\Repositories\CommentRepository;

arch()
	->expect(CommentRepository::class)
	->toHaveMethods([
		'getAll', 'getByPageId', 'save', 'update', 'remove', 'removeFromResult', 'updateLastCommentId',
	]);
