<?php

declare(strict_types=1);

use LightPortal\Validators\PageValidator;
use LightPortal\Validators\ValidatorInterface;

arch()
    ->expect(PageValidator::class)
    ->toImplement(ValidatorInterface::class);
