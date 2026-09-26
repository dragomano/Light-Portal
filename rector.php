<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/src',
	])
	->withSkip([
		__DIR__ . '**/Tasks/*',
		__DIR__ . '**/Libs/*',
		__DIR__ . '**/vendor/*',
		UnwrapFutureCompatibleIfPhpVersionRector::class,
	])
	->withParallel(360)
	->withIndent(indentChar: "\t")
	->withImportNames(importShortClasses: false, removeUnusedImports: true)
	->withPreparedSets(deadCode: true)
	->withPhpSets();
