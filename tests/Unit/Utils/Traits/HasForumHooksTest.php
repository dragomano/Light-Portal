<?php

declare(strict_types=1);

use Bugo\Compat\IntegrationHook;
use LightPortal\Enums\ForumHook;
use LightPortal\Utils\Traits\HasForumHooks;
use Tests\ReflectionAccessor;

it('applies hook with existing method name', function () {
    $testClass = new class {
        use HasForumHooks;

        public function actions(): void {}
    };

    $reflection = new ReflectionAccessor($testClass);

    $result = null;
    try {
        $reflection->callProtectedMethod('applyHook', [ForumHook::actions]);
        $result = 'success';
    } catch (Exception $e) {
        $result = $e->getMessage();
    }

    expect($result)->toBe('success');
});

it('applies hook with __invoke when method does not exist', function () {
    $testClass = new class {
        use HasForumHooks;
    };

    $reflection = new ReflectionAccessor($testClass);

    $result = null;
    try {
        $reflection->callProtectedMethod('applyHook', [ForumHook::actions]);
        $result = 'success';
    } catch (Exception $e) {
        $result = $e->getMessage();
    }

    expect($result)->toBe('success');
});
