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

use Bugo\Bricks\Tables\IdColumn;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Models\TagFactory;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\UI\Tables\ContextMenuColumn;
use LightPortal\UI\Tables\IconColumn;
use LightPortal\UI\Tables\StatusColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Validators\TagValidator;

if (! defined('SMF'))
	die('No direct access...');

final class TagArea extends AbstractArea
{
	public function __construct(TagRepositoryInterface $repository, EventDispatcherInterface $dispatcher)
	{
		parent::__construct($repository, $dispatcher);
	}

	protected function getEntityName(): string
	{
		return 'tag';
	}

	protected function getEntityNamePlural(): string
	{
		return 'tags';
	}

	protected function getCustomActionHandlers(): array
	{
		return [];
	}

	protected function getTableColumns(): array
	{
		return [
			IdColumn::make()->setSort($this->getEntityName() . '_id'),
			IconColumn::make(),
			TitleColumn::make(entity: $this->getEntityNamePlural()),
			StatusColumn::make(),
			ContextMenuColumn::make(),
		];
	}

	protected function getValidatorClass(): string
	{
		return TagValidator::class;
	}

	protected function getFactoryClass(): string
	{
		return TagFactory::class;
	}
}
