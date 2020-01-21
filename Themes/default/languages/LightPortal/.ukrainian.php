<?php

/**
 * .ukrainian language file
 *
 * @package Light Portal
 * @author Bugo https://dragomano.ru/mods/light-portal
 */

$txt['lp_portal'] = 'Портал';
$txt['lp_forum']  = 'Форум';

// Settings
$txt['lp_settings']                            = 'Налаштування порталу';
$txt['lp_php_mysql_info']                      = 'Версія моду: <strong>%1$s</strong>, версія PHP: <strong>%2$s</strong>, версія %3$s: <strong>%4$s</strong>.';
$txt['lp_frontpage_title']                     = 'Заголовок головної сторінки';
$txt['lp_frontpage_disable']                   = 'Відключити головну сторінку';
$txt['lp_frontpage_mode']                      = 'Що повинно відображатися як перша сторінка';
$txt['lp_frontpage_mode_set']                  = array('Головна сторінка', 'Всі теми з обраних розділів');
$txt['lp_frontpage_boards']                    = 'Розділи-джерела статей для першої сторінки';
$txt['lp_frontpage_layout']                    = 'Кількість колонок для виведення статей';
$txt['lp_show_images_in_articles']             = 'Показувати зображення, знайдені в статтях';
$txt['lp_subject_size']                        = 'Розмір заголовка статей (у символах)';
$txt['lp_teaser_size']                         = 'Розмір тизера статей (у символах)';
$txt['lp_num_per_page']                        = 'Максимальна кількість елементів у списку сторінок (для пагінації)';
$txt['lp_standalone']                          = 'Автономний режим';
$txt['lp_standalone_help']                     = 'Буде відключено все, крім сторінок порталу та областей, що ігноруються';
$txt['lp_standalone_excluded_actions']         = 'Області, що ігноруються';
$txt['lp_standalone_excluded_actions_subtext'] = 'Вкажіть області, які повинні залишатися доступними в автономному режимі.';
$txt['lp_page_editor_type_default']            = 'Тип редактора сторінок за замовчуванням';
$txt['lp_hide_blocks_in_admin_section']        = 'Приховувати активні блоки в адмінці';
$txt['lp_open_graph']                          = 'Open Graph';
$txt['lp_page_og_image']                       = 'Використовувати зображення з тексту статті';
$txt['lp_page_og_image_set']                   = array('Ні', 'Перше знайдене', 'Останнє знайдене');
$txt['lp_page_itemprop_address']               = 'Адреса вашої організації';
$txt['lp_page_itemprop_phone']                 = 'Телефон вашої організації';
$txt['groups_light_portal_view']               = 'Хто може переглядати елементи порталу';
$txt['groups_light_portal_manage']             = 'Хто може керувати блоками та сторінками';

// Actions
$txt['lp_title']       = 'Заголовок';
$txt['lp_actions']     = 'Дії';
$txt['lp_action_on']   = 'Увімкнути';
$txt['lp_action_off']  = 'Вимкнути';
$txt['lp_action_move'] = 'Перемістити';

// Blocks
$txt['lp_blocks']                        = 'Блоки';
$txt['lp_blocks_manage']                 = 'Керування блоками';
$txt['lp_blocks_manage_tab_description'] = 'Тут перераховані всі блоки порталу, що використовуються. Для додавання додаткового блоку скористайтеся відповідною кнопкою.';
$txt['lp_blocks_add']                    = 'Додати блок';
$txt['lp_blocks_add_title']              = 'Додавання блоку';
$txt['lp_blocks_add_tab_description']    = 'Блоків поки небагато, але найбільш універсальні є - грайтеся:)';
$txt['lp_blocks_add_instruction']        = 'Оберіть потрібний блок, натиснувши на нього.';
$txt['lp_blocks_edit_title']             = 'Редагування блоку';
$txt['lp_blocks_edit_tab_description']   = $txt['lp_blocks_add_tab_description'];
$txt['lp_block_content']                 = 'Вміст';
$txt['lp_block_icon_cheatsheet']         = '<br><span class="smalltext"><a href="https://fontawesome.com/cheatsheet/free/solid" target="_blank" rel="noopener">Більше іконок</a></span>';
$txt['lp_block_type']                    = 'Тип блоку';
$txt['lp_block_priority']                = 'Пріоритет';
$txt['lp_block_placement']               = 'Розташування';

$txt['lp_block_placement_set'] = array(
	'header' => 'Шапка',
	'top'    => 'Центральна частина (верх)',
	'left'   => 'Ліва панель',
	'right'  => 'Права панель',
	'bottom' => 'Центральна частина (низ)',
	'footer' => 'Підвал'
);

$txt['lp_block_areas']         = 'Області';
$txt['lp_block_areas_subtext'] = '<div class="information alternative smalltext">Вкажіть одну або кілька областей (через кому) для відображення в них блоку:<br>
<ul>
	<li><strong>all</strong> — відображати всюди</li>
	<li><strong>forum</strong> — відображати лише на форумі</li>
	<li><strong>portal</strong> — відображати лише на порталі (включно зі сторінками)</li>
	<li><strong>custom_action</strong> — відображати в області <em>index.php?action</em>=<strong>custom_action</strong></li>
	<li><strong>page=alias</strong> — відображати на сторінці <em>index.php?page</em>=<strong>alias</strong></li>
</ul></div>';
$txt['lp_block_title_class']   = 'CSS клас заголовка';
$txt['lp_block_title_style']   = 'CSS стилі заголовка';
$txt['lp_block_content_class'] = 'CSS клас вмісту';
$txt['lp_block_content_style'] = 'CSS стилі вмісту';

$txt['lp_block_types'] = array(
	'bbc'  => 'Блок з ББ-кодом',
	'html' => 'Блок з HTML-кодом',
	'php'  => 'Блок з PHP-кодом'
);
$txt['lp_block_types_descriptions'] = array(
	'bbc'  => 'У цьому блоці як контент можна використовувати будь-які ББ-теги форуму.',
	'html' => 'У цьому блоці як контент можна використовувати будь-які теги HTML.',
	'php'  => 'У цьому блоці як контент можна використовувати довільний код PHP.'
);

// Pages
$txt['lp_pages']                        = 'Сторінки';
$txt['lp_pages_main']                   = 'Головна сторінка';
$txt['lp_pages_manage']                 = 'Керування сторінками';
$txt['lp_pages_manage_tab_description'] = 'Тут перераховані всі сторінки порталу. Для додавання нової сторінки скористайтеся відповідною кнопкою.';
$txt['lp_pages_add']                    = 'Додати сторінку';
$txt['lp_pages_add_title']              = 'Додавання сторінки';
$txt['lp_pages_add_tab_description']    = 'Зверніть особливу увагу на <strong>аліас</strong> сторінки - він використовується в адресному рядку і може містити тільки латинські символи та цифри!<br>У головної сторінки аліас завжди рівний "/".';
$txt['lp_pages_edit_title']             = 'Редагування сторінки';
$txt['lp_pages_edit_tab_description']   = $txt['lp_pages_add_tab_description'];
$txt['lp_extra_pages']                  = 'Додаткові сторінки';
$txt['lp_page_types']                   = array('bbc' => 'ББ-код', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                   = 'Аліас';
$txt['lp_page_content']                 = $txt['lp_block_content'];
$txt['lp_page_type']                    = 'Тип сторінки';
$txt['lp_page_description']             = 'Опис';
$txt['lp_page_keywords']                = 'Ключові слова';
$txt['lp_permissions']                  = array('Показувати адмінам', 'Показувати гостям', 'Показувати користувачам', 'Показувати всім');
$txt['lp_no_items']                     = 'Поки що нічого немає. Давайте додамо?';

// Errors
$txt['lp_page_not_found']             = 'Сторінку не знайдено!';
$txt['lp_block_not_found']            = 'Блок не знайдений!';
$txt['lp_post_error_no_title']        = 'Не вказаний заголовок!';
$txt['lp_post_error_no_alias']        = 'Не вказаний аліас!';
$txt['lp_post_error_no_valid_alias']  = 'Зазначений аліас неправильний!';
$txt['lp_post_error_no_unique_alias'] = 'Сторінка з таким аліасом вже існує!';
$txt['lp_post_error_no_content']      = 'Не вказано зміст!';
$txt['lp_post_error_no_areas']        = 'Не вказана область разташування!';
$txt['lp_page_not_editable']          = 'Вам заборонено редагування цієї сторінки!';
$txt['lp_addon_not_installed']        = 'Плагін %1$s не встановлений';

// Who
$txt['lp_who_viewing_frontpage'] = 'Переглядає <a href="%1$s">головну сторінку порталу</a>.';
$txt['lp_who_viewing_page']      = 'Переглядає <a href="%1$s">сторінку порталу</a>.';

// Permissions
$txt['permissiongroup_light_portal']       = LP_NAME;
$txt['permissionname_light_portal_view']   = $txt['group_perms_name_light_portal_view']   = 'Перегляд елементів порталу';
$txt['permissionname_light_portal_manage'] = $txt['group_perms_name_light_portal_manage'] = 'Керування блоками і сторінками';
$txt['permissionhelp_light_portal_view']   = 'Можливість переглядати сторінки і блоки порталу';
$txt['permissionhelp_light_portal_manage'] = 'Доступ до керування сторінками і блоками порталу.';
$txt['cannot_light_portal_view']           = 'Вибачте, вам заборонений перегляд порталу!';
$txt['cannot_light_portal_manage']         = 'Вибачте, вам заборонено керування порталом!';
$txt['cannot_light_portal_view_page']      = 'Вибачте, вам заборонений перегляд цієї сторінки!';

// Time units
$txt['lp_days_set']       = array('день','дня','днів');
$txt['lp_minutes_set']    = array('хвилину','хвилини','хвилин');
$txt['lp_seconds_set']    = array('секунду','секунды','секунд');
$txt['lp_remained']       = 'Залишилося %1$s';
$txt['lp_time_label_ago'] = ' тому';

// Views/replies units
$txt['lp_replies_set'] = array('відповідь', 'відповіді', 'відповідей');
$txt['lp_views_set']   = array('перегляд', 'перегляду', 'переглядів');

// Copyrights
$txt['lp_credits']         = 'Копірайти';
$txt['lp_used_components'] = 'Компоненти порталу';
