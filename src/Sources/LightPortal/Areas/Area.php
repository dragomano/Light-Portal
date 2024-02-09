<?php declare(strict_types=1);

/**
 * Area.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Utils\{Config, Lang, Theme, Utils};

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
			$object['content'] = Utils::$smcFunc['htmlspecialchars']($object['content']);
		}

		return $object['content'];
	}

	public function prepareTitleFields(string $entity = 'page', bool $required = true): void
	{
		$this->checkSubmitOnce('register');

		$this->prepareIconList();

		$this->prepareTopicList();

		$this->prepareMemberList();

		$languages = empty(Config::$modSettings['userLanguage'])
			? [Config::$language]
			: array_unique([Utils::$context['user']['language'], Config::$language]);

		$value = '
			<div>';

		if (count(Utils::$context['lp_languages']) > 1) {
			$value .= '
				<nav' . (Utils::$context['right_to_left'] ? '' : ' class="floatleft"') . '>';

			foreach (Utils::$context['lp_languages'] as $key => $lang) {
				$value .= '
					<a
						class="button floatnone"
						:class="{ \'active\': tab === \'' . $key . '\' }"
						@click.prevent="tab = \'' . $key . '\';
							window.location.hash = \'' . $key . '\';
							$nextTick(() => { setTimeout(() => { document.querySelector(\'input[name=title_' . $key . ']\').focus() }, 50); });"
					>' . $lang['name'] . '</a>';
			}

			$value .= '
				</nav>';
		}

		foreach (array_keys(Utils::$context['lp_languages']) as $key) {
			$value .= '
				<div x-show="tab === \'' . $key . '\'">
					<input
						type="text"
						name="title_' . $key . '"
						x-model="title_' . $key . '"
						value="' . (Utils::$context['lp_' . $entity]['titles'][$key] ?? '') . '"
						' . (in_array($key, $languages) && $required ? ' required' : '') . '
					>
				</div>';
		}

		$value .= '
			</div>';

		CustomField::make('title', Lang::$txt['lp_title'])
			->setTab('content')
			->setValue($value);
	}

	public function preparePostFields(): void
	{
		foreach (Utils::$context['posting_fields'] as $item => $data) {
			if (! empty($data['input']['after'])) {
				$tag = 'div';

				if (isset($data['input']['type']) && in_array($data['input']['type'], ['checkbox', 'number']))
					$tag = 'span';

				Utils::$context['posting_fields'][$item]['input']['after'] = "<$tag class=\"descbox alternative2 smalltext\">{$data['input']['after']}</$tag>";
			}

			// Add label for html type
			if (isset($data['label']['html']) && $data['label']['html'] !== ' ') {
				Utils::$context['posting_fields'][$item]['label']['html'] = '<label for="' . $item . '">' . $data['label']['html'] . '</label>';
			}

			// Fancy checkbox
			if (isset($data['input']['type']) && $data['input']['type'] === 'checkbox') {
				$data['input']['attributes']['class'] = 'checkbox';
				$data['input']['after'] = '<label class="label" for="' . $item . '"></label>' . (Utils::$context['posting_fields'][$item]['input']['after'] ?? '');
				Utils::$context['posting_fields'][$item] = $data;
			}

			if (empty($data['input']['tab']))
				Utils::$context['posting_fields'][$item]['input']['tab'] = 'tuning';
		}

		Theme::loadTemplate('LightPortal/ManageSettings');
	}

	public function toggleStatus(array $items = [], string $type = 'block'): void
	{
		if (empty($items))
			return;

		Utils::$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_' . ($type === 'block' ? 'blocks' : 'pages') . '
			SET status = CASE status WHEN 1 THEN 0 WHEN 0 THEN 1 WHEN 2 THEN 1 WHEN 3 THEN 0 END
			WHERE ' . ($type === 'block' ? 'block' : 'page') . '_id IN ({array_int:items})',
			[
				'items' => $items
			]
		);

		Utils::$context['lp_num_queries']++;
	}

	public function getPreviewTitle(string $prefix = ''): string
	{
		return $this->getFloatSpan(
			(empty($prefix) ? '' : ($prefix . ' ')) . Utils::$context['preview_title'],
			Utils::$context['right_to_left'] ? 'right' : 'left'
		) . $this->getFloatSpan(
			Lang::$txt['preview'],
			Utils::$context['right_to_left'] ? 'left' : 'right'
		) . '<br>';
	}

	public function getFloatSpan(string $text, string $direction = 'left'): string
	{
		return '<span class="float' . $direction . '">' . $text . '</span>';
	}

	public function getDefaultTypes(): array
	{
		return [
			'bbc' => [
				'icon' => 'fab fa-bimobject'
			],
			'html' => [
				'icon' => 'fab fa-html5'
			],
			'php' => [
				'icon' => 'fab fa-php'
			]
		];
	}
}
