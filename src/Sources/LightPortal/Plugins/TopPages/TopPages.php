<?php declare(strict_types=1);

/**
 * @package TopPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\TopPages;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\UI\Fields\CheckboxField;
use LightPortal\UI\Fields\NumberField;
use LightPortal\UI\Fields\RadioField;
use LightPortal\Utils\ParamWrapper;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use Laminas\Db\Sql\Predicate\Expression;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-balance-scale-left')]
class TopPages extends Block
{
	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'popularity_type'   => 'comments',
			'num_pages'         => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'popularity_type'   => FILTER_DEFAULT,
			'num_pages'         => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN,
		];
	}

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

		$subQuery = $this->sql->select()
			->from('lp_translations')
			->columns(['title'])
			->where([
				'item_id = p.page_id',
				'type = ?' => 'page',
				new Expression('lang IN (?, ?)', [User::$me->language, Config::$language]),
			])
			->order(new Expression('lang = ? DESC', [User::$me->language]))
			->limit(1);

		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns([
				'page_id', 'slug', 'type', 'num_views', 'num_comments',
				'page_title' => $subQuery,
			])
			->where([
				'status'          => Status::ACTIVE->value,
				'deleted_at'      => 0,
				'created_at <= ?' => time(),
				'permissions'     => Permission::all(),
			])
			->order('p.num_' . $type . ' DESC')
			->limit($numPages);

		$result = $this->sql->execute($select);

		$pages = [];
		foreach ($result as $row) {
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

		return $pages;
	}

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

					$dd->addHtml(Str::html('span', (string) $countText));

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
