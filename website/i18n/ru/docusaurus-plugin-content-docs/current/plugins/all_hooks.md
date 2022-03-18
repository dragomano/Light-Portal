---
sidebar_position: 3
---

# Хуки портала
Light Portal замечательно расширяется благодаря плагинам. А хуки помогают плагинам взаимодействовать с различными компонентами портала.

## Основные хуки

### init
> переопределение $txt-переменных, подключение хуков SMF и т. д.

### prepareEditor
(`$context['lp_block']` для блока, `$context['lp_page']` для страницы)
> добавление вашего кода в области редактирования контента страниц/блоков

### parseContent
(`&$content, $type`)
> парсинг контента произвольных типов блоков/страниц

### prepareContent
(`&$content, $type, $block_id, $type`)
> добавление индивидуального контента внутри плагина

### credits
(`&$links`)
> добавление копирайтов используемых библиотек и скриптов

### addAdminAreas
(`&$admin_areas`)
> добавление произвольных областей через стандартный хук SMF integrate_admin_areas

## Работа с блоками

### blockOptions
(`&$options`)
> добавление параметров блоков

### prepareBlockFields
> добавление полей в окне редактирования блоков

### validateBlockData
(`&$parameters, $context['current_block']['type']`)
> добавление правил валидации при создании/редактировании блоков

### findBlockErrors
(`$data, &$post_errors`)
> добавление пользовательской обработки ошибок при создании/редактировании блоков

### onBlockSaving
(`$item`)
> выполнение вашего кода при сохранении настроек блоков

### onBlockRemoving
(`$items`)
> выполнение вашего кода при удалении блоков

## Работа со страницами

### pageOptions
(`&$options`)
> добавление параметров страниц

### preparePageFields
> добавление полей в окне редактирования страниц

### validatePageData
(`&$parameters`)
> добавление правил валидации при создании/редактировании страниц

### findPageErrors
(`$data, &$post_errors`)
> добавление пользовательской обработки ошибок при создании/редактировании страниц

### onPageSaving
(`$item`)
> выполнение вашего кода при сохранении настроек страниц

### onPageRemoving
(`$items`)
> выполнение вашего кода при удалении страниц

### preparePageData
(`&$data`, `$is_author`)
> дополнительная обработка данных текущей страницы портала

### comments
> добавление виджетов комментариев в нижней части текущей страницы портала

## Работа с плагинами

### addSettings
(`&$config_vars`)
> добавление индивидуальных настроек плагинов

### saveSettings
(`&$plugin_options`)
> выполнение вашего кода при сохранении настроек плагинов

## Настройки портала

### addBasicSettings / addBasicSaveSettings
(`&$config_vars`) / (`&$save_vars`)
> добавление и сохранение настроек на вкладке «Общие настройки»

### addExtraSettings / addExtraSaveSettings
(`&$config_vars`) / (`&$save_vars`)
> добавление и сохранение настроек на вкладке «Страницы и блоки»

### addPanelsSettings / addPanelsSaveSettings
(`&$config_vars`) / (`&$save_vars`)
> добавление и сохранение настроек на вкладке «Панели»

### addMiscSettings / addMiscSaveSettings
(`&$config_vars`) / (`&$save_vars`)
> добавление и сохранение настроек на вкладке «Дополнительно»

### addBlockAreas
(`&$subActions`)
> добавление дополнительных вкладок в области «Блоки»

### addPageAreas
(`&$subActions`)
> добавление дополнительных вкладок в области «Страницы»

## Работа со статьями

### frontModes
(`&$this->modes`)
> добавление собственных режимов для главной страницы

### frontCustomTemplate
> добавление собственных шаблонов для главной страницы

### frontAssets
> добавление собственных скриптов и стилей для главной страницы

### frontTopics
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> добавление дополнительных полей, таблиц, условий «Where», параметров и сортировок в функции _init_

### frontTopicsOutput
(`&$topics, $row`)
> различные манипуляции с результатами запроса в функции _getData_

### frontPages
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> добавление дополнительных полей, таблиц, условий «Where», параметров и сортировок в функции _init_

### frontPagesOutput
(`&$pages, $row`)
> различные манипуляции с результатами запроса в функции _getData_

### frontBoards
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> добавление дополнительных полей, таблиц, условий «Where», параметров и сортировок в функции _init_

### frontBoardsOutput
(`&$boards, $row`)
> различные манипуляции с результатами запроса в функции _getData_

## Работа с иконками

### prepareIconList
(`&$all_icons, &$template`)
> добавление собственного списка иконок (вместо FontAwesome)

### prepareIconTemplate
(`&$template, $icon`)
> добавление собственного шаблона для отображения иконок

### changeIconSet
(`&$set`)
> возможность добавить или переопределить текущие иконки интерфейса

Не так уж и много, Карл?