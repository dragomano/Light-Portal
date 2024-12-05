<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\UI\Tables;

use Bugo\Bricks\Tables\TableBuilder;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Str;

use function implode;
use function str_replace;

class PortalTableBuilder extends TableBuilder implements PortalTableBuilderInterface
{
	protected function __construct(string $id, string $title)
	{
		parent::__construct($id, $title);

		$this->paginate(20);
		$this->setNoItemsLabel(Lang::$txt['lp_no_items']);
		$this->setFormAction(Utils::$context['form_action'] ?? Utils::$context['canonical_url'] ?? Config::$scripturl);
	}

	public function withCreateButton(string $entity, string $title = ''): static
	{
		$this->setTitle($title ?: Str::html('span', ['class' => 'floatright'])
			->addHtml(
				Str::html('a', [
					'href' => implode('', [
						Config::$scripturl . "?action=admin;area=lp_$entity;sa=add;",
						Utils::$context['session_var'] . '=' . Utils::$context['session_id']
					]),
					'x-data' => '',
				])
					->setHtml(str_replace(
						' class=',
						' @mouseover="entity.toggleSpin($event.target)" @mouseout="entity.toggleSpin($event.target)" class=',
						Icon::get('plus', Lang::$txt["lp_{$entity}_add"])
					))
			) . parent::getTitle()
		);

		return $this;
	}
}
