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

/**
 * @property array $context
 * @property array $modSettings
 * @property array $txt
 * @property-read array $smcFunc
 * @property-read array $editortxt
 * @property-read array $user_info
 * @property-read array $user_profile
 * @property-read array $user_settings
 * @property-read array $memberContext
 * @property-read array $settings
 * @property-read array $options
 * @property-read string $db_type
 * @property-read string $db_prefix
 * @property-read string $language
 * @property-read string $scripturl
 * @property-read string $boardurl
 * @property-read string $boarddir
 * @property-read string $sourcedir
 * @property-read string $cachedir
 */
trait SMFTrait
{
	private array $smfGlobals = [
		'context', 'modSettings', 'txt', 'smcFunc', 'editortxt', 'user_info', 'user_profile', 'user_settings', 'memberContext',
		'settings', 'options', 'db_type', 'db_prefix', 'language', 'scripturl', 'boardurl', 'boarddir', 'sourcedir', 'cachedir'
	];

	/**
	 * @return mixed|void
	 */
	public function &__get(string $name)
	{
		if (in_array($name, $this->smfGlobals))
			return $GLOBALS[$name];

		$this->logError('[LP] unsupported property: ' . $name);
	}

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

	protected function allowedTo(string $permission): bool|int
	{
		return allowedTo($permission);
	}

	protected function redirect(string $url = ''): void
	{
		redirectexit($url);
	}

	protected function loadTemplate(string $template, string $sub_template = ''): void
	{
		loadTemplate($template);

		if ($sub_template)
			$this->context['sub_template'] = $sub_template;
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
		require_once $this->sourcedir . '/Subs-Members.php';

		return membersAllowedTo($permission);
	}

	protected function getNotifyPrefs(int|array $members, string|array $prefs = '', bool $process_default = false): array
	{
		require_once $this->sourcedir . '/Subs-Notify.php';

		return getNotifyPrefs($members, $prefs, $process_default);
	}

	protected function loadEssential(): void
	{
		require_once $this->sourcedir . '/ScheduledTasks.php';

		loadEssentialThemeData();
	}

	protected function loadEmailTemplate(string $template, array $replacements = [], string $lang = '', bool $loadLang = true): array
	{
		require_once $this->sourcedir . '/Subs-Post.php';

		return loadEmailTemplate($template, $replacements, $lang, $loadLang);
	}

	/**
	 * @throws ErrorException
	 */
	protected function sendmail(array $to, string $subject, string $message, string $from = null, string $message_id = null, bool $send_html = false, int $priority = 3): void
	{
		require_once $this->sourcedir . '/Subs-Post.php';

		sendmail($to, $subject, $message, $from, $message_id, $send_html, $priority);
	}

	protected function createControlRichedit(array $editorOptions): void
	{
		require_once $this->sourcedir . '/Subs-Editor.php';

		create_control_richedit($editorOptions);

		$this->context['post_box_name'] = $editorOptions['id'];

		$this->addJavaScriptVar('oEditorID', $this->context['post_box_name'], true);
		$this->addJavaScriptVar('oEditorObject', 'oEditorHandle_' . $this->context['post_box_name'], true);

		ob_start();

		template_control_richedit($this->context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		$this->context['posting_fields']['content']['label']['html'] = '<label>' . $this->txt['lp_content'] . '</label>';
		$this->context['posting_fields']['content']['input']['html'] = ob_get_clean();
		$this->context['posting_fields']['content']['input']['tab'] = 'content';
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

	protected function loadJavaScriptFile(string $fileName, array $params = [], string $id = ''): void
	{
		loadJavaScriptFile($fileName, $params, $id);
	}

	protected function loadExtJS(string $fileName, array $params = [], string $id = ''): void
	{
		$this->loadJavaScriptFile($fileName, array_merge($params, ['external' => true]), $id);
	}

	protected function addInlineCss(string $css): void
	{
		addInlineCss($css);
	}

	protected function addJavaScriptVar(string $key, $value, $escape = false): void
	{
		addJavaScriptVar($key, $value, $escape);
	}

	protected function addInlineJavaScript(string $javascript, $defer = false): void
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
		require_once $this->sourcedir . '/Subs-List.php';

		createList($listOptions);

		$this->context['sub_template'] = 'show_list';
		$this->context['default_list'] = $listOptions['id'];
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
		require_once $this->sourcedir . '/Subs-Post.php';

		preparsecode($message);
	}

	protected function unPreparseCode(string $message): array|string|null
	{
		require_once $this->sourcedir . '/Subs-Post.php';

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

	protected function sendStatus($code): void
	{
		send_http_status($code);
	}

	protected function getBoardList(array $options = []): array
	{
		require_once $this->sourcedir . '/Subs-MessageIndex.php';

		$defaultOptions = [
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true,
			'excluded_boards' => empty($this->modSettings['recycle_board']) ? null : [(int) $this->modSettings['recycle_board']],
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

	/**
	 * Get a list of all used classes for blocks with a header
	 *
	 * Получаем список всех используемых классов для блоков с заголовком
	 */
	private function getTitleClasses(): array
	{
		return [
			'cat_bar'              => '<div class="cat_bar"><h3 class="catbg">%1$s</h3></div>',
			'title_bar'            => '<div class="title_bar"><h3 class="titlebg">%1$s</h3></div>',
			'sub_bar'              => '<div class="sub_bar"><h3 class="subbg">%1$s</h3></div>',
			'noticebox'            => '<div class="noticebox"><h3>%1$s</h3></div>',
			'infobox'              => '<div class="infobox"><h3>%1$s</h3></div>',
			'descbox'              => '<div class="descbox"><h3>%1$s</h3></div>',
			'generic_list_wrapper' => '<div class="generic_list_wrapper"><h3>%1$s</h3></div>',
			'progress_bar'         => '<div class="progress_bar"><h3>%1$s</h3></div>',
			'popup_content'        => '<div class="popup_content"><h3>%1$s</h3></div>',
			''                     => '<div>%1$s</div>',
		];
	}

	/**
	 * Get a list of all used classes for blocks with content
	 *
	 * Получаем список всех используемых классов для блоков с контентом
	 */
	private function getContentClasses(): array
	{
		return [
			'roundframe'           => '<div class="roundframe noup">%1$s</div>',
			'roundframe2'          => '<div class="roundframe">%1$s</div>',
			'windowbg'             => '<div class="windowbg noup">%1$s</div>',
			'windowbg2'            => '<div class="windowbg">%1$s</div>',
			'information'          => '<div class="information">%1$s</div>',
			'errorbox'             => '<div class="errorbox">%1$s</div>',
			'noticebox'            => '<div class="noticebox">%1$s</div>',
			'infobox'              => '<div class="infobox">%1$s</div>',
			'descbox'              => '<div class="descbox">%1$s</div>',
			'bbc_code'             => '<div class="bbc_code">%1$s</div>',
			'generic_list_wrapper' => '<div class="generic_list_wrapper">%1$s</div>',
			''                     => '<div>%1$s</div>',
		];
	}
}
