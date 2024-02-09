<?php declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/src',
	])
	->withSkip([
		__DIR__ . '/src/database.php',
		__DIR__ . '**/Libs/*',
		__DIR__ . '**/vendor/*',
		__DIR__ . '**/langs/*',
	])
	->withParallel(360)
	->withIndent(indentChar: "\t")
	->withImportNames(importShortClasses: false, removeUnusedImports: true)
	->withPreparedSets(deadCode: true)
	->withPhpSets();
