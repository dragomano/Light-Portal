<?php declare(strict_types=1);

use Bugo\LightPortal\Validators\CategoryValidator;
use Bugo\LightPortal\Validators\ValidatorInterface;

arch()
	->expect(CategoryValidator::class)
	->toImplement(ValidatorInterface::class);
