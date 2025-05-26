<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Exports;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Sapi;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Repositories\BlockRepository;
use DomDocument;
use DOMException;

use function array_filter;
use function array_map;
use function in_array;
use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class BlockExport extends AbstractExport
{
	protected string $entity = 'blocks';

	public function __construct(private readonly BlockRepository $repository) {}

	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_export_blocks';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_blocks_export'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_blocks_export'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_blocks;sa=export';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_blocks_export_description'],
		];

		$this->run();

		Utils::$context['lp_current_blocks'] = $this->repository->getAll();
	}

	protected function getData(): array
	{
		if ($this->isEntityEmpty())
			return [];

		$blocks = $this->hasEntityInRequest() ? $this->request()->get($this->entity) : [];

		$result = Db::$db->query('', '
			SELECT
				b.*, pt.lang, pp.name, pp.value,
				COALESCE(pt.title, {string:empty_string}) AS title,
				COALESCE(pt.content, {string:empty_string}) AS content,
				COALESCE(pt.description, {string:empty_string}) AS description
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_translations AS pt ON (
					b.block_id = pt.item_id AND pt.type = {literal:block}
				)
				LEFT JOIN {db_prefix}lp_params AS pp ON (
					b.block_id = pp.item_id AND pp.type = {literal:block}
				)
			WHERE ((title IS NOT NULL AND title != {string:empty_string})
				OR (content IS NOT NULL AND content != {string:empty_string})
				OR (description IS NOT NULL AND description != {string:empty_string}))' . (empty($blocks) ? '' : '
				AND b.block_id IN ({array_int:blocks})'),
			[
				'empty_string' => '',
				'blocks'       => $blocks,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['block_id']] ??= [
				'block_id'      => $row['block_id'],
				'icon'          => $row['icon'],
				'type'          => $row['type'],
				'placement'     => $row['placement'],
				'priority'      => $row['priority'],
				'permissions'   => $row['permissions'],
				'status'        => $row['status'],
				'areas'         => $row['areas'],
				'title_class'   => $row['title_class'],
				'content_class' => $row['content_class'],
			];

			if ($row['lang'] && $row['title']) {
				$items[$row['block_id']]['titles'][$row['lang']] = trim($row['title']);
			}

			if ($row['lang'] && $row['content']) {
				$items[$row['block_id']]['contents'][$row['lang']] = $row['content'];
			}

			if ($row['lang'] && $row['description']) {
				$items[$row['block_id']]['descriptions'][$row['lang']] = trim($row['description']);
			}

			if ($row['name'] && $row['value']) {
				$items[$row['block_id']]['params'][$row['name']] = $row['value'];
			}
		}

		Db::$db->free_result($result);

		return array_map(static fn($item) => array_filter($item), $items);
	}

	protected function getFile(): string
	{
		if (empty($items = $this->getData()))
			return '';

		try {
			$xml = new DomDocument('1.0', 'utf-8');
			$root = $xml->appendChild($xml->createElement('light_portal'));

			$xml->formatOutput = true;

			$xmlElements = $root->appendChild($xml->createElement($this->entity));

			$items = $this->getGeneratorFrom($items);

			foreach ($items() as $item) {
				$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
				foreach ($item as $key => $val) {
					$xmlName = $xmlElement->appendChild(
						in_array($key, ['block_id', 'priority', 'permissions', 'status'])
							? $xml->createAttribute($key)
							: $xml->createElement($key)
					);

					if (in_array($key, ['titles', 'params'])) {
						foreach ($val as $k => $v) {
							$xmlTitle = $xmlName->appendChild($xml->createElement($k));
							$xmlTitle->appendChild($xml->createTextNode($v));
						}
					} elseif (in_array($key, ['contents', 'descriptions'])) {
						foreach ($val as $k => $v) {
							$xmlContent = $xmlName->appendChild($xml->createElement($k));
							$xmlContent->appendChild($xml->createCDATASection($v));
						}
					} else {
						$xmlName->appendChild($xml->createTextNode($val));
					}
				}
			}

			$file = Sapi::getTempDir() . '/lp_blocks_backup.xml';
			$xml->save($file);
		} catch (DOMException $e) {
			ErrorHandler::log('[LP] ' . Lang::$txt['lp_blocks_export'] . ': ' . $e->getMessage(), 'user');
		}

		return $file ?? '';
	}
}
