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

namespace LightPortal\Areas;

use Bugo\Bricks\Tables\Column;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Bricks\Tables\Row;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Enums\Tab;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Models\CategoryFactory;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\UI\Fields\TextareaField;
use LightPortal\UI\Tables\ContextMenuColumn;
use LightPortal\UI\Tables\IconColumn;
use LightPortal\UI\Tables\PortalTableBuilderInterface;
use LightPortal\UI\Tables\StatusColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Str;
use LightPortal\Validators\CategoryValidator;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryArea extends AbstractArea
{
	public function __construct(CategoryRepositoryInterface $repository, EventDispatcherInterface $dispatcher)
	{
		parent::__construct($repository, $dispatcher);
	}

	protected function getEntityName(): string
	{
		return 'category';
	}

	protected function getEntityNamePlural(): string
	{
		return 'categories';
	}

	protected function getCustomActionHandlers(): array
	{
		return [
			'update_priority' => fn($data) => $this->getRepository()
				->updatePriority($this->parseIds($data['update_priority'])),
		];
	}

	protected function getDefaultSortColumn(): string
	{
		return 'priority';
	}

	protected function getTableScript(): string
	{
		Theme::loadJavaScriptFile('light_portal/Sortable.min.js');

		return <<<JS
			const entity = new Category();
			new window['Sortable'](document.querySelector("#lp_categories tbody"), {
			    handle: ".handle",
			    animation: 150,
			    onSort: e => entity.updatePriority(e)
			});
		JS;
	}

	protected function buildTableWithBulkActions(): PortalTableBuilderInterface
	{
		return parent::buildTableWithBulkActions()
			->addRow(Row::make($this->getSortControls()));
	}

	private function getSortControls(): string
	{
		$url = Utils::$context['lp_actions_url'] ?? '';

		return '<div hidden data-signals="{ sort_ids: \'\' }">'
			. '<input id="lp_category_sort_ids" type="hidden" data-bind="sort_ids">'
			. '<button id="lp_category_sort_trigger" type="button"'
			. ' data-on:click="@post(\'' . $url . '\', {payload: {update_priority: $sort_ids}})"></button>'
			. '</div>';
	}

	protected function getTableColumns(): array
	{
		return [
			IdColumn::make()->setSort($this->getEntityName() . '_id'),
			IconColumn::make(),
			TitleColumn::make(entity: $this->getEntityNamePlural()),
			Column::make('priority', __('lp_block_priority'))
				->setStyle('width: 12%')
				->setData(static fn($entry) => Str::html('div')->data('id', $entry['id'])
					->setHtml($entry['priority'] . ' ' .
						Icon::get('sort', __('lp_action_move'), 'handle ')), 'centertext')
				->setSort('priority'),
			StatusColumn::make(),
			ContextMenuColumn::make()
		];
	}

	protected function getValidatorClass(): string
	{
		return CategoryValidator::class;
	}

	protected function getFactoryClass(): string
	{
		return CategoryFactory::class;
	}

	protected function prepareSpecificFields(): void
	{
		TextareaField::make('description', __('lp_category_description'))
			->setTab(Tab::SEO)
			->setAttribute('maxlength', 255)
			->setValue($this->getContextEntity()['description'] ?? '');
	}

	protected function finalizePreviewTitle(array $entity): void
	{
		Utils::$context['preview_title'] = $this->getPreviewTitle(
			Icon::parse($entity['icon'] ?? '')
		);
	}

	private function getRepository(): CategoryRepositoryInterface
	{
		$repository = $this->repository;

		assert($repository instanceof CategoryRepositoryInterface);

		return $repository;
	}
}
