<?php declare(strict_types=1);

/**
 * TopicArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Articles;

use Bugo\LightPortal\Utils\{BBCodeParser, Config, Lang, User, Utils};

if (! defined('SMF'))
	die('No direct access...');

class TopicArticle extends AbstractArticle
{
	protected array $selectedBoards = [];

	public function init(): void
	{
		$this->selectedBoards = empty(Config::$modSettings['lp_frontpage_boards']) ? [] : explode(',', Config::$modSettings['lp_frontpage_boards']);

		$this->params = [
			'current_member'    => User::$info['id'],
			'is_approved'       => 1,
			'id_poll'           => 0,
			'id_redirect_topic' => 0,
			'attachment_type'   => 0,
			'selected_boards'   => $this->selectedBoards
		];

		$this->orders = [
			't.id_last_msg DESC',
			'mf.poster_time DESC',
			'mf.poster_time',
			'date DESC'
		];

		$this->hook('frontTopics', [&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders]);
	}

	public function getData(int $start, int $limit): array
	{
		if (empty($this->selectedBoards) && Config::$modSettings['lp_frontpage_mode'] === 'all_topics')
			return [];

		$this->params += [
			'start' => $start,
			'limit' => $limit
		];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT
				t.id_topic, t.id_board, t.num_views, t.num_replies, t.is_sticky, t.id_first_msg, t.id_member_started, mf.subject, mf.body AS body, mf.smileys_enabled, COALESCE(mem.real_name, mf.poster_name) AS poster_name, mf.poster_time, mf.id_member, ml.id_msg, ml.id_member AS last_poster_id, ml.poster_name AS last_poster_name, ml.body AS last_body, ml.poster_time AS last_msg_time, GREATEST(mf.poster_time, mf.modified_time) AS date, b.name, ' . (empty(Config::$modSettings['lp_show_images_in_articles']) ? '' : '(
					SELECT id_attach
					FROM {db_prefix}attachments
					WHERE id_msg = t.id_first_msg
						AND width <> 0
						AND height <> 0
						AND approved = {int:is_approved}
						AND attachment_type = {int:attachment_type}
					ORDER BY id_attach
					LIMIT 1
				) AS id_attach, ') . (User::$info['is_guest'] ? '0' : 'COALESCE(lt.id_msg, lmr.id_msg, -1) + 1') . ' AS new_from, ml.id_msg_modified' . (empty($this->columns) ? '' : ',
				' . implode(', ', $this->columns)) . '
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS ml ON (t.id_last_msg = ml.id_msg)
				INNER JOIN {db_prefix}messages AS mf ON (t.id_first_msg = mf.id_msg)
				INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)
				LEFT JOIN {db_prefix}members AS mem ON (mf.id_member = mem.id_member)' . (User::$info['is_guest'] ? '' : '
				LEFT JOIN {db_prefix}log_topics AS lt ON (t.id_topic = lt.id_topic AND lt.id_member = {int:current_member})
				LEFT JOIN {db_prefix}log_mark_read AS lmr ON (t.id_board = lmr.id_board AND lmr.id_member = {int:current_member})') . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE t.id_poll = {int:id_poll}
				AND t.approved = {int:is_approved}
				AND t.id_redirect_topic = {int:id_redirect_topic}' . (empty($this->selectedBoards) ? '' : '
				AND t.id_board IN ({array_int:selected_boards})') . '
				AND {query_wanna_see_board}' . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)) . '
			ORDER BY ' . (empty(Config::$modSettings['lp_frontpage_order_by_replies']) ? '' : 't.num_replies DESC, ') . $this->orders[Config::$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
			LIMIT {int:start}, {int:limit}',
			$this->params
		);

		$topics = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			if (! isset($topics[$row['id_topic']])) {
				$this->cleanBbcode($row['subject']);

				Lang::censorText($row['subject']);

				$body = $last_body = '';

				if (! empty(Config::$modSettings['lp_show_teaser'])) {
					Lang::censorText($row['body']);
					Lang::censorText($row['last_body']);

					$body = preg_replace('~\[spoiler.*].*?\[/spoiler]~Usi', '', $row['body']);
					$body = preg_replace('~\[quote.*].*?\[/quote]~Usi', '', $body);
					$body = preg_replace('~\[table.*].*?\[/table]~Usi', '', $body);
					$body = preg_replace('~\[code.*].*?\[/code]~Usi', '', $body);

					$last_body = preg_replace('~\[spoiler.*].*?\[/spoiler]~Usi', '', $row['last_body']);
					$last_body = preg_replace('~\[quote.*].*?\[/quote]~Usi', '', $last_body);
					$last_body = preg_replace('~\[table.*].*?\[/table]~Usi', '', $last_body);
					$last_body = preg_replace('~\[code.*].*?\[/code]~Usi', '', $last_body);

					$body      = BBCodeParser::load()->parse($body, (bool) $row['smileys_enabled'], $row['id_first_msg']);
					$last_body = BBCodeParser::load()->parse($last_body, (bool) $row['smileys_enabled'], $row['id_msg']);
				}

				$image = empty(Config::$modSettings['lp_show_images_in_articles']) ? '' : $this->getImageFromText(BBCodeParser::load()->parse($row['body'], false));

				if (! empty($row['id_attach']) && empty($image)) {
					$image = Config::$scripturl . '?action=dlattach;topic=' . $row['id_topic'] . '.0;attach=' . $row['id_attach'] . ';image';
				}

				$topics[$row['id_topic']] = [
					'id' => $row['id_topic'],
					'section' => [
						'name' => $row['name'],
						'link' => Config::$scripturl . '?board=' . $row['id_board'] . '.0'
					],
					'author' => [
						'id' => $author_id = (int) (empty(Config::$modSettings['lp_frontpage_article_sorting']) ? $row['last_poster_id'] : $row['id_member']),
						'link' => Config::$scripturl . '?action=profile;u=' . $author_id,
						'name' => empty(Config::$modSettings['lp_frontpage_article_sorting']) ? $row['last_poster_name'] : $row['poster_name']
					],
					'date' => empty(Config::$modSettings['lp_frontpage_article_sorting']) && $row['last_msg_time'] ? $row['last_msg_time'] : $row['poster_time'],
					'title' => $row['subject'],
					'link' => Config::$scripturl . '?topic=' . $row['id_topic'] . '.0',
					'is_new' => $row['new_from'] <= $row['id_msg_modified'] && $row['last_poster_id'] != User::$info['id'],
					'views' => [
						'num' => $row['num_views'],
						'title' => Lang::$txt['lp_views'],
						'after' => ''
					],
					'replies' => [
						'num' => $row['num_replies'],
						'title' => Lang::$txt['lp_replies'],
						'after' => ''
					],
					'css_class' => $row['is_sticky'] ? ' sticky' : '',
					'image' => $image,
					'can_edit' => User::$info['is_admin'] || (User::$info['id'] && $row['id_member'] == User::$info['id']),
					'edit_link' => Config::$scripturl . '?action=post;msg=' . $row['id_first_msg'] . ';topic=' . $row['id_topic'] . '.0'
				];

				if (! empty(Config::$modSettings['lp_show_teaser']))
					$topics[$row['id_topic']]['teaser'] = $this->getTeaser(empty(Config::$modSettings['lp_frontpage_article_sorting']) ? $last_body : $body);

				if ($row['new_from'] && $row['new_from'] <= $row['id_msg_modified'])
					$topics[$row['id_topic']]['link'] = Config::$scripturl . '?topic=' . $row['id_topic'] . '.new;topicseen#new';

				$topics[$row['id_topic']]['msg_link'] = $topics[$row['id_topic']]['link'];

				if ($row['num_replies'])
					$topics[$row['id_topic']]['msg_link'] = Config::$scripturl . '?msg=' . $row['id_msg'];

				if (! empty(Config::$modSettings['lp_frontpage_article_sorting']) && Config::$modSettings['lp_frontpage_article_sorting'] == 3)
					$topics[$row['id_topic']]['date'] = $row['date'];
			}

			$this->hook('frontTopicsOutput', [&$topics, $row]);
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $this->getItemsWithUserAvatars($topics);
	}

	public function getTotalCount(): int
	{
		if (empty($this->selectedBoards) && Config::$modSettings['lp_frontpage_mode'] === 'all_topics')
			return 0;

		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT COUNT(t.id_topic)
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE t.approved = {int:is_approved}
				AND t.id_poll = {int:id_poll}
				AND t.id_redirect_topic = {int:id_redirect_topic}' . (empty($this->selectedBoards) ? '' : '
				AND t.id_board IN ({array_int:selected_boards})') . '
				AND {query_wanna_see_board}' . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)),
			$this->params
		);

		[$num_topics] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return (int) $num_topics;
	}
}
