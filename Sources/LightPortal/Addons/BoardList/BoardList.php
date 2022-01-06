<?php

/**
 * BoardList.php
 *
 * @package BoardList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 04.01.22
 */

namespace Bugo\LightPortal\Addons\BoardList;

use Bugo\LightPortal\Addons\Plugin;

class BoardList extends Plugin
{
	public string $icon = 'far fa-list-alt';

	public function blockOptions(array &$options)
	{
		$options['board_list']['no_content_class'] = true;

		$options['board_list']['parameters'] = [
			'category_class' => 'title_bar',
			'board_class'    => 'roundframe',
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'board_list')
			return;

		$parameters['category_class'] = FILTER_SANITIZE_STRING;
		$parameters['board_class']    = FILTER_SANITIZE_STRING;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'board_list')
			return;

		$data = [];
		foreach ($this->getCategoryClasses() as $key => $template) {
			$data[] = "\t\t\t\t" . '{innerHTML: `' . sprintf($template, empty($key) ? $this->txt['no'] : $key, '') . '`, text: "' . $key . '", selected: ' . ($key == $this->context['lp_block']['options']['parameters']['category_class'] ? 'true' : 'false') . '}';
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#category_class",
			data: [' . "\n" . implode(",\n", $data) . '
			],
			hideSelectedOption: true,
			showSearch: false,
			closeOnSelect: true
		});', true);

		$this->context['posting_fields']['category_class']['label']['text'] = $this->txt['lp_board_list']['category_class'];
		$this->context['posting_fields']['category_class']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id' => 'category_class'
			],
			'options' => [],
			'tab' => 'appearance'
		];

		$data = [];
		foreach ($this->context['lp_all_content_classes'] as $key => $template) {
			$data[] = "\t\t\t\t" . '{innerHTML: `' . sprintf($template, empty($key) ? $this->txt['no'] : $key, '') . '`, text: "' . $key . '", selected: ' . ($key == $this->context['lp_block']['options']['parameters']['board_class'] ? 'true' : 'false') . '}';
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#board_class",
			data: [' . "\n" . implode(",\n", $data) . '
			],
			hideSelectedOption: true,
			showSearch: false,
			closeOnSelect: true
		});', true);

		$this->context['posting_fields']['board_class']['label']['text'] = $this->txt['lp_board_list']['board_class'];
		$this->context['posting_fields']['board_class']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id' => 'board_class'
			],
			'options' => [],
			'tab' => 'appearance'
		];
	}

	public function getData(): array
	{
		$this->require('Subs-MessageIndex');

		$boardListOptions = [
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true,
		];

		return getBoardList($boardListOptions);
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'board_list')
			return;

		$board_list = $this->cache('board_list_addon_b' . $block_id . '_u' . $this->context['user']['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData');

		if (empty($board_list))
			return;

		$this->context['current_board'] = $this->context['current_board'] ?? 0;

		foreach ($board_list as $category) {
			if ($parameters['category_class'])
				echo sprintf($this->getCategoryClasses()[$parameters['category_class']], $category['name']);

			$content = '
				<ul class="smalltext">';

			foreach ($category['boards'] as $board) {
				$content .= '
					<li>';

				if ($board['child_level']) {
					$content .= '
						<ul class="smalltext">
							<li>' . ($this->context['current_board'] == $board['id'] ? '<strong>' : '') . '&raquo; <a href="' . $this->scripturl . '?board=' . $board['id'] . '.0">' . $board['name'] . '</a>' . ($this->context['current_board'] == $board['id'] ? '</strong>' : '') . '</li>
						</ul>';
					} else {
					$content .= '
						' . ($this->context['current_board'] == $board['id'] ? '<strong>' : '') . '<a href="' . $this->scripturl . '?board=' . $board['id'] . '.0">' . $board['name'] . '</a>' . ($this->context['current_board'] == $board['id'] ? '</strong>' : '');
					}

				$content .= '
					</li>';
			}

			$content .= '
				</ul>';

			echo sprintf($this->context['lp_all_content_classes'][$parameters['board_class']], $content, null);
		}
	}

	private function getCategoryClasses(): array
	{
		return [
			'title_bar' => '<div class="title_bar"><h4 class="titlebg">%1$s</h4></div>',
			'sub_bar'   => '<div class="sub_bar"><h4 class="subbg">%1$s</h4></div>',
		];
	}
}
