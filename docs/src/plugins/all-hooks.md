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

    $userData = $this->cache('user_info_addon_u' . $this->context['user']['id'])
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

### blockOptions

(`&$options`)

> adding your block parameters

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

> adding custom fields to the block post area

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

> adding custom validating rules when block adding/editing

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

> adding custom error handling when block adding/editing

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

> custom actions on saving/edition blocks

### onBlockRemoving

(`$items`)

> custom actions on removing blocks

## Work with pages

### pageOptions

(`&$options`)

> adding your page parameters

```php
public function pageOptions(array &$options): void
{
    $options['meta_robots'] = '';
    $options['meta_rating'] = '';
}
```

### preparePageFields

> adding custom fields to the page post area

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

> adding custom validating rules when page adding/editing

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

> adding custom error handling when page adding/editing

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

> adding custom buttons below each comment

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

## Work with plugins

### addSettings

(`&$config_vars`)

> adding custom settings of your plugin

```php
public function addSettings(array &$config_vars): void
{
    $config_vars['disqus'][] = ['text', 'shortname', 'subtext' => $this->txt['lp_disqus']['shortname_subtext'], 'required' => true];
}
```

### saveSettings

(`&$plugin_options`)

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

    $this->modSettings['lp_frontpage_mode'] = $this->mode;
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
    $this->loadExtJS('https://' . $this->context['lp_disqus_plugin']['shortname'] . '.disqus.com/count.js');
}
```

### frontTopics

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> adding custom columns, tables, wheres, params and orders to _init_ function

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

(`&$all_icons, &$template`)

> adding custom list of icons (instead of FontAwesome)

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

> adding custom template for displaying icons

### changeIconSet

(`&$set`)

> ability to extend interface icons available via `$this->context['lp_icon_set']` array

## Portal settings

### updateAdminAreas

(`&$areas`)

> adding the portal custom areas in the Administration Center

```php
public function updateAdminAreas(array &$areas): void
{
    if ($this->user_info['is_admin'])
        $areas['lp_pages']['subsections']['import_from_ep'] = [$this->context['lp_icon_set']['import'] . $this->txt['lp_eh_portal']['label_name']];
}
```

### updateBlockAreas

(`&$areas`)

> adding custom tabs into Block area settings

```php
public function updateBlockAreas(array &$areas): void
{
    if ($this->user_info['is_admin'])
        $areas['import_from_tp'] = [new BlockImport, 'main'];
}
```

### updatePageAreas

(`&$areas`)

> adding custom tabs into Page area settings

```php
public function updatePageAreas(array &$areas): void
{
    if ($this->user_info['is_admin'])
        $areas['import_from_ep'] = [new Import, 'main'];
}
```

### updatePluginAreas

(`&$areas`)

> adding custom tabs into Plugin area settings

```php
public function updatePluginAreas(array &$areas): void
{
    $areas['add'] = [new Handler, 'add'];
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
