<?php

/**
 * .ukrainian language file
 *
 * @package Light Portal
 */

$txt['lp_portal'] = 'Портал';
$txt['lp_forum']  = 'Форум';

$txt['lp_new_version_is_available'] = 'Доступна нова версія!';

$txt['lp_article']  = 'Стаття';
$txt['lp_no_items'] = 'Немає елементів для відображення.';
$txt['lp_example']  = 'Приклад: ';

// Settings
$txt['lp_settings']  = 'Налаштування порталу';
$txt['lp_base']      = 'Налаштування головної сторінки і статей';
$txt['lp_base_info'] = 'Версія моду: <strong>%1$s</strong>, версія PHP: <strong>%2$s</strong>, версія %3$s: <strong>%4$s</strong>.<br>Обговорити баги і фічі порталу можна в <a class="bbc_link" href="https://t.me/joinchat/FcgZ0EmYWHPonD4KW5deKQ">Телеграм-групі</a>.';

$txt['lp_frontpage_title']         = 'Заголовок головної сторінки';
$txt['lp_frontpage_mode']          = 'Перша сторінка порталу';
$txt['lp_frontpage_mode_set']      = array('Відключивши', 'Зазначена сторінка', 'Всі теми з обраних розділів', 'Всі активні сторінки порталу', 'Вибрані розділи');
$txt['lp_frontpage_id']            = 'Сторінка порталу для відображення в якості головної';
$txt['lp_frontpage_boards']        = 'Розділи-джерела статей для першої сторінки';
$txt['lp_frontpage_layout']        = 'Кількість колонок для виведення статей';
$txt['lp_frontpage_layout_set']    = array('1 колонка', '2 колонки', '3 колонки', '4 колонки', '6 колонок');
$txt['lp_show_images_in_articles'] = 'Показувати зображення, знайдені в статтях';
$txt['lp_image_placeholder']       = 'URL-адреса картинки-заглушки за замовчуванням';
$txt['lp_subject_size']            = 'Розмір заголовка статей (у символах)';
$txt['lp_teaser_size']             = 'Розмір тизера статей (у символах)';
$txt['lp_num_items_per_page']      = 'Кількість елементів на сторінці (для пагінації)';

$txt['lp_standalone_mode']     = $txt['lp_standalone_mode_title'] = 'Автономний режим';
$txt['lp_standalone_url']      = 'URL-адреса головної сторінки порталу в автономному режимі';
$txt['lp_standalone_url_help'] = 'Можна вказати свою URL-адресу для відображення в якості головної сторінки порталу (наприклад, <strong>https://yourforum/portal.php</strong>).<br>У цьому випадку головна сторінка форуму залишиться доступною за адресою <strong>https://yourforum/index.php</strong>.<br><br>Щоб вивести головну сторінку порталу, у файлі <em>portal.php</em> повинен бути приблизно такий код:<br><pre><code class="bbc_code">
require(dirname(__FILE__) . \'/SSI.php\');
<br>
Bugo\LightPortal\FrontPage::show();
<br>
obExit(true);</code></pre><br>
Вимкніть параметр "<strong>Enable local storage of cookies</strong>", якщо файл <em>portal.php</em> знаходиться поза директорії форуму (Maintenance => Настроювання Сервера => Cookies and Sessions).';
$txt['lp_standalone_mode_disabled_actions']         = 'Області, що відключаються';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Вкажіть області, які повинні бути ВІДКЛЮЧЕНІ в автономному режимі';

$txt['groups_light_portal_view']             = 'Хто може переглядати елементи порталу';
$txt['groups_light_portal_manage_blocks']    = 'Хто може керувати блоками';
$txt['groups_light_portal_manage_own_pages'] = 'Хто може керувати своїми сторінками';
$txt['lp_manage_permissions']                = 'Увага: деякі сторінки i блоки можуть містити небезпечний HTML/PHP контент, тому не надавайте це право всім підряд!';

$txt['lp_debug_and_caching']       = 'Налагодження та кешування';
$txt['lp_show_debug_info']         = 'Відображати час завантаження та кількість запитів порталу';
$txt['lp_show_debug_info_subtext'] = 'Інформація буде доступна тільки адміністраторам!';
$txt['lp_cache_update_interval']   = 'Інтервал оновлення кешу';

$txt['lp_extra']      = 'Сторінки і блоки';
$txt['lp_extra_info'] = 'Тут знаходяться загальні налаштування сторінок і блоків.';

$txt['lp_show_tags_on_page']            = 'Відображати ключові слова у верхній частині сторінки';
$txt['lp_show_comment_block']           = 'Відображати блок коментарів';
$txt['lp_show_comment_block_set']       = array('none' => 'Ні', 'default' => 'Вбудований');
$txt['lp_num_comments_per_page']        = 'Кількість батьківських коментарів на сторінці';
$txt['lp_page_editor_type_default']     = 'Тип редактора сторінок за замовчуванням';
$txt['lp_hide_blocks_in_admin_section'] = 'Приховувати активні блоки в адмінці';
$txt['lp_panels']                       = 'Панелі';
$txt['lp_panel_direction']              = 'Напрямок блоків в панелях';
$txt['lp_panel_direction_set']          = array('Вертикальне', 'Горизонтальне');
$txt['lp_open_graph']                   = 'Open Graph';
$txt['lp_page_og_image']                = 'Використовувати зображення з тексту статті';
$txt['lp_page_og_image_set']            = array('Ні', 'Перше знайдене', 'Останнє знайдене');
$txt['lp_page_itemprop_address']        = 'Адреса вашої організації';
$txt['lp_page_itemprop_phone']          = 'Телефон вашої організації';

// Plugins
$txt['lp_plugins']      = 'Плагіни';
$txt['lp_plugins_desc'] = 'Будь-який з плагінів можна включити або виключити. А деякі ще й налаштувати!';
$txt['lp_plugins_info'] = 'Тут перераховані встановлені плагіни порталу. Ви завжди можете створити новий, скориставшись <a href="%1$s" target="_blank" rel="noopener">інструкцією</a>.';

$txt['lp_plugins_hooks_types'] = array(
	'block'   => 'Блок',
	'editor'  => 'Редактор',
	'comment' => 'Віджет коментарів',
	'parser'  => 'Парсер контента',
	'article' => 'Обробка статей',
	'other'   => 'Різне'
);

// Actions
$txt['lp_title']        = 'Заголовок';
$txt['lp_actions']      = 'Дії';
$txt['lp_action_on']    = 'Увімкнути';
$txt['lp_action_off']   = 'Вимкнути';
$txt['lp_action_clone'] = 'Клонувати';
$txt['lp_action_move']  = 'Перемістити';
$txt['lp_read_more']    = 'Читати далі...';

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
$txt['lp_block_icon_cheatsheet']         = 'Список іконок';
$txt['lp_block_type']                    = 'Тип блоку';
$txt['lp_block_priority']                = 'Пріоритет';
$txt['lp_block_icon_type']               = 'Тип іконки';
$txt['lp_block_icon_type_set']           = array('fas' => 'Solid', 'far' => 'Regular', 'fab' => 'Brands');
$txt['lp_block_placement']               = 'Розташування';
$txt['lp_block_placement_set']           = array(
	'header' => 'Шапка',
	'top'    => 'Центральна частина (верх)',
	'left'   => 'Ліва панель',
	'right'  => 'Права панель',
	'bottom' => 'Центральна частина (низ)',
	'footer' => 'Підвал'
);

$txt['lp_block_areas']         = 'Області';
$txt['lp_block_areas_subtext'] = 'Вкажіть одну або кілька областей (через кому) для відображення в них блоку:<br>
<ul class="bbc_list">
	<li><strong>all</strong> — відображати всюди</li>
	<li><strong>forum</strong> — відображати лише на форумі (включаючи розділи та теми)</li>
	<li><strong>board=id</strong> — відображати в розділі з ідентифікатором <strong>id</strong> (включаючи всі теми всередині розділу)</li>
	<li><strong>topic=id</strong> — відображати в темі з ідентифікатором <strong>id</strong></li>
	<li><strong>portal</strong> — відображати лише на порталі (включно зі сторінками)</li>
	<li><strong>custom_action</strong> — відображати в області <em>index.php?action</em>=<strong>custom_action</strong></li>
	<li><strong>page=alias</strong> — відображати на сторінці <em>index.php?page</em>=<strong>alias</strong></li>
</ul>';
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
$txt['lp_pages_manage']                 = 'Керування сторінками';
$txt['lp_pages_manage_tab_description'] = 'Тут перераховані всі сторінки порталу. Для додавання нової сторінки скористайтеся відповідною кнопкою.';
$txt['lp_pages_add']                    = 'Додати сторінку';
$txt['lp_pages_add_title']              = 'Додавання сторінки';
$txt['lp_pages_add_tab_description']    = 'Заповніть заголовок і аліас сторінки. Після цього можна буде змінити її тип, використовувати попередній перегляд і збереження.';
$txt['lp_pages_edit_title']             = 'Редагування сторінки';
$txt['lp_pages_edit_tab_description']   = $txt['lp_pages_add_tab_description'];
$txt['lp_extra_pages']                  = 'Сторінки порталу';
$txt['lp_page_types']                   = array('bbc' => 'ББ-код', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                   = 'Аліас';
$txt['lp_page_alias_subtext']           = 'Ім\'я сторінки має починатися з латинської літери і складатися з малих латинських букв, цифр і знака підкреслення.';
$txt['lp_page_content']                 = $txt['lp_block_content'];
$txt['lp_page_type']                    = 'Тип сторінки';
$txt['lp_page_description']             = 'Опис';
$txt['lp_page_keywords']                = 'Ключові слова';
$txt['lp_page_keywords_after']          = 'Використовуйте кому для розділення';
$txt['lp_permissions']                  = array('Показувати адмінам', 'Показувати гостям', 'Показувати користувачам', 'Показувати всім');

$txt['lp_page_options'] = array(
	'show_author_and_date' => 'Показувати автора і дату створення',
	'allow_comments'       => 'Дозволити коментарі'
);

// Import and Export
$txt['lp_pages_export']                  = 'Експорт сторінок';
$txt['lp_pages_import']                  = 'Імпорт сторінок';
$txt['lp_pages_export_tab_description']  = 'Тут можна експортувати потрібні сторінки для створення резервної копії або для імпорту на іншому форумі.';
$txt['lp_pages_import_tab_description']  = 'Тут можна імпортувати з резервної копії збережені раніше сторінки порталу.';
$txt['lp_blocks_export']                 = 'Експорт блоків';
$txt['lp_blocks_import']                 = 'Імпорт блоків';
$txt['lp_blocks_export_tab_description'] = 'Тут можна експортувати потрібні блокі для створення резервної копії або для імпорту на іншому форумі.';
$txt['lp_blocks_import_tab_description'] = 'Тут можна імпортувати з резервної копії збережені раніше блокі порталу.';
$txt['lp_export_run']                    = 'Експортувати виділені';
$txt['lp_import_run']                    = 'Імпортувати';
$txt['lp_export_all']                    = 'Експортувати всі';

// Tags
$txt['lp_all_page_tags']    = 'Всі теги сторінок порталу';
$txt['lp_all_tags_by_key']  = 'Всі сторінки з тегом «%1$s»';
$txt['lp_no_selected_tag']  = 'Вказаний тег не знайдено.';
$txt['lp_no_tags']          = 'Тегів поки немає.';
$txt['lp_keyword_column']   = 'Ключове слово';
$txt['lp_frequency_column'] = 'Частотність';

// Comments
$txt['lp_comments']            = 'Коментарi';
$txt['lp_comment_placeholder'] = 'Введіть текст коментаря...';

$txt['alert_group_light_portal']           = LP_NAME;
$txt['alert_page_comment']                 = 'При розміщенні нового коментаря до моєї сторінки';
$txt['alert_new_comment_page_comment']     = '{member_link} залишив(а) коментар <a href="{comment_link}">{comment_title}</a>';
$txt['alert_page_comment_reply']           = 'При отриманні відповіді на мій коментар';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} відповів (а) на ваш коментар <a href="{comment_link}">{comment_title}</a>';

// Errors
$txt['lp_page_not_found']             = 'Сторінку не знайдено!';
$txt['lp_page_not_activated']         = 'Запитувана сторінка відключена!';
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
$txt['lp_who_viewing_tags']      = 'Переглядає <a href="%1$s">теги сторінок порталу</a>.';
$txt['lp_who_viewing_the_tag']   = 'Переглядає список сторінок з тегом <a href="%1$s" class="bbc_link">%2$s</a>.';

// Permissions
$txt['permissiongroup_light_portal']                 = LP_NAME;
$txt['permissionname_light_portal_view']             = $txt['group_perms_name_light_portal_view']             = 'Перегляд елементів порталу';
$txt['permissionname_light_portal_manage_blocks']    = $txt['group_perms_name_light_portal_manage_blocks']    = 'Управління блоками';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Управління своїми сторінками';
$txt['permissionhelp_light_portal_view']             = 'Можливість переглядати сторінки і блоки порталу.';
$txt['permissionhelp_light_portal_manage_blocks']    = 'Доступ до управління блоками порталу.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Доступ до управління своїми сторінками.';
$txt['cannot_light_portal_view']                     = 'Вибачте, вам заборонений перегляд порталу!';
$txt['cannot_light_portal_manage_blocks']            = 'Вибачте, вам заборонено керування блоками порталу!';
$txt['cannot_light_portal_manage_own_pages']         = 'Вибачте, вам заборонено Керування сторінками порталу!';
$txt['cannot_light_portal_view_page']                = 'Вибачте, вам заборонений перегляд цієї сторінки!';

// Time units
$txt['lp_days_set']       = array('день','дня','днів');
$txt['lp_hours_set']      = array('година','години','годин');
$txt['lp_minutes_set']    = array('хвилину','хвилини','хвилин');
$txt['lp_seconds_set']    = array('секунду','секунды','секунд');
$txt['lp_tomorrow']       = '<strong>Завтра</strong> в ';
$txt['lp_just_now']       = 'Щойно';
$txt['lp_time_label_in']  = 'Через %1$s';
$txt['lp_time_label_ago'] = ' тому';

// Social units
$txt['lp_posts_set']    = array('повідомлення', 'повідомлення', 'повідомлень');
$txt['lp_replies_set']  = array('відповідь', 'відповіді', 'відповідей');
$txt['lp_views_set']    = array('перегляд', 'перегляду', 'переглядів');
$txt['lp_comments_set'] = array('коментар', 'коментарі', 'коментарів');

// Other units
$txt['lp_users_set']   = array('користувач', 'користувача', 'користувачів');
$txt['lp_guests_set']  = array('гість', 'гостя', 'гостей');
$txt['lp_spiders_set'] = array('павук', 'павука', 'павуків');
$txt['lp_hidden_set']  = array('прихований', 'прихованих');
$txt['lp_buddies_set'] = array('друг', 'друга', 'друзів');

// Credits
$txt['lp_used_components'] = 'Компоненти порталу';

// Debug info
$txt['lp_load_page_stats'] = 'Завантажено за %1$.3f сек. Запитів до бази: %2$d.';
