<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Imports\Custom\AbstractCustomBlockImport;
use LightPortal\DataHandlers\Imports\Custom\AbstractCustomImport;

arch()
    ->expect(AbstractCustomBlockImport::class)
    ->toBeAbstract()
    ->extending(AbstractCustomImport::class);

it('has required properties', function () {
    $mock = mock(AbstractCustomBlockImport::class)->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    $typeReflection = new ReflectionProperty($mock, 'type');
    $entityReflection = new ReflectionProperty($mock, 'entity');

    expect($typeReflection->getValue($mock))->toBe('block')
        ->and($entityReflection->getValue($mock))->toBe('blocks');
});
