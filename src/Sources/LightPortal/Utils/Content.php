<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils;

use Bugo\Compat\IntegrationHook;
use Bugo\Compat\Parsers\BBCodeParser;
use Bugo\Compat\Utils;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventManagerFactory;
use LightPortal\Renderers\PurePHP;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

class Content
{
	public static function prepare(
		string $type = 'bbc',
		int $block_id = 0,
		int $cache_time = 0,
		array $parameters = []
	): string
	{
		ob_start();

		$parameters = new ParamWrapper($parameters);

		app(EventManagerFactory::class)()->dispatch(
			PortalHook::prepareContent,
			[
				'type'       => $type,
				'id'         => $block_id,
				'cacheTime'  => $cache_time,
				'parameters' => $parameters,
			]
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
			$renderer = app(PurePHP::class);

			return $renderer->renderString($content);
		}

		app(EventManagerFactory::class)()->dispatch(
			PortalHook::parseContent,
			[
				'content' => &$content,
				'type'    => $type,
			]
		);

		return $content;
	}
}
