<?php

namespace Bugo\LightPortal;

/**
 * Subs.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.5
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Subs
{
	/**
	 * Using cache
	 * Используем кэш
	 *
	 * @param string $data
	 * @param string $getData
	 * @param int $time
	 * @return mixed
	 */
	public static function useCache($data, $getData, $time = 3600)
	{
		if (($$data = cache_get_data('light_portal_' . $data, $time)) == null) {
			$$data = null;

			if (method_exists(__CLASS__, $getData))
				$$data = self::$getData();
			elseif (function_exists($getData))
				$$data = $getData();

			cache_put_data('light_portal_' . $data, $$data, $time);
		}

		return $$data;
	}

	/**
	 * Get information about all active blocks of the portal
	 * Получаем информацию обо всех активных блоках портала
	 *
	 * @return array
	 */
	public static function getActiveBLocks()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT
				b.block_id, b.icon, b.type, b.content, b.placement, b.priority, b.permissions, b.areas, b.title_class, b.title_style, b.content_class, b.content_style,
				bt.lang, bt.title, bp.name, bp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_block_titles AS bt ON (bt.block_id = b.block_id)
				LEFT JOIN {db_prefix}lp_block_params AS bp ON (bp.block_id = b.block_id)
			WHERE b.status = {int:status}
			ORDER BY b.placement, b.priority',
			array(
				'status' => 1
			)
		);

		$active_blocks = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['content']);

			if (!isset($active_blocks[$row['block_id']]))
				$active_blocks[$row['block_id']] = array(
					'id'            => $row['block_id'],
					'icon'          => $row['icon'],
					'type'          => $row['type'],
					'content'       => $row['content'],
					'placement'     => $row['placement'],
					'priority'      => $row['priority'],
					'permissions'   => $row['permissions'],
					'areas'         => explode(',', $row['areas']),
					'title_class'   => $row['title_class'],
					'title_style'   => $row['title_style'],
					'content_class' => $row['content_class'],
					'content_style' => $row['content_style'],
					'can_show'      => self::canShowItem($row['permissions'])
				);

			$active_blocks[$row['block_id']]['title'][$row['lang']] = $row['title'];

			if (!empty($row['name']))
				$active_blocks[$row['block_id']]['parameters'][$row['name']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);

		return $active_blocks;
	}

	/**
	 * Prepare information about current blocks of the portal
	 * Собираем информацию о текущих блоках портала
	 *
	 * @return void
	 */
	public static function loadBlocks()
	{
		global $context;

		$context['lp_all_title_classes']   = self::getTitleClasses();
		$context['lp_all_content_classes'] = self::getContentClasses();

		$context['lp_active_blocks'] = self::useCache('active_blocks', 'getActiveBlocks');

		if (empty($context['lp_active_blocks']))
			return;
	}

	/**
	 * Load used CSS
	 * Подключаем используемые таблицы стилей
	 *
	 * @return void
	 */
	public static function loadCssFiles()
	{
		global $context;

		if (!empty($context['lp_active_blocks']) || isset($_GET['page']) || empty($context['current_action']) || $context['current_action'] == 'admin') {
			loadCssFile('light_portal/flexboxgrid.min.css'); // https://cdn.jsdelivr.net/npm/flexboxgrid@6/dist/flexboxgrid.min.css
			loadCssFile('light_portal/fontawesome.min.css'); // https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5/css/fontawesome.min.css
			loadCssFile('light_portal/light_portal.css');
		}
	}

	/**
	 * Remove unnecessary areas for standalone mode
	 * Удаляем ненужные в автономном режиме области
	 *
	 * @param array $data
	 * @return void
	 */
	public static function unsetUnusedActions(&$data)
	{
		global $modSettings, $context;

		$excluded_actions = !empty($modSettings['lp_standalone_excluded_actions']) ? explode(',', $modSettings['lp_standalone_excluded_actions']) : [];
		foreach ($data as $action => $dump) {
			if (!in_array($action, $excluded_actions))
				unset($data[$action]);
		}

		if (!in_array('search', $excluded_actions))
			$context['allow_search'] = false;

		if (!in_array('moderate', $excluded_actions))
			$context['allow_moderation_center'] = false;

		if (!in_array('calendar', $excluded_actions))
			$context['allow_calendar'] = false;

		if (!in_array('mlist', $excluded_actions))
			$context['allow_memberlist'] = false;
	}

	/**
	 * Parse content depending on the type
	 * Парсим контент в зависимости от типа
	 *
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	public static function parseContent(&$content, $type = 'bbc')
	{
		global $context;

		switch ($type) {
			case 'bbc':
				$content = parse_bbc($content);
				break;
			case 'html':
				$content = un_htmlspecialchars($content);
				break;
			case 'php':
				$content = trim(un_htmlspecialchars($content));
				$content = trim($content, '<?php');
				$content = trim($content, '?>');
				ob_start();
				try {
					$content = html_entity_decode($content, ENT_COMPAT, $context['character_set'] ?? 'UTF-8');
					eval($content);
				} catch (\ParseError $p) {
					echo $p->getMessage();
				}
				$content = ob_get_clean();
				break;
			default:
				self::runAddons('parseContent', array(&$content, $type));
		}
	}

	/**
	 * Get nested directories
	 * Получаем вложенные директории
	 *
	 * @param string $path
	 * @return array
	 */
	private static function getNestedDirs($path)
	{
		$dirs = glob(rtrim($path, "/") . "/*", GLOB_ONLYDIR) or array();

		$nested_dirs = [];
		foreach ($dirs as $path)
			$nested_dirs[] = $path;

		return $nested_dirs;
	}

	/**
	 * Get LP addons
	 * Получаем имена имеющихся дополнений
	 *
	 * @return array
	 */
	private static function getAddons()
	{
		$addons = [];
		foreach (glob(LP_ADDONS . '/*.php') as $filename) {
			$filename = basename($filename);
			if ($filename !== 'index.php')
				$addons[] = str_replace('.php', '', $filename);
		}

		$dirs = self::getNestedDirs(LP_ADDONS);
		foreach ($dirs as $dir)
			$addons[] = basename($dir) . '\\' . basename($dir);

		return $addons;
	}

	/**
	 * Run addons
	 * Подключаем аддоны
	 *
	 * @param string $type ('init', 'blockOptions', 'prepareEditor', 'validateBlockData', 'prepareBlockFields', 'parseContent', 'prepareContent', 'credits')
	 * @param array $vars (extra variables for changing)
	 * @return void
	 */
	public static function runAddons($type = 'init', $vars = [])
	{
		$light_portal_addons = self::useCache('addons', 'getAddons');

		if (empty($light_portal_addons))
			return;

		foreach ($light_portal_addons as $addon) {
			$class = __NAMESPACE__ . '\Addons\\' . $addon;
			$function = $class . '::' . $type;

			self::loadAddonLanguage($addon);

			if (method_exists($class, $type))
				call_user_func_array($function, $vars);
		}
	}

	/**
	 * Require the language file of the addon
	 * Подключаем языковой файл аддона
	 *
	 * @param string $addon
	 * @return void
	 */
	public static function loadAddonLanguage($addon)
	{
		global $txt;

		$addon    = explode("\\", $addon)[0];
		$base_dir = LP_ADDONS . '/' . $addon . '/langs/';

		$languages = array(
			'english',
			Helpers::getUserLanguage()
		);

		foreach ($languages as $lang) {
			$lang_file = $base_dir . $lang . '.php';
			if (is_file($lang_file)) {
				require_once($lang_file);
			}
		}
	}

	/**
	 * Check whether the current user can view the portal item according to their access rights
	 * Проверяем, может ли текущий пользователь просматривать элемент портала, согласно его правам доступа
	 *
	 * @param int $permissions
	 * @return bool
	 */
	public static function canShowItem($permissions)
	{
		global $user_info;

		switch ($permissions) {
			case 0:
				return $user_info['is_admin'] == 1;
			case 1:
				return $user_info['is_guest'] == 1;
			case 2:
				return !empty($user_info['id']);
			default:
				return true;
		}
	}

	/**
	 * Creating meta data for SEO
	 * Формируем мета-данные для SEO
	 *
	 * @return void
	 */
	public static function setMeta()
	{
		global $context, $modSettings, $settings, $smcFunc;

		if (empty($context['lp_page']))
			return;

		$context['meta_description']  = $context['lp_page']['description'];
		$modSettings['meta_keywords'] = $context['lp_page']['keywords'];
		$context['optimus_og_type']['article']['published_time'] = date('Y-m-d\TH:i:s', $context['lp_page']['created_at']);
		$context['optimus_og_type']['article']['modified_time']  = date('Y-m-d\TH:i:s', $context['lp_page']['updated_at']);

		// Looking for an image in the page content | Ищем ссылку на последнее изображение в тексте страницы
		if (!empty($modSettings['lp_page_og_image'])) {
			$image_found = preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $context['lp_page']['content'], $values);
			if ($image_found && is_array($values)) {
				$all_images = array_pop($values);
				$image = $modSettings['lp_page_og_image'] == 1 ? array_shift($all_images) : array_pop($all_images);
				$settings['og_image'] = $smcFunc['htmlspecialchars']($image);
			}
		}
	}

	/**
	 * Load BBCode editor
	 * Подключаем редактор ББ-кода
	 *
	 * @param string $content
	 * @return void
	 */
	public static function createBbcEditor($content = '')
	{
		global $sourcedir, $context;

		$editorOptions = array(
			'id'           => 'content',
			'value'        => $content,
			'height'       => '300px',
			'width'        => '100%',
			'preview_type' => 2,
			'required'     => true
		);

		require_once($sourcedir . '/Subs-Editor.php');
		create_control_richedit($editorOptions);

		$context['post_box_name'] = $editorOptions['id'];

		addJavaScriptVar('oEditorID', $context['post_box_name'], true);
		addJavaScriptVar('oEditorObject', 'oEditorHandle_' . $context['post_box_name'], true);
	}

	/**
	 * Request a list of all localizations of the forum
	 * Запрашиваем список всех локализаций форума
	 *
	 * @return void
	 */
	public static function getForumLanguages()
	{
		global $modSettings, $context, $language;

		getLanguages();

		// Only one language by default! | Если на форуме отключен выбор языков, оставим только один, заданный по умолчанию
		if (empty($modSettings['userLanguage'])) {
			$default_lang = $context['languages'][$language];
			$context['languages'] = [];
			$context['languages'][$language] = $default_lang;
		}
	}

	/**
	 * Get a list of all used classes for blocks with a header
	 * Получаем список всех используемых классов для блоков с заголовком
	 *
	 * @return array
	 */
	public static function getTitleClasses()
	{
		return [
			'div.cat_bar > h3.catbg'        => '<div class="cat_bar"><h3 class="catbg"%2$s>%1$s%3$s</h3></div>',
			'div.title_bar > h3.titlebg'    => '<div class="title_bar"><h3 class="titlebg"%2$s>%1$s%3$s</h3></div>',
			'div.title_bar > h4.titlebg'    => '<div class="title_bar"><h4 class="titlebg"%2$s>%1$s%3$s</h4></div>',
			'div.sub_bar > h3.subbg'        => '<div class="sub_bar"><h3 class="subbg"%2$s>%1$s%3$s</h3></div>',
			'div.sub_bar > h4.subbg'        => '<div class="sub_bar"><h4 class="subbg"%2$s>%1$s%3$s</h4></div>',
			'div.errorbox > h3'             => '<div class="errorbox"><h3%2$s>%1$s%3$s</h3></div>',
			'div.generic_list_wrapper > h3' => '<div class="generic_list_wrapper"><h3%2$s>%1$s%3$s</h3></div>'
		];
	}

	/**
	 * Get a list of all used classes for blocks with content
	 * Получаем список всех используемых классов для блоков с контентом
	 *
	 * @return array
	 */
	public static function getContentClasses()
	{
		return [
			'div.roundframe.noup' => '<div class="roundframe noup"%2$s>%1$s</div>',
			'div.roundframe'      => '<div class="roundframe"%2$s>%1$s</div>',
			'div.windowbg'        => '<div class="windowbg"%2$s>%1$s</div>',
			'div.information'     => '<div class="information"%2$s>%1$s</div>',
			'div.errorbox'        => '<div class="errorbox"%2$s>%1$s</div>',
			'div.noticebox'       => '<div class="noticebox"%2$s>%1$s</div>',
			'div.infobox'         => '<div class="infobox"%2$s>%1$s</div>',
			'div.descbox'         => '<div class="descbox"%2$s>%1$s</div>',
			'_'                   => '%1$s' // Empty class == w\o div
		];
	}

	/**
	 * Return copyright information
	 * Возвращаем информацию об авторских правах
	 *
	 * @return string
	 */
	public static function getCredits()
	{
		global $scripturl;

		return '<a class="bbc_link" href="https://dragomano.ru/mods/light-portal" target="_blank" rel="noopener">' . LP_NAME . '</a> | &copy; <a href="' . $scripturl . '?action=credits;sa=light_portal">2019&ndash;2020</a>, Bugo | Licensed under <a href="https://github.com/dragomano/Light-Portal/blob/master/LICENSE" target="_blank" rel="noopener">BSD</a>';
	}

	/**
	 * Collect information about used components
	 * Формируем информацию об используемых компонентах
	 *
	 * @return void
	 */
	public static function getComponentCredits()
	{
		global $context;

		$links = [];

		$links[] = array(
			'title' => 'Flexbox Grid',
			'link' => 'https://github.com/kristoferjoseph/flexboxgrid',
			'author' => '2013 Kristofer Joseph',
			'license' => array(
				'name' => 'the Apache License',
				'link' => 'https://github.com/kristoferjoseph/flexboxgrid/blob/master/LICENSE'
			)
		);
		$links[] = array(
			'title' => 'Sortable',
			'link' => 'https://github.com/SortableJS/Sortable',
			'license' => array(
				'name' => 'the MIT License (MIT)',
				'link' => 'https://github.com/SortableJS/Sortable/blob/master/LICENSE'
			)
		);
		$links[] = array(
			'title' => 'Font Awesome Free',
			'link' => 'https://fontawesome.com/cheatsheet/free/solid',
			'license' => array(
				'name' => 'the Font Awesome Free License',
				'link' => 'https://github.com/FortAwesome/Font-Awesome/blob/master/LICENSE.txt'
			)
		);

		// Adding copyrights of used plugins | Возможность добавить копирайты используемых плагинов
		self::runAddons('credits', array(&$links));

		$context['lp_components'] = $links;
	}
}
