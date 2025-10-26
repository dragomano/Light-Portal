<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Imports\Custom\AbstractCustomCategoryImport;
use LightPortal\DataHandlers\Imports\Custom\AbstractCustomImport;

arch()
    ->expect(AbstractCustomCategoryImport::class)
    ->toBeAbstract()
    ->extending(AbstractCustomImport::class);

it('has required properties', function () {
    $mock = mock(AbstractCustomCategoryImport::class)->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    $typeReflection = new ReflectionProperty($mock, 'type');
    $entityReflection = new ReflectionProperty($mock, 'entity');

    expect($typeReflection->getValue($mock))->toBe('category')
        ->and($entityReflection->getValue($mock))->toBe('categories');
});
