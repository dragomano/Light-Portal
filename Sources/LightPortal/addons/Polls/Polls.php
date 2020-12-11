<?php

namespace Bugo\LightPortal\Addons\Polls;

/**
 * Polls
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Polls
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public $addon_icon = 'fas fa-poll';

	/**
	 * The topic-poll ID
	 *
	 * ID темы-опроса
	 *
	 * @var int
	 */
	private $selected_item = 0;

	/**
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['polls']['parameters']['selected_item'] = $this->selected_item;
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'polls')
			return;

		$parameters['selected_item'] = FILTER_VALIDATE_INT;
	}

	/**
	 * Adding fields specifically for this block
	 *
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'polls')
			return;

		$context['posting_fields']['selected_item']['label']['text'] = $txt['lp_polls_addon_selected_item'];
		$context['posting_fields']['selected_item']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'selected_item'
			),
			'options' => array(),
			'tab' => 'content'
		);

		$polls = $this->getAll();

		foreach ($polls as $key => $value) {
			if (RC2_CLEAN) {
				$context['posting_fields']['selected_item']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['selected_item']
				);
			} else {
				$context['posting_fields']['selected_item']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['selected_item']
				);
			}
		}
	}

	/**
	 * Get the poll by topic id
	 *
	 * Получаем опрос по идентификатору темы
	 *
	 * @param int $topic
	 * @return array
	 */
	public function getData($topic = 0)
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');

		return ssi_showPoll($topic, 'array');
	}

	/**
	 * Form the content block
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $boardurl, $context, $txt, $scripturl;

		if ($type !== 'polls')
			return;

		$poll = $this->getData($parameters['selected_item']);

		ob_start();

		if (!empty($poll)) {
			if ($poll['allow_vote']) {
				echo '
		<form action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', $context['character_set'], '">
			<strong>', $poll['question'], '</strong><br>
			', !empty($poll['allowed_warning']) ? $poll['allowed_warning'] . '<br>' : '';

				foreach ($poll['options'] as $option) {
					echo '
			<label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label><br>';
				}

				echo '
			<input type="submit" value="', $txt['poll_vote'], '" class="button">
			<input type="hidden" name="poll" value="', $poll['id'], '">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</form>';
			} else {
				echo '
		<div>
			<strong>
				<a class="bbc_link" href="', $scripturl, '?topic=', $poll['topic'], '.0">', $poll['question'], '</a>
			</strong>
			<dl class="stats">';

				foreach ($poll['options'] as $option) {
					echo '
				<dt>', $option['option'], '</dt>
				<dd class="statsbar generic_bar righttext">';

					if ($poll['allow_view_results']) {
						echo '
					<div class="bar', (empty($option['percent']) ? ' empty"' : '" style="width: ' . $option['percent'] . '%"'), '></div>
					<span>', $option['votes'], ' (', $option['percent'], '%)</span>';
					}

					echo '
				</dd>';
				}

				echo '
			</dl>', ($poll['allow_view_results'] ? '
			<strong>' . $txt['poll_total_voters'] . ': ' . $poll['total_votes'] . '</strong>' : ''), '
		</div>';
			}
		} else {
			echo $txt['lp_polls_addon_no_items'];
		}

		$content = ob_get_clean();
	}

	/**
	 * Get all active polls
	 *
	 * Получаем все текущие опросы
	 *
	 * @return array
	 */
	private function getAll()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT t.id_topic, p.question
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}polls AS p ON (p.id_poll = t.id_poll)
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			WHERE {query_see_board}
				AND t.approved = {int:is_approved}',
			array(
				'is_approved' => 1
			)
		);

		$polls = [];
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$polls[$row['id_topic']] = $row['question'];

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $polls;
	}
}
