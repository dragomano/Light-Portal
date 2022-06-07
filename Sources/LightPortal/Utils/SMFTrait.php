<?php declare(strict_types=1);

/**
 * SMFTrait.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Utils;

if (! defined('SMF'))
	die('No direct access...');

/**
 * @property array $context
 * @property array $modSettings
 * @property array $txt
 * @property array $db_cache
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
 */
trait SMFTrait
{
	private array $smfGlobals = [
		'context', 'modSettings', 'txt', 'db_cache', 'smcFunc', 'editortxt',
		'user_info', 'user_profile', 'user_settings', 'memberContext', 'settings',
		'options', 'db_type', 'db_prefix', 'language', 'scripturl', 'boardurl', 'boarddir', 'sourcedir'
	];

	/**
	 * @return mixed
	 */
	public function &__get(string $name)
	{
		if (in_array($name, $this->smfGlobals))
			return $GLOBALS[$name];

		$this->logError('[LP] unsupported property: ' . $name);
	}

	protected function unHtmlSpecialChars(string $string): string
	{
		return un_htmlspecialchars($string);
	}

	protected function middleware(string $permission)
	{
		isAllowedTo($permission);
	}

	protected function allowedTo(string $permission)
	{
		return allowedTo($permission);
	}

	protected function redirect(string $url = '')
	{
		redirectexit($url);
	}

	protected function loadTemplate(string $template)
	{
		loadTemplate($template);
	}

	protected function loadLanguage(string $language)
	{
		loadLanguage($language);
	}

	protected function fatalLangError(string $error, $log = 'general',  $sprintf = [], $status = 403)
	{
		fatal_lang_error($error, $log, $sprintf, $status);
	}

	protected function fatalError(string $message)
	{
		fatal_error($message, false);
	}

	protected function logError(string $message, string $level = 'user')
	{
		log_error($message, $level);
	}

	protected function getLanguages()
	{
		getLanguages();
	}

	protected function getShortenText(string $text, int $length = 150): string
	{
		return shorten_subject($text, $length);
	}

	protected function loadMemberData($users): array
	{
		return loadMemberData($users);
	}

	protected function loadMemberContext($user, bool $display_custom_fields = false)
	{
		return loadMemberContext($user, $display_custom_fields);
	}

	protected function prepareInstalledThemes()
	{
		require_once $this->sourcedir . '/Subs-Themes.php';

		get_installed_themes();
	}

	protected function createControlRichedit(array $editorOptions)
	{
		require_once $this->sourcedir . '/Subs-Editor.php';

		create_control_richedit($editorOptions);

		$this->context['post_box_name'] = $editorOptions['id'];

		$this->addJavaScriptVar('oEditorID', $this->context['post_box_name'], true);
		$this->addJavaScriptVar('oEditorObject', 'oEditorHandle_' . $this->context['post_box_name'], true);

		ob_start();

		template_control_richedit($this->context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		$this->context['posting_fields']['content']['input']['html'] = '<div>' . ob_get_clean() . '</div>';
		$this->context['posting_fields']['content']['input']['tab'] = 'content';
	}

	protected function updateSettings(array $settings)
	{
		updateSettings($settings);
	}

	protected function loadCSSFile(string $fileName, array $params = [], string $id = '')
	{
		loadCSSFile($fileName, $params, $id);
	}

	protected function loadJavaScriptFile(string $fileName, array $params = [], string $id = '')
	{
		loadJavaScriptFile($fileName, $params, $id);
	}

	protected function addInlineCss(string $css)
	{
		addInlineCss($css);
	}

	protected function addJavaScriptVar(string $key, $value, $escape = false)
	{
		addJavaScriptVar($key, $value, $escape);
	}

	protected function addInlineJavaScript(string $javascript, $defer = false)
	{
		addInlineJavaScript($javascript, $defer);
	}

	protected function jsonDecode($json, $returnAsArray = false, $logIt = true)
	{
		return smf_json_decode($json, $returnAsArray, $logIt);
	}

	protected function checkSession()
	{
		checkSession();
	}

	protected function fetchWebData(string $url)
	{
		return fetch_web_data($url);
	}

	protected function censorText(string &$text)
	{
		censorText($text);
	}

	protected function createList(array $listOptions)
	{
		require_once $this->sourcedir . '/Subs-List.php';

		createList($listOptions);

		$this->context['sub_template'] = 'show_list';
		$this->context['default_list'] = $listOptions['id'];
	}

	protected function obExit($header = null)
	{
		obExit($header);
	}

	protected function checkSubmitOnce(string $action)
	{
		checkSubmitOnce($action);
	}

	protected function preparseCode(string &$message)
	{
		require_once $this->sourcedir . '/Subs-Post.php';

		preparsecode($message);
	}

	protected function unPreparseCode(string $message)
	{
		require_once $this->sourcedir . '/Subs-Post.php';

		return un_preparsecode($message);
	}

	protected function saveDBSettings(array $save_vars)
	{
		saveDBSettings($save_vars);
	}

	protected function prepareDBSettingContext(array $config_vars)
	{
		prepareDBSettingContext($config_vars);
	}

	protected function callHelper($action)
	{
		call_helper($action);
	}

	protected function dbExtend(string $type = 'extra')
	{
		db_extend($type);
	}

	protected function parseBbc($message, $smileys = true, $cache_id = '', $parse_tags = [])
	{
		return parse_bbc($message, $smileys, $cache_id, $parse_tags);
	}

	protected function sendStatus($code)
	{
		send_http_status($code);
	}

	protected function getBoardList(array $options = []): array
	{
		require_once $this->sourcedir . '/Subs-MessageIndex.php';

		return getBoardList($options);
	}

	protected function membersAllowedTo(string $permission): array
	{
		require_once $this->sourcedir . '/Subs-Members.php';

		return membersAllowedTo($permission);
	}

	protected function getNotifyPrefs(array $members, string $prefs = ''): array
	{
		require_once $this->sourcedir . '/Subs-Notify.php';

		return getNotifyPrefs($members, $prefs, true);
	}

	protected function updateMemberData(array $members, array $data)
	{
		updateMemberData($members, $data);
	}

	protected function constructPageIndex(string $base_url, &$start, int $max_value, int $num_per_page): string
	{
		return constructPageIndex($base_url, $start, $max_value, $num_per_page);
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
			'roundframe'           => '<div class="roundframe noup" %2$s>%1$s</div>',
			'roundframe2'          => '<div class="roundframe" %2$s>%1$s</div>',
			'windowbg'             => '<div class="windowbg noup" %2$s>%1$s</div>',
			'windowbg2'            => '<div class="windowbg" %2$s>%1$s</div>',
			'information'          => '<div class="information" %2$s>%1$s</div>',
			'errorbox'             => '<div class="errorbox" %2$s>%1$s</div>',
			'noticebox'            => '<div class="noticebox" %2$s>%1$s</div>',
			'infobox'              => '<div class="infobox" %2$s>%1$s</div>',
			'descbox'              => '<div class="descbox" %2$s>%1$s</div>',
			'bbc_code'             => '<div class="bbc_code" %2$s>%1$s</div>',
			'generic_list_wrapper' => '<div class="generic_list_wrapper" %2$s>%1$s</div>',
			''                     => '<div%2$s>%1$s</div>',
		];
	}
}
