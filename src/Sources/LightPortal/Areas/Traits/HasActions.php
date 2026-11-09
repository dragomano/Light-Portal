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

namespace LightPortal\Areas\Traits;

use Bugo\Compat\User;
use LightPortal\Utils\DatastarSender;

if (! defined('SMF'))
	die('No direct access...');

trait HasActions
{
	/**
	 * The common entry point for the list actions: POST requests with the "actions" param
	 * are processed as Datastar signals, or as a JSON payload from non-Datastar clients
	 */
	protected function performActions(): void
	{
		if ($this->request()->hasNot('actions'))
			return;

		if ($this->request()->method() !== 'POST')
			return;

		User::$me->checkSession('request');

		$isDatastarRequest = DatastarSender::isDatastarRequest();

		if ($isDatastarRequest) {
			(new DatastarSender())->sendHeaders();
		}

		$data = $isDatastarRequest
			? DatastarSender::readSignals()
			: $this->request()->json();

		$this->processActions($data);
		$this->clearCache();

		if ($isDatastarRequest) {
			exit;
		}

		$this->response()->exit([
			'success' => true,
			'count'   => $this->getFreshQuantity(),
		]);
	}

	protected function processActions(array $data): void
	{
		foreach ($this->getActionHandlers() as $key => $handler) {
			if (isset($data[$key])) {
				$handler($data);
				break;
			}
		}
	}

	abstract protected function getActionHandlers(): array;

	abstract protected function clearCache(): void;

	abstract protected function getFreshQuantity(): ?int;
}
