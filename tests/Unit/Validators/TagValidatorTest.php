<?php

declare(strict_types=1);

use LightPortal\Validators\TagValidator;
use LightPortal\Validators\ValidatorInterface;

arch()
    ->expect(TagValidator::class)
    ->toImplement(ValidatorInterface::class);
