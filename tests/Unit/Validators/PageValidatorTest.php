<?php

declare(strict_types=1);

use Bugo\LightPortal\Validators\PageValidator;
use Bugo\LightPortal\Validators\ValidatorInterface;

arch()
    ->expect(PageValidator::class)
    ->toImplement(ValidatorInterface::class);
