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

namespace Bugo\LightPortal\DataHandlers;

use Bugo\LightPortal\DataHandlers\Traits\HasDataOperations;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;

if (! defined('SMF'))
	die('No direct access...');

abstract class DataHandler implements DataHandlerInterface
{
	use HasDataOperations;

	protected string $entity;

	public function __construct(protected DatabaseInterface $db, protected ErrorHandlerInterface $errorHandler) {}

	abstract public function main(): void;
}
