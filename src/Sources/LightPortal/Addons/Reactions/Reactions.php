<?php

/**
 * Reactions.php
 *
 * @package Reactions (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 1.11.23
 */

namespace Bugo\LightPortal\Addons\Reactions;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class Reactions extends Plugin
{
	public string $type = 'page_options';

	public function pageOptions(array &$options): void
	{
		$options['allow_reactions'] = false;
	}

	public function validatePageData(array &$parameters): void
	{
		$parameters['allow_reactions'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function preparePageFields(): void
	{
		$this->context['posting_fields']['allow_reactions']['label']['text'] = $this->txt['lp_reactions']['allow_reactions'];
		$this->context['posting_fields']['allow_reactions']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'allow_reactions',
				'checked' => (bool) $this->context['lp_page']['options']['allow_reactions']
			]
		];
	}

	public function preparePageData(array &$data, bool $is_author): void
	{
		if (empty($data['options']['allow_reactions']))
			return;

		$this->context['reaction_url'] = LP_PAGE_URL . $data['alias'];
		$this->context['can_react'] = empty($is_author);

		$reactions = json_decode($data['options']['reactions'] ?? '', true) ?? [];

		$this->context['reaction_buttons'] = $this->getButtons();
		$this->context['prepared_buttons'] = json_decode($this->context['reaction_buttons'], true);
		$this->context['prepared_reactions'] = json_decode($this->getReactionsWithCount($reactions), true);

		if ($this->request()->has('get_reactions')) {
			$json = $this->request()->json();

			if (isset($json['comment'])) {
				$comment_reactions = $this->getReactions($json['comment'], 'comment');
				exit($this->getReactionsWithCount($comment_reactions));
			}

			exit($this->getReactionsWithCount($reactions));
		}

		if ($this->request()->has('add_reaction')) {
			$json = $this->request()->json();

			if (isset($json['reaction'])) {
				if (isset($json['comment'])) {
					$comment_reactions = $this->getReactions($json['comment'], 'comment');
					$comment_reactions[$this->user_info['id']] = $json['reaction'];
					$this->addReaction($json['comment'], json_encode($comment_reactions), 'comment');
					$this->cache()->forget('page_' . $data['alias'] . '_comments');
				} else {
					$reactions[$this->user_info['id']] = $json['reaction'];
					$this->addReaction($data['id'], json_encode($reactions));
					$this->cache()->forget('page_' . $data['alias']);
				}
			}
		}

		$this->setTemplate();
	}

	public function afterPageContent()
	{
		if (empty($this->context['lp_page']['options']['allow_reactions']))
			return;

		show_page_reactions();
	}

	public function commentButtons(array $comment, array &$buttons): void
	{
		if (empty($this->context['lp_page']['options']['allow_reactions']))
			return;

		$comment['can_react'] = $comment['poster']['id'] !== $this->user_info['id'];
		$comment['reactions'] = json_decode($comment['params']['reactions'] ?? '', true) ?? [];
		$comment['prepared_reactions'] = $this->getReactionsWithCount($comment['reactions']);
		$comment['prepared_buttons'] = json_decode($comment['prepared_reactions'], true);

		ob_start();

		show_comment_reactions($comment);

		$buttons[] = ob_get_clean();
	}

	private function getReactionsWithCount(array $reactions): string
	{
		return json_encode(array_count_values($reactions) ?? '', JSON_FORCE_OBJECT);
	}

	private function getButtons(): string
	{
		$buttons = [
			[
				'name' => 'like',
				'title' => $this->txt['lp_reactions']['titles'][0],
				'emoji' => '👍',
			],
			[
				'name' => 'dislike',
				'title' => $this->txt['lp_reactions']['titles'][1],
				'emoji' => '👎',
			],
			[
				'name' => 'love',
				'title' => $this->txt['lp_reactions']['titles'][2],
				'emoji' => '❤️',
			],
			[
				'name' => 'laugh',
				'title' => $this->txt['lp_reactions']['titles'][3],
				'emoji' => '😆',
			],
			[
				'name' => 'sad',
				'title' => $this->txt['lp_reactions']['titles'][4],
				'emoji' => '😢',
			],
			[
				'name' => 'angry',
				'title' => $this->txt['lp_reactions']['titles'][5],
				'emoji' => '😡',
			]
		];

		return json_encode($buttons);
	}

	private function getReactions(int $id, string $entity = 'page'): array
	{
		$result = $this->smcFunc['db_query']('', '
			SELECT value
			FROM {db_prefix}lp_params
			WHERE item_id = {int:id}
				AND type = {string:entity}
				AND name = {literal:reactions}
			LIMIT 1',
			[
				'id'     => $id,
				'entity' => $entity
			]
		);

		[$reactions] = $this->smcFunc['db_fetch_row']($result);

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return json_decode($reactions ?? '', true) ?? [];
	}

	private function addReaction(int $id, string $value, string $entity = 'page'): void
	{
		$this->smcFunc['db_insert']('replace',
			'{db_prefix}lp_params',
			[
				'item_id' => 'int',
				'type'    => 'string',
				'name'    => 'string',
				'value'   => 'string'
			],
			[
				'item_id' => $id,
				'type'    => $entity,
				'name'    => 'reactions',
				'value'   => $value
			],
			['item_id', 'type', 'name']
		);

		$this->context['lp_num_queries']++;
	}
}
