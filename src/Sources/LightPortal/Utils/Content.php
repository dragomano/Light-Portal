<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\BBCodeParser;
use Bugo\Compat\IntegrationHook;
use Bugo\Compat\Sapi;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Event;
use ParseError;

use function file_put_contents;
use function html_entity_decode;
use function ob_get_clean;
use function ob_start;
use function str_replace;
use function tempnam;
use function trim;
use function unlink;

if (! defined('SMF'))
	die('No direct access...');

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

		app('events')->dispatch(
			PortalHook::prepareContent,
			new Event(new class ($type, $block_id, $cache_time, $parameters) {
				public function __construct(
					public readonly string $type,
					public readonly int $id,
					public readonly int $cacheTime,
					public readonly array $parameters
				) {}
			})
		);

		return ob_get_clean();
	}

	public static function parse(string $content, string $type = 'bbc'): string
	{
		if ($type === ContentType::BBC->name()) {
			$content = BBCodeParser::load()->parse($content);

			IntegrationHook::call('integrate_paragrapher_string', [&$content]);

			return $content;
		} elseif ($type === ContentType::HTML->name()) {
			return Utils::htmlspecialcharsDecode($content);
		} elseif ($type === ContentType::PHP->name()) {
			$content = trim(Utils::htmlspecialcharsDecode($content) ?? '');
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

		app('events')->dispatch(
			PortalHook::parseContent,
			new Event(new class ($content, $type) {
				public function __construct(public string &$content, public readonly string $type) {}
			})
		);

		return $content;
	}
}
