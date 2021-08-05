<?php

/**
 * RecentTopics
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\RecentTopics;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class RecentTopics extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-book-open';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['recent_topics']['no_content_class'] =  true;

		$options['recent_topics']['parameters'] = [
			'use_simple_style' => false,
			'num_topics'       => 10,
			'exclude_boards'   => '',
			'include_boards'   => '',
			'show_avatars'     => false,
			'update_interval'  => 600,
		];
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'recent_topics')
			return;

		$parameters['use_simple_style'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['num_topics']       = FILTER_VALIDATE_INT;
		$parameters['exclude_boards']   = FILTER_SANITIZE_STRING;
		$parameters['include_boards']   = FILTER_SANITIZE_STRING;
		$parameters['show_avatars']     = FILTER_VALIDATE_BOOLEAN;
		$parameters['update_interval']  = FILTER_VALIDATE_INT;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'recent_topics')
			return;

		$context['posting_fields']['use_simple_style']['label']['text'] = $txt['lp_recent_topics']['use_simple_style'];
		$context['posting_fields']['use_simple_style']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'use_simple_style',
				'checked' => !empty($context['lp_block']['options']['parameters']['use_simple_style'])
			),
			'tab' => 'appearance'
		);

		$context['posting_fields']['num_topics']['label']['text'] = $txt['lp_recent_topics']['num_topics'];
		$context['posting_fields']['num_topics']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_topics',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_topics']
			)
		);

		$context['posting_fields']['exclude_boards']['label']['text'] = $txt['lp_recent_topics']['exclude_boards'];
		$context['posting_fields']['exclude_boards']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_topics']['exclude_boards_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['exclude_boards'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['include_boards']['label']['text'] = $txt['lp_recent_topics']['include_boards'];
		$context['posting_fields']['include_boards']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_topics']['include_boards_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['include_boards'] ?? '',
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['show_avatars']['label']['text'] = $txt['lp_recent_topics']['show_avatars'];
		$context['posting_fields']['show_avatars']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_avatars',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_avatars']) && empty($context['lp_block']['options']['parameters']['use_simple_style'])
			)
		);

		$context['posting_fields']['update_interval']['label']['text'] = $txt['lp_recent_topics']['update_interval'];
		$context['posting_fields']['update_interval']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'update_interval',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['update_interval']
			)
		);
	}

	/**
	 * Get the recent topics of the forum
	 *
	 * Получаем последние темы форума
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function getData($parameters)
	{
		global $boarddir;

		if (!empty($parameters['exclude_boards']))
			$exclude_boards = explode(',', $parameters['exclude_boards']);

		if (!empty($parameters['include_boards']))
			$include_boards = explode(',', $parameters['include_boards']);

		require_once($boarddir . '/SSI.php');
		$topics = ssi_recentTopics($parameters['num_topics'], $exclude_boards ?? null, $include_boards ?? null, 'array');

		if (empty($topics))
			return [];

		if (!empty($parameters['show_avatars']) && empty($parameters['use_simple_style'])) {
			$posters = array_map(function ($item) {
				return $item['poster']['id'];
			}, $topics);

			loadMemberData(array_unique($posters));

			$topics = array_map(function ($item) {
				global $memberContext, $modSettings;

				if (!empty($item['poster']['id'])) {
					if (!isset($memberContext[$item['poster']['id']]))
						loadMemberContext($item['poster']['id']);

					$item['poster']['avatar'] = $memberContext[$item['poster']['id']]['avatar']['image'];
				} else {
					$item['poster']['avatar'] = '<img class="avatar" src="' . $modSettings['avatar_url'] . '/default.png" loading="lazy" alt="'. $item['poster']['name'] . '">';
				}

				return $item;
			}, $topics);
		}

		return $topics;
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $user_info, $scripturl, $txt, $context;

		if ($type !== 'recent_topics')
			return;

		$recent_topics = Helpers::cache(
			'recent_topics_addon_b' . $block_id . '_u' . $user_info['id'],
			'getData',
			__CLASS__,
			$parameters['update_interval'] ?? $cache_time,
			$parameters
		);

		if (!empty($recent_topics)) {
			ob_start();

			echo '
		<ul class="recent_topics noup">';

			if (!empty($parameters['use_simple_style'])) {
				foreach ($recent_topics as $topic) {
					echo '
			<li class="windowbg">
				<div class="smalltext">', $topic['time'], '</div>';

					echo $topic['link'];

					echo '
				<div class="smalltext', $context['right_to_left'] ? ' floatright' : '', '">
					<i class="fas fa-eye"></i> ', $topic['views'], '&nbsp;
					<i class="fas fa-comment"></i> ', $topic['replies'], '
				</div>
			</li>';
				}
			} else {
				foreach ($recent_topics as $topic) {
					echo '
			<li class="windowbg">';

					if (!empty($parameters['show_avatars']))
						echo '
				<span class="poster_avatar" title="', $topic['poster']['name'], '">', $topic['poster']['avatar'], '</span>';

					if ($topic['is_new'])
						echo '
				<a class="new_posts" href="', $scripturl, '?topic=', $topic['topic'], '.msg', $topic['new_from'], ';topicseen#new">', $txt['new'], '</a> ';

					echo $topic['icon'], ' ', $topic['link'];

					if (empty($parameters['show_avatars']))
						echo '
				<br><span class="smalltext">', $txt['by'], ' ', $topic['poster']['link'], '</span>';

					echo '
				<br><span class="smalltext">', Helpers::getFriendlyTime($topic['timestamp'], true), '</span>
			</li>';
				}
			}


			echo '
		</ul>';

			$content = ob_get_clean();
		}
	}
}
