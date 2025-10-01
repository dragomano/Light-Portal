<?php declare(strict_types=1);

/**
 * @package TopPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 01.10.25
 */

namespace Bugo\LightPortal\Plugins\TopPages;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\Utils\ParamWrapper;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-balance-scale-left')]
class TopPages extends Block
{
	#[HookAttribute(PortalHook::prepareBlockParams)]
	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'popularity_type'   => 'comments',
			'num_pages'         => 10,
			'show_numbers_only' => false,
		];
	}

	#[HookAttribute(PortalHook::validateBlockParams)]
	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'popularity_type'   => FILTER_DEFAULT,
			'num_pages'         => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	#[HookAttribute(PortalHook::prepareBlockFields)]
	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		RadioField::make('popularity_type', $this->txt['type'])
			->setOptions(array_combine(['comments', 'views'], $this->txt['type_set']))
			->setValue($options['popularity_type']);

		NumberField::make('num_pages', $this->txt['num_pages'])
			->setAttribute('min', 1)
			->setValue($options['num_pages']);

		CheckboxField::make('show_numbers_only', $this->txt['show_numbers_only'])
			->setValue($options['show_numbers_only']);
	}

	public function getData(ParamWrapper $parameters): array
	{
		$type = Str::typed('string', $parameters['popularity_type'], default: 'comments');
		$numPages = Str::typed('int', $parameters['num_pages'], default: 10);

		$result = Db::$db->query('
			SELECT p.page_id, p.slug, p.type, p.num_views, p.num_comments,
				(
					SELECT title
					FROM {db_prefix}lp_translations
					WHERE item_id = p.page_id
						AND type = {literal:page}
						AND lang IN ({string:lang}, {string:fallback_lang})
					ORDER BY lang = {string:lang} DESC
					LIMIT 1
				) AS page_title
			FROM {db_prefix}lp_pages AS p
			WHERE p.status = {int:status}
				AND p.deleted_at = 0
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY ' . ($type === 'comments' ? 'p.num_comments' : 'p.num_views') . ' DESC
			LIMIT {int:limit}',
			[
				'lang'          => User::$me->language,
				'fallback_lang' => Config::$language,
				'status'        => Status::ACTIVE->value,
				'current_time'  => time(),
				'permissions'   => Permission::all(),
				'limit'         => $numPages,
			]
		);

		$pages = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			if (Setting::isFrontpage($row['slug']))
				continue;

			Lang::censorText($row['page_title']);

			$pages[$row['page_id']] = [
				'num_comments' => $row['num_comments'],
				'num_views'    => $row['num_views'],
				'href'         => LP_PAGE_URL . $row['slug'],
				'title'        => $row['page_title'],
			];
		}

		Db::$db->free_result($result);

		return $pages;
	}

	#[HookAttribute(PortalHook::prepareContent)]
	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$topPages = $this->langCache($this->name . '_addon_b' . $e->args->id)
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData($parameters));

		if ($topPages) {
			$type = Str::typed('string',$parameters['popularity_type'], default: 'comments');
			$max = $topPages[array_key_first($topPages)]['num_' . $type];

			if (empty($max)) {
				echo $this->txt['no_items'];
			} else {
				$dl = Str::html('dl', ['class' => 'stats']);

				foreach ($topPages as $page) {
					if ($page['num_' . $type] < 1 || empty($title = $page['title']))
						continue;

					$width = $page['num_' . $type] * 100 / $max;

					$dt = Str::html('dt')
						->addHtml(Str::html('a', $title)->href($page['href']));

					$dd = Str::html('dd', ['class' => 'statsbar generic_bar righttext']);
					$barClass = empty($page['num_' . $type]) ? 'bar empty' : 'bar';
					$barStyle = empty($page['num_' . $type]) ? null : 'width: ' . $width . '%';

					$bar = Str::html('div', ['class' => $barClass, 'style' => $barStyle]);
					$dd->addHtml($bar);

					$countText = $parameters['show_numbers_only']
						? $page['num_' . $type]
						: Lang::getTxt('lp_' . $type . '_set', [$type => $page['num_' . $type]]);

					$dd->addHtml(Str::html('span', $countText));

					$dl->addHtml($dt);
					$dl->addHtml($dd);
				}

				echo $dl;
			}
		} else {
			echo $this->txt['no_items'];
		}
	}
}
