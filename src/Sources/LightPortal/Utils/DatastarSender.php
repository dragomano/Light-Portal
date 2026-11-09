<?php declare(strict_types=1);

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

use starfederation\datastar\enums\ElementPatchMode;
use starfederation\datastar\ServerSentEventGenerator;

if (! defined('SMF'))
	die('No direct access...');

final class DatastarSender
{
	private ServerSentEventGenerator $sse;

	private bool $headersSent = false;

	public function __construct(?ServerSentEventGenerator $sse = null)
	{
		$this->sse = $sse ?? new ServerSentEventGenerator();
	}

	public static function isDatastarRequest(): bool
	{
		return isset($_SERVER['HTTP_DATASTAR_REQUEST'])
			&& strtolower((string) $_SERVER['HTTP_DATASTAR_REQUEST']) === 'true';
	}

	public static function readSignals(): array
	{
		return ServerSentEventGenerator::readSignals();
	}

	public function sendHeaders(): void
	{
		if ($this->headersSent)
			return;

		if (PHP_SAPI !== 'cli') {
			while (ob_get_level() > 0) {
				ob_end_clean();
			}
		}

		$this->headersSent = true;
		$this->sse->sendHeaders();
	}

	public function patchElements(
		string $html,
		string $selector = '',
		ElementPatchMode|string $mode = ElementPatchMode::Outer
	): void {
		$this->sendHeaders();

		$options = [];

		if ($selector !== '') {
			$options['selector'] = $selector;
		}

		if ($mode !== '') {
			$options['mode'] = $mode;
		}

		$this->sse->patchElements($html, $options);
	}

	public function removeElements(string $selector): void
	{
		$this->sendHeaders();
		$this->sse->removeElements($selector);
	}

	public function patchSignals(array $signals): void
	{
		$this->sendHeaders();
		$this->sse->patchSignals($signals);
	}

	public function redirect(string $url): void
	{
		$this->sendHeaders();
		$this->sse->location($url);
	}

	public function executeScript(string $script): void
	{
		$this->sendHeaders();
		$this->sse->executeScript($script);
	}
}
