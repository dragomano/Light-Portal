<?php

namespace Bugo\LightPortal\Front;

use Exception;
use Bugo\LightPortal\{Addons, Helpers};

/**
 * TopicArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class TopicArticle extends AbstractArticle
{
	/**
	 * @var array
	 */
	protected $selected_boards = [];

	/**
	 * Initialize class properties
	 *
	 * Инициализируем свойства класса
	 *
	 * @return void
	 */
	public function init()
	{
		global $modSettings, $user_info;

		$this->selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		$this->params = [
			'current_member'    => $user_info['id'],
			'is_approved'       => 1,
			'id_poll'           => 0,
			'id_redirect_topic' => 0,
			'attachment_type'   => 0,
			'selected_boards'   => $this->selected_boards
		];

		$this->orders = [
			't.id_last_msg DESC',
			'mf.poster_time DESC',
			'mf.poster_time',
			'date DESC'
		];

		Addons::run('frontTopics', array(&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders));
	}

	/**
	 * Get topics from selected boards
	 *
	 * Получаем темы из выбранных разделов
	 *
	 * @param int $start
	 * @param int $limit
	 * @return array
	 * @throws Exception
	 */
	public function getData(int $start, int $limit): array
	{
		global $modSettings, $user_info, $smcFunc, $scripturl, $memberContext, $txt;

		if (empty($this->selected_boards) && $modSettings['lp_frontpage_mode'] == 'all_topics')
			return [];

		if (($topics = Helpers::cache()->get('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit)) === null) {
			$this->params += array(
				'start' => $start,
				'limit' => $limit
			);

			$request = $smcFunc['db_query']('', '
				SELECT
					t.id_topic, t.id_board, t.num_views, t.num_replies, t.is_sticky, t.id_first_msg, t.id_member_started, mf.subject, mf.body AS body, mf.smileys_enabled, COALESCE(mem.real_name, mf.poster_name) AS poster_name, mf.poster_time, mf.id_member, ml.id_msg, ml.id_member AS last_poster_id, ml.poster_name AS last_poster_name, ml.body AS last_body, ml.poster_time AS last_msg_time, GREATEST(mf.poster_time, mf.modified_time) AS date, b.name, ' . (!empty($modSettings['lp_show_images_in_articles']) ? '(
						SELECT id_attach
						FROM {db_prefix}attachments
						WHERE id_msg = t.id_first_msg
							AND width <> 0
							AND height <> 0
							AND approved = {int:is_approved}
							AND attachment_type = {int:attachment_type}
						ORDER BY id_attach
						LIMIT 1
					) AS id_attach, ' : '') . ($user_info['is_guest'] ? '0' : 'COALESCE(lt.id_msg, lmr.id_msg, -1) + 1') . ' AS new_from, ml.id_msg_modified' . (!empty($this->columns) ? ',
					' . implode(', ', $this->columns) : '') . '
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS ml ON (t.id_last_msg = ml.id_msg)
					INNER JOIN {db_prefix}messages AS mf ON (t.id_first_msg = mf.id_msg)
					INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)
					LEFT JOIN {db_prefix}members AS mem ON (mf.id_member = mem.id_member)' . ($user_info['is_guest'] ? '' : '
					LEFT JOIN {db_prefix}log_topics AS lt ON (t.id_topic = lt.id_topic AND lt.id_member = {int:current_member})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (t.id_board = lmr.id_board AND lmr.id_member = {int:current_member})') . (!empty($this->tables) ? '
					' . implode("\n\t\t\t\t\t", $this->tables) : '') . '
				WHERE t.id_poll = {int:id_poll}
					AND t.approved = {int:is_approved}
					AND t.id_redirect_topic = {int:id_redirect_topic}' . (!empty($this->selected_boards) ? '
					AND t.id_board IN ({array_int:selected_boards})' : '') . '
					AND {query_wanna_see_board}' . (!empty($this->wheres) ? '
					' . implode("\n\t\t\t\t\t", $this->wheres) : '') . '
				ORDER BY ' . (!empty($modSettings['lp_frontpage_order_by_num_replies']) ? 't.num_replies DESC, ' : '') . $this->orders[$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
				LIMIT {int:start}, {int:limit}',
				$this->params
			);

			$topics = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				if (!isset($topics[$row['id_topic']])) {
					Helpers::cleanBbcode($row['subject']);

					censorText($row['subject']);

					$body = $last_body = '';

					if (!empty($modSettings['lp_show_teaser'])) {
						censorText($row['body']);
						censorText($row['last_body']);

						$body = preg_replace('~\[spoiler.*].*?\[/spoiler]~Usi', '', $row['body']);
						$body = preg_replace('~\[quote.*].*?\[/quote]~Usi', '', $body);
						$body = preg_replace('~\[table.*].*?\[/table]~Usi', '', $body);
						$body = preg_replace('~\[code.*].*?\[/code]~Usi', '', $body);

						$last_body = preg_replace('~\[spoiler.*].*?\[/spoiler]~Usi', '', $row['last_body']);
						$last_body = preg_replace('~\[quote.*].*?\[/quote]~Usi', '', $last_body);
						$last_body = preg_replace('~\[table.*].*?\[/table]~Usi', '', $last_body);
						$last_body = preg_replace('~\[code.*].*?\[/code]~Usi', '', $last_body);

						$body      = parse_bbc($body, $row['smileys_enabled'], $row['id_first_msg']);
						$last_body = parse_bbc($last_body, $row['smileys_enabled'], $row['id_msg']);
					}

					$image = null;
					if (!empty($row['id_attach']))
						$image = $scripturl . '?action=dlattach;topic=' . $row['id_topic'] . '.0;attach=' . $row['id_attach'] . ';image';

					if (!empty($modSettings['lp_show_images_in_articles']) && empty($image)) {
						$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', parse_bbc($row['body'], false), $value);
						$image = $first_post_image ? array_pop($value) : null;
					}

					$topics[$row['id_topic']] = array(
						'id'        => $row['id_topic'],
						'section'   => array(
							'name' => $row['name'],
							'link' => $scripturl . '?board=' . $row['id_board'] . '.0'
						),
						'author'    => array(
							'id'   => $author_id = !empty($modSettings['lp_frontpage_article_sorting']) ? $row['id_member'] : $row['last_poster_id'],
							'link' => $scripturl . '?action=profile;u=' . $author_id,
							'name' => !empty($modSettings['lp_frontpage_article_sorting']) ? $row['poster_name'] : $row['last_poster_name']
						),
						'date'      => empty($modSettings['lp_frontpage_article_sorting']) && !empty($row['last_msg_time']) ? $row['last_msg_time'] : $row['poster_time'],
						'title'     => $row['subject'],
						'link'      => $scripturl . '?topic=' . $row['id_topic'] . '.0',
						'is_new'    => $row['new_from'] <= $row['id_msg_modified'] && $row['last_poster_id'] != $user_info['id'],
						'views'     => array(
							'num'   => $row['num_views'],
							'title' => $txt['lp_views'],
							'after' => ''
						),
						'replies'   => array(
							'num'   => $row['num_replies'],
							'title' => $txt['lp_replies'],
							'after' => ''
						),
						'css_class' => $row['is_sticky'] ? ' sticky' : '',
						'image'     => $image,
						'can_edit'  => $user_info['is_admin'] || (!empty($user_info['id']) && $row['id_member'] == $user_info['id']),
						'edit_link' => $scripturl . '?action=post;msg=' . $row['id_first_msg'] . ';topic=' . $row['id_topic'] . '.0'
					);

					loadMemberData($author_id);

					$topics[$row['id_topic']]['author']['avatar'] = $modSettings['avatar_url'] . '/default.png';
					if (loadMemberContext($author_id, true)) {
						$topics[$row['id_topic']]['author']['avatar'] = $memberContext[$author_id]['avatar']['href'];
					}

					if (!empty($modSettings['lp_show_teaser']))
						$topics[$row['id_topic']]['teaser'] = Helpers::getTeaser(!empty($modSettings['lp_frontpage_article_sorting']) ? $body : $last_body);

					if (!empty($row['new_from']) && $row['new_from'] <= $row['id_msg_modified'])
						$topics[$row['id_topic']]['link'] = $scripturl . '?topic=' . $row['id_topic'] . '.new;topicseen#new';

					$topics[$row['id_topic']]['msg_link'] = $topics[$row['id_topic']]['link'];

					if (!empty($topics[$row['id_topic']]['num_replies']))
						$topics[$row['id_topic']]['msg_link'] = $scripturl . '?msg=' . $row['id_msg'];

					if (!empty($modSettings['lp_frontpage_article_sorting']) && $modSettings['lp_frontpage_article_sorting'] == 3)
						$topics[$row['id_topic']]['date'] = $row['date'];
				}

				Addons::run('frontTopicsOutput', array(&$topics, $row));
			}

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit, $topics);
		}

		return $topics;
	}

	/**
	 * Get count of active topics from selected boards
	 *
	 * Получаем количество активных тем из выбранных разделов
	 *
	 * @return int
	 */
	public function getTotalCount(): int
	{
		global $modSettings, $user_info, $smcFunc;

		if (empty($this->selected_boards) && $modSettings['lp_frontpage_mode'] == 'all_topics')
			return 0;

		if (($num_topics = Helpers::cache()->get('articles_u' . $user_info['id'] . '_total')) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(t.id_topic)
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)' . (!empty($this->tables) ? '
					' . implode("\n\t\t\t\t\t", $this->tables) : '') . '
				WHERE t.approved = {int:is_approved}
					AND t.id_poll = {int:id_poll}
					AND t.id_redirect_topic = {int:id_redirect_topic}' . (!empty($this->selected_boards) ? '
					AND t.id_board IN ({array_int:selected_boards})' : '') . '
					AND {query_wanna_see_board}' . (!empty($this->wheres) ? '
					' . implode("\n\t\t\t\t\t", $this->wheres) : ''),
				$this->params
			);

			[$num_topics] = $smcFunc['db_fetch_row']($request);

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_total', $num_topics);
		}

		return (int) $num_topics;
	}
}
