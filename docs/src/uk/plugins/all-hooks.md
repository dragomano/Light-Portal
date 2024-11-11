---
description: Список усіх доступних хуків порталу
order: 4
---

# Портальні гачки

Легкий портал прекрасно розширений завдяки плагінам. Хуки дозволяють плагінам взаємодіяти з різними компонентами порталу.

## Основні хуки

### init

> змінювати змінну $txt , запуск SMF хуків, тощо.

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

> обробка вмісту власних типів блоків/сторінок

```php
public function parseContent(Event $e): void
{
    if ($e->args->type === 'markdown') {
        $e->args->content = $this->getParsedContent($e->args->content);
    }
}
```

### prepareContent

> додавання користувацького вмісту вашого плагіна

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

> додавання будь-якого коду на блоку/сторінку редагування області

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

> допомагає при завантаженні таблиць стилів, які вам потрібні

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

## Робота з блоками

### prepareBlockParams

> додавання параметрів блока

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

> додавання користувацьких правил перевірки при додаванні/редагуванні

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

> додавання користувацької помилки обробки при додаванні/редагуванні

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

> додавання індивідуальних полів в область посту блоку

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

> настроювані дії для блоків збереження/редагування

### onBlockRemoving

> налаштовувані дії щодо видалення блоків

## Робота з сторінками

### preparePageParams

> додавання параметрів сторінки

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> додавання користувацьких правил перевірки при додаванні/редагуванні сторінки

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> додавання користувацької помилки обробки при додаванні сторінки/редагуванні

### preparePageFields

> додавання індивідуальних полів в область повідомлення сторінки

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

> настроювані дії на сторінках збереження/редагування

### onPageRemoving

> користувацькі дії на видаленні сторінок

### preparePageData

> додаткової підготовки даних порталу поточної сторінки

```php
public function preparePageData(): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> можливість відображати що-небудь перед вмістом порталу сторінки

### afterPageContent

> можливість відобразити що-небудь після вмісту порталу

### comments

> додавання сценарію власного коментаря до порталу поточного перегляду сторінки

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

> додавання індивідуальних кнопок нижче кожного коментаря

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

## Робота з плагінами

### addSettings

> додавання власних налаштувань вашого плагіна

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

> додаткові дії після збереження налаштувань плагіна

### prepareAssets

> збереження зовнішніх стилів, скриптів та зображень, щоб поліпшити завантаження ресурсів

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Робота з статтями

### frontModes

> додавання індивідуальних режимів для титульної сторінки

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> додавання користувацької логіки на фронт-сторінці

### customLayoutExtensions

> дозволяє додати власні розширення макета

```php
public function customLayoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> додавання користувацьких скриптів і стилів на фронт-сторінці

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

> додавання індивідуальних стовпців, таблиць, додатків, парамів і замовлень до функції _init_

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

> різні маніпуляції з результатами запиту для функції _getData_

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> додавання індивідуальних стовпців, таблиць, додатків, парамів і замовлень до функції _init_

### frontPagesRow

> різні маніпуляції з результатами запиту для функції _getData_

### frontBoards

> додавання індивідуальних стовпців, таблиць, додатків, парамів і замовлень до функції _init_

### frontBoardsRow

> різні маніпуляції з результатами запиту для функції _getData_

## Робота з іконками

### prepareIconList

> додавання настроюваного списку значків (замість FontAwesome)

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

> додавання користувацького шаблону для відображення значків

### changeIconSet

> можливість розширити піктограми інтерфейсу, доступні через масив "Utils::$context['lp_icon_set']

## Налаштування порталу

### extendBasicConfig

> додавання користувацьких конфігурації в базовій області параметрів порталу

### updateAdminAreas

> додавання своїх областей порталу в адміністраторському центрі

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

> додавання власних вкладок в налаштування області блоку

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> додавання власних вкладок в налаштування області сторінки

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> додавання власних вкладок в налаштування області категорій

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> додавання власних вкладок в налаштування області тегів

### updatePluginAreas

> додавання власних вкладок в налаштування області плагіну

```php
public function updatePluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Додатково

### credits

> додавання авторських прав використаних бібліотек/скриптів і т.д.

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
