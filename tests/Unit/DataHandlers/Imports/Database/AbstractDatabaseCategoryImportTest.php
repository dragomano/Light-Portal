<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Imports\Database\AbstractDatabaseCategoryImport;
use LightPortal\DataHandlers\Imports\Database\AbstractDatabaseImport;
use Tests\ReflectionAccessor;

arch()
    ->expect(AbstractDatabaseCategoryImport::class)
    ->toBeAbstract()
    ->extending(AbstractDatabaseImport::class);

it('has required properties', function () {
    $mock = mock(AbstractDatabaseCategoryImport::class)->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    $reflection = new ReflectionAccessor($mock);

    expect($reflection->getProtectedProperty('type'))->toBe('category')
        ->and($reflection->getProtectedProperty('entity'))->toBe('categories');
});
