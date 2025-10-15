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

namespace Bugo\LightPortal\DataHandlers\Imports;

use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FileInterface;

if (! defined('SMF'))
	die('No direct access...');

class BlockImport extends XmlImporter
{
	protected string $entity = 'blocks';

	public function __construct(
		PortalSqlInterface $sql,
		FileInterface $file,
		ErrorHandlerInterface $errorHandler
	)
	{
		parent::__construct($this->entity, $sql, $file, $errorHandler);
	}

	protected function processItems(): void
	{
		$items = $translations = $params = [];

		foreach ($this->xml->{$this->entity}->item as $item) {
			$items[] = [
				'block_id'      => $blockId = intval($item['block_id']),
				'icon'          => (string) $item->icon,
				'type'          => (string) $item->type,
				'placement'     => (string) $item->placement,
				'priority'      => intval($item['priority']),
				'permissions'   => intval($item['permissions']),
				'status'        => intval($item['status']),
				'areas'         => (string) $item->areas,
				'title_class'   => (string) $item->title_class,
				'content_class' => (string) $item->content_class,
			];

			$translations = array_merge($translations, $this->extractTranslations($item, $blockId));
			$params = array_merge($params, $this->extractParams($item, $blockId));
		}

		$this->startTransaction($items);

		$results = $this->insertData('lp_blocks', $items, ['block_id'], true);
		$results = $this->replaceTranslations($translations, $results);
		$results = $this->replaceParams($params, $results);

		$this->finishTransaction($results);
	}
}
