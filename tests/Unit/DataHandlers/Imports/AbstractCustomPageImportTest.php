<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Imports\Custom\AbstractCustomImport;
use LightPortal\DataHandlers\Imports\Custom\AbstractCustomPageImport;

arch()
    ->expect(AbstractCustomPageImport::class)
    ->toBeAbstract()
    ->extending(AbstractCustomImport::class);

it('has required properties', function () {
    $mock = Mockery::mock(AbstractCustomPageImport::class)->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    $typeReflection = new ReflectionProperty($mock, 'type');
    $entityReflection = new ReflectionProperty($mock, 'entity');

    expect($typeReflection->getValue($mock))->toBe('page')
        ->and($entityReflection->getValue($mock))->toBe('pages');
});
