<?php

/**
 * @package Polls (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 19.11.24
 */

namespace Bugo\LightPortal\Plugins\Polls;

use Bugo\Compat\{Config, Db, Lang, Utils};
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\InputField;
use Bugo\LightPortal\UI\Fields\SelectField;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class Polls extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-poll';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params['selected_item'] = 0;
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params['selected_item'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields(Event $e): void
	{
		$polls = $this->getAll();

		if (empty($polls)) {
			InputField::make('selected_item', $this->txt['selected_item'])
				->setType('input')
				->setTab(Tab::CONTENT)
				->setDescription($this->txt['no_items'])
				->setAttribute('disabled', true);
		} else {
			SelectField::make('selected_item', $this->txt['selected_item'])
				->setTab(Tab::CONTENT)
				->setOptions($polls)
				->setValue($e->args->options['selected_item']);
		}
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$poll = $this->getFromSSI('showPoll', $parameters['selected_item'], 'array');

		if ($poll) {
			if ($poll['allow_vote']) {
				$form = Str::html('form', [
					'action' => Config::$boardurl . '/SSI.php?ssi_function=pollVote',
					'method' => 'post',
					'accept-charset' => Utils::$context['character_set'],
				]);

				$form->addHtml(Str::html('strong')->setText($poll['question']))->addHtml('<br>');

				if (! empty($poll['allowed_warning'])) {
					$form->addHtml($poll['allowed_warning'] . '<br>');
				}

				foreach ($poll['options'] as $option) {
					$label = Str::html('label', ['for' => $option['id']])
						->setText($option['vote_button'] . ' ' . $option['option']);
					$form->addHtml($label)->addHtml('<br>');
				}

				$form->addHtml(Str::html('input', [
					'type' => 'submit',
					'value' => Lang::$txt['poll_vote'],
					'class' => 'button',
				]));
				$form->addHtml(Str::html('input', [
					'type' => 'hidden',
					'name' => 'poll',
					'value' => $poll['id'],
				]));
				$form->addHtml(Str::html('input', [
					'type' => 'hidden',
					'name' => Utils::$context['session_var'],
					'value' => Utils::$context['session_id'],
				]));

				echo $form;
			} else {
				$div = Str::html('div');
				$div->addHtml(Str::html('strong')->addHtml(Str::html('a', [
					'class' => 'bbc_link',
					'href' => Config::$scripturl . '?topic=' . $poll['topic'] . '.0',
				])->setText($poll['question'])));

				$dl = Str::html('dl', ['class' => 'stats']);
				foreach ($poll['options'] as $option) {
					$dt = Str::html('dt')->setText($option['option']);
					$dd = Str::html('dd', ['class' => 'statsbar generic_bar righttext']);

					if ($poll['allow_view_results']) {
						$bar = Str::html('div', [
							'class' => 'bar' . (empty($option['percent']) ? ' empty' : ''),
							'style' => empty($option['percent']) ? '' : 'width: ' . $option['percent'] . '%',
						]);
						$dd->addHtml($bar);
						$dd->addHtml(Str::html('span')->setText($option['votes'] . ' (' . $option['percent'] . '%)'));
					}

					$dl->addHtml($dt)->addHtml($dd);
				}

				$div->addHtml($dl);
				if ($poll['allow_view_results']) {
					$div->addHtml(Str::html('strong')->setText(Lang::$txt['poll_total_voters'] . ': ' . $poll['total_votes']));
				}

				echo $div;
			}
		} else {
			echo $this->txt['no_items'];
		}
	}

	private function getAll(): array
	{
		$result = Db::$db->query('', '
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
		while ($row = Db::$db->fetch_assoc($result))
			$polls[$row['id_topic']] = $row['question'];

		Db::$db->free_result($result);

		return $polls;
	}
}
