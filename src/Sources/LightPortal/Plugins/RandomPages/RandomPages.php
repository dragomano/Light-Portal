<?php declare(strict_types=1);

/**
 * @package RandomPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 26.10.25
 */

namespace LightPortal\Plugins\RandomPages;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Enums\Tab;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\UI\Fields\CheckboxField;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\NumberField;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\Utils\DateTime;
use LightPortal\Utils\ParamWrapper;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasTranslationJoins;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-random')]
class RandomPages extends Block
{
	use HasTranslationJoins;

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'exclude_categories' => '',
			'include_categories' => '',
			'num_pages'          => 10,
			'show_num_views'   => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'exclude_categories' => FILTER_DEFAULT,
			'include_categories' => FILTER_DEFAULT,
			'num_pages'          => FILTER_VALIDATE_INT,
			'show_num_views'     => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('exclude_categories', $this->txt['exclude_categories'])
			->setTab(Tab::CONTENT)
			->setValue(fn() => SelectFactory::category([
				'id'    => 'exclude_categories',
				'hint'  => $this->txt['exclude_categories_select'],
				'value' => $options['exclude_categories'] ?? '',
			]));

		CustomField::make('include_categories', $this->txt['include_categories'])
			->setTab(Tab::CONTENT)
			->setValue(fn() => SelectFactory::category([
				'id'    => 'include_categories',
				'hint'  => $this->txt['include_categories_select'],
				'value' => $options['include_categories'] ?? '',
			]));

		NumberField::make('num_pages', $this->txt['num_pages'])
			->setAttribute('min', 1)
			->setValue($options['num_pages']);

		CheckboxField::make('show_num_views', $this->txt['show_num_views'])
			->setValue($options['show_num_views']);
	}

	public function getData(ParamWrapper $parameters): array
	{
		$excludeCategories = empty($parameters['exclude_categories'])
			? []
			: array_map(intval(...), explode(',', (string) $parameters['exclude_categories']));

		$includeCategories = empty($parameters['include_categories'])
			? []
			: array_map(intval(...), explode(',', (string) $parameters['include_categories']));

		$pagesCount = Str::typed('int', $parameters['num_pages']);
		if (empty($pagesCount)) {
			return [];
		}

		$params = [
			'guest'          => Lang::$txt['guest_title'],
			'status'         => Status::ACTIVE->value,
			'entry_type'     => EntryType::DEFAULT->name(),
			'current_time'   => time(),
			'permissions'    => Permission::all(),
		];

		$selectPages = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['page_id'])
			->where([
				'p.status'          => $params['status'],
				'p.entry_type'      => $params['entry_type'],
				'p.deleted_at'      => 0,
				'p.created_at <= ?' => $params['current_time'],
				'p.permissions'     => $params['permissions'],
			])
			->order(new Expression('MD5(CONCAT(p.page_id, CURRENT_TIMESTAMP))'))
			->limit($pagesCount);

		if (! empty($excludeCategories)) {
			$selectPages->where->notIn('p.category_id', $excludeCategories);
		}

		if (! empty($includeCategories)) {
			$selectPages->where->in('p.category_id', $includeCategories);
		}

		$result = $this->sql->execute($selectPages);

		$pageIds = [];
		foreach ($result as $row) {
			$pageIds[] = $row['page_id'];
		}

		if (empty($pageIds)) {
			$parameters['num_pages'] = $pagesCount - 1;

			return $this->getData($parameters);
		}

		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns([
				'page_id',
				'slug',
				'created_at',
				'num_views',
				'author_id',
			])
			->join(
				['mem' => 'members'],
				'p.author_id = mem.id_member',
				['author_name' => new Expression('COALESCE(mem.real_name, ?)', [$params['guest']])],
				Select::JOIN_LEFT
			);

		$this->addTranslationJoins($select);

		$select->where(new Expression('COALESCE(NULLIF(t.title, ""), tf.title, "") <> ""'));

		$select->columns([
			'page_id',
			'slug',
			'created_at',
			'num_views',
			'author_id',
			'author_name' => new Expression('COALESCE(mem.real_name, ?)', [$params['guest']]),
		]);

		$select->where->in('p.page_id', $pageIds);

		$result = $this->sql->execute($select);

		$pages = [];
		foreach ($result as $row) {
			Lang::censorText($row['title']);

			$pages[] = [
				'page_id'     => $row['page_id'],
				'slug'        => $row['slug'],
				'created_at'  => $row['created_at'],
				'num_views'   => $row['num_views'],
				'author_id'   => $row['author_id'],
				'author_name' => $row['author_name'],
				'title'       => $row['title'],
			];
		}

		return $pages;
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$randomPages = $this->langCache($this->name . '_addon_b' . $e->args->id)
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData($parameters));

		if ($randomPages) {
			$ul = Str::html('ul', ['class' => $this->name . ' noup']);

			$i = 0;
			foreach ($randomPages as $page) {
				if (empty($title = $page['title']))
					continue;

				$li = Str::html('li', ['class' => 'generic_list_wrapper bg ' . ($i % 2 === 0 ? 'odd' : 'even')]);
				$link = Str::html('a', $title)->href(Config::$scripturl . '?' . LP_PAGE_PARAM . '=' . $page['slug']);
				$author = empty($page['author_id'])
					? $page['author_name']
					: Str::html('a', $page['author_name'])
						->href(Config::$scripturl . '?action=profile;u=' . $page['author_id']);

				$li
					->addHtml($link)
					->addText(' ' . Lang::$txt['by'] . ' ')
					->addHtml($author)
					->addHtml(', ' . DateTime::relative($page['created_at']));

				$parameters['show_num_views'] && $li
					->addText(' (' . Lang::getTxt('lp_views_set', ['views' => $page['num_views']]) . ')');

				$ul->addHtml($li);
				$i++;
			}

			echo $ul;
		} else {
			echo Str::html('div', ['class' => 'infobox'])
				->setText($this->txt['none']);
		}
	}
}
