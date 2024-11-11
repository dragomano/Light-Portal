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
    /* call integrate_actions hook */
    $this->applyHook('actions');
}

/* integrate_actions hook */
public function actions(): void
{
    if ($this->request()->is(LP_ACTION) && $this->request()->has('turbo')) {
        $this->showXml();
    }
}
```

### parseContent

> парсинг контента произвольных типов блоков/страниц

```php
public function parseContent(Event $e): void
{
    if ($e->args->type === 'markdown') {
        $e->args->content = $this->getParsedContent($e->args->content);
    }
}
```

### prepareContent

> добавление индивидуального контента внутри плагина

```php
public function prepareContent(Event $e): void
{
    if ($e->args->data->type !== 'user_info')
        return;

    $this->setTemplate();

    $userData = $this->cache('user_info_addon_u' . Utils::$context['user']['id'])
        ->setLifeTime($e->args->data->cache_time)
        ->setFallback(self::class, 'getData');

    show_user_info($userData);
}
```

### prepareEditor

> добавление вашего кода в области редактирования контента страниц/блоков

```php
public function prepareEditor(Event $e): void
{
    if ($e->args->object['type'] !== 'markdown')
        return;

    Lang::load('Editor');

    Theme::loadCSSFile('https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.css', ['external' => true]);

    Theme::addInlineCss('
    .editor-toolbar button {
        box-shadow: none;
    }');
}
```

### preloadStyles

> помогает с предварительной загрузкой необходимых вам таблиц стилей

::: code-group

```php [PHP]
public function preloadStyles(Event $e): void
{
    $e->args->styles[] = 'https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons@1/css/all/all.css';
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

> добавление параметров блоков

```php
public function prepareBlockParams(Event $e): void
{
    if (Utils::$context['current_block']['type'] !== 'article_list')
        return;

    $e->args->params = [
        'body_class'     => 'descbox',
        'display_type'   => 0,
        'include_topics' => '',
        'include_pages'  => '',
        'seek_images'    => false
    ];
}
```

### validateBlockParams

> добавление правил валидации при создании/редактировании блоков

```php
public function validateBlockParams(Event $e): void
{
    if (Utils::$context['current_block']['type'] !== 'article_list')
        return;

    $e->args->params = [
        'body_class'     => FILTER_DEFAULT,
        'display_type'   => FILTER_VALIDATE_INT,
        'include_topics' => FILTER_DEFAULT,
        'include_pages'  => FILTER_DEFAULT,
        'seek_images'    => FILTER_VALIDATE_BOOLEAN,
    ];
}
```

### findBlockErrors

> добавление пользовательской обработки ошибок при создании/редактировании блоков

```php
public function findBlockErrors(Event $e): void
{
    if ($e->args->data['placement'] !== 'ads')
        return;

    Lang::$txt['lp_post_error_no_ads_placement'] = Lang::$txt['lp_ads_block']['no_ads_placement'];

    if (empty($e->args->data['parameters']['ads_placement'])) {
        $e->args->errors[] = 'no_ads_placement';
    }
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
        ->setTab(BlockArea::TAB_CONTENT)
        ->setOptions(Lang::$txt['lp_article_list']['display_type_set'])
        ->setValue(Utils::$context['lp_block']['options']['display_type']);

    CheckboxField::make('seek_images', Lang::$txt['lp_article_list']['seek_images'])
        ->setValue(Utils::$context['lp_block']['options']['seek_images']);
}
```

### onBlockSaving

> выполнение вашего кода при сохранении/изменении блоков

### onBlockRemoving

> выполнение вашего кода при удалении блоков

## Работа со страницами

### preparePageParams

> добавление параметров страниц

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> добавление правил валидации при создании/редактировании страниц

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> добавление пользовательской обработки ошибок при создании/редактировании страниц

### preparePageFields

> добавление полей в окне редактирования страниц

```php
public function preparePageFields(): void
{
    VirtualSelectField::make('meta_robots', Lang::$txt['lp_extended_meta_tags']['meta_robots'])
        ->setTab(PageArea::TAB_SEO)
        ->setOptions(array_combine($this->meta_robots, Lang::$txt['lp_extended_meta_tags']['meta_robots_set']))
        ->setValue(Utils::$context['lp_page']['options']['meta_robots']);
}
```

### onPageSaving

> выполнение вашего кода при сохранении/изменении страниц

### onPageRemoving

> выполнение вашего кода при удалении страниц

### preparePageData

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

> добавление пользовательских кнопок под каждым комментарием

```php
public function commentButtons(Event $e): void
{
    if (empty(Utils::$context['lp_page']['options']['allow_reactions']))
        return;

    $comment = $e->args->comment;

    $comment['can_react'] = $comment['poster']['id'] !== User::$info['id'];
    $comment['reactions'] = json_decode($comment['params']['reactions'] ?? '', true) ?? [];
    $comment['prepared_reactions'] = $this->getReactionsWithCount($comment['reactions']);
    $comment['prepared_buttons'] = json_decode($comment['prepared_reactions'], true);

    ob_start();

    show_comment_reactions($comment);

    $e->args->buttons[] = ob_get_clean();
}
```

## Работа с плагинами

### addSettings

> добавление индивидуальных настроек плагинов

```php
public function addSettings(Event $e): void
{
    $e->args->settings['disqus'][] = [
        'text',
        'shortname',
        'subtext' => Lang::$txt['lp_disqus']['shortname_subtext'],
        'required' => true,
    ];
}
```

### saveSettings

> выполнение вашего кода при сохранении настроек плагинов

### prepareAssets

> сохранение внешних стилей, скриптов и изображений для повышения скорости загрузки ресурсов

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Работа со статьями

### frontModes

> добавление собственных режимов для главной страницы

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> добавление пользовательской логики на главной странице

### customLayoutExtensions

> позволяет добавлять пользовательские расширения макетов

```php
public function customLayoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> добавление собственных скриптов и стилей для главной страницы

```php
public function frontAssets(): void
{
    Theme::loadJavaScriptFile(
        'https://' . Utils::$context['lp_disqus_plugin']['shortname'] . '.disqus.com/count.js',
        ['external' => true],
    );
}
```

### frontTopics

> добавление дополнительных полей, таблиц, условий «Where», параметров и сортировок в функции _init_

```php
public function frontTopics(Event $e): void
{
    if (! class_exists('TopicRatingBar'))
        return;

    $e->args->columns[] = 'tr.total_votes, tr.total_value';
    $e->args->tables[]  = 'LEFT JOIN {db_prefix}topic_ratings AS tr ON (t.id_topic = tr.id)';
}
```

### frontTopicsRow

> различные манипуляции с результатами запроса в функции _getData_

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> добавление дополнительных полей, таблиц, условий «Where», параметров и сортировок в функции _init_

### frontPagesRow

> различные манипуляции с результатами запроса в функции _getData_

### frontBoards

> добавление дополнительных полей, таблиц, условий «Where», параметров и сортировок в функции _init_

### frontBoardsRow

> различные манипуляции с результатами запроса в функции _getData_

## Работа с иконками

### prepareIconList

> добавление собственного списка иконок (вместо FontAwesome)

```php
public function prepareIconList(Event $e): void
{
    if (($mainIcons = $this->cache()->get('all_main_icons', 30 * 24 * 60 * 60)) === null) {
        $set = $this->getIconSet();

        $mainIcons = [];
        foreach ($set as $icon) {
            $mainIcons[] = $this->prefix . $icon;
        }

        $this->cache()->put('all_main_icons', $mainIcons, 30 * 24 * 60 * 60);
    }

    $$e->args->icons = array_merge($$e->args->icons, $mainIcons);
}
```

### prepareIconTemplate

> добавление собственного шаблона для отображения иконок

### changeIconSet

> возможность расширения иконок интерфейса, доступных через массив `Utils::$context['lp_icon_set']`

## Настройки портала

### extendBasicConfig

> добавление пользовательских параметров в области основных настроек портала

### updateAdminAreas

> добавление пользовательских областей портала в админке

```php
public function updateAdminAreas(Event $e): void
{
    if (User::$info['is_admin']) {
        $e->args->areas['lp_pages']['subsections']['import_from_ep'] = [
            Utils::$context['lp_icon_set']['import'] . Lang::$txt['lp_eh_portal']['label_name']
        ];
    }
}
```

### updateBlockAreas

> добавление дополнительных вкладок в области «Блоки»

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> добавление дополнительных вкладок в области «Страницы»

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> добавление дополнительных вкладок в области «Категории»

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> добавление дополнительных вкладок в области «Теги»

### updatePluginAreas

> добавление дополнительных вкладок в области «Плагины»

```php
public function updatePluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Дополнительно

### credits

> добавление копирайтов используемых библиотек и скриптов

```php
public function credits(Event $e): void
{
    $e->args->links[] = [
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
