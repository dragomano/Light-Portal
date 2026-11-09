<?php declare(strict_types = 1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;

if (! defined('SMF'))
	die('No direct access...');

class Response implements ResponseInterface
{
	public function json(mixed $data, int $flags = 0): false|string
	{
		header('Content-Type: application/json; charset=utf-8');

		return json_encode($data, $flags);
	}

	public function exit(mixed $data, int $flags = 0): never
	{
		exit($this->json($data, $flags));
	}

	public function redirect(string $url = ''): void
	{
		if (DatastarSender::isDatastarRequest()) {
			(new DatastarSender)->redirect($this->buildRedirectUrl($url));

			exit;
		}

		Utils::redirectexit($url);
	}

	/**
	 * The classic redirectexit() prepends scripturl to query-style locations,
	 * so the Datastar location event must receive the same absolute URL
	 */
	private function buildRedirectUrl(string $url): string
	{
		if (preg_match('~^(ftp|https?)://~', $url) === 1 || str_starts_with($url, 'about:')) {
			return $url;
		}

		return Config::$scripturl . ($url !== '' ? '?' . $url : '');
	}
}
