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

    $userData = $this->cache('user_info_addon_u' . $this->context['user']['id'])
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

### preloadScripts

(`&$scripts`)

> помогает с предварительной загрузкой необходимых вам скриптов

::: code-group

```php [PHP]
public function preloadScripts(array &$scripts): void
{
    $scripts[] = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/js/all.min.js';
}
```

```html [HTML]
<link
  rel="preload"
  href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/js/all.min.js"
  as="script"
/>
```

:::

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

### blockOptions

(`&$options`)

> добавление параметров блоков

```php
public function blockOptions(array &$options): void
{
    $options['article_list']['no_content_class'] = true;

    $options['article_list']['parameters'] = [
        'body_class'     => 'descbox',
        'display_type'   => 0,
        'include_topics' => '',
        'include_pages'  => '',
        'seek_images'    => false
    ];
}
```

### prepareBlockFields

> добавление полей в окне редактирования блоков

```php
public function prepareBlockFields(): void
{
    if ($this->context['lp_block']['type'] !== 'article_list')
        return;

    RadioField::make('display_type', $this->txt['lp_article_list']['display_type'])
        ->setTab('content')
        ->setOptions($this->txt['lp_article_list']['display_type_set'])
        ->setValue($this->context['lp_block']['options']['parameters']['display_type']);

    CheckboxField::make('seek_images', $this->txt['lp_article_list']['seek_images'])
        ->setValue($this->context['lp_block']['options']['parameters']['seek_images']);
}
```

### validateBlockData

(`&$parameters, $context['current_block']['type']`)

> добавление правил валидации при создании/редактировании блоков

```php
public function validateBlockData(array &$parameters, string $type): void
{
    if ($type !== 'article_list')
        return;

    $parameters['body_class']     = FILTER_DEFAULT;
    $parameters['display_type']   = FILTER_VALIDATE_INT;
    $parameters['include_topics'] = FILTER_DEFAULT;
    $parameters['include_pages']  = FILTER_DEFAULT;
    $parameters['seek_images']    = FILTER_VALIDATE_BOOLEAN;
}
```

### findBlockErrors

(`&$post_errors, $data`)

> добавление пользовательской обработки ошибок при создании/редактировании блоков

```php
public function findBlockErrors(array &$post_errors, array $data): void
{
    if ($data['placement'] !== 'ads')
        return;

    $this->txt['lp_post_error_no_ads_placement'] = $this->txt['lp_ads_block']['no_ads_placement'];

    if (empty($data['parameters']['ads_placement']))
        $post_errors[] = 'no_ads_placement';
}
```

### onBlockSaving

(`$item`)

> выполнение вашего кода при сохранении блоков

### onBlockRemoving

(`$items`)

> выполнение вашего кода при удалении блоков

## Работа со страницами

### pageOptions

(`&$options`)

> добавление параметров страниц

```php
public function pageOptions(array &$options): void
{
    $options['meta_robots'] = '';
    $options['meta_rating'] = '';
}
```

### preparePageFields

> добавление полей в окне редактирования страниц

```php
public function preparePageFields(): void
{
    VirtualSelectField::make('meta_robots', $this->txt['lp_extended_meta_tags']['meta_robots'])
        ->setTab('seo')
        ->setOptions(array_combine($this->meta_robots, $this->txt['lp_extended_meta_tags']['meta_robots_set']))
        ->setValue($this->context['lp_page']['options']['meta_robots']);
}
```

### validatePageData

(`&$parameters`)

> добавление правил валидации при создании/редактировании страниц

```php
public function validatePageData(array &$parameters): void
{
    $parameters += [
        'meta_robots' => FILTER_DEFAULT,
        'meta_rating' => FILTER_DEFAULT,
    ];
}
```

### findPageErrors

(`&$post_errors, $data`)

> добавление пользовательской обработки ошибок при создании/редактировании страниц

### onPageSaving

(`$item`)

> выполнение вашего кода при сохранении страниц

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
    if (! empty($this->modSettings['lp_show_comment_block']) && $this->modSettings['lp_show_comment_block'] === 'disqus' && ! empty($this->context['lp_disqus_plugin']['shortname'])) {
        $this->context['lp_disqus_comment_block'] = '
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
    if (empty($this->context['lp_page']['options']['allow_reactions']))
        return;

    $comment['can_react'] = $comment['poster']['id'] !== $this->user_info['id'];
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

(`&$config_vars`)

> добавление индивидуальных настроек плагинов

```php
public function addSettings(array &$config_vars): void
{
    $config_vars['disqus'][] = ['text', 'shortname', 'subtext' => $this->txt['lp_disqus']['shortname_subtext'], 'required' => true];
}
```

### saveSettings

(`&$plugin_options`)

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

    $this->modSettings['lp_frontpage_mode'] = $this->mode;
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
    $this->loadExtJS('https://' . $this->context['lp_disqus_plugin']['shortname'] . '.disqus.com/count.js');
}
```

### frontTopics

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> добавление дополнительных полей, таблиц, условий «Where», параметров и сортировок в функции _init_

```php
public function frontTopics(array &$custom_columns, array &$custom_tables): void
{
    if (! class_exists('TopicRatingBar'))
        return;

    $custom_columns[] = 'tr.total_votes, tr.total_value';
    $custom_tables[]  = 'LEFT JOIN {db_prefix}topic_ratings AS tr ON (t.id_topic = tr.id)';
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

(`&$all_icons, &$template`)

> добавление собственного списка иконок (вместо FontAwesome)

```php
public function prepareIconList(array &$all_icons): void
{
    if (($icons = $this->cache()->get('all_main_icons', 30 * 24 * 60 * 60)) === null) {
        $set = $this->getIconSet();

        $icons = [];
        foreach ($set as $icon) {
            $icons[] = $this->prefix . $icon;
        }

        $this->cache()->put('all_main_icons', $icons, 30 * 24 * 60 * 60);
    }

    $all_icons = array_merge($all_icons, $icons);
}
```

### prepareIconTemplate

(`&$template, $icon`)

> добавление собственного шаблона для отображения иконок

### changeIconSet

(`&$set`)

> возможность расширения иконок интерфейса, доступных через массив `$this->context['lp_icon_set']`

## Настройки портала

### updateAdminAreas

(`&$areas`)

> добавление пользовательских областей портала в админке

```php
public function updateAdminAreas(array &$areas): void
{
    if ($this->user_info['is_admin'])
        $areas['lp_pages']['subsections']['import_from_ep'] = [$this->context['lp_icon_set']['import'] . $this->txt['lp_eh_portal']['label_name']];
}
```

### updateBlockAreas

(`&$areas`)

> добавление дополнительных вкладок в области «Блоки»

```php
public function updateBlockAreas(array &$areas): void
{
    if ($this->user_info['is_admin'])
        $areas['import_from_tp'] = [new BlockImport, 'main'];
}
```

### updatePageAreas

(`&$areas`)

> добавление дополнительных вкладок в области «Страницы»

```php
public function updatePageAreas(array &$areas): void
{
    if ($this->user_info['is_admin'])
        $areas['import_from_ep'] = [new Import, 'main'];
}
```

### updatePluginAreas

(`&$areas`)

> добавление дополнительных вкладок в области «Плагины»

```php
public function updatePluginAreas(array &$areas): void
{
    $areas['add'] = [new Handler, 'add'];
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
