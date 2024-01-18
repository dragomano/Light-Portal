<?php declare(strict_types=1);

/**
 * Mail.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Utils;

use ErrorException;
use function loadEmailTemplate;
use function sendmail;

if (! defined('SMF'))
	die('No direct access...');

final class Mail
{
	public static function loadEmailTemplate(string $template, array $replacements = [], string $lang = '', bool $loadLang = true): array
	{
		require_once Config::$sourcedir . DIRECTORY_SEPARATOR . 'Subs-Post.php';

		return loadEmailTemplate($template, $replacements, $lang, $loadLang);
	}

	/**
	 * @throws ErrorException
	 */
	public static function send(array $to, string $subject, string $message, string $from = null, string $message_id = null, bool $send_html = false, int $priority = 3): void
	{
		require_once Config::$sourcedir . DIRECTORY_SEPARATOR . 'Subs-Post.php';

		sendmail($to, $subject, $message, $from, $message_id, $send_html, $priority);
	}
}
