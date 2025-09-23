<?php

declare(strict_types=1);

use Bugo\LightPortal\Validators\TagValidator;
use Bugo\LightPortal\Validators\ValidatorInterface;

arch()
    ->expect(TagValidator::class)
    ->toImplement(ValidatorInterface::class);
