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

namespace LightPortal\Database;

use Laminas\Db\Adapter\Adapter;

if (! defined('SMF'))
	die('No direct access...');

class PortalAdapter extends Adapter implements PortalAdapterInterface
{
	public function __construct(protected array $config)
	{
		parent::__construct($this->config);
	}

	public function getConfig(): array
	{
		return $this->config;
	}

	public function getPrefix(): string
	{
		return $this->config['prefix'] ?? '';
	}

	public function getVersion(): string
	{
		if ($this->getTitle() === 'SQLite') {
			$result = $this->query('SELECT sqlite_version() AS version', self::QUERY_MODE_EXECUTE);
		} else {
			$result = $this->query('SELECT VERSION() AS version', self::QUERY_MODE_EXECUTE);
		}

		return $result->current()['version'];
	}

	public function getTitle(): string
	{
		return $this->getPlatform()->getName();
	}
}
