<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\DownloadRequest;

arch()
    ->expect(DownloadRequest::class)
    ->toBeInvokable();
