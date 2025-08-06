<?php declare(strict_types=1);

use Bugo\LightPortal\Validators\BlockValidator;
use Bugo\LightPortal\Validators\ValidatorInterface;

arch()
	->expect(BlockValidator::class)
	->toImplement(ValidatorInterface::class);
