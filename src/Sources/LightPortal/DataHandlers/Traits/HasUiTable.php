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

namespace LightPortal\DataHandlers\Traits;

use Bugo\Bricks\Tables\RowPosition;
use Bugo\Compat\Lang;
use LightPortal\UI\Tables\ExportButtonsRow;
use LightPortal\UI\Tables\PortalTableBuilder;
use LightPortal\Utils\Traits\HasTablePresenter;

if (! defined('SMF'))
	die('No direct access...');

trait HasUiTable
{
	use HasTablePresenter;

	protected function addUiTable(): void
	{
		$this->getTablePresenter()->show(
			PortalTableBuilder::make('lp_' . $this->entity, Lang::$txt['lp_' . $this->entity . '_export'])
				->setDefaultSortColumn('id')
				->setItems($this->repository->getAll(...))
				->setCount($this->repository->getTotalCount(...))
				->addColumns($this->defineUiColumns())
				->addRows([
					ExportButtonsRow::make()
						->setPosition(RowPosition::ABOVE_COLUMN_HEADERS),
					ExportButtonsRow::make()
				])
		);
	}

	abstract protected function defineUiColumns(): array;
}
