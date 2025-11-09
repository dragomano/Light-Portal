<?php declare(strict_types=1);

require_once __DIR__ . '/init.php';

$setup = PortalSetup::init();
$installer = $setup->getInstaller();

if (! $installer->install()) {
	exit('<b>Error:</b> An error occurred while adding the portal tables to the database!');
}

try {
	$setup->deletePortalFiles();
	$setup->copyPortalFiles();
} catch (RuntimeException $e) {
	$setup->handleError($e->getMessage());
} finally {
	$setup->finalize('Database changes are complete! Please wait...');
}
