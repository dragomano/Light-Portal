<?php

/**
 * TopPages.php
 *
 * @package TopPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 15.12.21
 */

namespace Bugo\LightPortal\Addons\TopPages;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class TopPages extends Plugin
{
	public string $icon = 'fas fa-balance-scale-left';

	public function blockOptions(array &$options)
	{
		$options['top_pages']['parameters'] = [
			'popularity_type'   => 'comments',
			'num_pages'         => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'top_pages')
			return;

		$parameters['popularity_type']   = FILTER_SANITIZE_STRING;
		$parameters['num_pages']         = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'top_pages')
			return;

		$context['posting_fields']['popularity_type']['label']['text'] = $txt['lp_top_pages']['type'];
		$context['posting_fields']['popularity_type']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'popularity_type'
			),
			'options' => array()
		);

		$types = array_combine(array('comments', 'views'), $txt['lp_top_pages']['type_set']);

		foreach ($types as $key => $value) {
			$context['posting_fields']['popularity_type']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['popularity_type']
			);
		}

		$context['posting_fields']['num_pages']['label']['text'] = $txt['lp_top_pages']['num_pages'];
		$context['posting_fields']['num_pages']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_pages',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_pages']
			)
		);

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_pages']['show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_numbers_only',
				'checked' => ! empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	public function getData(array $parameters): array
	{
		global $smcFunc, $scripturl;

		$titles = Helper::getAllTitles();

		$request = $smcFunc['db_query']('', '
			SELECT page_id, alias, type, num_views, num_comments
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})
			ORDER BY ' . ($parameters['popularity_type'] == 'comments' ? 'num_comments' : 'num_views') . ' DESC
			LIMIT {int:limit}',
			array(
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Helper::getPermissions(),
				'limit'        => $parameters['num_pages']
			)
		);

		$pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helper::isFrontpage($row['alias']))
				continue;

			$pages[$row['page_id']] = array(
				'title'        => $titles[$row['page_id']] ?? [],
				'num_comments' => $row['num_comments'],
				'num_views'    => $row['num_views'],
				'href'         => $scripturl . '?' . LP_PAGE_PARAM . '=' . $row['alias']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $pages;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $user_info, $txt;

		if ($type !== 'top_pages')
			return;

		$top_pages = Helper::cache('top_pages_addon_b' . $block_id . '_u' . $user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData', $parameters);

		if (! empty($top_pages)) {
			$max = $top_pages[array_key_first($top_pages)]['num_' . $parameters['popularity_type']];

			if (empty($max))
				echo $txt['lp_top_pages']['no_items'];
			else {
				echo '
		<dl class="stats">';

				foreach ($top_pages as $page) {
					if ($page['num_' . $parameters['popularity_type']] < 1 || empty($title = Helper::getTranslatedTitle($page['title'])))
						continue;

					$width = $page['num_' . $parameters['popularity_type']] * 100 / $max;

					echo '
			<dt>
				<a href="', $page['href'], '">', $title, '</a>
			</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($page['num_' . $parameters['popularity_type']]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $page['num_' . $parameters['popularity_type']] : Helper::getSmartContext('lp_' . $parameters['popularity_type'] . '_set', [$parameters['popularity_type'] => $page['num_' . $parameters['popularity_type']]])), '</span>
			</dd>';
				}

				echo '
		</dl>';
			}
		} else {
			echo $txt['lp_top_pages']['no_items'];
		}
	}
}
