<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\Utils\SessionInterface;
use LightPortal\Utils\Traits\HasSession;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Utils::$context['lp_sorting_options'] = [];
    Utils::$context['lp_current_sorting'] = null;

    $this->sessionMock = mock(SessionInterface::class);
    $this->sessionMock->shouldReceive('withKey')->andReturnSelf();

    AppMockRegistry::set(SessionInterface::class, $this->sessionMock);

    $this->testClass = new class {
        use HasSession;
    };

    $this->reflection = new ReflectionAccessor($this->testClass);
});

afterEach(function () {
    AppMockRegistry::clear(SessionInterface::class);
});

describe('HasSession::session()', function () {
    it('returns session interface when key is null', function () {
        $result = $this->testClass->session(null);

        expect($result)->toBeInstanceOf(SessionInterface::class);
    });

    it('returns session interface with key when key is provided', function () {
        $sessionKey = 'test_key';
        $result = $this->testClass->session($sessionKey);

        expect($result)->toBeInstanceOf(SessionInterface::class);
    });
});
