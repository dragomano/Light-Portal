<?php

/**
 * TopPosters
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\TopPosters;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class TopPosters extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-users';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['top_posters']['parameters'] = [
			'show_avatars'      => true,
			'num_posters'       => 10,
			'show_numbers_only' => false,
		];
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'top_posters')
			return;

		$parameters['show_avatars']      = FILTER_VALIDATE_BOOLEAN;
		$parameters['num_posters']       = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
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
				'checked' => !empty($context['lp_block']['options']['parameters']['show_avatars'])
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
				'checked' => !empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	/**
	 * Get the list of top members
	 *
	 * Получаем список лучших пользователей
	 *
	 * @param array $parameters
	 * @return array
	 * @throws \Exception
	 */
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
			if (!isset($memberContext[$row['id_member']]))
				loadMemberContext($row['id_member']);

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

	/**
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $user_info, $txt;

		if ($type !== 'top_posters')
			return;

		$top_posters = Helpers::cache('top_posters_addon_b' . $block_id . '_u' . $user_info['id'])
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

			if (!empty($poster['avatar']))
				echo $poster['avatar'];

			$width = $poster['posts'] * 100 / $max;

			echo ' ', $poster['link'], '
			</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($poster['posts']) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $poster['posts'] : Helpers::getText($poster['posts'], $txt['lp_posts_set'])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
