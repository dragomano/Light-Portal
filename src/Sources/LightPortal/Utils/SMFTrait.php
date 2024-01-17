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
 * @version 2.4
 */

namespace Bugo\LightPortal\Utils;

use ErrorException;
use Exception;

if (! defined('SMF'))
	die('No direct access...');

trait SMFTrait
{
	protected function applyHook(string $name, string|array $method = '', string $file = ''): void
	{
		$name = str_replace('integrate_', '', $name);

		if (func_num_args() === 1)
			$method = lcfirst($this->getCamelName($name));

		if (is_array($method)) {
			$method = $method[0] . '::' . str_replace('#', '', $method[1] ?? '__invoke');
		} else {
			$method = static::class . '::' . str_replace('#', '', $method);
		}

		$file = $file ?: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];

		if ($name === 'pre_load_theme') {
			$name = 'user_info';
		}

		add_integration_function('integrate_' . $name, $method . '#', false, $file);
	}

	protected function unHtmlSpecialChars(string $string): string
	{
		return un_htmlspecialchars($string);
	}

	protected function middleware(string|array $permission): void
	{
		isAllowedTo($permission);
	}

	protected function allowedTo(string $permission): bool
	{
		return (bool) allowedTo($permission);
	}

	protected function redirect(string $url = ''): void
	{
		redirectexit($url);
	}

	protected function loadTemplate(string $template, string $sub_template = ''): void
	{
		loadTemplate($template);

		if ($sub_template)
			Utils::$context['sub_template'] = $sub_template;
	}

	protected function loadLanguage(string $language, string $lang = ''): void
	{
		loadLanguage($language, $lang);
	}

	protected function fatalLangError(string $error, $status = 403): void
	{
		fatal_lang_error($error, false, null, $status);
	}

	protected function fatalError(string $message): void
	{
		fatal_error($message, false);
	}

	protected function logError(string $message, string $level = 'user'): void
	{
		log_error($message, $level);
	}

	protected function getLanguages(): array
	{
		return getLanguages();
	}

	protected function getShortenText(string $text, int $length = 150): string
	{
		return shorten_subject($text, $length);
	}

	protected function loadMemberData(array $users, string $set = 'normal'): array
	{
		return loadMemberData($users, false, $set);
	}

	/**
	 * @throws Exception
	 */
	protected function loadMemberContext($user, bool $display_custom_fields = false): bool|array
	{
		return loadMemberContext($user, $display_custom_fields);
	}

	protected function membersAllowedTo(string $permission): array
	{
		$this->require('Subs-Members');

		return membersAllowedTo($permission);
	}

	protected function getNotifyPrefs(int|array $members, string|array $prefs = '', bool $process_default = false): array
	{
		$this->require('Subs-Notify');

		return getNotifyPrefs($members, $prefs, $process_default);
	}

	protected function loadEssential(): void
	{
		$this->require('ScheduledTasks');

		loadEssentialThemeData();
	}

	protected function loadEmailTemplate(string $template, array $replacements = [], string $lang = '', bool $loadLang = true): array
	{
		$this->require('Subs-Post');

		return loadEmailTemplate($template, $replacements, $lang, $loadLang);
	}

	/**
	 * @throws ErrorException
	 */
	protected function sendmail(array $to, string $subject, string $message, string $from = null, string $message_id = null, bool $send_html = false, int $priority = 3): void
	{
		$this->require('Subs-Post');

		sendmail($to, $subject, $message, $from, $message_id, $send_html, $priority);
	}

	protected function createControlRichedit(array $editorOptions): void
	{
		$this->require('Subs-Editor');

		create_control_richedit($editorOptions);

		Utils::$context['post_box_name'] = $editorOptions['id'];

		$this->addJavaScriptVar('oEditorID', Utils::$context['post_box_name'], true);
		$this->addJavaScriptVar('oEditorObject', 'oEditorHandle_' . Utils::$context['post_box_name'], true);

		ob_start();

		template_control_richedit(Utils::$context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		Utils::$context['posting_fields']['content']['label']['html'] = '<label>' . Lang::$txt['lp_content'] . '</label>';
		Utils::$context['posting_fields']['content']['input']['html'] = ob_get_clean();
		Utils::$context['posting_fields']['content']['input']['tab'] = 'content';
	}

	protected function updateSettings(array $settings): void
	{
		updateSettings($settings);
	}

	protected function loadCSSFile(string $fileName, array $params = [], string $id = ''): void
	{
		loadCSSFile($fileName, $params, $id);
	}

	protected function loadExtCSS(string $fileName, array $params = [], string $id = ''): void
	{
		$this->loadCSSFile($fileName, array_merge($params, ['external' => true]), $id);
	}

	protected function loadJSFile(string $fileName, array $params = [], string $id = ''): void
	{
		loadJavaScriptFile($fileName, $params, $id);
	}

	protected function loadExtJS(string $fileName, array $params = [], string $id = ''): void
	{
		$this->loadJSFile($fileName, array_merge($params, ['external' => true]), $id);
	}

	protected function addInlineCss(string $css): void
	{
		addInlineCss($css);
	}

	protected function addJavaScriptVar(string $key, $value, $escape = false): void
	{
		addJavaScriptVar($key, $value, $escape);
	}

	protected function addInlineJS(string $javascript, $defer = false): void
	{
		addInlineJavaScript($javascript, $defer);
	}

	protected function jsonDecode($json, $returnAsArray = true, $logIt = true): array
	{
		return smf_json_decode($json, $returnAsArray, $logIt);
	}

	protected function checkSession(): void
	{
		checkSession();
	}

	protected function fetchWebData(string $url): bool|string
	{
		return fetch_web_data($url);
	}

	protected function censorText(string &$text): void
	{
		censorText($text);
	}

	protected function createList(array $listOptions): void
	{
		$this->require('Subs-List');

		createList($listOptions);

		Utils::$context['sub_template'] = 'show_list';
		Utils::$context['default_list'] = $listOptions['id'];
	}

	protected function obExit($header = null): void
	{
		obExit($header);
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

	protected function callHelper($action): void
	{
		call_helper($action);
	}

	protected function dbExtend(string $type = 'extra'): void
	{
		db_extend($type);
	}

	protected function parseBbc($message, $smileys = true, $cache_id = '', $parse_tags = []): array|string
	{
		return parse_bbc($message, $smileys, $cache_id, $parse_tags);
	}

	protected function sendStatus(int $code): void
	{
		send_http_status($code);
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

	protected function updateMemberData(array $members, array $data): void
	{
		updateMemberData($members, $data);
	}

	protected function constructPageIndex(string $base_url, &$start, int $max_value, int $num_per_page): string
	{
		return constructPageIndex($base_url, $start, $max_value, $num_per_page);
	}

	protected function logAction(string $action, array $extra = []): int
	{
		return logAction($action, $extra);
	}

	protected function jsEscape(string $string, bool $as_json = false): string
	{
		return JavaScriptEscape($string, $as_json);
	}

	protected function sentenceList(array $list): string
	{
		return sentence_list($list);
	}

	protected function chmod(string $file): bool
	{
		return smf_chmod($file);
	}

	protected function memoryReturnBytes(string $val): int
	{
		return memoryReturnBytes($val);
	}
}
