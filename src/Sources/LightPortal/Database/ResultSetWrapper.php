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

namespace LightPortal\Database;

use ArrayObject;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;

if (! defined('SMF'))
	die('No direct access...');

class ResultSetWrapper implements ResultInterface
{
	private int $position = 0;

	public function __construct(private readonly ResultSet $resultSet) {}

	public function getGeneratedValue(string $name = 'id'): null
	{
		return null;
	}

	public function count(): int
	{
		return $this->resultSet->count();
	}

	public function current(): array|ArrayObject|null
	{
		return $this->resultSet->current();
	}

	public function next(): void
	{
		$this->resultSet->next();
		$this->position++;
	}

	public function key(): int
	{
		return $this->position;
	}

	public function valid(): bool
	{
		return $this->resultSet->valid();
	}

	public function rewind(): void
	{
		$this->resultSet->rewind();
		$this->position = 0;
	}

	public function getAffectedRows(): int
	{
		return $this->resultSet->count();
	}

	public function getResource(): ResultSet
	{
		return $this->resultSet;
	}

	public function buffer(): ResultSet
	{
		return $this->resultSet->buffer();
	}

	public function isBuffered(): ?bool
	{
		return $this->resultSet->isBuffered();
	}

	public function isQueryResult(): bool
	{
		return true;
	}

	public function getFieldCount(): int
	{
		return $this->resultSet->getFieldCount();
	}
}
