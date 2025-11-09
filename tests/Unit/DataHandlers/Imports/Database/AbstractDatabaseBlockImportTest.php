<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Imports\Database\AbstractDatabaseBlockImport;
use LightPortal\DataHandlers\Imports\Database\AbstractDatabaseImport;
use Tests\ReflectionAccessor;

arch()
    ->expect(AbstractDatabaseBlockImport::class)
    ->toBeAbstract()
    ->extending(AbstractDatabaseImport::class);

it('has required properties', function () {
    $mock = mock(AbstractDatabaseBlockImport::class)->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    $reflection = new ReflectionAccessor($mock);

    expect($reflection->getProtectedProperty('type'))->toBe('block')
        ->and($reflection->getProtectedProperty('entity'))->toBe('blocks');
});
