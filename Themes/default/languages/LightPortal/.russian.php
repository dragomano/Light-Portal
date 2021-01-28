<?php

/**
 * .russian language file
 *
 * @package Light Portal
 */

$txt['lp_portal'] = 'Портал';
$txt['lp_forum']  = 'Форум';

$txt['lp_new_version_is_available'] = 'Доступна новая версия!';

$txt['lp_article']  = 'Статья';
$txt['lp_no_items'] = 'Нет элементов для отображения.';
$txt['lp_example']  = 'Пример: ';
$txt['lp_content']  = 'Содержимое';
$txt['lp_my_pages'] = 'Мои страницы';
$txt['lp_views']    = 'Просмотры';
$txt['lp_replies']  = 'Ответы';

// Settings
$txt['lp_settings']  = 'Настройки портала';
$txt['lp_base']      = 'Настройки главной страницы и статей';
$txt['lp_base_info'] = 'Версия мода: <strong>%1$s</strong>, версия PHP: <strong>%2$s</strong>, версия %3$s: <strong>%4$s</strong>.<br>Обсудить на русском баги и фичи портала можно на <a class="bbc_link" href="https://dragomano.ru/forum">форуме разработчика</a>.<br>Вы также можете <a class="bbc_link" href="https://boosty.to/bugo">стать спонсором на Boosty.to</a> или <a class="bbc_link" href="https://ko-fi.com/U7U41XD2G">купить разработчику чашку кофе</a>.';

$txt['lp_frontpage_title']    = 'Заголовок главной страницы';
$txt['lp_frontpage_mode']     = 'Первая страница портала';
$txt['lp_frontpage_mode_set'] = array(
	'Отключить',
	'chosen_page'   => 'Указанная страница',
	'all_topics'    => 'Все темы из выбранных разделов',
	'all_pages'     => 'Все активные страницы портала',
	'chosen_boards' => 'Выбранные разделы',
	'chosen_topics' => 'Выбранные темы',
	'chosen_pages'  => 'Выбранные страницы'
);
$txt['lp_frontpage_alias']                   = 'Страница портала для отображения в качестве главной';
$txt['lp_frontpage_alias_subtext']           = 'Укажите алиас существующей страницы.';
$txt['lp_frontpage_boards']                  = 'Разделы-источники статей для первой страницы';
$txt['lp_frontpage_topics']                  = 'Темы-источники статей для первой страницы';
$txt['lp_frontpage_topics_subtext']          = 'Идентификаторы нужных тем, через запятую.';
$txt['lp_frontpage_pages']                   = 'Страницы-источники статей для первой страницы';
$txt['lp_frontpage_pages_subtext']           = 'Идентификаторы нужных страниц, через запятую.';
$txt['lp_show_images_in_articles']           = 'Показывать изображения, найденные в статьях';
$txt['lp_show_images_in_articles_help']      = 'Сначала проверяется, есть ли в статье вложение (если статья основана на теме форума), затем — есть ли в статье тег IMG с картинкой.';
$txt['lp_image_placeholder']                 = 'URL-адрес картинки-заглушки по умолчанию';
$txt['lp_frontpage_time_format']             = 'Формат времени в карточках статей';
$txt['lp_frontpage_time_format_set']         = array('Полный', 'Как на форуме', 'Свой');
$txt['lp_frontpage_custom_time_format']      = 'Свой формат времени';
$txt['lp_frontpage_custom_time_format_help'] = 'См. список возможных параметров в <a class="bbc_link" href="https://www.php.net/manual/ru/datetime.format.php">документации</a>.';
$txt['lp_show_teaser']                       = 'Отображать краткое содержание (тизер) статей';
$txt['lp_teaser_size']                       = 'Размер тизера статей (в символах)';
$txt['lp_show_author']                       = 'Отображать информацию об авторе статьи';
$txt['lp_show_author_help']                  = 'В случае вывода карточки раздела это будет информация о категории.';
$txt['lp_show_num_views_and_comments']       = 'Отображать информацию о количестве просмотров и комментариев';
$txt['lp_frontpage_order_by_num_replies']    = 'Первыми выводить статьи с наибольшим количеством комментариев';
$txt['lp_frontpage_article_sorting']         = 'Сортировка статей';
$txt['lp_frontpage_article_sorting_set']     = array('По последнему комментарию', 'По дате создания (новые вверху)', 'По дате создания (старые вверху)', 'По дате обновления (свежие вверху)');
$txt['lp_frontpage_article_sorting_help']    = 'При выборе первого варианта в карточках статей отображаются даты и авторы последних комментариев (при их наличии).';
$txt['lp_frontpage_layout']                  = 'Количество колонок для вывода статей';
$txt['lp_frontpage_layout_set']              = array('1 колонка', '2 колонки', '3 колонки', '4 колонки', '6 колонок');
$txt['lp_num_items_per_page']                = 'Количество элементов на странице (для пагинации)';

$txt['lp_standalone_mode_title']                    = 'Автономный режим';
$txt['lp_standalone_url']                           = 'URL-адрес главной страницы портала в автономном режиме';
$txt['lp_standalone_url_help']                      = 'Можно указать свой URL-адрес для отображения в качестве главной страницы портала (например, <strong>https://yourforum/portal.php</strong>).<br>В этом случае главная страница форума останется доступной по адресу <strong>https://yourforum/index.php</strong>.<br><br>В качестве примера в комплекте с порталом идёт файл <em>portal.php</em> — можете использовать его.<br><br>Отключите параметр «<strong>Использовать локальное хранение куки</strong>», если хотите разместить <em>portal.php</em> вне директории форума (Обслуживание => Настройки сервера => Куки и сессии).';
$txt['lp_standalone_mode_disabled_actions']         = 'Отключаемые области';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Укажите области, которые должны быть ОТКЛЮЧЕНЫ в автономном режиме.';
$txt['lp_standalone_mode_disabled_actions_help']    = 'Например, если нужно отключить область «Поиск» (index.php?action=<strong>search</strong>), добавьте <strong>search</strong> в текстовое поле.';

$txt['groups_light_portal_view']             = 'Кто может просматривать элементы портала';
$txt['groups_light_portal_manage_blocks']    = 'Кто может управлять блоками';
$txt['groups_light_portal_manage_own_pages'] = 'Кто может управлять своими страницами';
$txt['groups_light_portal_approve_pages']    = 'Кто может размещать свои страницы без модерации';
$txt['lp_manage_permissions']                = 'Некоторые страницы могут содержать опасный HTML/PHP контент, поэтому не разрешайте их создавать всем подряд!';

// Pages and blocks
$txt['lp_extra']      = 'Страницы и блоки';
$txt['lp_extra_info'] = 'Здесь находятся общие настройки страниц и блоков.';

$txt['lp_show_page_permissions']            = 'Отображать информацию о правах доступа к странице';
$txt['lp_show_page_permissions_subtext']    = 'Видят только те, у кого есть право редактирования страницы.';
$txt['lp_show_tags_on_page']                = 'Отображать ключевые слова в верхней части страницы';
$txt['lp_show_items_as_articles']           = 'Отображать элементы на страницах тегов и рубрик в виде карточек';
$txt['lp_show_related_pages']               = 'Отображать блок похожих страниц';
$txt['lp_show_comment_block']               = 'Отображать блок комментариев';
$txt['lp_disabled_bbc_in_comments']         = 'Разрешённые ББ-теги в комментариях';
$txt['lp_disabled_bbc_in_comments_subtext'] = 'Можно задействовать любые теги, <a class="bbc_link" href="%1$s">разрешённые для использования</a> на форуме.';
$txt['lp_show_comment_block_set']           = array('none' => 'Нет', 'default' => 'Встроенный');
$txt['lp_time_to_change_comments']          = 'Время, в течение которого можно изменить свой комментарий';
$txt['lp_num_comments_per_page']            = 'Количество родительских комментариев на странице';
$txt['lp_page_editor_type_default']         = 'Тип редактора страниц по умолчанию';
$txt['lp_permissions_default']              = 'Права доступа для страниц и блоков по умолчанию';
$txt['lp_hide_blocks_in_admin_section']     = 'Скрывать активные блоки в админке';

$txt['lp_schema_org']            = 'Микроразметка Schema для контактов';
$txt['lp_page_og_image']         = 'Использовать изображение из текста статьи';
$txt['lp_page_og_image_set']     = array('Нет', 'Первое найденное', 'Последнее найденное');
$txt['lp_page_itemprop_address'] = 'Адрес вашей организации';
$txt['lp_page_itemprop_phone']   = 'Телефон вашей организации';

$txt['lp_permissions'] = array('Показывать админам', 'Показывать гостям', 'Показывать пользователям', 'Показывать всем');

// Categories
$txt['lp_categories']                 = 'Рубрики';
$txt['lp_categories_info']            = 'Здесь можно создать и отредактировать рубрики портала, для категоризации страниц.<br>Для изменения порядка рубрики просто перетащите её на новую позицию.';
$txt['lp_categories_manage']          = 'Управление рубриками';
$txt['lp_categories_add']             = 'Добавить рубрику';
$txt['lp_categories_desc']            = 'Описание';
$txt['lp_category']                   = 'Рубрика';
$txt['lp_no_category']                = 'Без рубрики';
$txt['lp_all_categories']             = 'Все рубрики портала';
$txt['lp_all_pages_with_category']    = 'Все страницы в рубрике «%1$s»';
$txt['lp_all_pages_without_category'] = 'Все страницы без рубрики';
$txt['lp_category_not_found']         = 'Указанная рубрика не найдена.';
$txt['lp_no_categories']              = 'Рубрик пока нет.';
$txt['lp_total_pages_column']         = 'Всего страниц';

// Panels
$txt['lp_panels']               = 'Панели';
$txt['lp_panels_info']          = 'Здесь можно настроить ширину некоторых панелей, а также направление блоков.<br><strong>%1$s</strong> использует <a class="bbc_link" href="%2$s" target="_blank" rel="noopener">12-колоночную сетку</a> для отображения блоков в 6 панелях.';
$txt['lp_swap_header_footer']   = 'Поменять местами шапку и подвал';
$txt['lp_swap_left_right']      = 'Поменять местами левую и правую панели';
$txt['lp_swap_top_bottom']      = 'Поменять местами верхнюю и нижнюю центральные панели';
$txt['lp_panel_layout_preview'] = 'На макете ниже можно задать количество колонок для некоторых панелей, в зависимости от ширины окна браузера.';
$txt['lp_left_panel_sticky']    = $txt['lp_right_panel_sticky'] = 'Закрепить';
$txt['lp_panel_direction_note'] = 'Здесь можно изменить направление блоков для каждой панели.';
$txt['lp_panel_direction']      = 'Направление блоков в панелях';
$txt['lp_panel_direction_set']  = array('Вертикальное', 'Горизонтальное');

// Разное
$txt['lp_misc']                           = 'Дополнительно';
$txt['lp_misc_info']                      = 'Здесь находятся дополнительные настройки портала, которые пригодятся разработчикам шаблонов и плагинов.';
$txt['lp_fontawesome_compat_themes']      = 'Отметьте темы, которые используют иконки Font Awesome';
$txt['lp_fontawesome_compat_themes_help'] = 'Опция совместимости с шаблонами, использующими иконки Font Awesome.';
$txt['lp_debug_and_caching']              = 'Отладка и кэширование';
$txt['lp_show_debug_info']                = 'Отображать время загрузки и количество запросов портала';
$txt['lp_show_debug_info_help']           = 'Информация будет доступна только администраторам!';
$txt['lp_cache_update_interval']          = 'Интервал обновления кэша';

// Actions
$txt['lp_title']        = 'Заголовок';
$txt['lp_actions']      = 'Действия';
$txt['lp_action_on']    = 'Включить';
$txt['lp_action_off']   = 'Отключить';
$txt['lp_action_clone'] = 'Клонировать';
$txt['lp_action_move']  = 'Переместить';
$txt['lp_read_more']    = 'Читать далее...';

// Blocks
$txt['lp_blocks']                    = 'Блоки';
$txt['lp_blocks_manage']             = 'Управление блоками';
$txt['lp_blocks_manage_description'] = 'Здесь перечислены все используемые блоки портала. Для добавления дополнительного блока воспользуйтесь соответствующей кнопкой.';
$txt['lp_blocks_add']                = 'Добавить блок';
$txt['lp_blocks_add_title']          = 'Добавление блока';
$txt['lp_blocks_add_description']    = 'Блоки могут содержать любой контент, в зависимости от своего типа.';
$txt['lp_blocks_add_instruction']    = 'Выберите нужный блок, нажав на него.';
$txt['lp_blocks_edit_title']         = 'Редактирование блока';
$txt['lp_blocks_edit_description']   = $txt['lp_blocks_add_description'];
$txt['lp_block_icon_cheatsheet']     = 'Список иконок';
$txt['lp_block_type']                = 'Тип блока';
$txt['lp_block_note']                = 'Примечание';
$txt['lp_block_priority']            = 'Приоритет';
$txt['lp_block_icon_type']           = 'Тип иконки';
$txt['lp_block_icon_type_set']       = array('fas' => 'Solid', 'far' => 'Regular', 'fab' => 'Brands');
$txt['lp_block_placement']           = 'Расположение';
$txt['lp_block_placement_set']       = array(
	'header' => 'Шапка',
	'top'    => 'Центральная часть (верх)',
	'left'   => 'Левая панель',
	'right'  => 'Правая панель',
	'bottom' => 'Центральная часть (низ)',
	'footer' => 'Подвал'
);

$txt['lp_block_areas']            = 'Области';
$txt['lp_block_areas_subtext']    = 'Укажите одну или несколько областей (через запятую) для отображения в них блока:';
$txt['lp_block_areas_area_th']    = 'Область';
$txt['lp_block_areas_display_th'] = 'Отображение';
$txt['lp_block_areas_values']     = array(
	'везде',
	'в области <em>index.php?action</em>=<strong>custom_action</strong> (например: portal,forum,search)',
	'на всех страницах портала',
	'на странице <em>index.php?page</em>=<strong>alias</strong>',
	'во всех разделах',
	'в разделе с идентификатором <strong>id</strong> (включая все темы внутри раздела)',
	'в разделах id1, id2, id3',
	'в разделах id3 и id7',
	'во всех темах',
	'в теме с идентификатором <strong>id</strong>',
	'в темах id1, id2, id3',
	'в темах id3 и id7'
);

$txt['lp_block_title_class']   = 'CSS класс заголовка';
$txt['lp_block_title_style']   = 'CSS стили заголовка';
$txt['lp_block_content_class'] = 'CSS класс содержимого';
$txt['lp_block_content_style'] = 'CSS стили содержимого';

$txt['lp_block_types'] = array(
	'bbc'  => 'Блок с ББ-кодом',
	'html' => 'Блок с HTML-кодом',
	'php'  => 'Блок с PHP-кодом'
);
$txt['lp_block_types_descriptions'] = array(
	'bbc'  => 'В этом блоке в качестве контента можно использовать любые форумные теги.',
	'html' => 'В этом блоке в качестве контента можно использовать любые теги HTML.',
	'php'  => 'В этом блоке в качестве контента можно использовать произвольный код PHP.'
);

// Pages
$txt['lp_pages']                     = 'Страницы';
$txt['lp_pages_manage']              = 'Управление страницами';
$txt['lp_pages_manage_all_pages']    = 'Здесь перечислены все страницы портала.';
$txt['lp_pages_manage_own_pages']    = 'Здесь перечислены все созданные вами страницы.';
$txt['lp_pages_manage_description']  = 'Для добавления новой страницы воспользуйтесь соответствующей кнопкой.';
$txt['lp_pages_add']                 = 'Добавить страницу';
$txt['lp_pages_add_title']           = 'Добавление страницы';
$txt['lp_pages_add_description']     = 'Заполните заголовок страницы. После этого можно будет сменить её тип, использовать предварительный просмотр и сохранение.';
$txt['lp_pages_edit_title']          = 'Редактирование страницы';
$txt['lp_pages_edit_description']    = 'Внесите необходимые изменения.';
$txt['lp_pages_extra']               = 'Страницы портала';
$txt['lp_pages_search']              = 'Алиас или заголовок';
$txt['lp_page_types']                = array('bbc' => 'ББ-код', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                = 'Алиас';
$txt['lp_page_alias_subtext']        = 'Алиас страницы должен начинаться с латинской буквы и состоять из строчных латинских букв, цифр и знака подчеркивания.';
$txt['lp_page_type']                 = 'Тип страницы';
$txt['lp_page_description']          = 'Описание';
$txt['lp_page_keywords']             = 'Ключевые слова';
$txt['lp_page_keywords_placeholder'] = 'Выберите теги или добавьте новые';
$txt['lp_page_publish_datetime']     = 'Дата и время публикации';
$txt['lp_page_author']               = 'Передача авторства';
$txt['lp_page_author_placeholder']   = 'Укажите имя пользователя для передачи прав на страницу';
$txt['lp_page_author_search_length'] = 'Введите не менее 3 символов';

$txt['lp_page_options'] = array(
	'show_author_and_date' => 'Показывать автора и дату создания',
	'show_related_pages'   => 'Показывать похожие страницы',
	'allow_comments'       => 'Разрешить комментарии'
);

// Tabs
$txt['lp_tab_content']          = 'Контент';
$txt['lp_tab_seo']              = 'SEO';
$txt['lp_tab_access_placement'] = 'Доступ и размещение';
$txt['lp_tab_appearance']       = 'Оформление';
$txt['lp_tab_tuning']           = 'Тюнинг';

// Import and Export
$txt['lp_pages_export']              = 'Экспорт страниц';
$txt['lp_pages_import']              = 'Импорт страниц';
$txt['lp_pages_export_description']  = 'Здесь можно экспортировать нужные страницы для создания резервной копии или для переноса на другой форум.';
$txt['lp_pages_import_description']  = 'Здесь можно импортировать из резервной копии сохраненные ранее страницы портала.';
$txt['lp_blocks_export']             = 'Экспорт блоков';
$txt['lp_blocks_import']             = 'Импорт блоков';
$txt['lp_blocks_export_description'] = 'Здесь можно экспортировать нужные блоки для создания резервной копии или для переноса на другой форум.';
$txt['lp_blocks_import_description'] = 'Здесь можно импортировать из резервной копии сохраненные ранее блоки портала.';
$txt['lp_export_run']                = 'Экспортировать выделенные';
$txt['lp_import_run']                = 'Импортировать';
$txt['lp_export_all']                = 'Экспортировать все';

// Plugins
$txt['lp_plugins']                    = 'Плагины';
$txt['lp_plugins_manage']             = 'Управление плагинами';
$txt['lp_plugins_manage_description'] = 'Здесь перечислены установленные плагины портала. Вы всегда можете создать новый, воспользовавшись <a class="bbc_link" href="%1$s" target="_blank" rel="noopener">инструкцией</a> или кнопочкой «+» ниже.';
$txt['lp_plugins_desc']               = 'Плагины расширяют возможности портала и его компонентов, предоставляя дополнительные функции, которых нет в ядре.';
$txt['lp_plugins_add']                = 'Добавить плагин';
$txt['lp_plugins_add_title']          = 'Добавление плагина';
$txt['lp_plugins_add_description']    = 'Мастер создания плагинов поможет подготовить болванку для дальнейших изменений. Внимательно заполните предлагаемые поля.';
$txt['lp_plugins_add_information']    = 'Файлы плагина будут сохранены в директории %1$s<br>Обязательно загляните туда и проверьте/отредактируйте нужные файлы.';

$txt['lp_plugins_tab_content']    = 'Основные данные';
$txt['lp_plugins_tab_copyrights'] = 'Авторские права';
$txt['lp_plugins_tab_settings']   = 'Настройки';
$txt['lp_plugins_tab_tuning']     = 'Дополнительно';

$txt['lp_plugins_hooks_types'] = array(
	'block'     => 'Блок',
	'editor'    => 'Редактор',
	'comment'   => 'Виджет комментариев',
	'parser'    => 'Парсер контента',
	'article'   => 'Обработка статей',
	'frontpage' => 'Макет главной страницы',
	'impex'     => 'Импорт и экспорт',
	'other'     => 'Разное'
);

$txt['lp_plugin_name']              = 'Название плагина';
$txt['lp_plugin_name_subtext']      = 'Латинскими буквами, без пробелов!';
$txt['lp_plugin_type']              = 'Тип плагина';
$txt['lp_plugin_site_subtext']      = 'Сайт, на котором можно будет скачать новые версии этого плагина.';
$txt['lp_plugin_license']           = 'Лицензия плагина';
$txt['lp_plugin_license_own']       = 'Своя лицензия';
$txt['lp_plugin_license_name']      = 'Название лицензии';
$txt['lp_plugin_license_link']      = 'Ссылка на лицензию';
$txt['lp_plugin_smf_hooks']         = 'Используются хуки SMF?';
$txt['lp_plugin_components']        = 'Используются сторонние скрипты?';
$txt['lp_plugin_components_name']   = 'Название компонента';
$txt['lp_plugin_components_link']   = 'Ссылка на сайт компонента';
$txt['lp_plugin_components_author'] = 'Автор компонента';

$txt['lp_plugin_option_name']  = 'Имя опции (латиница)';
$txt['lp_plugin_option_type']  = 'Тип опции';
$txt['lp_plugin_option_types'] = array(
	'text'       => 'Текстовое поле',
	'url'        => 'Веб-адрес',
	'color'      => 'Выбор цвета',
	'int'        => 'Ввод чисел',
	'check'      => 'Поле-флажок',
	'multicheck' => 'Список с выбором нескольких значений',
	'select'     => 'Список'
);

$txt['lp_plugin_option_default_value']        = 'Значение по умолчанию';
$txt['lp_plugin_option_variants']             = 'Возможные значения';
$txt['lp_plugin_option_variants_placeholder'] = 'Несколько вариантов через запятую';
$txt['lp_plugin_option_translations']         = 'Локализация';
$txt['lp_plugin_new_option']                  = 'Добавить опцию';

// Tags
$txt['lp_all_page_tags']          = 'Все теги страниц портала';
$txt['lp_all_tags_by_key']        = 'Все страницы с тегом «%1$s»';
$txt['lp_tag_not_found']          = 'Указанный тег не найден.';
$txt['lp_no_tags']                = 'Тегов пока нет.';
$txt['lp_keyword_column']         = 'Ключевое слово';
$txt['lp_frequency_column']       = 'Частотность';
$txt['lp_sorting_label']          = 'Сортировка';
$txt['lp_sort_by_title_desc']     = 'По заголовку (по убыванию)';
$txt['lp_sort_by_title']          = 'По заголовку (по возрастанию)';
$txt['lp_sort_by_created_desc']   = 'По дате создания (сначала новые)';
$txt['lp_sort_by_created']        = 'По дате создания (сначала старые)';
$txt['lp_sort_by_updated_desc']   = 'По дате обновления (сначала новые)';
$txt['lp_sort_by_updated']        = 'По дате обновления (сначала старые)';
$txt['lp_sort_by_author_desc']    = 'По автору (по убыванию)';
$txt['lp_sort_by_author']         = 'По автору (по возрастанию)';
$txt['lp_sort_by_num_views_desc'] = 'По количеству просмотров (по убыванию)';
$txt['lp_sort_by_num_views']      = 'По количеству просмотров (по возрастанию)';

// Related pages
$txt['lp_related_pages'] = 'Похожие страницы';

// Comments
$txt['lp_comments']            = 'Комментарии';
$txt['lp_comment_placeholder'] = 'Введите текст комментария...';

// Comment alerts
$txt['alert_group_light_portal']           = LP_NAME;
$txt['alert_page_comment']                 = 'При размещении нового комментария к моей странице';
$txt['alert_new_comment_page_comment']     = '{member_link} оставил(а) комментарий {page_comment_new_comment}';
$txt['alert_page_comment_reply']           = 'При получении ответа на мой комментарий';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} ответил(а) на ваш комментарий {page_comment_reply_new_reply}';

// Errors
$txt['lp_page_not_found']             = 'Страница не найдена!';
$txt['lp_page_not_activated']         = 'Запрашиваемая страница отключена!';
$txt['lp_page_not_editable']          = 'Вам запрещено редактирование этой страницы!';
$txt['lp_page_visible_but_disabled']  = 'Страница видна вам, но не активирована!';
$txt['lp_block_not_found']            = 'Блок не найден!';
$txt['lp_post_error_no_title']        = 'Не указан заголовок!';
$txt['lp_post_error_no_alias']        = 'Не указан алиас!';
$txt['lp_post_error_no_valid_alias']  = 'Указанный алиас не правильный!';
$txt['lp_post_error_no_unique_alias'] = 'Страница с таким алиасом уже существует!';
$txt['lp_post_error_no_content']      = 'Не указано содержание!';
$txt['lp_post_error_no_areas']        = 'Не указана область размещения!';
$txt['lp_post_error_no_valid_areas']  = 'Область размещения задана неправильно!';
$txt['lp_post_error_no_name']         = 'Не указано имя!';
$txt['lp_post_error_no_valid_name']   = 'Указанное имя не соответствует правилам!';
$txt['lp_post_error_no_unique_name']  = 'Плагин с таким именем уже существует!';
$txt['lp_post_error_no_description']  = 'Не указано описание!';
$txt['lp_addon_not_installed']        = 'Плагин %1$s не установлен';
$txt['lp_addon_add_failed']           = 'Директория <strong>/Sources/LightPortal/addons</strong> должна иметь права на запись!';
$txt['lp_wrong_import_file']          = 'Неправильный файл для импорта...';
$txt['lp_import_failed']              = 'Не удалось осуществить импорт...';

// Who
$txt['lp_who_viewing_frontpage']       = 'Просматривает <a href="%1$s">главную страницу портала</a>.';
$txt['lp_who_viewing_index']           = 'Просматривает главную страницу <a href="%1$s">портала</a> или <a href="%2$s">форума</a>.';
$txt['lp_who_viewing_page']            = 'Просматривает <a href="%1$s">страницу портала</a>.';
$txt['lp_who_viewing_tags']            = 'Просматривает <a href="%1$s">теги страниц портала</a>.';
$txt['lp_who_viewing_the_tag']         = 'Просматривает список страниц с тегом <a href="%1$s" class="bbc_link">%2$s</a>.';
$txt['lp_who_viewing_portal_settings'] = 'Просматривает или изменяет <a href="%1$s">настройки портала</a>.';
$txt['lp_who_viewing_portal_blocks']   = 'Просматривает <a href="%1$s">блоки портала</a> в админке.';
$txt['lp_who_viewing_editing_block']   = 'Редактирует блок портала (#%1$d).';
$txt['lp_who_viewing_adding_block']    = 'Добавляет блок портала.';
$txt['lp_who_viewing_portal_pages']    = 'Просматривает <a href="%1$s">страницы портала</a> в админке.';
$txt['lp_who_viewing_editing_page']    = 'Редактирует страницу портала (#%1$d).';
$txt['lp_who_viewing_adding_page']     = 'Добавляет страницу портала.';

// Permissions
$txt['permissiongroup_light_portal']                 = LP_NAME;
$txt['permissionname_light_portal_view']             = $txt['group_perms_name_light_portal_view']             = 'Просмотр элементов портала';
$txt['permissionname_light_portal_manage_blocks']    = $txt['group_perms_name_light_portal_manage_blocks']    = 'Управление блоками';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Управление своими страницами';
$txt['permissionname_light_portal_approve_pages']    = $txt['group_perms_name_light_portal_approve_pages']    = 'Публикация страниц без модерации';
$txt['permissionhelp_light_portal_view']             = 'Возможность просматривать страницы и блоки портала.';
$txt['permissionhelp_light_portal_manage_blocks']    = 'Доступ к управлению блоками портала.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Доступ к управлению своими страницами.';
$txt['permissionhelp_light_portal_approve_pages']    = 'Возможность размещать свои страницы без модерации.';
$txt['cannot_light_portal_view']                     = 'Извините, вам запрещен просмотр портала!';
$txt['cannot_light_portal_manage_blocks']            = 'Извините, вам запрещено управление блоками портала!';
$txt['cannot_light_portal_manage_own_pages']         = 'Извините, вам запрещено управление страницами портала!';
$txt['cannot_light_portal_approve_pages']            = 'Извините, вам запрещено размещать страницы без модерации!';
$txt['cannot_light_portal_view_page']                = 'Извините, вам не разрешен просмотр этой страницы!';

// Time units
$txt['lp_days_set']       = array('день', 'дня', 'дней');
$txt['lp_hours_set']      = array('час', 'часа', 'часов');
$txt['lp_minutes_set']    = array('минуту', 'минуты', 'минут');
$txt['lp_seconds_set']    = array('секунду', 'секунды', 'секунд');
$txt['lp_tomorrow']       = '<strong>Завтра</strong> в ';
$txt['lp_just_now']       = 'Только что';
$txt['lp_time_label_in']  = 'Через %1$s';
$txt['lp_time_label_ago'] = ' назад';

// Social units
$txt['lp_posts_set']    = array('сообщение', 'сообщения', 'сообщений');
$txt['lp_replies_set']  = array('ответ', 'ответа', 'ответов');
$txt['lp_views_set']    = array('просмотр', 'просмотра', 'просмотров');
$txt['lp_comments_set'] = array('комментарий', 'комментария', 'комментариев');

// Other units
$txt['lp_users_set']   = array('пользователь', 'пользователя', 'пользователей');
$txt['lp_guests_set']  = array('гость', 'гостя', 'гостей');
$txt['lp_spiders_set'] = array('бот', 'бота', 'ботов');
$txt['lp_hidden_set']  = array('скрытый', 'скрытых');
$txt['lp_buddies_set'] = array('друг', 'друга', 'друзей');

// Credits
$txt['lp_used_components'] = 'Компоненты портала';

// Debug info
$txt['lp_load_page_stats'] = 'Загружено за %1$.3f сек. Запросов к базе данных: %2$d.';
