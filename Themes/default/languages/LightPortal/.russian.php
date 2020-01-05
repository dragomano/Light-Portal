<?php

/**
 * .russian language file
 *
 * @package Light Portal
 * @author Bugo https://dragomano.ru/mods/light-portal
 */

$txt['lp_portal'] = 'Портал';
$txt['lp_forum']  = 'Форум';

// Settings
$txt['lp_settings']                            = 'Настройки портала';
$txt['lp_php_mysql_info']                      = 'Версия мода: <strong>%1$s</strong>, версия PHP: <strong>%2$s</strong>, версия %3$s: <strong>%4$s</strong>.';
$txt['lp_main_page_title']                     = 'Заголовок главной страницы';
$txt['lp_main_page_disable']                   = 'Отключить главную страницу';
$txt['lp_standalone']                          = 'Автономный режим';
$txt['lp_standalone_help']                     = 'Будет отключено всё, кроме страниц портала и игнорируемых областей.';
$txt['lp_standalone_excluded_actions']         = 'Игнорируемые области';
$txt['lp_standalone_excluded_actions_subtext'] = 'Укажите области, которые должны оставаться доступными в автономном режиме.';
$txt['lp_page_editor_type_default']            = 'Тип редактора страниц по умолчанию';
$txt['lp_num_per_page']                        = 'Максимальное количество элементов в списке страниц (для пагинации)';
$txt['groups_light_portal_view']               = 'Кто может просматривать элементы портала';
$txt['groups_light_portal_manage']             = 'Кто может управлять порталом';

// Actions
$txt['lp_title']       = 'Заголовок';
$txt['lp_actions']     = 'Действия';
$txt['lp_action_on']   = 'Включить';
$txt['lp_action_off']  = 'Отключить';
$txt['lp_action_move'] = 'Переместить';

// Blocks
$txt['lp_blocks']                        = 'Блоки';
$txt['lp_blocks_manage']                 = 'Управление блоками';
$txt['lp_blocks_manage_tab_description'] = 'Здесь перечислены все используемые блоки портала. Для добавления дополнительного блока воспользуйтесь соответствующей кнопкой.';
$txt['lp_blocks_add']                    = 'Добавить блок';
$txt['lp_blocks_add_title']              = 'Добавление блока';
$txt['lp_blocks_add_tab_description']    = 'Блоков пока немного, но самые универсальные есть — играйтесь :)';
$txt['lp_blocks_add_instruction']        = 'Выберите нужный блок, нажав на него.';
$txt['lp_blocks_edit_title']             = 'Редактирование блока';
$txt['lp_blocks_edit_tab_description']   = $txt['lp_blocks_add_tab_description'];
$txt['lp_block_content']                 = 'Содержимое';
$txt['lp_block_icon_cheatsheet']         = '<div class="smalltext"><a href="https://fontawesome.com/cheatsheet/free/solid" target="_blank" rel="noopener">Больше иконок</a></div>';
$txt['lp_block_type']                    = 'Тип блока';
$txt['lp_block_priority']                = 'Приоритет';
$txt['lp_block_placement']               = 'Расположение';

$txt['lp_block_placement_set'] = array(
    'header' => 'Шапка',
    'top'    => 'Центральная часть (верх)',
    'left'   => 'Левая панель',
    'right'  => 'Правая панель',
    'bottom' => 'Центральная часть (низ)',
    'footer' => 'Подвал'
);

$txt['lp_block_areas']         = 'Области';
$txt['lp_block_areas_subtext'] = '<div class="infobox smalltext">Укажите одну или несколько областей (через запятую) для отображения в них блока:<br>
<ul>
    <li><strong>all</strong> — отображать везде</li>
    <li><strong>forum</strong> — отображать только на форуме</li>
    <li><strong>portal</strong> — отображать только на портале (включая страницы)</li>
    <li><strong>custom_action</strong> — отображать в области <em>index.php?action</em>=<strong>custom_action</strong></li>
    <li><strong>page=alias</strong> — отображать на странице <em>index.php?page</em>=<strong>alias</strong></li>
</ul></div>';
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
    'bbc'  => 'В этом блоке в качестве контента можно использовать любые ББ-теги форума.',
    'html' => 'В этом блоке в качестве контента можно использовать любые теги HTML.',
    'php'  => 'В этом блоке в качестве контента можно использовать произвольный код PHP.'
);

// Pages
$txt['lp_pages']                        = 'Страницы';
$txt['lp_pages_main']                   = 'Главная страница';
$txt['lp_pages_manage']                 = 'Управление страницами';
$txt['lp_pages_manage_tab_description'] = 'Здесь перечислены все страницы портала. Для добавления новой страницы воспользуйтесь соответствующей кнопкой.';
$txt['lp_pages_add']                    = 'Добавить страницу';
$txt['lp_pages_add_title']              = 'Добавление страницы';
$txt['lp_pages_add_tab_description']    = 'Обратите особое внимание на <strong>алиас</strong> страницы — он используется в адресной строке и может содержать только латинские символы и цифры!<br>У главной страницы алиас всегда равен "/".';
$txt['lp_pages_edit_title']             = 'Редактирование страницы';
$txt['lp_pages_edit_tab_description']   = $txt['lp_pages_add_tab_description'];
$txt['lp_extra_pages']                  = 'Дополнительные страницы';
$txt['lp_page_types']                   = array('bbc' => 'ББ-код', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                   = 'Алиас';
$txt['lp_page_content']                 = $txt['lp_block_content'];
$txt['lp_page_type']                    = 'Тип страницы';
$txt['lp_page_description']             = 'Описание';
$txt['lp_page_keywords']                = 'Ключевые слова';
$txt['lp_permissions']                  = array('Показывать админам', 'Показывать гостям', 'Показывать пользователям', 'Показывать всем');
$txt['lp_no_items']                     = 'Пока ничего нет. Давайте добавим?';

// Errors
$txt['lp_page_not_found']             = 'Страница не найдена!';
$txt['lp_block_not_found']            = 'Блок не найден!';
$txt['lp_post_error_no_title']        = 'Не указан заголовок!';
$txt['lp_post_error_no_alias']        = 'Не указан алиас!';
$txt['lp_post_error_no_valid_alias']  = 'Указанный алиас не правильный!';
$txt['lp_post_error_no_unique_alias'] = 'Страница с таким алиасом уже существует!';
$txt['lp_post_error_no_content']      = 'Не указано содержание!';
$txt['lp_post_error_no_areas']        = 'Не указана область размещения!';

// Who
$txt['lp_who_main'] = 'Просматривает <a href="%1$s">главную страницу портала</a>.';
$txt['lp_who_page'] = 'Просматривает <a href="%1$s">страницу портала</a>.';

// Permissions
$txt['permissiongroup_light_portal']       = LP_NAME;
$txt['permissionname_light_portal_view']   = $txt['group_perms_name_light_portal_view']   = 'Просмотр портала';
$txt['permissionname_light_portal_manage'] = $txt['group_perms_name_light_portal_manage'] = 'Управление порталом';
$txt['permissionhelp_light_portal_view']   = 'Возможность просматривать страницы и блоки портала.';
$txt['permissionhelp_light_portal_manage'] = 'Доступ к управлению страницами и блоками портала, а также к его настройкам.';
$txt['cannot_light_portal_view']           = 'Извините, вам запрещен просмотр портала!';
$txt['cannot_light_portal_manage']         = 'Извините, вам запрещено управление порталом!';
$txt['cannot_light_portal_view_page']      = 'Извините, вам не разрешен просмотр этой страницы!';
