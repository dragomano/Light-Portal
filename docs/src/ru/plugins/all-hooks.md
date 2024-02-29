---
description: Список всех доступных хуков портала
order: 4
---

# Хуки портала

Light Portal замечательно расширяется благодаря плагинам. А хуки помогают плагинам взаимодействовать с различными компонентами портала.

## Основные хуки

### init

> переопределение $txt-переменных, подключение хуков SMF и т. д.

```php
public function init(): void
{
    // integrate_actions hook
    $this->applyHook('actions');
}

public function actions(): void
{
    if ($this->request()->is(LP_ACTION) && $this->request()->has('turbo'))
        $this->showXml();
}
```

### parseContent

(`&$content, $type`)

> парсинг контента произвольных типов блоков/страниц

```php
public function parseContent(string &$content, string $type): void
{
    if ($type === 'markdown')
        $content = $this->getParsedContent($content);
}
```

### prepareContent

(`$data, $parameters`)

> добавление индивидуального контента внутри плагина

```php
public function prepareContent($data): void
{
    if ($data->type !== 'user_info')
        return;

    $this->setTemplate();

    $userData = $this->cache('user_info_addon_u' . Utils::$context['user']['id'])
        ->setLifeTime($data->cache_time)
        ->setFallback(self::class, 'getData');

    show_user_info($userData);
}
```

### prepareEditor

(`$context['lp_block']` for block, `$context['lp_page']` for page)

> добавление вашего кода в области редактирования контента страниц/блоков

```php
public function prepareEditor(array $object): void
{
    if ($object['type'] !== 'markdown')
        return;

    $this->loadLanguage('Editor');

    $this->loadExtCSS('https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.css');

    $this->addInlineCss('
    .editor-toolbar button {
        box-shadow: none;
    }');
}
```

### preloadStyles

(`&$styles`)

> помогает с предварительной загрузкой необходимых вам таблиц стилей

::: code-group

```php [PHP]
public function preloadStyles(array &$styles): void
{
    $styles[] = 'https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons@1/css/all/all.css';
}
```

```html [HTML]
<link
  rel="preload"
  href="https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons@1/css/all/all.css"
  as="style"
  onload="this.onload=null;this.rel='stylesheet'"
/>
```

:::

## Работа с блоками

### prepareBlockParams

(`&$params`)

> добавление параметров блоков

```php
public function prepareBlockParams(array &$params): void
{
    if (Utils::$context['current_block']['type'] !== 'article_list')
        return;

    $params = [
        'body_class'     => 'descbox',
        'display_type'   => 0,
        'include_topics' => '',
        'include_pages'  => '',
        'seek_images'    => false
    ];
}
```

### validateBlockParams

(`&$params`)

> добавление правил валидации при создании/редактировании блоков

```php
public function validateBlockParams(array &$params): void
{
    if (Utils::$context['current_block']['type'] !== 'article_list')
        return;

    $params = [
        'body_class'     => FILTER_DEFAULT,
        'display_type'   => FILTER_VALIDATE_INT,
        'include_topics' => FILTER_DEFAULT,
        'include_pages'  => FILTER_DEFAULT,
        'seek_images'    => FILTER_VALIDATE_BOOLEAN,
    ];
}
```

### findBlockErrors

(`&$errors, $data`)

> добавление пользовательской обработки ошибок при создании/редактировании блоков

```php
public function findBlockErrors(array &$errors, array $data): void
{
    if ($data['placement'] !== 'ads')
        return;

    Lang::$txt['lp_post_error_no_ads_placement'] = Lang::$txt['lp_ads_block']['no_ads_placement'];

    if (empty($data['parameters']['ads_placement']))
        $errors[] = 'no_ads_placement';
}
```

### prepareBlockFields

> добавление полей в окне редактирования блоков

```php
public function prepareBlockFields(): void
{
    if (Utils::$context['current_block']['type'] !== 'article_list')
        return;

    RadioField::make('display_type', Lang::$txt['lp_article_list']['display_type'])
        ->setTab('content')
        ->setOptions(Lang::$txt['lp_article_list']['display_type_set'])
        ->setValue(Utils::$context['lp_block']['options']['display_type']);

    CheckboxField::make('seek_images', Lang::$txt['lp_article_list']['seek_images'])
        ->setValue(Utils::$context['lp_block']['options']['seek_images']);
}
```

### onBlockSaving

(`$item`)

> выполнение вашего кода при сохранении/изменении блоков

### onBlockRemoving

(`$items`)

> выполнение вашего кода при удалении блоков

## Работа со страницами

### preparePageParams

(`&$params`)

> добавление параметров страниц

```php
public function preparePageParams(array &$params): void
{
    $params['meta_robots'] = '';
    $params['meta_rating'] = '';
}
```

### validatePageParams

(`&$params`)

> добавление правил валидации при создании/редактировании страниц

```php
public function validatePageParams(array &$params): void
{
    $params['meta_robots'] = FILTER_DEFAULT;
    $params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

(`&$errors, $data`)

> добавление пользовательской обработки ошибок при создании/редактировании страниц

### preparePageFields

> добавление полей в окне редактирования страниц

```php
public function preparePageFields(): void
{
    VirtualSelectField::make('meta_robots', Lang::$txt['lp_extended_meta_tags']['meta_robots'])
        ->setTab('seo')
        ->setOptions(array_combine($this->meta_robots, Lang::$txt['lp_extended_meta_tags']['meta_robots_set']))
        ->setValue(Utils::$context['lp_page']['options']['meta_robots']);
}
```

### onPageSaving

(`$item`)

> выполнение вашего кода при сохранении/изменении страниц

### onPageRemoving

(`$items`)

> выполнение вашего кода при удалении страниц

### preparePageData

(`&$data`, `$is_author`)

> дополнительная обработка данных текущей страницы портала

```php
public function preparePageData(): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> возможность вывести что-нибудь перед контентом страницы

### afterPageContent

> возможность вывести что-нибудь после контента страницы

### comments

> добавление виджетов комментариев в нижней части текущей страницы портала

```php
public function comments(): void
{
    if (! empty(Config::$modSettings['lp_show_comment_block']) && Config::$modSettings['lp_show_comment_block'] === 'disqus' && ! empty(Utils::$context['lp_disqus_plugin']['shortname'])) {
        Utils::$context['lp_disqus_comment_block'] = '
            <div id="disqus_thread" class="windowbg"></div>
            <script>
                <!-- Your code -->
            </script>';
    }
}
```

### commentButtons

(`$comment`, `&$buttons`)

> добавление пользовательских кнопок под каждым комментарием

```php
public function commentButtons(array $comment, array &$buttons): void
{
    if (empty(Utils::$context['lp_page']['options']['allow_reactions']))
        return;

    $comment['can_react'] = $comment['poster']['id'] !== User::$info['id'];
    $comment['reactions'] = json_decode($comment['params']['reactions'] ?? '', true) ?? [];
    $comment['prepared_reactions'] = $this->getReactionsWithCount($comment['reactions']);
    $comment['prepared_buttons'] = json_decode($comment['prepared_reactions'], true);

    ob_start();

    show_comment_reactions($comment);

    $buttons[] = ob_get_clean();
}
```

## Работа с плагинами

### addSettings

(`&$settings`)

> добавление индивидуальных настроек плагинов

```php
public function addSettings(array &$settings): void
{
    $settings['disqus'][] = ['text', 'shortname', 'subtext' => Lang::$txt['lp_disqus']['shortname_subtext'], 'required' => true];
}
```

### saveSettings

(`&$settings`)

> выполнение вашего кода при сохранении настроек плагинов

### prepareAssets

(`&$assets`)

> сохранение внешних стилей, скриптов и изображений для повышения скорости загрузки ресурсов

```php
public function prepareAssets(array &$assets): void
{
    $assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Работа со статьями

### frontModes

(`&$modes`)

> добавление собственных режимов для главной страницы

```php
public function frontModes(array &$modes): void
{
    $modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> добавление пользовательской логики на главной странице

### customLayoutExtensions

(`&$extensions`)

> позволяет добавлять пользовательские расширения макетов

```php
public function customLayoutExtensions(array &$extensions): void
{
    $extensions[] = '.twig';
}
```

### frontAssets

> добавление собственных скриптов и стилей для главной страницы

```php
public function frontAssets(): void
{
    $this->loadExtJS('https://' . Utils::$context['lp_disqus_plugin']['shortname'] . '.disqus.com/count.js');
}
```

### frontTopics

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> добавление дополнительных полей, таблиц, условий «Where», параметров и сортировок в функции _init_

```php
public function frontTopics(array &$columns, array &$tables): void
{
    if (! class_exists('TopicRatingBar'))
        return;

    $columns[] = 'tr.total_votes, tr.total_value';
    $tables[]  = 'LEFT JOIN {db_prefix}topic_ratings AS tr ON (t.id_topic = tr.id)';
}
```

### frontTopicsOutput

(`&$topics, $row`)

> различные манипуляции с результатами запроса в функции _getData_

```php
public function frontTopicsOutput(array &$topics, array $row): void
{
    $topics[$row['id_topic']]['rating'] = empty($row['total_votes']) ? 0 : (number_format($row['total_value'] / $row['total_votes']));
}
```

### frontPages

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> добавление дополнительных полей, таблиц, условий «Where», параметров и сортировок в функции _init_

### frontPagesOutput

(`&$pages, $row`)

> различные манипуляции с результатами запроса в функции _getData_

### frontBoards

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> добавление дополнительных полей, таблиц, условий «Where», параметров и сортировок в функции _init_

### frontBoardsOutput

(`&$boards, $row`)

> различные манипуляции с результатами запроса в функции _getData_

## Работа с иконками

### prepareIconList

(`&$icons, &$template`)

> добавление собственного списка иконок (вместо FontAwesome)

```php
public function prepareIconList(array &$icons): void
{
    if (($mainIcons = $this->cache()->get('all_main_icons', 30 * 24 * 60 * 60)) === null) {
        $set = $this->getIconSet();

        $mainIcons = [];
        foreach ($set as $icon) {
            $mainIcons[] = $this->prefix . $icon;
        }

        $this->cache()->put('all_main_icons', $mainIcons, 30 * 24 * 60 * 60);
    }

    $icons = array_merge($icons, $mainIcons);
}
```

### prepareIconTemplate

(`&$template, $icon`)

> добавление собственного шаблона для отображения иконок

### changeIconSet

(`&$set`)

> возможность расширения иконок интерфейса, доступных через массив `Utils::$context['lp_icon_set']`

## Настройки портала

### updateAdminAreas

(`&$areas`)

> добавление пользовательских областей портала в админке

```php
public function updateAdminAreas(array &$areas): void
{
    if (User::$info['is_admin']) {
        $areas['lp_pages']['subsections']['import_from_ep'] = [
            Utils::$context['lp_icon_set']['import'] . Lang::$txt['lp_eh_portal']['label_name']
        ];
    }
}
```

### updateBlockAreas

(`&$areas`)

> добавление дополнительных вкладок в области «Блоки»

```php
public function updateBlockAreas(array &$areas): void
{
    $areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

(`&$areas`)

> добавление дополнительных вкладок в области «Страницы»

```php
public function updatePageAreas(array &$areas): void
{
    $areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

(`&$areas`)

> добавление дополнительных вкладок в области «Категории»

```php
public function updateCategoryAreas(array &$areas): void
{
    $areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

(`&$areas`)

> добавление дополнительных вкладок в области «Теги»

### updatePluginAreas

(`&$areas`)

> добавление дополнительных вкладок в области «Плагины»

```php
public function updatePluginAreas(array &$areas): void
{
    $areas['add'] = [new Handler(), 'add'];
}
```

## Дополнительно

### credits

(`&$links`)

> добавление копирайтов используемых библиотек и скриптов

```php
public function credits(array &$links): void
{
    $links[] = [
        'title' => 'Uicons',
        'link' => 'https://www.flaticon.com/uicons',
        'author' => 'Flaticon',
        'license' => [
            'name' => 'ISC License',
            'link' => 'https://www.freepikcompany.com/legal#nav-flaticon-agreement'
        ]
    ];
}
```
