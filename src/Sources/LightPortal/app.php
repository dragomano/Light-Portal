<?php /** @noinspection PhpIgnoredClassAliasDeclaration */

declare(strict_types=1);

if (! defined('SMF'))
	die('We gotta get out of here!');

require_once __DIR__ . '/Libs/autoload.php';

use Laminas\Loader\StandardAutoloader;
use Bugo\LightPortal\{AddonHandler, Integration};
use Bugo\LightPortal\Utils\{Config, Lang, Theme, User, Utils};

// Register autoloader
$loader = new StandardAutoloader();
$loader->registerNamespace('Bugo\LightPortal', __DIR__);
$loader->register();

if (str_starts_with(SMF_VERSION, '3.0')) {
	class_alias('SMF\\ServerSideIncludes', 'Bugo\\LightPortal\\Utils\\ServerSideIncludes');
	class_alias('SMF\\IntegrationHook', 'Bugo\\LightPortal\\Utils\\IntegrationHook');
	class_alias('SMF\\ErrorHandler', 'Bugo\\LightPortal\\Utils\\ErrorHandler');
	class_alias('SMF\\BBCodeParser', 'Bugo\\LightPortal\\Utils\\BBCodeParser');
	class_alias('SMF\\Cache\\CacheApi', 'Bugo\\LightPortal\\Utils\\CacheApi');
	class_alias('SMF\\Actions\\Notify', 'Bugo\\LightPortal\\Utils\\Notify');
	class_alias('SMF\\Theme', 'Bugo\\LightPortal\\Utils\\SMFTheme');
	class_alias('SMF\\Config', 'Bugo\\LightPortal\\Utils\\Config');
	class_alias('SMF\\Lang', 'Bugo\\LightPortal\\Utils\\SMFLang');
	class_alias('SMF\\Utils', 'Bugo\\LightPortal\\Utils\\Utils');
	class_alias('SMF\\Mail', 'Bugo\\LightPortal\\Utils\\Mail');
	class_alias('SMF\\User', 'Bugo\\LightPortal\\Utils\\User');
	class_alias('SMF\\Sapi', 'Bugo\\LightPortal\\Utils\\Sapi');
	class_alias('Bugo\\LightPortal\\Actions\\BoardIndexNext', 'Bugo\\LightPortal\\Actions\\BoardIndex');
	class_alias('Bugo\\LightPortal\\Utils\\SMFTraitNext', 'Bugo\\LightPortal\\Utils\\SMFTrait');
	class_alias('Bugo\\LightPortal\\Utils\\LangNext', 'Bugo\\LightPortal\\Utils\\Lang');
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
