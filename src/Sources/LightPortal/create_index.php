<?php

if (! getenv('COMPOSER_BINARY')) {
	die('This script can only be called through Composer!');
}

$directory = new RecursiveDirectoryIterator(__DIR__);
$iterator = new RecursiveIteratorIterator($directory);
foreach ($iterator as $directory) {
	if (str_contains((string) $directory->getPathname(), 'Libs'))
		continue;

	if ($directory->isDir() && ! file_exists($directory . '/index.php')) {
		file_put_contents($directory . '/index.php', '<?php

if (file_exists(dirname(__DIR__) . \'/index.php\'))
	include (dirname(__DIR__) . \'/index.php\');
else
	exit;
');
	}
}