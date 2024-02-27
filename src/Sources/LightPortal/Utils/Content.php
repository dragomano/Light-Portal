<?php declare(strict_types=1);

/**
 * Content.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\{BBCodeParser, IntegrationHook, Sapi, Utils};
use Bugo\LightPortal\AddonHandler;
use ParseError;

final class Content
{
	public static function prepare(
		string $type = 'bbc',
		int $block_id = 0,
		int $cache_time = 0,
		array $parameters = []
	): string
	{
		ob_start();

		$data = new class($type, $block_id, $cache_time) {
			public function __construct(
				public string $type = 'bbc',
				public int $id = 0,
				public int $cacheTime = 0
			) {}
		};

		AddonHandler::getInstance()->run('prepareContent', [$data, $parameters]);

		return ob_get_clean();
	}

	public static function parse(string $content, string $type = 'bbc'): string
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
				$tempFile = tempnam(Sapi::getTempDir(), 'code');

				file_put_contents(
					$tempFile, '<?php ' . html_entity_decode($content, ENT_COMPAT, 'UTF-8')
				);

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
}
