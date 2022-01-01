<?php

if (! defined('SMF'))
	die('No direct access...');

// Register autoloader
spl_autoload_register(function ($classname) {
	if (strpos($classname, 'Bugo\LightPortal') === false)
		return false;

	$classname = str_replace('\\', '/', str_replace('Bugo\LightPortal\\', '', $classname));
	$file_path = __DIR__ . '/' . $classname . '.php';

	if (! file_exists($file_path))
		return false;

	require_once $file_path;
});

// Define helper functions
function prepare_content(string $type = 'bbc', int $block_id = 0, int $cache_time = 0): string
{
	global $context;

	! empty($block_id) && ! empty($context['lp_active_blocks'][$block_id])
		? $parameters = $context['lp_active_blocks'][$block_id]['parameters'] ?? []
		: $parameters = $context['lp_block']['options']['parameters'] ?? [];

	ob_start();

	(new \Bugo\LightPortal\Addon)->run('prepareContent', [$type, $block_id, $cache_time, $parameters]);

	return ob_get_clean();
}

function parse_content(string $content, string $type = 'bbc'): string
{
	if ($type === 'bbc') {
		$content = \parse_bbc($content);

		// Integrate with the Paragrapher mod
		\call_integration_hook('integrate_paragrapher_string', [&$content]);

		return $content;
	} elseif ($type === 'html') {
		return \un_htmlspecialchars($content);
	} elseif ($type === 'php') {
		$content = trim(\un_htmlspecialchars($content));
		$content = trim($content, '<?php');
		$content = trim($content, '?>');

		ob_start();

		try {
			eval(html_entity_decode($content, ENT_COMPAT, 'UTF-8'));
		} catch (\ParseError $p) {
			echo $p->getMessage();
		}

		return ob_get_clean();
	}

	(new \Bugo\LightPortal\Addon)->run('parseContent', [&$content, $type]);

	return $content;
}

/**
 * @see https://symfony.com/doc/current/translation/message_format.html
 * @see https://unicode-org.github.io/cldr-staging/charts/37/supplemental/language_plural_rules.html
 * @see https://www.php.net/manual/en/class.messageformatter.php
 * @see https://intl.rmcreative.ru
 */
function __(string $pattern, array $values = []): string
{
	global $txt;

	if (extension_loaded('intl')) {
		return \MessageFormatter::formatMessage($txt['lang_locale'], $txt[$pattern] ?? $pattern, $values) ?? '';
	}

	\log_error('[LP] getSmartContext helper: enable intl extension', 'critical');

	return '';
}

// Run portal
$portal = new \Bugo\LightPortal\Integration;
$portal->hooks();
