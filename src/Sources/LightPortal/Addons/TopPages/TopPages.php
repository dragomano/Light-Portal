<?php

/**
 * @package TopPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 10.10.24
 */

namespace Bugo\LightPortal\Addons\TopPages;

use Bugo\Compat\{Lang, User, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, NumberField, RadioField};
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Utils\{Setting, Str};

use function array_combine;
use function array_key_first;
use function time;

if (! defined('LP_NAME'))
	die('No direct access...');

class TopPages extends Block
{
	public string $icon = 'fas fa-balance-scale-left';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_pages')
			return;

		$params = [
			'popularity_type'   => 'comments',
			'num_pages'         => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_pages')
			return;

		$params = [
			'popularity_type'   => FILTER_DEFAULT,
			'num_pages'         => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_pages')
			return;

		RadioField::make('popularity_type', Lang::$txt['lp_top_pages']['type'])
			->setOptions(array_combine(['comments', 'views'], Lang::$txt['lp_top_pages']['type_set']))
			->setValue(Utils::$context['lp_block']['options']['popularity_type']);

		NumberField::make('num_pages', Lang::$txt['lp_top_pages']['num_pages'])
			->setAttribute('min', 1)
			->setValue(Utils::$context['lp_block']['options']['num_pages']);

		CheckboxField::make('show_numbers_only', Lang::$txt['lp_top_pages']['show_numbers_only'])
			->setValue(Utils::$context['lp_block']['options']['show_numbers_only']);
	}

	public function getData(array $parameters): array
	{
		$titles = $this->getEntityData('title');

		$result = Utils::$smcFunc['db_query']('', '
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
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			if (Setting::isFrontpage($row['slug']))
				continue;

			$pages[$row['page_id']] = [
				'title'        => $titles[$row['page_id']] ?? [],
				'num_comments' => $row['num_comments'],
				'num_views'    => $row['num_views'],
				'href'         => LP_PAGE_URL . $row['slug']
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $pages;
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'top_pages')
			return;

		$parameters['show_numbers_only'] ??= false;

		$topPages = $this->cache('top_pages_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if ($topPages) {
			$max = $topPages[array_key_first($topPages)]['num_' . $parameters['popularity_type']];

			if (empty($max))
				echo Lang::$txt['lp_top_pages']['no_items'];
			else {
				echo '
		<dl class="stats">';

				foreach ($topPages as $page) {
					if ($page['num_' . $parameters['popularity_type']] < 1 || empty($title = Str::getTranslatedTitle($page['title'])))
						continue;

					$width = $page['num_' . $parameters['popularity_type']] * 100 / $max;

					echo '
			<dt>
				<a href="', $page['href'], '">', $title, '</a>
			</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($page['num_' . $parameters['popularity_type']]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $page['num_' . $parameters['popularity_type']] : Lang::getTxt('lp_' . $parameters['popularity_type'] . '_set', [$parameters['popularity_type'] => $page['num_' . $parameters['popularity_type']]])), '</span>
			</dd>';
				}

				echo '
		</dl>';
			}
		} else {
			echo Lang::$txt['lp_top_pages']['no_items'];
		}
	}
}
