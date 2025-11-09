<?php

declare(strict_types=1);

use LightPortal\Hooks\DownloadRequest;

arch()
    ->expect(DownloadRequest::class)
    ->toBeInvokable();
