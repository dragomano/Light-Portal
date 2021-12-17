<?php

/**
 * TopPosters.php
 *
 * @package TopPosters (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 15.12.21
 */

namespace Bugo\LightPortal\Addons\TopPosters;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class TopPosters extends Plugin
{
	public string $icon = 'fas fa-users';

	public function blockOptions(array &$options)
	{
		$options['top_posters']['parameters'] = [
			'show_avatars'      => true,
			'num_posters'       => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'top_posters')
			return;

		$parameters['show_avatars']      = FILTER_VALIDATE_BOOLEAN;
		$parameters['num_posters']       = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'top_posters')
			return;

		$context['posting_fields']['show_avatars']['label']['text'] = $txt['lp_top_posters']['show_avatars'];
		$context['posting_fields']['show_avatars']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_avatars',
				'checked' => ! empty($context['lp_block']['options']['parameters']['show_avatars'])
			)
		);

		$context['posting_fields']['num_posters']['label']['text'] = $txt['lp_top_posters']['num_posters'];
		$context['posting_fields']['num_posters']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_posters',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_posters']
			)
		);

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_posters']['show_numbers_only'];
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
		global $smcFunc, $memberContext, $scripturl;

		$request = $smcFunc['db_query']('', '
			SELECT id_member, real_name, posts
			FROM {db_prefix}members
			WHERE posts > {int:num_posts}
			ORDER BY posts DESC
			LIMIT {int:num_posters}',
			array(
				'num_posts'   => 0,
				'num_posters' => $parameters['num_posters']
			)
		);

		$result = $smcFunc['db_fetch_all']($request);

		if (empty($result))
			return [];

		loadMemberData(array_column($result, 'id_member'));

		$posters = [];
		foreach ($result as $row) {
			if (! isset($memberContext[$row['id_member']]))
				try {
					loadMemberContext($row['id_member']);
				} catch (\Exception $e) {
					log_error('[LP] TopPosters addon: ' . $e->getMessage(), 'user');
				}

			$posters[] = array(
				'name'   => $row['real_name'],
				'link'   => allowedTo('profile_view') ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>' : $row['real_name'],
				'avatar' => $parameters['show_avatars'] ? $memberContext[$row['id_member']]['avatar']['image'] : null,
				'posts'  => $row['posts']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $posters;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $user_info, $txt;

		if ($type !== 'top_posters')
			return;

		$top_posters = Helper::cache('top_posters_addon_b' . $block_id . '_u' . $user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData', $parameters);

		if (empty($top_posters))
			return;

		echo '
		<dl class="top_posters stats">';

		$max = $top_posters[0]['posts'];

		foreach ($top_posters as $poster) {
			echo '
			<dt>';

			if (! empty($poster['avatar']))
				echo $poster['avatar'];

			$width = $poster['posts'] * 100 / $max;

			echo ' ', $poster['link'], '
			</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($poster['posts']) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $poster['posts'] : Helper::getPluralText($poster['posts'], $txt['lp_posts_set'])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
