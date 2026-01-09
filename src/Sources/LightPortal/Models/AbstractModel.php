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

namespace LightPortal\Models;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractModel implements ModelInterface
{
	protected array $fields = [];

	protected array $extraFields = [];

	protected array $aliases = [];

	private array $data = [];

	public function __construct(array $data = [])
	{
		$this->hydrate($data);
	}

	protected function hydrate(array $data): void
	{
		foreach ($this->aliases as $alias => $property) {
			if (isset($data[$alias])) {
				$data[$property] = $data[$alias];
				unset($data[$alias]);
			}
		}

		$allowedKeys  = array_merge(array_keys($this->fields), array_keys($this->extraFields));
		$filteredData = array_intersect_key($data, array_flip($allowedKeys));

		$this->data = array_merge($this->fields, $this->extraFields, $filteredData);
	}

	public function toArray(): array
	{
		return $this->data;
	}
}
