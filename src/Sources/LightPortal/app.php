<?php

if (! defined('SMF'))
	die('We gotta get out of here!');

require_once __DIR__ . '/Libs/autoload.php';

use Laminas\Loader\StandardAutoloader;
use Bugo\LightPortal\{AddonHandler, Integration};

// Register autoloader
$loader = new StandardAutoloader();
$loader->registerNamespace('Bugo\LightPortal', __DIR__);
$loader->register();

// Define important helper functions
function call_portal_hook(string $hook, array $params = [], array $plugins = []): void
{
	AddonHandler::getInstance()->run($hook, $params, $plugins);
}

function prepare_content(string $type = 'bbc', int $block_id = 0, int $cache_time = 0, array $parameters = []): string
{
	ob_start();

	$data = new class($type, $block_id, $cache_time) {
		public function __construct(public string $type = 'bbc', public int $block_id = 0, public int $cache_time = 0)
		{
		}
	};

	call_portal_hook('prepareContent', [$data, $parameters]);

	return ob_get_clean();
}

function parse_content(string $content, string $type = 'bbc'): string
{
	if ($type === 'bbc') {
		$content = parse_bbc($content);

		// Integrate with the Paragrapher mod
		call_integration_hook('integrate_paragrapher_string', [&$content]);

		return $content;
	} elseif ($type === 'html') {
		return un_htmlspecialchars($content);
	} elseif ($type === 'php') {
		$content = trim(un_htmlspecialchars($content));
		$content = str_replace('<?php', '', $content);
		$content = str_replace('?>', '', $content);

		ob_start();

		try {
			$tempFile = tempnam(sys_get_temp_dir(), 'code');

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
(new Integration)->hooks();
