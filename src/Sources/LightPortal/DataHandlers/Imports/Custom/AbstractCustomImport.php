<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 3.0
 */

namespace Bugo\LightPortal\DataHandlers\Imports\Custom;

use Bugo\Bricks\Tables\TablePresenter;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Sapi;
use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\DataHandler;
use Bugo\LightPortal\DataHandlers\Traits\HasDataOperations;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\UI\Tables\ImportButtonsRow;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\Utils\Traits\HasRequest;

use function app;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomImport extends DataHandler implements CustomImportInterface
{
	use HasDataOperations;
	use HasRequest;

	protected string $type = 'custom';

	protected string $langKey;

	protected string $formAction;

	protected string $uiTableId;

	protected string $sortColumn = 'title';

	abstract public function getAll(int $start, int $limit, string $sort): array;

	abstract public function getTotalCount(): int;

	public function main(): void
	{
		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt[$this->langKey]['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_' . $this->entity . '_import'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_' . $this->entity . ';sa=' . $this->formAction;

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt[$this->langKey][$this->type . '_import_desc'],
		];

		$this->run();

		app(TablePresenter::class)->show(
			PortalTableBuilder::make($this->uiTableId, Lang::$txt['lp_' . $this->entity . '_import'])
				->withParams(50, defaultSortColumn: $this->sortColumn)
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
				->addColumns($this->defineUiColumns())
				->addRow(ImportButtonsRow::make())
		);
	}

	abstract protected function defineUiColumns(): array;

	abstract protected function getItems(array $ids): array;

	abstract protected function getResults(array $items): array;

	protected function run(): void
	{
		if ($this->request()->isEmpty($this->entity) && $this->request()->hasNot('import_all'))
			return;

		Sapi::setTimeLimit();

		$data = $this->request()->has($this->entity) && $this->request()->hasNot('import_all')
			? $this->request()->get($this->entity)
			: [];

		$items = $this->getItems($data);

		$this->startTransaction($items);

		$results = $this->importItems($items);

		$this->finishTransaction($results);
	}

	protected function importItems(array $items): array
	{
		$translations = [];
		foreach ($items as $id => $item) {
			$translations[] = [
				'type'        => $this->type,
				'lang'        => Config::$language,
				'title'       => $item['title'] ?? '',
				'content'     => $item['content'] ?? '',
				'description' => $item['description'] ?? '',
			];

			unset($items[$id]['title'], $items[$id]['content'], $items[$id]['description']);
		}

		$results = $this->getResults($items);

		if ($translations && $results) {
			foreach ($results as $key => $value) {
				$translations[$key]['item_id'] = $value;
			}

			$results = $this->replaceTranslations($translations, $results, '');
		}

		return $results;
	}

	protected function getPermission(array $row): int
	{
		$permissions = $this->extractPermissions($row);

		if (is_int($permissions)) {
			return $permissions;
		}

		return match (true) {
			count($permissions) == 1 && $permissions[0] == -1 => Permission::GUEST->value,
			count($permissions) == 1 && $permissions[0] == 0 => Permission::MEMBER->value,
			in_array(-1, $permissions, true),
			in_array(0, $permissions, true) => Permission::ALL->value,
			default => Permission::ADMIN->value,
		};
	}
}
