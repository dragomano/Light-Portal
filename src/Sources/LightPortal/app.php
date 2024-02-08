<?php /** @noinspection PhpIgnoredClassAliasDeclaration */

declare(strict_types=1);

if (! defined('SMF'))
	die('We gotta get out of here!');

require_once __DIR__ . '/Libs/autoload.php';

use Laminas\Loader\StandardAutoloader;
use Bugo\LightPortal\Integration;
use Bugo\LightPortal\Utils\{Config, Lang, Theme, User, Utils};

// Register autoloader
$loader = new StandardAutoloader();
$loader->registerNamespace('Bugo\LightPortal', __DIR__);
$loader->register();

if (str_starts_with(SMF_VERSION, '3.0')) {
	$aliases = [
		'SMF\\ServerSideIncludes'                    => 'Bugo\\LightPortal\\Utils\\ServerSideIncludes',
		'SMF\\IntegrationHook'                       => 'Bugo\\LightPortal\\Utils\\IntegrationHook',
		'SMF\\ErrorHandler'                          => 'Bugo\\LightPortal\\Utils\\ErrorHandler',
		'SMF\\BBCodeParser'                          => 'Bugo\\LightPortal\\Utils\\BBCodeParser',
		'SMF\\Cache\\CacheApi'                       => 'Bugo\\LightPortal\\Utils\\CacheApi',
		'SMF\\Actions\\Notify'                       => 'Bugo\\LightPortal\\Utils\\Notify',
		'SMF\\Theme'                                 => 'Bugo\\LightPortal\\Utils\\SMFTheme',
		'SMF\\Config'                                => 'Bugo\\LightPortal\\Utils\\Config',
		'SMF\\Lang'                                  => 'Bugo\\LightPortal\\Utils\\SMFLang',
		'SMF\\Utils'                                 => 'Bugo\\LightPortal\\Utils\\Utils',
		'SMF\\Mail'                                  => 'Bugo\\LightPortal\\Utils\\Mail',
		'SMF\\User'                                  => 'Bugo\\LightPortal\\Utils\\User',
		'SMF\\Sapi'                                  => 'Bugo\\LightPortal\\Utils\\Sapi',
		'Bugo\\LightPortal\\Actions\\BoardIndexNext' => 'Bugo\\LightPortal\\Actions\\BoardIndex',
		'Bugo\\LightPortal\\Utils\\SMFTraitNext'     => 'Bugo\\LightPortal\\Utils\\SMFTrait',
		'Bugo\\LightPortal\\Utils\\LangNext'         => 'Bugo\\LightPortal\\Utils\\Lang',
	];

	$applyAlias = static fn($class, $alias) => class_alias($class, $alias);

	array_map($applyAlias, array_keys($aliases), $aliases);
} else {
	array_map(fn($u) => new $u(), [
		Lang::class,
		User::class,
		Theme::class,
		Utils::class,
		Config::class,
	]);
}

// This is the way
(new Integration())();
