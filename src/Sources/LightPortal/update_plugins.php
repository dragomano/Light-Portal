<?php

if (! getenv('COMPOSER_BINARY')) {
	die('This script can only be called through Composer!');
}

$files = glob(__DIR__ . '/Plugins/*/composer.json');

foreach ($files as $file) {
	$directory = dirname($file);

	echo "Updating dependencies in the directory: " . basename($directory) . PHP_EOL;

	chdir($directory);

	exec('composer update --no-dev -o', $output, $returnCode);

	if ($returnCode === 0) {
		echo "Dependency update completed successfully!" . PHP_EOL;
	} else {
		echo "Error during updating dependencies: " . implode(PHP_EOL, $output) . PHP_EOL;
	}
}
