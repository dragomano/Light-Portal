<?php declare(strict_types=1);

/**
 * SMFTrait.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

if (! defined('SMF'))
	die('No direct access...');

trait SMFTrait
{
	protected function applyHook(string $name, string $method = ''): void
	{
		$name = str_replace('integrate_', '', $name);

		if (func_num_args() === 1) {
			$method = lcfirst($this->getCamelName($name));
		}

		$method = static::class . '::' . str_replace('#', '', $method);

		$file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];

		if ($name === 'pre_load_theme') {
			$name = 'user_info';
		}

		add_integration_function('integrate_' . $name, $method . '#', false, $file);
	}

	protected function createControlRichedit(array $editorOptions): void
	{
		$this->require('Subs-Editor');

		create_control_richedit($editorOptions);

		Utils::$context['post_box_name'] = $editorOptions['id'];

		Theme::addJavaScriptVar('oEditorID', Utils::$context['post_box_name'], true);
		Theme::addJavaScriptVar('oEditorObject', 'oEditorHandle_' . Utils::$context['post_box_name'], true);

		ob_start();

		template_control_richedit(Utils::$context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		Utils::$context['posting_fields']['content']['label']['html'] = '<label>' . Lang::$txt['lp_content'] . '</label>';
		Utils::$context['posting_fields']['content']['input']['html'] = ob_get_clean();
		Utils::$context['posting_fields']['content']['input']['tab'] = 'content';
	}

	protected function fetchWebData(string $url): bool|string
	{
		return fetch_web_data($url);
	}

	protected function createList(array $listOptions): void
	{
		$this->require('Subs-List');

		createList($listOptions);

		Utils::$context['sub_template'] = 'show_list';
		Utils::$context['default_list'] = $listOptions['id'];
	}

	protected function checkSubmitOnce(string $action): void
	{
		checkSubmitOnce($action);
	}

	protected function preparseCode(string &$message): void
	{
		$this->require('Subs-Post');

		preparsecode($message);
	}

	protected function unPreparseCode(string $message): array|string|null
	{
		$this->require('Subs-Post');

		return un_preparsecode($message);
	}

	protected function saveDBSettings(array $save_vars): void
	{
		saveDBSettings($save_vars);
	}

	protected function prepareDBSettingContext(array $config_vars): void
	{
		prepareDBSettingContext($config_vars);
	}

	protected function dbExtend(string $type = 'extra'): void
	{
		db_extend($type);
	}

	protected function getBoardList(array $options = []): array
	{
		$this->require('Subs-MessageIndex');

		$defaultOptions = [
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true,
			'excluded_boards' => empty(Config::$modSettings['recycle_board']) ? null : [(int) Config::$modSettings['recycle_board']],
		];

		if (isset($options['included_boards']))
			unset($defaultOptions['excluded_boards']);

		return getBoardList(array_merge($defaultOptions, $options));
	}

	protected function constructPageIndex(string $base_url, &$start, int $max_value, int $num_per_page): string
	{
		return constructPageIndex($base_url, $start, $max_value, $num_per_page);
	}

	protected function logAction(string $action, array $extra = []): int
	{
		return logAction($action, $extra);
	}
}
