<?php

declare(strict_types=1);

use LightPortal\Hooks\Integration;
use LightPortal\PortalApp;
use Tests\AppMockRegistry;

it('runs the integration hook on construction', function () {
    $spy = new class {
        public bool $called = false;

        public function __invoke(): void
        {
            $this->called = true;
        }
    };

    AppMockRegistry::set(Integration::class, $spy);

    new PortalApp();

    expect($spy->called)->toBeTrue();
});
