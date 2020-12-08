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
$txt['lp_content']  = 'Вміст';

// Settings
$txt['lp_settings']  = 'Налаштування порталу';
$txt['lp_base']      = 'Налаштування головної сторінки і статей';
$txt['lp_base_info'] = 'Версія моду: <strong>%1$s</strong>, версія PHP: <strong>%2$s</strong>, версія %3$s: <strong>%4$s</strong>.<br>Обговорити баги і фічі порталу можна на <a class="bbc_link" href="https://dragomano.ru/forum">форумі розробника</a>.<br>Ви також можете стати спонсором на <a class="bbc_link" href="https://www.patreon.com/bugo">Patreon</a> або на <a class="bbc_link" href="https://boosty.to/bugo">Boosty.to</a>.';

$txt['lp_frontpage_title']                = 'Заголовок головної сторінки';
$txt['lp_frontpage_mode']                 = 'Перша сторінка порталу';
$txt['lp_frontpage_mode_set']             = array('Відключивши', 'Зазначена сторінка', 'Всі теми з обраних розділів', 'Всі активні сторінки порталу', 'Вибрані розділи');
$txt['lp_frontpage_alias']                = 'Сторінка порталу для відображення в якості головної';
$txt['lp_frontpage_alias_subtext']        = 'Вкажіть аліас існуючої сторінк.';
$txt['lp_frontpage_boards']               = 'Розділи-джерела статей для першої сторінки';
$txt['lp_frontpage_card_alt_layout']      = 'Альтернативне розташування елементів в картках';
$txt['lp_frontpage_card_alt_layout_help'] = 'Дата і автор міняються місцями.';
$txt['lp_frontpage_order_by_num_replies'] = 'Першими виводити статті з найбільшою кількістю коментарів';
$txt['lp_frontpage_article_sorting']      = 'Сортування статей';
$txt['lp_frontpage_article_sorting_set']  = array('За останнім коментарем', 'За датою створення (нові вгорі)', 'За датою створення (старі вгорі)', 'За датою оновлення (свіжі вгорі)');
$txt['lp_frontpage_article_sorting_help'] = 'При виборі першого варіанту в картках статей відображаються дати і автори останніх коментарів (при їх наявності).';
$txt['lp_frontpage_layout']               = 'Кількість колонок для виведення статей';
$txt['lp_frontpage_layout_set']           = array('1 колонка', '2 колонки', '3 колонки', '4 колонки', '6 колонок');
$txt['lp_show_images_in_articles']        = 'Показувати зображення, знайдені в статтях';
$txt['lp_show_images_in_articles_help']   = 'Спочатку перевіряється, чи є в статті вкладення (якщо стаття заснована на темі форуму), потім — чи є в статті тег IMG з картинкою.';
$txt['lp_image_placeholder']              = 'URL-адреса картинки-заглушки за замовчуванням';
$txt['lp_teaser_size']                    = 'Розмір тизера статей (у символах)';
$txt['lp_teaser_size_help']               = 'Використовується в деяких аддонах, а також може використовуватися в призначених для користувача макетах головної сторінки.';
$txt['lp_num_items_per_page']             = 'Кількість елементів на сторінці (для пагінації)';

$txt['lp_standalone_mode_title'] = 'Автономний режим';
$txt['lp_standalone_url']        = 'URL-адреса головної сторінки порталу в автономному режимі';
$txt['lp_standalone_url_help']   = 'Можна вказати свою URL-адресу для відображення в якості головної сторінки порталу (наприклад, <strong>https://yourforum/portal.php</strong>).<br>У цьому випадку головна сторінка форуму залишиться доступною за адресою <strong>https://yourforum/index.php</strong>.<br><br>Щоб вивести головну сторінку порталу, у файлі <em>portal.php</em> повинен бути приблизно такий код:<br><pre><code class="bbc_code">
require(dirname(__FILE__) . \'/SSI.php\');
<br>
(new Bugo\LightPortal\FrontPage)->show();</code></pre><br>
Вимкніть параметр "<strong>Enable local storage of cookies</strong>", якщо файл <em>portal.php</em> знаходиться поза директорії форуму (Maintenance => Настроювання Сервера => Cookies and Sessions).';
$txt['lp_standalone_mode_disabled_actions']         = 'Області, що відключаються';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Вкажіть області, які повинні бути ВІДКЛЮЧЕНІ в автономному режимі';
$txt['lp_standalone_mode_disabled_actions_help']    = 'Наприклад, якщо потрібно відключити область "Пошук" (index.php?action=<strong>search</strong>), додайте <strong>search</strong> в текстове поле.';

$txt['groups_light_portal_view']             = 'Хто може переглядати елементи порталу';
$txt['groups_light_portal_manage_blocks']    = 'Хто може керувати блоками';
$txt['groups_light_portal_manage_own_pages'] = 'Хто може керувати своїми сторінками';
$txt['groups_light_portal_approve_pages']    = 'Хто може розміщувати свої сторінки без модерації';
$txt['lp_manage_permissions']                = 'Увага: деякі сторінки i блоки можуть містити небезпечний HTML/PHP контент, тому не надавайте це право всім підряд!';

// Pages and blocks
$txt['lp_extra']      = 'Сторінки і блоки';
$txt['lp_extra_info'] = 'Тут знаходяться загальні налаштування сторінок і блоків.';

$txt['lp_show_tags_on_page']            = 'Відображати ключові слова у верхній частині сторінки';
$txt['lp_show_tags_as_articles']        = 'Відображати списки статей з однаковим тегом у вигляді карток';
$txt['lp_show_related_pages']           = 'Відображати блок схожих сторінок';
$txt['lp_show_comment_block']           = 'Відображати блок коментарів';
$txt['lp_disabled_bbc_in_comments']     = 'Дозволені ББ-теги в коментарях';
$txt['lp_show_comment_block_set']       = array('none' => 'Ні', 'default' => 'Вбудований');
$txt['lp_time_to_change_comments']      = 'Час, протягом якого можна змінити свій коментар';
$txt['lp_num_comments_per_page']        = 'Кількість батьківських коментарів на сторінці';
$txt['lp_page_editor_type_default']     = 'Тип редактора сторінок за замовчуванням';
$txt['lp_hide_blocks_in_admin_section'] = 'Приховувати активні блоки в адмінці';

$txt['lp_open_graph']            = 'Open Graph';
$txt['lp_page_og_image']         = 'Використовувати зображення з тексту статті';
$txt['lp_page_og_image_set']     = array('Ні', 'Перше знайдене', 'Останнє знайдене');
$txt['lp_page_itemprop_address'] = 'Адреса вашої організації';
$txt['lp_page_itemprop_phone']   = 'Телефон вашої організації';

$txt['lp_permissions'] = array('Показувати адмінам', 'Показувати гостям', 'Показувати користувачам', 'Показувати всім');

// Panels
$txt['lp_panels']               = 'Панелі';
$txt['lp_panels_info']          = 'Тут можна налаштувати ширину деяких панелей, а також напрямок блоків.<br><strong>%1$s</strong> використовує <a class="bbc_link" href="%2$s" target="_blank" rel="noopener">12-колонкову сітку</a> для відображення блоків у 6 панелях.';
$txt['lp_swap_header_footer']   = 'Поміняти місцями шапку і підвал';
$txt['lp_swap_left_right']      = 'Поміняти місцями ліву і праву панелі';
$txt['lp_swap_top_bottom']      = 'Поміняти місцями верхню і нижню центральні панелі';
$txt['lp_panel_layout_note']    = 'Змінюйте ширину вікна браузера і дивіться, який клас використовується.';
$txt['lp_browser_width']        = 'Ширина вікна браузера';
$txt['lp_used_class']           = 'Використовуваний клас';
$txt['lp_panel_layout_preview'] = 'На макеті нижче можна задати кількість колонок для деяких панелей, в залежності від ширини вікна браузера.';
$txt['lp_left_panel_sticky']    = $txt['lp_right_panel_sticky'] = 'Закріпити';
$txt['lp_panel_direction_note'] = 'Тут можна змінити напрямок блоків для кожної панелі.';
$txt['lp_panel_direction']      = 'Напрямок блоків в панелях';
$txt['lp_panel_direction_set']  = array('Вертикальне', 'Горизонтальне');

// Plugins
$txt['lp_plugins']      = 'Плагіни';
$txt['lp_plugins_desc'] = 'Будь-який з плагінів можна включити або виключити. А деякі ще й налаштувати!';
$txt['lp_plugins_info'] = 'Тут перераховані встановлені плагіни порталу. Ви завжди можете створити новий, скориставшись <a class="bbc_link" href="%1$s" target="_blank" rel="noopener">інструкцією</a>.';

$txt['lp_plugins_hooks_types'] = array(
	'block'     => 'Блок',
	'editor'    => 'Редактор',
	'comment'   => 'Віджет коментарів',
	'parser'    => 'Парсер контента',
	'article'   => 'Обробка статей',
	'frontpage' => 'Макет головної сторінки',
	'impex'     => 'Імпорт та експорт',
	'other'     => 'Різне'
);

// Misc
$txt['lp_misc']                           = 'Додатково';
$txt['lp_misc_info']                      = 'Тут знаходяться додаткові налаштування порталу, які стануть в нагоді розробникам шаблонів і плагінів.';
$txt['lp_fontawesome_compat_themes']      = 'Відзначте теми, які підтримують (включають в себе) іконки Font Awesome';
$txt['lp_fontawesome_compat_themes_help'] = 'Опція сумісності з шаблонами, що використовують іконки Font Awesome.';
$txt['lp_debug_and_caching']              = 'Налагодження та кешування';
$txt['lp_show_debug_info']                = 'Відображати час завантаження та кількість запитів порталу';
$txt['lp_show_debug_info_help']           = 'Інформація буде доступна тільки адміністраторам!';
$txt['lp_cache_update_interval']          = 'Інтервал оновлення кешу';

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
$txt['lp_blocks_add_tab_description']    = 'Блоки можуть містити будь-який контент, в залежності від свого типу.';
$txt['lp_blocks_add_instruction']        = 'Оберіть потрібний блок, натиснувши на нього.';
$txt['lp_blocks_edit_title']             = 'Редагування блоку';
$txt['lp_blocks_edit_tab_description']   = $txt['lp_blocks_add_tab_description'];
$txt['lp_block_icon_cheatsheet']         = 'Список іконок';
$txt['lp_block_type']                    = 'Тип блоку';
$txt['lp_block_note']                    = 'Примітка';
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

$txt['lp_block_areas']            = 'Області';
$txt['lp_block_areas_subtext']    = 'Вкажіть одну або кілька областей (через кому) для відображення в них блоку:';
$txt['lp_block_areas_area_th']    = 'Область';
$txt['lp_block_areas_display_th'] = 'Відображення';
$txt['lp_block_areas_values']     = array(
	'всюди',
	'в області <em>index.php?action</em>=<strong>custom_action</strong> (наприклад: portal,forum,search)',
	'на всіх сторінках порталу',
	'на сторінці <em>index.php?page</em>=<strong>alias</strong>',
	'у всіх розділах',
	'в розділі з ідентифікатором <strong>id</strong> (включаючи всі теми всередині розділу)',
	'в розділах id1, id2, id3',
	'в розділах id3 i id7',
	'у всіх темах',
	'в темі з ідентифікатором <strong>id</strong>',
	'в темах id1, id2, id3',
	'в темах id3 і id7'
);

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
$txt['lp_pages_manage_all_pages']       = 'Тут перераховані всі сторінки порталу.';
$txt['lp_pages_manage_own_pages']       = 'Тут перераховані всі створені вами сторінки.';
$txt['lp_pages_manage_tab_description'] = 'Для додавання нової сторінки скористайтеся відповідною кнопкою.';
$txt['lp_pages_add']                    = 'Додати сторінку';
$txt['lp_pages_add_title']              = 'Додавання сторінки';
$txt['lp_pages_add_tab_description']    = 'Заповніть заголовок сторінки. Після цього можна буде змінити її тип, використовувати попередній перегляд і збереження.';
$txt['lp_pages_edit_title']             = 'Редагування сторінки';
$txt['lp_pages_edit_tab_description']   = 'Внесіть необхідні зміни.';
$txt['lp_extra_pages']                  = 'Сторінки порталу';
$txt['lp_search_pages']                 = 'Аліас або заголовок';
$txt['lp_page_types']                   = array('bbc' => 'ББ-код', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                   = 'Аліас';
$txt['lp_page_alias_subtext']           = 'Аліас сторінки має починатися з латинської літери і складатися з малих латинських букв, цифр і знака підкреслення.';
$txt['lp_page_type']                    = 'Тип сторінки';
$txt['lp_page_description']             = 'Опис';
$txt['lp_page_keywords']                = 'Ключові слова';
$txt['lp_page_keywords_only_unique']    = 'Можна додавати тільки унікальні теги';
$txt['lp_page_keywords_enter_to_add']   = 'Натисніть Enter для додавання <b>«${value}»</b>';
$txt['lp_page_publish_datetime']        = 'Дата і час публікації';

$txt['lp_page_options'] = array(
	'show_author_and_date' => 'Показувати автора і дату створення',
	'show_related_pages'   => 'Показувати схожі сторінки',
	'allow_comments'       => 'Дозволити коментарі'
);

// Tabs
$txt['lp_tab_content']          = 'Контент';
$txt['lp_tab_seo']              = 'SEO';
$txt['lp_tab_access_placement'] = 'Доступ і розміщення';
$txt['lp_tab_appearance']       = 'Оформлення';
$txt['lp_tab_tuning']           = 'Тюнінг';

// Import and Export
$txt['lp_pages_export']                  = 'Експорт сторінок';
$txt['lp_pages_import']                  = 'Імпорт сторінок';
$txt['lp_pages_export_tab_description']  = 'Тут можна експортувати потрібні сторінки для створення резервної копії або для перенесення на інший форум.';
$txt['lp_pages_import_tab_description']  = 'Тут можна імпортувати з резервної копії збережені раніше сторінки порталу.';
$txt['lp_blocks_export']                 = 'Експорт блоків';
$txt['lp_blocks_import']                 = 'Імпорт блоків';
$txt['lp_blocks_export_tab_description'] = 'Тут можна експортувати потрібні блоки для створення резервної копії або для перенесення на інший форум.';
$txt['lp_blocks_import_tab_description'] = 'Тут можна імпортувати з резервної копії збережені раніше блокі порталу.';
$txt['lp_export_run']                    = 'Експортувати виділені';
$txt['lp_import_run']                    = 'Імпортувати';
$txt['lp_export_all']                    = 'Експортувати всі';

// Tags
$txt['lp_all_page_tags']          = 'Всі теги сторінок порталу';
$txt['lp_all_tags_by_key']        = 'Всі сторінки з тегом "%1$s"';
$txt['lp_no_selected_tag']        = 'Вказаний тег не знайдено.';
$txt['lp_no_tags']                = 'Тегів поки немає.';
$txt['lp_keyword_column']         = 'Ключове слово';
$txt['lp_frequency_column']       = 'Частотність';
$txt['lp_sorting_label']          = 'Сортування';
$txt['lp_sort_by_created_desc']   = 'За датою створення (спочатку нові)';
$txt['lp_sort_by_created']        = 'За датою створення (спочатку старі)';
$txt['lp_sort_by_updated_desc']   = 'За датою оновлення (спочатку нові)';
$txt['lp_sort_by_updated']        = 'За датою оновлення (спочатку старі';
$txt['lp_sort_by_author_desc']    = 'За автором (за спаданням)';
$txt['lp_sort_by_author']         = 'За автором (за зростанням)';
$txt['lp_sort_by_num_views_desc'] = 'За кількістю переглядів (за спаданням)';
$txt['lp_sort_by_num_views']      = 'За кількістю переглядів (за зростанням)';

// Related pages
$txt['lp_related_pages'] = 'Схожі сторінки';

// Comments
$txt['lp_comments']            = 'Коментарi';
$txt['lp_comment_placeholder'] = 'Введіть текст коментаря...';

// Comment alerts
$txt['alert_group_light_portal']           = LP_NAME;
$txt['alert_page_comment']                 = 'При розміщенні нового коментаря до моєї сторінки';
$txt['alert_new_comment_page_comment']     = '{member_link} залишив(а) коментар {page_comment_new_comment}';
$txt['alert_page_comment_reply']           = 'При отриманні відповіді на мій коментар';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} відповів (а) на ваш коментар {page_comment_reply_new_reply}';

// Errors
$txt['lp_page_not_found']             = 'Сторінку не знайдено!';
$txt['lp_page_not_activated']         = 'Запитувана сторінка відключена!';
$txt['lp_page_not_editable']          = 'Вам заборонено редагування цієї сторінки!';
$txt['lp_page_visible_but_disabled']  = 'Сторінка видно вам, але не активована!';
$txt['lp_block_not_found']            = 'Блок не знайдений!';
$txt['lp_post_error_no_title']        = 'Не вказаний заголовок!';
$txt['lp_post_error_no_alias']        = 'Не вказаний аліас!';
$txt['lp_post_error_no_valid_alias']  = 'Зазначений аліас неправильний!';
$txt['lp_post_error_no_unique_alias'] = 'Сторінка з таким аліасом вже існує!';
$txt['lp_post_error_no_content']      = 'Не вказано зміст!';
$txt['lp_post_error_no_areas']        = 'Не вказана область разташування!';
$txt['lp_post_error_no_valid_areas']  = 'Область розміщення задана неправильно!';
$txt['lp_addon_not_installed']        = 'Плагін %1$s не встановлений';
$txt['lp_wrong_import_file']          = 'Неправильний файл для імпорту...';
$txt['lp_import_failed']              = 'Не вдалося здійснити імпорт...';

// Who
$txt['lp_who_viewing_frontpage']       = 'Переглядає <a href="%1$s">головну сторінку порталу</a>.';
$txt['lp_who_viewing_index']           = 'Переглядає головну сторінку <a href="%1$s">порталу</a> або <a href="%2$s">форуму</a>.';
$txt['lp_who_viewing_page']            = 'Переглядає <a href="%1$s">сторінку порталу</a>.';
$txt['lp_who_viewing_tags']            = 'Переглядає <a href="%1$s">теги сторінок порталу</a>.';
$txt['lp_who_viewing_the_tag']         = 'Переглядає список сторінок з тегом <a href="%1$s" class="bbc_link">%2$s</a>.';
$txt['lp_who_viewing_portal_settings'] = 'Переглядає або змінює <a href="%1$s">налаштування порталу</a>.';
$txt['lp_who_viewing_portal_blocks']   = 'Переглядає <a href="%1$s">блоки порталу</a> в адмінці.';
$txt['lp_who_viewing_editing_block']   = 'Редагує блок порталу (#%1$d).';
$txt['lp_who_viewing_adding_block']    = 'Додає блок порталу.';
$txt['lp_who_viewing_portal_pages']    = 'Переглядає <a href="%1$s">сторінки порталу</a> в адмінці.';
$txt['lp_who_viewing_editing_page']    = 'Редагує сторінку порталу (#%1$d).';
$txt['lp_who_viewing_adding_page']     = 'Додає сторінку порталу.';

// Permissions
$txt['permissiongroup_light_portal']                 = LP_NAME;
$txt['permissionname_light_portal_view']             = $txt['group_perms_name_light_portal_view']             = 'Перегляд елементів порталу';
$txt['permissionname_light_portal_manage_blocks']    = $txt['group_perms_name_light_portal_manage_blocks']    = 'Управління блоками';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Управління своїми сторінками';
$txt['permissionname_light_portal_approve_pages']    = $txt['group_perms_name_light_portal_approve_pages']    = 'Публікація сторінок без модерації';
$txt['permissionhelp_light_portal_view']             = 'Можливість переглядати сторінки і блоки порталу.';
$txt['permissionhelp_light_portal_manage_blocks']    = 'Доступ до управління блоками порталу.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Доступ до управління своїми сторінками.';
$txt['permissionhelp_light_portal_approve_pages']    = 'Можливість розміщувати свої сторінки без модерації.';
$txt['cannot_light_portal_view']                     = 'Вибачте, вам заборонений перегляд порталу!';
$txt['cannot_light_portal_manage_blocks']            = 'Вибачте, вам заборонено керування блоками порталу!';
$txt['cannot_light_portal_manage_own_pages']         = 'Вибачте, вам заборонено Керування сторінками порталу!';
$txt['cannot_light_portal_approve_pages']            = 'Вибачте, вам заборонено розміщувати сторінки без модерації!';
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
