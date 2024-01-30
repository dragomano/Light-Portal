<?php /** @noinspection PhpIgnoredClassAliasDeclaration */

declare(strict_types=1);

if (! defined('SMF'))
	die('We gotta get out of here!');

require_once __DIR__ . '/Libs/autoload.php';

use Laminas\Loader\StandardAutoloader;
use Bugo\LightPortal\{AddonHandler, Integration};
use Bugo\LightPortal\Utils\{BBCodeParser, Config, IntegrationHook, Lang, Theme, User, Utils};

// Register autoloader
$loader = new StandardAutoloader();
$loader->registerNamespace('Bugo\LightPortal', __DIR__);
$loader->register();

if (str_starts_with(SMF_VERSION, '3.0')) {
	class_alias('Bugo\\LightPortal\\Actions\\BoardIndexNext', 'Bugo\\LightPortal\\Actions\\BoardIndex');
	class_alias('Bugo\\LightPortal\\Utils\\SMFNextTrait', 'Bugo\\LightPortal\\Utils\\SMFTrait');
	class_alias('SMF\\ServerSideIncludes', 'Bugo\\LightPortal\\Utils\\ServerSideIncludes');
	class_alias('SMF\\IntegrationHook', 'Bugo\\LightPortal\\Utils\\IntegrationHook');
	class_alias('SMF\\ErrorHandler', 'Bugo\\LightPortal\\Utils\\ErrorHandler');
	class_alias('SMF\\BBCodeParser', 'Bugo\\LightPortal\\Utils\\BBCodeParser');
	class_alias('SMF\\Cache\\CacheApi', 'Bugo\\LightPortal\\Utils\\CacheApi');
	class_alias('SMF\\Actions\\Notify', 'Bugo\\LightPortal\\Utils\\Notify');
	class_alias('SMF\\Theme', 'Bugo\\LightPortal\\Utils\\SMFTheme');
	class_alias('SMF\\Config', 'Bugo\\LightPortal\\Utils\\Config');
	class_alias('SMF\\Utils', 'Bugo\\LightPortal\\Utils\\Utils');
	class_alias('SMF\\Lang', 'Bugo\\LightPortal\\Utils\\Lang');
	class_alias('SMF\\Mail', 'Bugo\\LightPortal\\Utils\\Mail');
	class_alias('SMF\\User', 'Bugo\\LightPortal\\Utils\\User');
} else {
	array_map(fn($u) => new $u(), [
		Lang::class,
		User::class,
		Theme::class,
		Utils::class,
		Config::class,
	]);
}

// Define important helper functions
function call_portal_hook(string $hook, array $params = [], array $plugins = []): void
{
	AddonHandler::getInstance()->run($hook, $params, $plugins);
}

function prepare_content(string $type = 'bbc', int $block_id = 0, int $cache_time = 0, array $parameters = []): string
{
	ob_start();

	$data = new class($type, $block_id, $cache_time) {
		public function __construct(
			public string $type = 'bbc',
			public int $block_id = 0,
			public int $cache_time = 0
		) {}
	};

	call_portal_hook('prepareContent', [$data, $parameters]);

	return ob_get_clean();
}

function parse_content(string $content, string $type = 'bbc'): string
{
	if ($type === 'bbc') {
		$content = BBCodeParser::load()->parse($content);

		IntegrationHook::call('integrate_paragrapher_string', [&$content]);

		return $content;
	} elseif ($type === 'html') {
		return Utils::htmlspecialcharsDecode($content);
	} elseif ($type === 'php') {
		$content = trim(Utils::htmlspecialcharsDecode($content));
		$content = str_replace('<?php', '', $content);
		$content = str_replace('?>', '', $content);

		ob_start();

		try {
			$tempFile = tempnam(Config::getTempDir(), 'code');

			file_put_contents($tempFile, '<?php ' . html_entity_decode($content, ENT_COMPAT, 'UTF-8'));

			include $tempFile;

			unlink($tempFile);
		} catch (ParseError $p) {
			echo $p->getMessage();
		}

		return ob_get_clean();
	}

	call_portal_hook('parseContent', [&$content, $type]);

	return $content;
}

// This is the way
(new Integration())();
