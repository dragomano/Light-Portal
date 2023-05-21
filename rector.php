<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Set\ValueObject\SetList;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/*',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/Sources/LightPortal/AddonHandler.php',
        __DIR__ . '/database.php',
        __DIR__ . '**/Libs/*',
        __DIR__ . '**/vendor/*',
        __DIR__ . '**/langs/*',
        CountOnNullRector::class,
        JsonThrowOnErrorRector::class,
    ]);

    $rectorConfig->parallel(seconds: 360);

    $rectorConfig->indent("\t", 4);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    $rectorConfig->sets([
        SetList::DEAD_CODE,
        LevelSetList::UP_TO_PHP_80
    ]);
};
