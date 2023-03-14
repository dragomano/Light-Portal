<?php

/**
 * BoardNews.php
 *
 * @package BoardNews (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 23.06.22
 */

namespace Bugo\LightPortal\Addons\BoardNews;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardNews extends Plugin
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-newspaper';

	public function blockOptions(array &$options)
	{
		$options['board_news']['parameters'] = [
			'board_id'      => 0,
			'num_posts'     => 5,
			'teaser_length' => 255,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'board_news')
			return;

		$parameters['board_id']      = FILTER_VALIDATE_INT;
		$parameters['num_posts']     = FILTER_VALIDATE_INT;
		$parameters['teaser_length'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'board_news')
			return;

		$this->context['posting_fields']['board_id']['label']['text'] = $this->txt['lp_board_news']['board_id'];
		$this->context['posting_fields']['board_id']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id' => 'board_id',
			],
			'options' => []
		];

		$board_list = $this->getBoardList([
			'ignore_boards'   => false,
			'use_permissions' => true,
			'not_redirection' => true,
			'excluded_boards' => empty($this->modSettings['recycle_board']) ? null : [(int) $this->modSettings['recycle_board']],
			'selected_board'  => empty($this->context['lp_block']['options']['parameters']['board_id']) ? false : $this->context['lp_block']['options']['parameters']['board_id']
		]);

		foreach ($board_list as $category) {
			$this->context['posting_fields']['board_id']['input']['options'][$category['name']] = ['options' => []];

			foreach ($category['boards'] as $board) {
				$this->context['posting_fields']['board_id']['input']['options'][$category['name']]['options'][$board['name']] = [
					'value'    => $board['id'],
					'selected' => (bool) $board['selected'],
					'label'    => ($board['child_level'] > 0 ? str_repeat('==', $board['child_level'] - 1) . '=&gt;' : '') . ' ' . $board['name']
				];
			}
		}

		$this->context['posting_fields']['num_posts']['label']['text'] = $this->txt['lp_board_news']['num_posts'];
		$this->context['posting_fields']['num_posts']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'num_posts',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['num_posts']
			]
		];

		$this->context['posting_fields']['teaser_length']['label']['text'] = $this->txt['lp_board_news']['teaser_length'];
		$this->context['posting_fields']['teaser_length']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'teaser_length',
				'value' => $this->context['lp_block']['options']['parameters']['teaser_length']
			]
		];
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'board_news')
			return;

		$teaser_length = empty($parameters['teaser_length']) ? null : $parameters['teaser_length'];

		$board_news = $this->cache('board_news_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(self::class, 'getFromSsi', 'boardNews', (int) $parameters['board_id'], (int) $parameters['num_posts'], null, $teaser_length, 'array');

		if (empty($board_news)) {
			echo $this->txt['lp_board_news']['no_posts'];
			return;
		}

		$this->loadJavaScriptFile('topic.js', ['defer' => false, 'minimize' => true], 'smf_topic');

		foreach ($board_news as $news) {
			$news['link'] = '<a href="' . $news['href'] . '">' . $this->translate('lp_comments_set', ['comments' => $news['replies']]) . '</a>';

			echo '
			<div class="news_item">
				<h3 class="news_header">
					', $news['icon'], '
					<a href="', $news['href'], '">', $news['subject'], '</a>
				</h3>
				<div class="news_timestamp">', $news['time'], ' ', $this->txt['by'], ' ', $news['poster']['link'], '</div>
				<div class="news_body" style="padding: 2ex 0">', $news['body'], '</div>
				', $news['link'], ($news['locked'] ? '' : ' | ' . $news['comment_link']), '';

			if (! empty($news['likes'])) {
				echo '
				<br class="clear">
				<ul>';

				if ($news['likes']['can_like']) {
					echo '
					<li class="smflikebutton" id="msg_', $news['message_id'], '_likes"><a href="', $this->scripturl, '?action=likes;ltype=msg;sa=like;like=', $news['message_id'], ';', $this->context['session_var'], '=', $this->context['session_id'], '" class="msg_like"><span class="', ($news['likes']['you'] ? 'unlike' : 'like'), '"></span>', ($news['likes']['you'] ? $this->txt['unlike'] : $this->txt['like']), '</a></li>';
				}

				if ($news['likes']['count'] > 0) {
					$this->context['some_likes'] = true;
					$count = $news['likes']['count'];
					$base = 'likes_';
					if ($news['likes']['you']) {
						$base = 'you_' . $base;
						$count--;
					}
					$base .= (isset($this->txt[$base . $count])) ? $count : 'n';

					echo '
					<li class="like_count smalltext">', sprintf($this->txt[$base], $this->scripturl . '?action=likes;sa=view;ltype=msg;like=' . $news['message_id'] . ';' . $this->context['session_var'] . '=' . $this->context['session_id'], comma_format($count)), '</li>';
				}

				echo '
				</ul>';
			}

			echo '
			</div>';

			if (! $news['is_last'])
				echo '
			<hr class="clear">';
		}
	}
}
