<?php

declare(strict_types=1);

use Bugo\Compat\Actions\MessageIndex as BaseMessageIndex;
use Bugo\LightPortal\Utils\MessageIndex;

arch()
    ->expect(MessageIndex::class)
    ->toExtend(BaseMessageIndex::class);
