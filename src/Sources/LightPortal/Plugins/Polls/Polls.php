<?php

/**
 * @package Polls (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\Polls;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Areas\Fields\{InputField, SelectField};
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;

if (! defined('LP_NAME'))
	die('No direct access...');

class Polls extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-poll';

	public function prepareBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'polls')
			return;

		$e->args->params['selected_item'] = 0;
	}

	public function validateBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'polls')
			return;

		$e->args->params['selected_item'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'polls')
			return;

		$polls = $this->getAll();

		if (empty($polls)) {
			InputField::make('selected_item', Lang::$txt['lp_polls']['selected_item'])
				->setType('input')
				->setTab(Tab::CONTENT)
				->setDescription(Lang::$txt['lp_polls']['no_items'])
				->setAttribute('disabled', true);
		} else {
			SelectField::make('selected_item', Lang::$txt['lp_polls']['selected_item'])
				->setTab(Tab::CONTENT)
				->setOptions($polls)
				->setValue(Utils::$context['lp_block']['options']['selected_item']);
		}
	}

	public function prepareContent(Event $e): void
	{
		[$data, $parameters] = [$e->args->data, $e->args->parameters];

		if ($data->type !== 'polls')
			return;

		$poll = $this->getFromSsi('showPoll', $parameters['selected_item'], 'array');

		if ($poll) {
			if ($poll['allow_vote']) {
				echo '
		<form action="', Config::$boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', Utils::$context['character_set'], '">
			<strong>', $poll['question'], '</strong><br>
			', empty($poll['allowed_warning']) ? '' : ($poll['allowed_warning'] . '<br>');

				foreach ($poll['options'] as $option) {
					echo '
			<label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label><br>';
				}

				echo '
			<input type="submit" value="', Lang::$txt['poll_vote'], '" class="button">
			<input type="hidden" name="poll" value="', $poll['id'], '">
			<input type="hidden" name="', Utils::$context['session_var'], '" value="', Utils::$context['session_id'], '">
		</form>';
			} else {
				echo '
		<div>
			<strong>
				<a class="bbc_link" href="', Config::$scripturl, '?topic=', $poll['topic'], '.0">', $poll['question'], '</a>
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
			<strong>' . Lang::$txt['poll_total_voters'] . ': ' . $poll['total_votes'] . '</strong>' : ''), '
		</div>';
			}
		} else {
			echo Lang::$txt['lp_polls']['no_items'];
		}
	}

	private function getAll(): array
	{
		$result = Utils::$smcFunc['db_query']('', '
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
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result))
			$polls[$row['id_topic']] = $row['question'];

		Utils::$smcFunc['db_free_result']($result);

		return $polls;
	}
}
