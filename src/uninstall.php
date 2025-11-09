<?php declare(strict_types=1);

require_once __DIR__ . '/init.php';

$setup = PortalSetup::init();
$installer = $setup->getInstaller();

if (! $installer->uninstall()) {
	exit('<b>Error:</b> An error occurred while removing the portal data from the database!');
}

try {
	$setup->deletePortalFiles();
} catch (RuntimeException $e) {
	$setup->handleError($e->getMessage());
} finally {
	$setup->finalize('I\'ll see you again. I promise ©️');
}
