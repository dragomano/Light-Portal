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
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Subs
{
	/**
	 * Load used styles and scripts
	 *
	 * Подключаем используемые таблицы стилей и скрипты
	 *
	 * @return void
	 */
	public static function loadCssFiles()
	{
		loadCssFile('https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5/css/all.min.css', array('external' => true));
		loadCssFile('light_portal/flexboxgrid.min.css');
		loadCssFile('light_portal/light_portal.css');
	}

	/**
	 *
	 * Prepare information about current blocks of the portal
	 *
	 * Собираем информацию о текущих блоках портала
	 *
	 * @return void
	 */
	public static function loadBlocks()
	{
		global $context;

		$context['lp_all_title_classes']   = self::getTitleClasses();
		$context['lp_all_content_classes'] = self::getContentClasses();

		$context['lp_active_blocks']    = Helpers::useCache('active_blocks_u' . $context['user']['id'], 'getActiveBlocks', __CLASS__);
		$context['lp_active_pages_num'] = Helpers::useCache('active_pages_num_u' . $context['user']['id'], 'getTotalQuantity', '\Bugo\LightPortal\ManagePages');
	}

	/**
	 * Get information about all active blocks of the portal
	 *
	 * Получаем информацию обо всех активных блоках портала
	 *
	 * @return array
	 */
	public static function getActiveBLocks()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT
				b.block_id, b.icon, b.icon_type, b.type, b.content, b.placement, b.priority, b.permissions, b.areas, b.title_class, b.title_style, b.content_class, b.content_style,
				bt.lang, bt.title, bp.name, bp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_block_titles AS bt ON (bt.block_id = b.block_id)
				LEFT JOIN {db_prefix}lp_params AS bp ON (bp.item_id = b.block_id AND bp.type = {string:type})
			WHERE b.status = {int:status}
			ORDER BY b.placement, b.priority',
			array(
				'type'   => 'block',
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
					'icon_type'     => $row['icon_type'],
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
					'can_show'      => Helpers::canShowItem($row['permissions'])
				);

			$active_blocks[$row['block_id']]['title'][$row['lang']] = $row['title'];

			if (!empty($row['name']))
				$active_blocks[$row['block_id']]['parameters'][$row['name']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);

		return $active_blocks;
	}

	/**
	 * Form an array of articles
	 *
	 * Формируем массив статей
	 *
	 * @param string $source (pages|topics|boards)
	 * @return void
	 */
	public static function prepareArticles($source = 'pages')
	{
		global $user_info, $modSettings, $context, $scripturl;

		switch ($source) {
			case 'topics':
				$function = 'getTopicsFromSelectedBoards';
				break;
			case 'boards':
				$function = 'getSelectedBoards';
				break;
			default:
				$function = 'getActivePages';
		}

		$articles = Helpers::useCache('frontpage_' . $source . '_u' . $user_info['id'], $function, __CLASS__);

		$total_items           = count($articles);
		$limit                 = $modSettings['lp_num_items_per_page'] ?? 10;
		$context['page_index'] = constructPageIndex($scripturl . '?action=portal', $_REQUEST['start'], $total_items, $limit);
		$context['start']      = &$_REQUEST['start'];
		$start                 = (int) $_REQUEST['start'];

		$context['lp_frontpage_articles'] = array_slice($articles, $start, $limit);

		loadJavaScriptFile('light_portal/jquery.matchHeight-min.js', array('minimize' => true));
		addInlineJavaScript('
		jQuery(document).ready(function ($) {
			$(".lp_frontpage_articles .roundframe").matchHeight();
		});', true);

		self::runAddons('frontpageAssets');
	}

	/**
	 * Get topics from selected boards
	 *
	 * Получаем темы из выбранных разделов
	 *
	 * @return array
	 */
	public static function getTopicsFromSelectedBoards()
	{
		global $modSettings, $smcFunc, $user_info, $scripturl, $settings;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return [];

		$custom_columns    = [];
		$custom_tables     = [];
		$custom_wheres     = [];
		$custom_parameters = [];

		self::runAddons('topicsAsArticles', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters));

		$request = $smcFunc['db_query']('', '
			SELECT
				t.id_topic, t.id_board, t.num_views, t.num_replies, t.is_sticky, t.id_first_msg, t.id_member_started, mf.subject, mf.body, mf.smileys_enabled, COALESCE(mem.real_name, mf.poster_name) AS poster_name, mf.poster_time, mf.id_member, ml.id_msg, b.name, ' . ($user_info['is_guest'] ? '0' : 'COALESCE(lt.id_msg, lmr.id_msg, -1) + 1') . ' AS new_from, ml.id_msg_modified' . (!empty($modSettings['lp_show_images_in_articles']) ? ', COALESCE(a.id_attach, 0) AS attach_id' : '') . (!empty($custom_columns) ? ',
				' . implode(', ', $custom_columns) : '') . '
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
				INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = mf.id_member)
				LEFT JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)' . ($user_info['is_guest'] ? '' : '
				LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
				LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = t.id_board AND lmr.id_member = {int:current_member})') . (!empty($modSettings['lp_show_images_in_articles']) ? '
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_msg = t.id_first_msg AND a.id_thumb <> 0 AND a.width > 0 AND a.height > 0)' : '') . (!empty($custom_tables) ? '
				' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
			WHERE t.approved = {int:is_approved}
				AND t.id_poll = {int:id_poll}
				AND t.id_redirect_topic = {int:id_redirect_topic}
				AND t.id_board IN ({array_int:selected_boards})
				AND {query_wanna_see_board}' . (!empty($custom_wheres) ? '
				' . implode("\n\t\t\t\t\t", $custom_wheres) : '') . '
			ORDER BY t.id_last_msg DESC',
			array_merge(array(
				'current_member'    => $user_info['id'],
				'is_approved'       => 1,
				'id_poll'           => 0,
				'id_redirect_topic' => 0,
				'selected_boards'   => $selected_boards
			), $custom_parameters)
		);

		$subject_size = !empty($modSettings['lp_subject_size']) ? (int) $modSettings['lp_subject_size'] : 0;
		$teaser_size  = !empty($modSettings['lp_teaser_size']) ? (int) $modSettings['lp_teaser_size'] : 0;

		$topics = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['subject']);
			censorText($row['body']);

			$row['subject'] = Helpers::cleanBbcode($row['subject']);

			$image = null;
			if (!empty($modSettings['lp_show_images_in_articles'])) {
				$body = parse_bbc($row['body'], false);
				$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $body, $value);
				$image = $first_post_image ? array_pop($value) : (!empty($row['attach_id']) ? $scripturl . '?action=dlattach;topic=' . $row['id_topic'] . ';attach=' . $row['attach_id'] . ';image' : ($settings['default_images_url'] . '/lp_default_image.png'));
			}

			$row['body'] = preg_replace('~\[spoiler\].*?\[\/spoiler\]~Usi', '', $row['body']);
			$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_first_msg']), array('<br>' => ' ')));
			if (!empty($teaser_size) && $smcFunc['strlen']($row['body']) > $teaser_size)
				$row['body'] = shorten_subject($row['body'], $teaser_size - 3);

			$colorClass = '';
			if ($row['is_sticky'])
				$colorClass .= ' alternative2';

			$topics[$row['id_topic']] = array(
				'id'          => $row['id_topic'],
				'poster_id'   => $row['id_member'],
				'poster_link' => $scripturl . '?action=profile;u=' . $row['id_member'],
				'poster_name' => $row['poster_name'],
				'time'        => Helpers::getFriendlyTime($row['poster_time']),
				'subject'     => !empty($subject_size) ? shorten_subject($row['subject'], $subject_size) : $row['subject'],
				'preview'     => $row['body'],
				'link'        => $scripturl . '?topic=' . $row['id_topic'] . ($row['new_from'] > $row['id_msg_modified'] ? '.0' : '.new;topicseen#new'),
				'board'       => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>',
				'is_sticky'   => !empty($row['is_sticky']),
				'is_new'      => $row['new_from'] <= $row['id_msg_modified'],
				'num_views'   => $row['num_views'],
				'num_replies' => $row['num_replies'],
				'css_class'   => $colorClass,
				'image'       => $image
			);

			self::runAddons('topicsAsArticlesResult', array(&$topics, $row));
		}

		$smcFunc['db_free_result']($request);

		return $topics;
	}

	/**
	 * Get active pages (except the main one)
	 *
	 * Получаем активные страницы
	 *
	 * @return array
	 */
	public static function getActivePages()
	{
		global $smcFunc, $modSettings, $settings, $scripturl, $user_info;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.title, p.alias, p.content, p.description, p.type, p.permissions, p.status, p.num_views,
				GREATEST(created_at, updated_at) AS date, mem.real_name AS author_name
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)
			WHERE p.alias != {string:alias}
				AND p.status = {int:status}
			ORDER BY date DESC',
			array(
				'alias'  => '/',
				'status' => 1
			)
		);

		$subject_size = !empty($modSettings['lp_subject_size']) ? (int) $modSettings['lp_subject_size'] : 0;
		$teaser_size  = !empty($modSettings['lp_teaser_size']) ? (int) $modSettings['lp_teaser_size'] : 0;

		$pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			self::parseContent($row['content'], $row['type']);

			$image = null;
			if (!empty($modSettings['lp_show_images_in_articles'])) {
				$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
				$image = $first_post_image ? array_pop($value) : ($settings['default_images_url'] . '/lp_default_image.png');
			}

			if (!empty($teaser_size) && !empty($row['description']))
				$row['description'] = shorten_subject($row['description'], $teaser_size - 3);

			$pages[$row['page_id']] = array(
				'id'          => $row['page_id'],
				'author_id'   => $row['author_id'],
				'author_link' => $scripturl . '?action=profile;u=' . $row['author_id'],
				'author_name' => $row['author_name'],
				'title'       => !empty($subject_size) ? shorten_subject($row['title'], $subject_size) : $row['title'],
				'alias'       => $row['alias'],
				'description' => $row['description'],
				'type'        => $row['type'],
				'num_views'   => $row['num_views'],
				'created_at'  => Helpers::getFriendlyTime($row['date']),
				'is_new'      => $user_info['last_login'] < $row['date'] && $row['author_id'] != $user_info['id'],
				'link'        => $scripturl . '?page=' . $row['alias'],
				'image'       => $image,
				'can_show'    => Helpers::canShowItem($row['permissions']),
				'can_edit'    => $user_info['is_admin'] || (allowedTo('light_portal_manage') && $row['author_id'] == $user_info['id'])
			);

			self::runAddons('pagesAsArticlesResult', array(&$pages, $row));
		}

		$smcFunc['db_free_result']($request);

		$pages = array_filter($pages, function ($item) {
			return $item['can_show'] == true;
		});

		return $pages;
	}

	/**
	 * Get selected boards
	 *
	 * Получаем выбранные разделы
	 *
	 * @return array
	 */
	public static function getSelectedBoards()
	{
		global $modSettings, $smcFunc, $user_info, $context, $settings, $scripturl;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT
				b.id_board, b.name, b.description, b.redirect, CASE WHEN b.redirect != {string:blank_string} THEN 1 ELSE 0 END AS is_redirect, b.num_posts,
				GREATEST(m.poster_time, m.modified_time) AS last_updated, m.id_msg, m.id_topic, c.name AS cat_name,' . ($user_info['is_guest'] ? ' 1 AS is_read, 0 AS new_from' : '
				(CASE WHEN COALESCE(lb.id_msg, 0) >= b.id_last_msg THEN 1 ELSE 0 END) AS is_read, COALESCE(lb.id_msg, -1) + 1 AS new_from') . (!empty($modSettings['lp_show_images_in_articles']) ? ', COALESCE(a.id_attach, 0) AS attach_id' : '') . '
			FROM {db_prefix}boards AS b
				LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
				LEFT JOIN {db_prefix}messages AS m ON (m.id_msg = b.id_last_msg)' . ($user_info['is_guest'] ? '' : '
				LEFT JOIN {db_prefix}log_boards AS lb ON (lb.id_board = b.id_board AND lb.id_member = {int:current_member})') . (!empty($modSettings['lp_show_images_in_articles']) ? '
				LEFT JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic)
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_msg = t.id_first_msg AND a.id_thumb <> 0 AND a.width > 0 AND a.height > 0)' : '') . '
			WHERE b.id_board IN ({array_int:selected_boards})
				AND {query_see_board}
			ORDER BY b.id_last_msg DESC',
			array(
				'blank_string'    => '',
				'current_member'  => $user_info['id'],
				'selected_boards' => $selected_boards
			)
		);

		$subject_size = !empty($modSettings['lp_subject_size']) ? (int) $modSettings['lp_subject_size'] : 0;
		$teaser_size  = !empty($modSettings['lp_teaser_size']) ? (int) $modSettings['lp_teaser_size'] : 0;

		$boards = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$board_name  = parse_bbc($row['name'], false, '', $context['description_allowed_tags']);
			$description = parse_bbc($row['description'], false, '', $context['description_allowed_tags']);
			$cat_name    = parse_bbc($row['cat_name'], false, '', $context['description_allowed_tags']);

			$image = null;
			if (!empty($modSettings['lp_show_images_in_articles'])) {
				$board_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $description, $value);
				$image = $board_image ? array_pop($value) : (!empty($row['attach_id']) ? $scripturl . '?action=dlattach;topic=' . $row['id_topic'] . ';attach=' . $row['attach_id'] . ';image' : ($settings['default_images_url'] . '/lp_default_image.png" width="100%'));
			}

			$description = strip_tags($description);

			$boards[$row['id_board']] = array(
				'id'          => $row['id_board'],
				'name'        => !empty($subject_size) ? shorten_subject($board_name, $subject_size) : $board_name,
				'description' => !empty($teaser_size) ? shorten_subject($description, $teaser_size) : $description,
				'category'    => $cat_name,
				'link'        => $row['is_redirect'] ? $row['redirect'] : $scripturl . '?board=' . $row['id_board'] . '.0',
				'is_redirect' => $row['is_redirect'],
				'is_updated'  => empty($row['is_read']),
				'num_posts'   => $row['num_posts'],
				'image'       => $image
			);

			if (!empty($row['last_updated'])) {
				$boards[$row['id_board']]['last_post'] = $scripturl . '?topic=' . $row['id_topic'] . '.msg' . ($user_info['is_guest'] ? $row['id_msg'] : $row['new_from']) . (empty($row['is_read']) ? ';boardseen' : '') . '#new';
				$boards[$row['id_board']]['last_updated'] = Helpers::getFriendlyTime($row['last_updated']);
			}

			self::runAddons('boardsAsArticlesResult', array(&$boards, $row));
		}

		$smcFunc['db_free_result']($request);

		return $selected_boards;
	}

	/**
	 * Remove unnecessary areas for standalone mode
	 *
	 * Удаляем ненужные в автономном режиме области
	 *
	 * @param array $data
	 * @return void
	 */
	public static function unsetUnusedActions(&$data)
	{
		global $modSettings, $context;

		$excluded_actions   = !empty($modSettings['lp_standalone_excluded_actions']) ? explode(',', $modSettings['lp_standalone_excluded_actions']) : [];
		$excluded_actions[] = 'portal';

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
	 * Prepare content to display
	 *
	 * Готовим контент к отображению в браузере
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @return void
	 */
	public static function prepareContent(&$content, $type = 'bbc', $block_id = 0, $cache_time = 0)
	{
		global $context;

		if (!empty($block_id) && !empty($context['lp_active_blocks'][$block_id]))
			$parameters = $context['lp_active_blocks'][$block_id]['parameters'] ?? [];
		else
			$parameters = $context['lp_block']['options']['parameters'] ?? [];

		self::runAddons('prepareContent', array(&$content, $type, $block_id, $cache_time, $parameters));
	}

	/**
	 * Parse content depending on the type
	 *
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
	 *
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
	 * Get names of the current addons
	 *
	 * Получаем имена имеющихся дополнений
	 *
	 * @return array
	 */
	public static function getAddons()
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
	 *
	 * Подключаем аддоны
	 *
	 * @param string $hook ('init', 'frontpageAssets', 'topicsAsArticles', 'topicsAsArticlesResult', 'pagesAsArticlesResult', 'boardsAsArticlesResult', 'comments', 'blockOptions', 'pageOptions', 'prepareEditor', 'validateBlockData', 'validatePageData', 'prepareBlockFields', 'preparePageFields', 'parseContent', 'prepareContent', 'addSettings', 'credits')
	 * @param array $vars (extra variables for changing)
	 * @return void
	 */
	public static function runAddons($hook = 'init', $vars = [])
	{
		$light_portal_addons = Helpers::useCache('addons', 'getAddons', __CLASS__);

		if (empty($light_portal_addons))
			return;

		foreach ($light_portal_addons as $addon) {
			$class    = __NAMESPACE__ . '\Addons\\' . $addon;
			$function = $class . '::' . $hook;

			self::loadAddonLanguage($addon);

			if (method_exists($class, $hook))
				call_user_func_array($function, $vars);
		}
	}

	/**
	 * Require the language file of the addon
	 *
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
			if (is_file($lang_file))
				require_once($lang_file);
		}
	}

	/**
	 * Creating meta data for SEO
	 *
	 * Формируем мета-данные для SEO
	 *
	 * @return void
	 */
	public static function setMeta()
	{
		global $context, $modSettings, $settings;

		if (empty($context['lp_page']))
			return;

		$modSettings['meta_keywords'] = $context['lp_page']['keywords'];
		$context['meta_description']  = $context['lp_page']['description'];
		$context['optimus_og_type']['article']['published_time'] = date('c', $context['lp_page']['created_at']);
		$context['optimus_og_type']['article']['modified_time']  = !empty($context['lp_page']['updated_at']) ? date('c', $context['lp_page']['updated_at']) : null;
		$context['optimus_og_type']['article']['author'] = $context['lp_page']['author'];

		if (!empty($modSettings['lp_page_og_image']) && !empty($context['lp_page']['image']))
			$settings['og_image'] = $context['lp_page']['image'];
	}

	/**
	 * Load BBCode editor
	 *
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
	 *
	 * Запрашиваем список всех локализаций форума
	 *
	 * @return void
	 */
	public static function getForumLanguages()
	{
		global $modSettings, $context, $language;

		getLanguages();

		// Only one language by default!
		// Если на форуме отключен выбор языков, оставим только один (заданный по умолчанию)
		if (empty($modSettings['userLanguage'])) {
			$default_lang = $context['languages'][$language];
			$context['languages'] = [];
			$context['languages'][$language] = $default_lang;
		}
	}

	/**
	 * Get a list of all used classes for blocks with a header
	 *
	 * Получаем список всех используемых классов для блоков с заголовком
	 *
	 * @return array
	 */
	public static function getTitleClasses()
	{
		return [
			'div.cat_bar > h3.catbg'        => '<div class="cat_bar"><h3 class="catbg">%1$s</h3></div>',
			'div.title_bar > h3.titlebg'    => '<div class="title_bar"><h3 class="titlebg">%1$s</h3></div>',
			'div.title_bar > h4.titlebg'    => '<div class="title_bar"><h4 class="titlebg">%1$s</h4></div>',
			'div.sub_bar > h3.subbg'        => '<div class="sub_bar"><h3 class="subbg">%1$s</h3></div>',
			'div.sub_bar > h4.subbg'        => '<div class="sub_bar"><h4 class="subbg">%1$s</h4></div>',
			'div.errorbox > h3'             => '<div class="errorbox"><h3>%1$s</h3></div>',
			'div.generic_list_wrapper > h3' => '<div class="generic_list_wrapper"><h3>%1$s</h3></div>'
		];
	}

	/**
	 * Get a list of all used classes for blocks with content
	 *
	 * Получаем список всех используемых классов для блоков с контентом
	 *
	 * @return array
	 */
	public static function getContentClasses()
	{
		return [
			'div.roundframe'  => '<div class="roundframe noup"%2$s>%1$s</div>',
			'div.windowbg'    => '<div class="windowbg noup"%2$s>%1$s</div>',
			'div.information' => '<div class="information"%2$s>%1$s</div>',
			'div.errorbox'    => '<div class="errorbox"%2$s>%1$s</div>',
			'div.noticebox'   => '<div class="noticebox"%2$s>%1$s</div>',
			'div.infobox'     => '<div class="infobox"%2$s>%1$s</div>',
			'div.descbox'     => '<div class="descbox"%2$s>%1$s</div>',
			'_'               => '%1$s' // Empty class == w\o div
		];
	}

	/**
	 * Return copyright information
	 *
	 * Возвращаем информацию об авторских правах
	 *
	 * @return string
	 */
	public static function getCredits()
	{
		global $scripturl;

		return '<a class="bbc_link" href="https://dragomano.ru/mods/light-portal" target="_blank" rel="noopener">' . LP_NAME . '</a> | &copy; <a href="' . $scripturl . '?action=credits;sa=light_portal">2019&ndash;2020</a>, Bugo | Licensed under the <a href="https://github.com/dragomano/Light-Portal/blob/master/LICENSE" target="_blank" rel="noopener">BSD 3-Clause</a> License';
	}

	/**
	 * Collect information about used components
	 *
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
			'title' => 'Font Awesome Free',
			'link' => 'https://fontawesome.com/cheatsheet/free',
			'license' => array(
				'name' => 'the Font Awesome Free License',
				'link' => 'https://github.com/FortAwesome/Font-Awesome/blob/master/LICENSE.txt'
			)
		);
		$links[] = array(
			'title' => 'Sortable.js',
			'link' => 'https://github.com/SortableJS/Sortable',
			'license' => array(
				'name' => 'the MIT License (MIT)',
				'link' => 'https://github.com/SortableJS/Sortable/blob/master/LICENSE'
			)
		);
		$links[] = array(
			'title' => 'jquery.matchHeight.js',
			'link' => 'https://github.com/liabru/jquery-match-height',
			'author' => '2015 Liam Brummitt',
			'license' => array(
				'name' => 'the MIT License (MIT)',
				'link' => 'https://github.com/liabru/jquery-match-height/blob/master/LICENSE'
			)
		);

		// Adding copyrights of used plugins
		// Возможность добавить копирайты используемых плагинов
		self::runAddons('credits', array(&$links));

		$context['lp_components'] = $links;
	}
}
