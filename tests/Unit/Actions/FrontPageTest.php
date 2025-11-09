<?php

declare(strict_types=1);

use LightPortal\Actions\FrontPage;
use LightPortal\Actions\ActionInterface;

arch()
    ->expect(FrontPage::class)
    ->toImplement(ActionInterface::class);
