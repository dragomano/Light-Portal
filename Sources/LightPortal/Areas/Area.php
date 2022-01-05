<?php

declare(strict_types = 1);

/**
 * Area.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Areas;

use function addJavaScriptVar;
use function create_control_richedit;
use function loadTemplate;
use function preparsecode;

if (! defined('SMF'))
	die('No direct access...');

trait Area
{
	public function createBbcEditor(string $content = '')
	{
		$editorOptions = [
			'id'           => 'content',
			'value'        => $content,
			'height'       => '1px',
			'width'        => '100%',
			'preview_type' => 2,
			'required'     => true
		];

		$this->require('Subs-Editor');
		create_control_richedit($editorOptions);

		$this->context['post_box_name'] = $editorOptions['id'];

		addJavaScriptVar('oEditorID', $this->context['post_box_name'], true);
		addJavaScriptVar('oEditorObject', 'oEditorHandle_' . $this->context['post_box_name'], true);
	}

	public function preparePostFields(string $defaultTab = 'tuning')
	{
		foreach ($this->context['posting_fields'] as $item => $data) {
			if (isset($data['input']['after'])) {
				$tag = 'div';

				if (isset($data['input']['type']) && in_array($data['input']['type'], ['checkbox', 'number']))
					$tag = 'span';

				$this->context['posting_fields'][$item]['input']['after'] = "<$tag class=\"descbox alternative2 smalltext\">{$data['input']['after']}</$tag>";
			}

			// Fancy checkbox
			if (isset($data['input']['type']) && $data['input']['type'] == 'checkbox') {
				$data['input']['attributes']['class'] = 'checkbox';
				$data['input']['after'] = '<label class="label" for="' . $item . '"></label>' . ($this->context['posting_fields'][$item]['input']['after'] ?? '');
				$this->context['posting_fields'][$item] = $data;
			}

			if (empty($data['input']['tab']))
				$this->context['posting_fields'][$item]['input']['tab'] = $defaultTab;
		}

		loadTemplate('LightPortal/ManageSettings');
	}

	public function toggleStatus(array $items = [], string $type = 'block')
	{
		if (empty($items))
			return;

		$new_status = $this->smcFunc['db_title'] === POSTGRE_TITLE ? 'CASE WHEN status = 1 THEN 0 ELSE 1 END' : '!status';

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_' . ($type === 'block' ? 'blocks' : 'pages') . '
			SET status = ' . $new_status . '
			WHERE ' . ($type === 'block' ? 'block' : 'page') . '_id IN ({array_int:items})',
			[
				'items' => $items
			]
		);

		$this->context['lp_num_queries']++;
	}

	public function prepareBbcContent(array &$entity)
	{
		if ($entity['type'] !== 'bbc')
			return;

		$entity['content'] = $this->smcFunc['htmlspecialchars']($entity['content'], ENT_QUOTES);

		$this->require('Subs-Post');

		preparsecode($entity['content']);
	}

	public function prepareIconList()
	{
		if ($this->request()->has('icons') === false)
			return;

		$data = $this->request()->json();

		if (empty($search = $data['search']))
			return;

		$search = trim($this->smcFunc['strtolower']($search));

		$all_icons = $this->getFaIcons();
		$template = '<i class="%1$s"></i>&nbsp;%1$s';

		$this->hook('prepareIconList', [&$all_icons, &$template]);

		$all_icons = array_filter($all_icons, fn($item) => strpos($item, $search) !== false);

		$results = [];
		foreach ($all_icons as $icon) {
			$results[] = [
				'innerHTML' => sprintf($template, $icon),
				'text'      => $icon
			];
		}

		exit(json_encode($results));
	}

	public function getFaIcons(): array
	{
		if (($icons = $this->cache()->get('all_icons', LP_CACHE_TIME * 7)) === null) {
			$content = file_get_contents('https://raw.githubusercontent.com/FortAwesome/Font-Awesome/master/metadata/icons.json');
			$json = json_decode($content);

			if (empty($json))
				return [];

			$icons = [];
			foreach ($json as $icon => $value) {
				foreach ($value->styles as $style) {
					$icons[] = 'fa' . substr($style, 0, 1) . ' fa-' . $icon;
				}
			}

			$this->cache()->put('all_icons', $icons, LP_CACHE_TIME * 7);
		}

		return $icons;
	}

	public function getPreviewTitle(string $prefix = ''): string
	{
		return $this->getFloatSpan((empty($prefix) ? '' : ($prefix . ' ')) . $this->context['preview_title'], $this->context['right_to_left'] ? 'right' : 'left') . $this->getFloatSpan($this->txt['preview'], $this->context['right_to_left'] ? 'left' : 'right') . '<br>';
	}

	public function getFloatSpan(string $text, string $direction = 'left'): string
	{
		return '<span class="float' . $direction . '">' . $text . '</span>';
	}
}
