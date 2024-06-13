<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/src',
	])
	->withSkip([
		__DIR__ . '/src/database.php',
		__DIR__ . '**/Tasks/Notifier.php',
		__DIR__ . '**/Libs/*',
		__DIR__ . '**/vendor/*',
		__DIR__ . '**/langs/*',
		__DIR__ . '**/index.php',
		StringClassNameToClassConstantRector::class,
		NullToStrictStringFuncCallArgRector::class,
	])
	->withParallel(360)
	->withIndent(indentChar: "\t")
	->withImportNames(importShortClasses: false, removeUnusedImports: true)
	->withPreparedSets(deadCode: true)
	->withPhpSets();
