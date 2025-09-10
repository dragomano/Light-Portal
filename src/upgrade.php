<?php declare(strict_types=1);

require_once __DIR__ . '/init.php';

checkRequirements();

$classMap = [];
$autoload = __DIR__ . '/Sources/LightPortal/Libs/composer/autoload_classmap.php';
if (file_exists($autoload)) {
	$classMap = include $autoload;
}

spl_autoload_register(function ($class) use ($classMap) {
	if (isset($classMap[$class]) && file_exists($classMap[$class])) {
		include_once $classMap[$class];
	}
});

include_once __DIR__ . '/Sources/LightPortal/Libs/bugo/smf-compat/src/app.php';

use Bugo\LightPortal\Migrations\Installer;

$installer = new Installer();

if (! $installer->upgrade()) {
	exit('<b>Error:</b> An error occurred while removing portal data from the database!');
}

try {
	copyDirectory(__DIR__ . '/Sources/LightPortal', dirname(__DIR__, 2) . '/Sources/LightPortal');
} catch (RuntimeException $e) {
	echo "Error: " . $e->getMessage();
} finally {
	echo 'Database changes are complete! Please wait...';
}
