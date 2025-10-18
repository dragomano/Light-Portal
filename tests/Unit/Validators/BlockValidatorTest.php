<?php

declare(strict_types=1);

use LightPortal\Validators\BlockValidator;
use LightPortal\Validators\ValidatorInterface;

arch()
    ->expect(BlockValidator::class)
    ->toImplement(ValidatorInterface::class);
