---
description: List of all available portal hooks
order: 4
---

# Portal hooks

Light Portal is wonderfully extensible thanks to plugins. Hooks allow plugins to interact with various components of the portal.

:::warning Important

Starting from version 3.0, hooks are defined using PHP attributes. You can name methods anything - the main thing is to specify the required `#[HookAttribute(PortalHook::hookName)]` attribute.

:::

## Basic hooks

### init

> redefining $txt variables, running SMF hooks, etc.

```php
#[HookAttribute(PortalHook::init)]
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

> parsing content of custom block/page types

```php
#[HookAttribute(PortalHook::parseContent)]
public function parseContent(Event $e): void
{
    $e->args->content = Content::parse($e->args->content, 'html');
}
```

### prepareContent

> adding custom content of your plugin

```php
#[HookAttribute(PortalHook::prepareContent)]
public function prepareContent(Event $e): void
{
    $this->setTemplate();

    $userData = $this->cache($this->name . '_addon_u' . Utils::$context['user']['id'])
        ->setLifeTime($e->args->cacheTime)
        ->setFallback(self::class, 'getData');

    show_user_info($userData);
}
```

### prepareEditor

> adding any code on block/page editing area

```php
#[HookAttribute(PortalHook::prepareEditor)]
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

> helps with preloading the stylesheets you need

::: code-group

```php [PHP]
#[HookAttribute(PortalHook::preloadStyles)]
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

## Work with blocks

### prepareBlockParams

> adding your block parameters

```php
#[HookAttribute(PortalHook::prepareBlockParams)]
public function prepareBlockParams(Event $e): void
{
    $e->args->params = [
        'body_class'     => 'descbox',
        'display_type'   => 0,
        'include_topics' => '',
        'include_pages'  => '',
        'seek_images'    => false,
    ];
}
```

### validateBlockParams

> adding custom validating rules when block adding/editing

```php
#[HookAttribute(PortalHook::validateBlockParams)]
public function validateBlockParams(Event $e): void
{
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

> adding custom error handling when block adding/editing

```php
#[HookAttribute(PortalHook::findBlockErrors)]
public function findBlockErrors(Event $e): void
{
    if ($e->args->data['placement'] !== 'ads')
        return;

    Lang::$txt['lp_post_error_no_ads_placement'] = $this->txt['no_ads_placement'];

    if (empty($e->args->data['parameters']['ads_placement'])) {
        $e->args->errors[] = 'no_ads_placement';
    }
}
```

### prepareBlockFields

> adding custom fields to the block post area

```php
#[HookAttribute(PortalHook::prepareBlockFields)]
public function prepareBlockFields(Event $e): void
{
    RadioField::make('display_type', $this->txt['display_type'])
        ->setTab(BlockArea::TAB_CONTENT)
        ->setOptions($this->txt['display_type_set'])
        ->setValue($e->args->options['display_type']);

    CheckboxField::make('seek_images', $this->txt['seek_images'])
        ->setValue($e->args->options['seek_images']);
}
```

### onBlockSaving

> custom actions on saving/editing blocks

### onBlockRemoving

> custom actions on removing blocks

```php
#[HookAttribute(PortalHook::onBlockRemoving)]
public function onBlockRemoving(Event $e): void
{
    foreach ($e->args->items as $item) {
        $this->cache()->forget('block_' . $item . '_cache');
    }
}
```

## Work with pages

### preparePageParams

> adding your page parameters

```php
#[HookAttribute(PortalHook::preparePageParams)]
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> adding custom validating rules when page adding/editing

```php
#[HookAttribute(PortalHook::validatePageParams)]
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> adding custom error handling when page adding/editing

### preparePageFields

> adding custom fields to the page post area

```php
#[HookAttribute(PortalHook::preparePageFields)]
public function preparePageFields(Event $e): void
{
    VirtualSelectField::make('meta_robots', $this->txt['meta_robots'])
        ->setTab(PageArea::TAB_SEO)
        ->setOptions(array_combine($this->meta_robots, $this->txt['meta_robots_set']))
        ->setValue($e->args->options['meta_robots']);
}
```

### onPageSaving

> custom actions on saving/editing pages

### onCustomPageImport

> custom actions on custom page import

```php
#[HookAttribute(PortalHook::onCustomPageImport)]
public function onCustomPageImport(Event $e): void
{
    $e->args->items = array_map(function ($item) {
        $item['title'] = Utils::$smcFunc['htmlspecialchars']($item['title']);

        return $item;
    }, $e->args->items);
}
```

### onPageRemoving

> custom actions on removing pages

```php
#[HookAttribute(PortalHook::onPageRemoving)]
public function onPageRemoving(Event $e): void
{
    foreach ($e->args->items as $item) {
        $this->cache()->forget('page_' . $item . '_cache');
    }
}
```

### preparePageData

> additional preparing the portal current page data

```php
#[HookAttribute(PortalHook::preparePageData)]
public function preparePageData(Event $e): void
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
#[HookAttribute(PortalHook::comments)]
public function comments(): void
{
    if (! empty(Config::$modSettings['lp_show_comment_block']) && Config::$modSettings['lp_show_comment_block'] === 'disqus' && ! empty($this->context['shortname'])) {
        Utils::$context['lp_disqus_comment_block'] = '
            <div id="disqus_thread" class="windowbg"></div>
            <script>
                <!-- Your code -->
            </script>';
    }
}
```

### commentButtons

> adding custom buttons below each comment

```php
#[HookAttribute(PortalHook::commentButtons)]
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

## Work with plugins

### addSettings

> adding custom settings of your plugin

```php
#[HookAttribute(PortalHook::addSettings)]
public function addSettings(Event $e): void
{
    $e->args->settings[$this->name][] = [
        'text',
        'shortname',
        'subtext' => $this->txt['shortname_subtext'],
        'required' => true,
    ];
}
```

### saveSettings

> additional actions after plugin settings saving

```php
#[HookAttribute(PortalHook::saveSettings)]
public function saveSettings(Event $e): void
{
    $this->cache()->flush();
}
```

### prepareAssets

> saving external styles, scripts, and images to improve resource speed loading

```php
#[HookAttribute(PortalHook::prepareAssets)]
public function prepareAssets(Event $e): void
{
    $e->args->assets['css'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Work with articles

### frontModes

> adding custom modes for the frontpage

```php
#[HookAttribute(PortalHook::frontModes)]
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> adding custom logic on the frontpage

```php
#[HookAttribute(PortalHook::frontLayouts)]
public function frontLayouts(Event $e): void
{
    if (! str_contains($e->args->layout, $this->extension))
        return;

    $e->args->renderer = new LatteRenderer();
}
```

### layoutExtensions

> lets add custom layout extensions

```php
#[HookAttribute(PortalHook::layoutExtensions)]
public function layoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> adding custom scripts and styles on the frontpage

```php
#[HookAttribute(PortalHook::frontAssets)]
public function frontAssets(): void
{
    $this->loadExternalResources([
        ['type' => 'css', 'url' => 'https://cdn.example.com/custom.css'],
        ['type' => 'js',  'url' => 'https://cdn.example.com/custom.js'],
    ]);
}
```

### frontTopics

> adding custom columns, tables, wheres, params and orders to _init_ function

```php
#[HookAttribute(PortalHook::frontTopics)]
public function frontTopics(Event $e): void
{
    $e->args->wheres[] = ['t.num_replies > ?' => 1];
}
```

### frontTopicsRow

> various manipulations with query results to _getData_ function

```php
#[HookAttribute(PortalHook::frontTopicsRow)]
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['replies'] = $e->args->row['num_replies'] ?? 0;
}
```

### frontPages

> adding custom columns, tables, wheres, params and orders to _init_ function

```php
#[HookAttribute(PortalHook::frontPages)]
public function frontPages(Event $e): void
{
    $e->args->joins[] = fn(Select $select) => $select->join(
        ['lc' => 'lp_comments'],
        'lp.page_id = lc.page_id',
        ['num_comments'],
        Select::JOIN_LEFT
    );

    $e->args->wheres[] = ['lc.approved' => 1];
}
```

### frontPagesRow

> various manipulations with query results to _getData_ function

```php
#[HookAttribute(PortalHook::frontPagesRow)]
public function frontPagesRow(Event $e): void
{
    $e->args->articles[$e->args->row['id']]['comments'] = $e->args->row['num_comments'] ?? 0;
}
```

### frontBoards

> adding custom columns, tables, wheres, params and orders to _init_ function

```php
#[HookAttribute(PortalHook::frontBoards)]
public function frontBoards(Event $e): void
{
    $e->args->columns['num_topics'] = new Expression('MIN(b.num_topics)');

    $e->args->wheres[] = fn(Select $select) => $select->where->greaterThan('b.num_topics', 5);
}
```

### frontBoardsRow

> various manipulations with query results to _getData_ function

```php
#[HookAttribute(PortalHook::frontBoardsRow)]
public function frontBoardsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_board']]['custom_field'] = 'value';
}
```

## Work with icons

### prepareIconList

> adding custom list of icons (instead of FontAwesome)

```php
#[HookAttribute(PortalHook::prepareIconList)]
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

> adding custom template for displaying icons

```php
#[HookAttribute(PortalHook::prepareIconTemplate)]
public function prepareIconTemplate(Event $e): void
{
    $e->args->template = "<i class=\"custom-class {$e->args->icon}\" aria-hidden=\"true\"></i>";
}
```

### changeIconSet

> ability to extend interface icons available via `Utils::$context['lp_icon_set']` array

```php
#[HookAttribute(PortalHook::changeIconSet)]
public function changeIconSet(Event $e): void
{
    $e->args->set['snowman'] = 'fa-solid fa-snowman';
}
```

## Portal settings

### extendBasicConfig

> adding custom configs in the portal basic settings area

```php
#[HookAttribute(PortalHook::extendBasicConfig)]
public function extendBasicConfig(Event $e): void
{
    $e->args->configVars[] = ['text', 'option_key', 'subtext' => $this->txt['my_mod_description']];
}
```

### extendAdminAreas

> adding the portal custom areas in the Administration Center

```php
#[HookAttribute(PortalHook::extendAdminAreas)]
public function extendAdminAreas(Event $e): void
{
    if (User::$info['is_admin']) {
        $e->args->areas['lp_pages']['subsections']['import_from_ep'] = [
            Utils::$context['lp_icon_set']['import'] . $this->txt['label_name']
        ];
    }
}
```

### extendBlockAreas

> adding custom tabs into Block area settings

```php
#[HookAttribute(PortalHook::extendBlockAreas)]
public function extendBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### extendPageAreas

> adding custom tabs into Page area settings

```php
#[HookAttribute(PortalHook::extendPageAreas)]
public function extendPageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### extendCategoryAreas

> adding custom tabs into Category area settings

```php
#[HookAttribute(PortalHook::extendCategoryAreas)]
public function extendCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### extendTagAreas

> adding custom tabs into Tag area settings

### extendPluginAreas

> adding custom tabs into Plugin area settings

```php
#[HookAttribute(PortalHook::extendPluginAreas)]
public function extendPluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Miscellaneous

### credits

> adding copyrights of used libraries/scripts, etc.

```php
#[HookAttribute(PortalHook::credits)]
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

### downloadRequest

> handling download requests for portal attachments

```php
#[HookAttribute(PortalHook::downloadRequest)]
public function downloadRequest(Event $e): void
{
    if ($e->args->attachRequest['id'] === (int) $this->request()->get('attach')) {
        // Some handling
    }
}
```
