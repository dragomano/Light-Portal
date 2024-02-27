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

use Laminas\Loader\StandardAutoloader;
use Bugo\LightPortal\Integration;

// Register autoloader
$loader = new StandardAutoloader();
$loader->registerNamespace('Bugo\LightPortal', __DIR__);
$loader->register();

if (str_starts_with(SMF_VERSION, '3.0')) {
	$aliases = [
		'Bugo\\LightPortal\\Actions\\BoardIndexNext' => 'Bugo\\LightPortal\\Actions\\BoardIndex',
		'Bugo\\LightPortal\\Utils\\LanguageNext'     => 'Bugo\\LightPortal\\Utils\\Language',
		'Bugo\\LightPortal\\Utils\\SMFTraitNext'     => 'Bugo\\LightPortal\\Utils\\SMFTrait',
	];

	$applyAlias = static fn($class, $alias) => class_alias($class, $alias);

	array_map($applyAlias, array_keys($aliases), $aliases);
}

// Development mode
if (is_file(__DIR__ . '/Libs/scssphp/scssphp/src/Compiler.php'))
	/** @noinspection PhpIgnoredClassAliasDeclaration */
	class_alias('Bugo\\LightPortal\\Compilers\\Sass', 'Bugo\\LightPortal\\Compilers\\Zero');

// This is the way
(new Integration())();
