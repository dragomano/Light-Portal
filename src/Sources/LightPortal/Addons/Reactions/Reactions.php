<?php

/**
 * @package Reactions (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 23.03.24
 */

namespace Bugo\LightPortal\Addons\Reactions;

use Bugo\Compat\{Lang, Theme, User, Utils};
use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Areas\Fields\CheckboxField;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class Reactions extends Plugin
{
	public string $type = 'page_options';

	public function preparePageParams(array &$params): void
	{
		$params['allow_reactions'] = false;
	}

	public function validatePageParams(array &$params): void
	{
		$params['allow_reactions'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function preparePageFields(): void
	{
		CheckboxField::make('allow_reactions', Lang::$txt['lp_reactions']['allow_reactions'])
			->setValue(Utils::$context['lp_page']['options']['allow_reactions']);
	}

	public function preparePageData(array $data, bool $isAuthor): void
	{
		if (empty($data['options']['allow_reactions']))
			return;

		Utils::$context['reaction_url'] = LP_PAGE_URL . $data['slug'];
		Utils::$context['can_react'] = empty($isAuthor);

		Theme::addInlineJavaScript('
			document.addEventListener("addReaction", (event) => {
				const isComment = typeof event.detail.comment !== "undefined"
				axios.post("' . Utils::$context['reaction_url'] . ';add_reaction", event.detail)
					.then(() => {
						isComment
						? axios
							.post("' . Utils::$context['reaction_url'] . ';get_reactions", {
								comment: event.detail.comment
							})
							.then(response => {
								window["commentReactions" + event.detail.comment].showButtons = false
								window["commentReactions" + event.detail.comment].reactions = response.data
							})
						: axios
							.get("' . Utils::$context['reaction_url'] . ';get_reactions")
							.then(response => {
								window.pageReactions.showButtons = false
								window.pageReactions.reactions = response.data
							})
					})
			})', true);

		$reactions = json_decode($data['options']['reactions'] ?? '', true) ?? [];

		Utils::$context['reaction_buttons'] = $this->getButtons();
		Utils::$context['prepared_buttons'] = json_decode(Utils::$context['reaction_buttons'], true);
		Utils::$context['prepared_reactions'] = json_decode($this->getReactionsWithCount($reactions), true);

		if ($this->request()->has('get_reactions')) {
			$json = $this->request()->json();

			if (isset($json['comment'])) {
				$commentReactions = $this->getReactions($json['comment'], 'comment');
				exit($this->getReactionsWithCount($commentReactions));
			}

			exit($this->getReactionsWithCount($reactions));
		}

		if ($this->request()->has('add_reaction')) {
			$json = $this->request()->json();

			if (isset($json['reaction'])) {
				if (isset($json['comment'])) {
					$commentReactions = $this->getReactions($json['comment'], 'comment');
					$commentReactions[User::$info['id']] = $json['reaction'];
					$this->addReaction($json['comment'], json_encode($commentReactions), 'comment');
					$this->cache()->forget('page_' . $data['slug'] . '_comments');
				} else {
					$reactions[User::$info['id']] = $json['reaction'];
					$this->addReaction($data['id'], json_encode($reactions));
					$this->cache()->forget('page_' . $data['slug']);
				}
			}
		}

		$this->setTemplate();
	}

	public function afterPageContent(): void
	{
		if (empty(Utils::$context['lp_page']['options']['allow_reactions']))
			return;

		show_page_reactions();
	}

	public function commentButtons(array $comment, array &$buttons): void
	{
		if (empty(Utils::$context['lp_page']['options']['allow_reactions']))
			return;

		$comment['can_react'] = $comment['poster']['id'] !== User::$info['id'];
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
				'title' => Lang::$txt['lp_reactions']['titles'][0],
				'emoji' => '👍',
			],
			[
				'name' => 'dislike',
				'title' => Lang::$txt['lp_reactions']['titles'][1],
				'emoji' => '👎',
			],
			[
				'name' => 'love',
				'title' => Lang::$txt['lp_reactions']['titles'][2],
				'emoji' => '❤️',
			],
			[
				'name' => 'laugh',
				'title' => Lang::$txt['lp_reactions']['titles'][3],
				'emoji' => '😆',
			],
			[
				'name' => 'sad',
				'title' => Lang::$txt['lp_reactions']['titles'][4],
				'emoji' => '😢',
			],
			[
				'name' => 'angry',
				'title' => Lang::$txt['lp_reactions']['titles'][5],
				'emoji' => '😡',
			]
		];

		return json_encode($buttons);
	}

	private function getReactions(int $id, string $entity = 'page'): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT value
			FROM {db_prefix}lp_params
			WHERE item_id = {int:id}
				AND type = {string:entity}
				AND name = {literal:reactions}
			LIMIT 1',
			[
				'id'     => $id,
				'entity' => $entity,
			]
		);

		[$reactions] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);

		return json_decode($reactions ?? '', true) ?? [];
	}

	private function addReaction(int $id, string $value, string $entity = 'page'): void
	{
		Utils::$smcFunc['db_insert']('replace',
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
	}
}
