<?php declare(strict_types=1);

/**
 * Area.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Areas;

if (! defined('SMF'))
	die('No direct access...');

trait Area
{
	use Query;

	public function createBbcEditor(string $content = ''): void
	{
		$editorOptions = [
			'id'           => 'content',
			'value'        => $content,
			'height'       => '1px',
			'width'        => '100%',
			'preview_type' => 2,
			'required'     => true
		];

		$this->createControlRichedit($editorOptions);
	}

	public function prepareContent(array $object): string
	{
		if ($object['type'] === 'html') {
			$object['content'] = $this->smcFunc['htmlspecialchars']($object['content']);
		}

		return $object['content'];
	}

	public function prepareTitleFields(string $entity = 'page', bool $required = true): void
	{
		$this->checkSubmitOnce('register');

		$this->prepareIconList();

		$this->prepareTopicList();

		$this->prepareMemberList();

		$languages = empty($this->modSettings['userLanguage']) ? [$this->language] : array_unique([$this->context['user']['language'], $this->language]);

		$this->context['posting_fields']['title']['label']['html'] = $this->txt['lp_title'];
		$this->context['posting_fields']['title']['input']['tab']  = 'content';
		$this->context['posting_fields']['title']['input']['html'] = '
			<div>';

		if (count($this->context['languages']) > 1) {
			$this->context['posting_fields']['title']['input']['html'] .= '
				<nav' . ($this->context['right_to_left'] ? '' : ' class="floatleft"') . '>';

			foreach ($this->context['languages'] as $lang) {
				$this->context['posting_fields']['title']['input']['html'] .= '
					<a
						class="button floatnone"
						:class="{ \'active\': tab === \'' . $lang['filename'] . '\' }"
						@click.prevent="tab = \'' . $lang['filename'] . '\'; window.location.hash = \'' . $lang['filename'] . '\'; $nextTick(() => { setTimeout(() => { document.querySelector(\'input[name=title_' . $lang['filename'] . ']\').focus() }, 50); });"
					>' . $lang['name'] . '</a>';
			}

			$this->context['posting_fields']['title']['input']['html'] .= '
				</nav>';
		}

		foreach ($this->context['languages'] as $lang) {
			$this->context['posting_fields']['title']['input']['html'] .= '
				<div x-show="tab === \'' . $lang['filename'] . '\'">
					<input
						type="text"
						name="title_' . $lang['filename'] . '"
						x-model="title_' . $lang['filename'] . '"
						value="' . ($this->context['lp_' . $entity]['title'][$lang['filename']] ?? '') . '"
						' . (in_array($lang['filename'], $languages) && $required ? ' required' : '') . '
					>
				</div>';
		}

		$this->context['posting_fields']['title']['input']['html'] .= '
			</div>';
	}

	public function preparePostFields(): void
	{
		foreach ($this->context['posting_fields'] as $item => $data) {
			if (isset($data['input']['after'])) {
				$tag = 'div';

				if (isset($data['input']['type']) && in_array($data['input']['type'], ['checkbox', 'number']))
					$tag = 'span';

				$this->context['posting_fields'][$item]['input']['after'] = "<$tag class=\"descbox alternative2 smalltext\">{$data['input']['after']}</$tag>";
			}

			// Add label for html type
			if (isset($data['label']['html']) && $data['label']['html'] !== ' ') {
				$this->context['posting_fields'][$item]['label']['html'] = '<label for="' . $item . '">' . $data['label']['html'] . '</label>';
			}

			// Fancy checkbox
			if (isset($data['input']['type']) && $data['input']['type'] === 'checkbox') {
				$data['input']['attributes']['class'] = 'checkbox';
				$data['input']['after'] = '<label class="label" for="' . $item . '"></label>' . ($this->context['posting_fields'][$item]['input']['after'] ?? '');
				$this->context['posting_fields'][$item] = $data;
			}

			if (empty($data['input']['tab']))
				$this->context['posting_fields'][$item]['input']['tab'] = 'tuning';
		}

		$this->loadTemplate('LightPortal/ManageSettings');
	}

	public function toggleStatus(array $items = [], string $type = 'block'): void
	{
		if (empty($items))
			return;

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_' . ($type === 'block' ? 'blocks' : 'pages') . '
			SET status = CASE status WHEN 1 THEN 0 WHEN 0 THEN 1 WHEN 2 THEN 1 WHEN 3 THEN 0 END
			WHERE ' . ($type === 'block' ? 'block' : 'page') . '_id IN ({array_int:items})',
			[
				'items' => $items
			]
		);

		$this->context['lp_num_queries']++;
	}

	public function getPreviewTitle(string $prefix = ''): string
	{
		return $this->getFloatSpan(
			(empty($prefix) ? '' : ($prefix . ' ')) . $this->context['preview_title'],
			$this->context['right_to_left'] ? 'right' : 'left'
		) . $this->getFloatSpan(
			$this->txt['preview'],
			$this->context['right_to_left'] ? 'left' : 'right'
		) . '<br>';
	}

	public function getFloatSpan(string $text, string $direction = 'left'): string
	{
		return '<span class="float' . $direction . '">' . $text . '</span>';
	}
}
