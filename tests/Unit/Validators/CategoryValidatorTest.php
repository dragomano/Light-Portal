<?php

declare(strict_types=1);

use LightPortal\Validators\CategoryValidator;
use LightPortal\Validators\ValidatorInterface;

arch()
    ->expect(CategoryValidator::class)
    ->toImplement(ValidatorInterface::class);
