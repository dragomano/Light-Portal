<?php

/**
 * Polls.php
 *
 * @package Polls (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 07.04.23
 */

namespace Bugo\LightPortal\Addons\Polls;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class Polls extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-poll';

	public function blockOptions(array &$options)
	{
		$options['polls']['parameters']['selected_item'] = 0;
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'polls')
			return;

		$parameters['selected_item'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'polls')
			return;

		$this->context['posting_fields']['selected_item']['label']['text'] = $this->txt['lp_polls']['selected_item'];

		$polls = $this->getAll();

		if (empty($polls)) {
			$this->context['posting_fields']['selected_item']['input'] = [
				'type' => 'input',
				'after' => $this->txt['lp_polls']['no_items'],
				'attributes' => [
					'id' => 'selected_item',
					'disabled' => true
				],
				'tab' => 'content'
			];
		} else {
			$this->context['posting_fields']['selected_item']['input'] = [
				'type' => 'select',
				'attributes' => [
					'id' => 'selected_item'
				],
				'options' => [],
				'tab' => 'content'
			];

			foreach ($polls as $key => $value) {
				$this->context['posting_fields']['selected_item']['input']['options'][$value] = [
					'value'    => $key,
					'selected' => $key == $this->context['lp_block']['options']['parameters']['selected_item']
				];
			}
		}
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'polls')
			return;

		$poll = $this->getFromSsi('showPoll', $parameters['selected_item'], 'array');

		if ($poll) {
			if ($poll['allow_vote']) {
				echo '
		<form action="', $this->boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', $this->context['character_set'], '">
			<strong>', $poll['question'], '</strong><br>
			', empty($poll['allowed_warning']) ? '' : ($poll['allowed_warning'] . '<br>');

				foreach ($poll['options'] as $option) {
					echo '
			<label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label><br>';
				}

				echo '
			<input type="submit" value="', $this->txt['poll_vote'], '" class="button">
			<input type="hidden" name="poll" value="', $poll['id'], '">
			<input type="hidden" name="', $this->context['session_var'], '" value="', $this->context['session_id'], '">
		</form>';
			} else {
				echo '
		<div>
			<strong>
				<a class="bbc_link" href="', $this->scripturl, '?topic=', $poll['topic'], '.0">', $poll['question'], '</a>
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
			<strong>' . $this->txt['poll_total_voters'] . ': ' . $poll['total_votes'] . '</strong>' : ''), '
		</div>';
			}
		} else {
			echo $this->txt['lp_polls']['no_items'];
		}
	}

	private function getAll(): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT t.id_topic, p.question
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}polls AS p ON (p.id_poll = t.id_poll)
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			WHERE {query_see_board}
				AND t.approved = {int:is_approved}',
			[
				'is_approved' => 1
			]
		);

		$polls = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request))
			$polls[$row['id_topic']] = $row['question'];

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $polls;
	}
}