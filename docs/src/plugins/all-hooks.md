---
description: List of all available portal hooks
order: 4
---

# Portal hooks

Light Portal is wonderfully extensible thanks to plugins. And hooks help plugins to interact with various components of the portal.

## Basic hooks

### init

> redefining $txt variables, running SMF hooks, etc.

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

> parsing content of custom block/page types

```php
public function parseContent(string &$content, string $type): void
{
    if ($type === 'markdown')
        $content = $this->getParsedContent($content);
}
```

### prepareContent

(`$data, $parameters`)

> adding custom content of your plugin

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

> adding any code on block/page editing area

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

> helps with preloading the scripts you need

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

> helps with preloading the stylesheets you need

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

## Work with blocks

### prepareBlockParams

(`&$params`)

> adding your block parameters

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

> adding custom validating rules when block adding/editing

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

> adding custom error handling when block adding/editing

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

> adding custom fields to the block post area

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

> custom actions on saving/edition blocks

### onBlockRemoving

(`$items`)

> custom actions on removing blocks

## Work with pages

### preparePageParams

(`&$params`)

> adding your page parameters

```php
public function preparePageParams(array &$params): void
{
    $params['meta_robots'] = '';
    $params['meta_rating'] = '';
}
```

### validatePageParams

(`&$params`)

> adding custom validating rules when page adding/editing

```php
public function validatePageParams(array &$params): void
{
    $params['meta_robots'] = FILTER_DEFAULT;
    $params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

(`&$errors, $data`)

> adding custom error handling when page adding/editing

### preparePageFields

> adding custom fields to the page post area

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

> custom actions on saving/edition pages

### onPageRemoving

(`$items`)

> custom actions on removing pages

### preparePageData

(`&$data`, `$is_author`)

> additional preparing the portal current page data

```php
public function preparePageData(): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> ability to display something before the portal page content

### afterPageContent

> ability to display something after the portal page content

### comments

> adding custom comment script to the portal current page view

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

> adding custom buttons below each comment

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

## Work with plugins

### addSettings

(`&$settings`)

> adding custom settings of your plugin

```php
public function addSettings(array &$settings): void
{
    $settings['disqus'][] = ['text', 'shortname', 'subtext' => Lang::$txt['lp_disqus']['shortname_subtext'], 'required' => true];
}
```

### saveSettings

(`&$settings`)

> additional actions after plugin settings saving

### prepareAssets

(`&$assets`)

> saving external styles, scripts, and images to improve resource speed loading

```php
public function prepareAssets(array &$assets): void
{
    $assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Work with articles

### frontModes

(`&$modes`)

> adding custom modes for the frontpage

```php
public function frontModes(array &$modes): void
{
    $modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> adding custom logic on the frontpage

### customLayoutExtensions

(`&$extensions`)

> lets add custom layout extensions

```php
public function customLayoutExtensions(array &$extensions): void
{
    $extensions[] = '.twig';
}
```

### frontAssets

> adding custom scripts and styles on the frontpage

```php
public function frontAssets(): void
{
    $this->loadExtJS('https://' . Utils::$context['lp_disqus_plugin']['shortname'] . '.disqus.com/count.js');
}
```

### frontTopics

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> adding custom columns, tables, wheres, params and orders to _init_ function

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

> various manipulations with query results to _getData_ function

```php
public function frontTopicsOutput(array &$topics, array $row): void
{
    $topics[$row['id_topic']]['rating'] = empty($row['total_votes']) ? 0 : (number_format($row['total_value'] / $row['total_votes']));
}
```

### frontPages

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> adding custom columns, tables, wheres, params and orders to _init_ function

### frontPagesOutput

(`&$pages, $row`)

> various manipulations with query results to _getData_ function

### frontBoards

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> adding custom columns, tables, wheres, params and orders to _init_ function

### frontBoardsOutput

(`&$boards, $row`)

> various manipulations with query results to _getData_ function

## Work with icons

### prepareIconList

(`&$icons, &$template`)

> adding custom list of icons (instead of FontAwesome)

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

> adding custom template for displaying icons

### changeIconSet

(`&$set`)

> ability to extend interface icons available via `Utils::$context['lp_icon_set']` array

## Portal settings

### updateAdminAreas

(`&$areas`)

> adding the portal custom areas in the Administration Center

```php
public function updateAdminAreas(array &$areas): void
{
    if (User::$info['is_admin'])
        $areas['lp_pages']['subsections']['import_from_ep'] = [Utils::$context['lp_icon_set']['import'] . Lang::$txt['lp_eh_portal']['label_name']];
}
```

### updateBlockAreas

(`&$areas`)

> adding custom tabs into Block area settings

```php
public function updateBlockAreas(array &$areas): void
{
    if (User::$info['is_admin'])
        $areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

(`&$areas`)

> adding custom tabs into Page area settings

```php
public function updatePageAreas(array &$areas): void
{
    if (User::$info['is_admin'])
        $areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updatePluginAreas

(`&$areas`)

> adding custom tabs into Plugin area settings

```php
public function updatePluginAreas(array &$areas): void
{
    $areas['add'] = [new Handler(), 'add'];
}
```

## Miscellaneous

### credits

(`&$links`)

> adding copyrights of used libraries/scripts, etc.

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
