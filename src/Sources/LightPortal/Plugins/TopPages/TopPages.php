<?php

/**
 * @package TopPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 19.11.24
 */

namespace Bugo\LightPortal\Plugins\TopPages;

use Bugo\Compat\{Db, Lang, User};
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Plugins\{Block, Event};
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\Utils\{Setting, Str};

use function array_combine;
use function array_key_first;
use function time;

if (! defined('LP_NAME'))
	die('No direct access...');

class TopPages extends Block
{
	public string $icon = 'fas fa-balance-scale-left';

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

	public function getData(array $parameters): array
	{
		$titles = $this->getEntityData('title');

		$result = Db::$db->query('', '
			SELECT page_id, slug, type, num_views, num_comments
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}
				AND deleted_at = 0
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})
			ORDER BY ' . ($parameters['popularity_type'] === 'comments' ? 'num_comments' : 'num_views') . ' DESC
			LIMIT {int:limit}',
			[
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Permission::all(),
				'limit'        => $parameters['num_pages'],
			]
		);

		$pages = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			if (Setting::isFrontpage($row['slug']))
				continue;

			$pages[$row['page_id']] = [
				'title'        => $titles[$row['page_id']] ?? [],
				'num_comments' => $row['num_comments'],
				'num_views'    => $row['num_views'],
				'href'         => LP_PAGE_URL . $row['slug']
			];
		}

		Db::$db->free_result($result);

		return $pages;
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$parameters['show_numbers_only'] ??= false;

		$topPages = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if ($topPages) {
			$max = $topPages[array_key_first($topPages)]['num_' . $parameters['popularity_type']];

			if (empty($max)) {
				echo $this->txt['no_items'];
			} else {
				$dl = Str::html('dl', ['class' => 'stats']);

				foreach ($topPages as $page) {
					if ($page['num_' . $parameters['popularity_type']] < 1 || empty($title = Str::getTranslatedTitle($page['title'])))
						continue;

					$width = $page['num_' . $parameters['popularity_type']] * 100 / $max;

					$dt = Str::html('dt')
						->addHtml(Str::html('a', $title)->href($page['href']));

					$dd = Str::html('dd', ['class' => 'statsbar generic_bar righttext']);
					$barClass = empty($page['num_' . $parameters['popularity_type']]) ? 'bar empty' : 'bar';
					$barStyle = empty($page['num_' . $parameters['popularity_type']]) ? null : 'width: ' . $width . '%';

					$bar = Str::html('div', ['class' => $barClass, 'style' => $barStyle]);
					$dd->addHtml($bar);

					$countText = $parameters['show_numbers_only']
						? $page['num_' . $parameters['popularity_type']]
						: Lang::getTxt(
							'lp_' . $parameters['popularity_type'] . '_set',
							[$parameters['popularity_type'] => $page['num_' . $parameters['popularity_type']]]
						);

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
