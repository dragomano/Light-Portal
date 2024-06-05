<?php declare(strict_types=1);

/**
 * app.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

if (! defined('SMF'))
	die('We gotta get out of here!');

require_once __DIR__ . '/Libs/autoload.php';

use Bugo\LightPortal\Areas\ConfigArea;
use Bugo\LightPortal\Areas\CreditArea;
use Bugo\LightPortal\Integration;
use Laminas\Loader\StandardAutoloader;

// Register autoloader
$loader = new StandardAutoloader();
$loader->registerNamespace('Bugo\LightPortal', __DIR__);
$loader->register();

if (str_starts_with(SMF_VERSION, '3.0')) {
	$aliases = [
		'Bugo\\LightPortal\\Actions\\BoardIndexNext' => 'Bugo\\LightPortal\\Actions\\BoardIndex',
		'Bugo\\LightPortal\\Utils\\LanguageNext'     => 'Bugo\\LightPortal\\Utils\\Language',
	];

	$applyAlias = static fn($class, $alias) => class_alias($class, $alias);

	array_map($applyAlias, array_keys($aliases), $aliases);
}

// Development mode
if (is_file(__DIR__ . '/Libs/scssphp/scssphp/src/Compiler.php')) {
	/** @noinspection PhpIgnoredClassAliasDeclaration */
	class_alias('Bugo\\LightPortal\\Compilers\\Sass', 'Bugo\\LightPortal\\Compilers\\Zero');
}

$app = new class {
	public function __construct()
	{
		if (SMF === 'BACKGROUND')
			return;

		(new Integration())();
		(new ConfigArea())();
		(new CreditArea())();
	}
};

// This is the way
new $app;
