<?php

use Bugo\LightPortal\{AddonHandler, Integration};

if (! defined('SMF'))
	die('No direct access...');

// Register autoloader
spl_autoload_register(function ($classname) {
	if (! str_contains($classname, 'Bugo\LightPortal'))
		return false;

	$classname = str_replace('\\', '/', str_replace('Bugo\LightPortal\\', '', $classname));
	$path = __DIR__ . '/' . $classname . '.php';

	if (! file_exists($path))
		return false;

	require_once $path;

	return true;
});

// Define important helper functions
function prepare_content(string $type = 'bbc', int $block_id = 0, int $cache_time = 0, array $parameters = []): string
{
	ob_start();

	AddonHandler::getInstance()->run('prepareContent', [$type, $block_id, $cache_time, $parameters]);

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

	AddonHandler::getInstance()->run('parseContent', [&$content, $type]);

	return $content;
}

// Run portal
$portal = new Integration();
$portal->hooks();
