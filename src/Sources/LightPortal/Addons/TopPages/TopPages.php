<?php

/**
 * TopPages.php
 *
 * @package TopPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\TopPages;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, NumberField, RadioField};

if (! defined('LP_NAME'))
	die('No direct access...');

class TopPages extends Block
{
	public string $icon = 'fas fa-balance-scale-left';

	public function blockOptions(array &$options): void
	{
		$options['top_pages']['parameters'] = [
			'popularity_type'   => 'comments',
			'num_pages'         => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'top_pages')
			return;

		$parameters['popularity_type']   = FILTER_DEFAULT;
		$parameters['num_pages']         = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'top_pages')
			return;

		RadioField::make('popularity_type', $this->txt['lp_top_pages']['type'])
			->setOptions(array_combine(['comments', 'views'], $this->txt['lp_top_pages']['type_set']))
			->setValue($this->context['lp_block']['options']['parameters']['popularity_type']);

		NumberField::make('num_pages', $this->txt['lp_top_pages']['num_pages'])
			->setAttribute('min', 1)
			->setValue($this->context['lp_block']['options']['parameters']['num_pages']);

		CheckboxField::make('show_numbers_only', $this->txt['lp_top_pages']['show_numbers_only'])
			->setValue($this->context['lp_block']['options']['parameters']['show_numbers_only']);
	}

	public function getData(array $parameters): array
	{
		$titles = $this->getEntityList('title');

		$result = $this->smcFunc['db_query']('', '
			SELECT page_id, alias, type, num_views, num_comments
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})
			ORDER BY ' . ($parameters['popularity_type'] === 'comments' ? 'num_comments' : 'num_views') . ' DESC
			LIMIT {int:limit}',
			[
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'limit'        => $parameters['num_pages']
			]
		);

		$pages = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
			if ($this->isFrontpage($row['alias']))
				continue;

			$pages[$row['page_id']] = [
				'title'        => $titles[$row['page_id']] ?? [],
				'num_comments' => $row['num_comments'],
				'num_views'    => $row['num_views'],
				'href'         => LP_PAGE_URL . $row['alias']
			];
		}

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $pages;
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'top_pages')
			return;

		$parameters['show_numbers_only'] ??= false;

		$top_pages = $this->cache('top_pages_addon_b' . $data->block_id . '_u' . $this->user_info['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if ($top_pages) {
			$max = $top_pages[array_key_first($top_pages)]['num_' . $parameters['popularity_type']];

			if (empty($max))
				echo $this->txt['lp_top_pages']['no_items'];
			else {
				echo '
		<dl class="stats">';

				foreach ($top_pages as $page) {
					if ($page['num_' . $parameters['popularity_type']] < 1 || empty($title = $this->getTranslatedTitle($page['title'])))
						continue;

					$width = $page['num_' . $parameters['popularity_type']] * 100 / $max;

					echo '
			<dt>
				<a href="', $page['href'], '">', $title, '</a>
			</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($page['num_' . $parameters['popularity_type']]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $page['num_' . $parameters['popularity_type']] : $this->translate('lp_' . $parameters['popularity_type'] . '_set', [$parameters['popularity_type'] => $page['num_' . $parameters['popularity_type']]])), '</span>
			</dd>';
				}

				echo '
		</dl>';
			}
		} else {
			echo $this->txt['lp_top_pages']['no_items'];
		}
	}
}
