---
description: Lijst van alle beschikbare portaalhaken
order: 4
---

# Portal hooks

Licht Portaal is geweldig uitbreidbaar dankzij plugins. Hooks zorgen ervoor dat plugins kunnen communiceren met verschillende componenten van de portal.

## Basis haken

### init

> $txt variabelen herdefiniëren, SMF hooks uitvoeren, etc.

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

> verwerking van inhoud van aangepaste block/pagina types

```php
public function parseContent(Event $e): void
{
    $e->args->content = Content::parse($e->args->content, 'html');
}
```

### prepareContent

> aangepaste inhoud van uw plugin toevoegen

```php
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

> elke code toevoegen aan blok/pagina bewerken gebied

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

> helpt met het voorladen van de stylesheets die u nodig heeft

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

## Werk met blokken

### prepareBlockParams

> blokparameters toevoegen

```php
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

> aangepaste validatieregels toevoegen wanneer blok toevoegen/bewerken

```php
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

> eigen foutmelding toevoegen bij blokkeren van toevoegen/bewerken

```php
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

> extra velden toevoegen aan het blok berichtgebied

```php
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

> aangepaste acties voor het opslaan/bewerken van blokken

### onBlockRemoving

> aangepaste acties voor het verwijderen van blokken

```php
public function onBlockRemoving(Event $e): void
{
    foreach ($e->args->items as $item) {
        $this->cache()->forget('block_' . $item . '_cache');
    }
}
```

## Werk met pagina's

### preparePageParams

> paginanameters toevoegen

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> aangepaste validatieregels toevoegen wanneer pagina toevoegen/bewerken

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> eigen foutmelding toevoegen wanneer pagina toevoegen/bewerken

### preparePageFields

> extra velden toevoegen aan het berichtgebied van de pagina

```php
public function preparePageFields(Event $e): void
{
    VirtualSelectField::make('meta_robots', $this->txt['meta_robots'])
        ->setTab(PageArea::TAB_SEO)
        ->setOptions(array_combine($this->meta_robots, $this->txt['meta_robots_set']))
        ->setValue($e->args->options['meta_robots']);
}
```

### onPageSaving

> aangepaste acties voor het opslaan/bewerken van pagina's

### onCustomPageImport

> custom actions on custom page import

```php
public function onCustomPageImport(Event $e): void
{
    $e->args->items = array_map(function ($item) {
        $item['title'] = Utils::$smcFunc['htmlspecialchars']($item['title']);

        return $item;
    }, $e->args->items);
}
```

### onPageRemoving

> aangepaste acties bij het verwijderen van pagina's

```php
public function onPageRemoving(Event $e): void
{
    foreach ($e->args->items as $item) {
        $this->cache()->forget('page_' . $item . '_cache');
    }
}
```

### preparePageData

> aanvullende voorbereiding van huidige pagina gegevens van de portal

```php
public function preparePageData(Event $e): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> mogelijkheid om iets voor de inhoud van de portalpagina weer te geven

### afterPageContent

> mogelijkheid om iets na de inhoud van de portalpagina weer te geven

### comments

> eigen commentaar script toevoegen aan de huidige pagina weergave van het portaal

```php
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

> eigen knoppen toevoegen onder elk commentaar

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

## Werk met plugins

### addSettings

> aangepaste instellingen van uw plugin toevoegen

```php
public function addSettings(Event $e): void
{
    $this->addDefaultValues(['some_color' => '#ff00ad']);

    $e->args->settings[$this->name] = SettingsFactory::make()
        ->text('some_text')
        ->check('some_check')
        ->int('some_int', ['min' => 1])
        ->select('some_select', [1, 2, 3])
        ->color('some_color')
        ->range('some_range', ['max' => 10])
        ->toArray();
}
```

### saveSettings

> aanvullende acties na het opslaan van de plugin instellingen

```php
public function saveSettings(Event $e): void
{
    $this->cache()->flush();
}
```

### prepareAssets

> externe stijlen, scripts en afbeeldingen opslaan om het laden van bronsnelheid te verbeteren

```php
public function prepareAssets(Event $e): void
{
    $builder = new AssetBuilder($this);
    $builder->scripts()->add('https://cdn.jsdelivr.net/npm/apexcharts@3/dist/apexcharts.min.js');
    $builder->css()->add('https://cdn.jsdelivr.net/npm/apexcharts@3/dist/apexcharts.min.css');
    $builder->appendTo($e->args->assets);
}
```

## Werk met artikelen

### frontModes

> aangepaste modi voor de voorpagina toevoegen

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    $e->args->currentMode = $this->mode;
}
```

### frontLayouts

> aangepaste logica toevoegen aan de voorpagina

```php
public function frontLayouts(Event $e): void
{
    if (! str_contains($e->args->layout, $this->extension))
        return;

    $e->args->renderer = new LatteRenderer();
}
```

### layoutExtensions

> Laat aangepaste lay-out extensies toevoegen

```php
public function layoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> aangepaste scripts en stijlen toevoegen op de voorpagina

```php
public function frontAssets(): void
{
    $this->loadExternalResources([
        ['type' => 'css', 'url' => 'https://cdn.example.com/custom.css'],
        ['type' => 'js',  'url' => 'https://cdn.example.com/custom.js'],
    ]);
}
```

### frontTopics

> aangepaste kolommen, tabellen, waarmee, parameters en orders worden toegevoegd aan de functie _init_

```php
public function frontTopics(Event $e): void
{
    $e->args->wheres[] = ['t.num_replies > ?' => 1];
}
```

### frontTopicsRow

> verschillende manipulaties met query resultaten naar _getData_ functie

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['replies'] = $e->args->row['num_replies'] ?? 0;
}
```

### frontPages

> adding custom columns, joins, where conditions, params and orders to _init_ function

```php
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

> verschillende manipulaties met query resultaten naar _getData_ functie

```php
public function frontPagesRow(Event $e): void
{
    $e->args->articles[$e->args->row['id']]['comments'] = $e->args->row['num_comments'] ?? 0;
}
```

### frontBoards

> aangepaste kolommen, tabellen, waarmee, parameters en orders worden toegevoegd aan de functie _init_

```php
public function frontBoards(Event $e): void
{
    $e->args->columns['num_topics'] = new Expression('MIN(b.num_topics)');

    $e->args->wheres[] = fn(Select $select) => $select->where->greaterThan('b.num_topics', 5);
}
```

### frontBoardsRow

> verschillende manipulaties met query resultaten naar _getData_ functie

```php
public function frontBoardsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_board']]['custom_field'] = 'value';
}
```

## Werken met pictogrammen

### prepareIconList

> aangepaste lijst met pictogrammen toevoegen (in plaats van FontAwesom)

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

> aangepaste sjabloon voor het weergeven van pictogrammen toevoegen

```php
public function prepareIconTemplate(Event $e): void
{
    $e->args->template = "<i class=\"custom-class {$e->args->icon}\" aria-hidden=\"true\"></i>";
}
```

### changeIconSet

> mogelijkheid om interface iconen die beschikbaar zijn via `Utils::$context['lp_icon_set']` array uit te breiden

```php
public function changeIconSet(Event $e): void
{
    $e->args->set['snowman'] = 'fa-solid fa-snowman';
}
```

## Portaal instellingen

### extendBasicConfig

> het toevoegen van aangepaste configuraties in het basisinstellingen gebied van de portal

```php
public function extendBasicConfig(Event $e): void
{
    $e->args->configVars[] = ['text', 'option_key', 'subtext' => $this->txt['my_mod_description']];
}
```

### extendAdminAreas

> het toevoegen van de aangepaste portaalgebieden in het Administratorcentrum

```php
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

> Aangepaste tabbladen toevoegen aan instellingen van Blok gebied

```php
public function extendBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### extendPageAreas

> Aangepaste tabbladen toevoegen aan instellingen van pagina-gebied

```php
public function extendPageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### extendCategoryAreas

> Aangepaste tabbladen toevoegen aan instellingen voor categoriegebied

```php
public function extendCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### extendTagAreas

> Aangepaste tabbladen toevoegen aan instellingen van het taggebied

### extendPluginAreas

> eigen tabbladen toevoegen aan Plugin instellingen

```php
public function extendPluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Diversen

### credits

> copyrights van gebruikte bibliotheken/scripts, etc. toe te voegen.

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

### downloadRequest

> handling download requests for portal attachments

```php
public function downloadRequest(Event $e): void
{
    if ($e->args->attachRequest['id'] === (int) $this->request()->get('attach')) {
        // Some handling
    }
}
```
