<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Imports\Database\AbstractDatabaseImport;
use LightPortal\DataHandlers\Imports\Database\AbstractDatabasePageImport;
use Tests\ReflectionAccessor;

arch()
    ->expect(AbstractDatabasePageImport::class)
    ->toBeAbstract()
    ->extending(AbstractDatabaseImport::class);

it('has required properties', function () {
    $mock = mock(AbstractDatabasePageImport::class)->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    $reflection = new ReflectionAccessor($mock);

    expect($reflection->getProperty('type'))->toBe('page')
        ->and($reflection->getProperty('entity'))->toBe('pages');
});
