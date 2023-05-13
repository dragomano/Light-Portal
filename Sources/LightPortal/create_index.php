<?php

if (! getenv('COMPOSER_BINARY')) {
	die('This script can only be called through Composer!');
}

$directories = new RecursiveDirectoryIterator('.');
foreach (new RecursiveIteratorIterator($directories) as $directory) {
	if ($directory->isDir() && ! file_exists($directory . '/index.php')) {
		file_put_contents($directory . '/index.php', '<?php

if (file_exists(dirname(__FILE__, 2) . \'/index.php\'))
	include (dirname(__FILE__, 2) . \'/index.php\');
else
	exit;
');
	}
}