---
description: Elenco di tutti gli hook del portale disponibili
order: 4
---

# Hook portale

Light Portal è meravigliosamente estensibile grazie ai plugin. Gli hook consentono ai plugin di interagire con vari componenti del portale.

## Hook base

### init

> ridefinizione delle variabili $txt, eseguendo hook SMF, ecc.

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

> analisi del contenuto dei tipi di blocco/pagina personalizzati

```php
public function parseContent(Event $e): void
{
    $e->args->content = Content::parse($e->args->content, 'html');
}
```

### prepareContent

> aggiunta di contenuto personalizzato del tuo plugin

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

> aggiunta di qualsiasi codice nell'area di modifica del blocco/pagina

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

> aiuta a precaricare i fogli di stile di cui hai bisogno

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

## Lavorare con i blocchi

### prepareBlockParams

> aggiunta dei parametri del blocco

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

> aggiunta di regole di convalida personalizzate durante l'aggiunta/modifica dei blocchi

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

> aggiunta della gestione personalizzata degli errori durante l'aggiunta/modifica dei blocchi

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

> aggiunta di campi personalizzati all'area dei blocchi

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

> azioni personalizzate sul salvataggio/modifica dei blocchi

### onBlockRemoving

> azioni personalizzate sulla rimozione dei blocchi

```php
public function onBlockRemoving(Event $e): void
{
    foreach ($e->args->items as $item) {
        $this->cache()->forget('block_' . $item . '_cache');
    }
}
```

## Lavorare con le pagine

### preparePageParams

> aggiunta dei parametri della pagina

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> aggiunta di regole di convalida personalizzate durante l'aggiunta/modifica dele pagine

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> aggiunta della gestione personalizzata degli errori durante l'aggiunta/modifica delle pagine

### preparePageFields

> aggiunta di campi personalizzati all'area delle pagine

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

> azioni personalizzate durante il salvataggio/modifica delle pagine

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

> azioni personalizzate sulla rimozione dele pagine

```php
public function onPageRemoving(Event $e): void
{
    foreach ($e->args->items as $item) {
        $this->cache()->forget('page_' . $item . '_cache');
    }
}
```

### preparePageData

> ulteriore preparazione dei dati della pagina corrente del portale

```php
public function preparePageData(Event $e): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> capacità di visualizzare qualcosa prima del contenuto della pagina del portale

### afterPageContent

> capacità di visualizzare qualcosa dopo il contenuto della pagina del portale

### comments

> aggiunta di commenti personalizzati alla visualizzazione della pagina corrente del portale

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

> aggiunta di pulsanti personalizzati sotto ogni commento

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

## Lavorare con i plugin

### addSettings

> aggiunta di impostazioni personalizzate del tuo plugin

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

> azioni aggiuntive dopo il salvataggio delle impostazioni del plugin

```php
public function saveSettings(Event $e): void
{
    $this->cache()->flush();
}
```

### prepareAssets

> salvataggio di stili, script e immagini esterni per migliorare la velocità di caricamento delle risorse

```php
public function prepareAssets(Event $e): void
{
    $builder = new AssetBuilder($this);
    $builder->scripts()->add('https://cdn.jsdelivr.net/npm/apexcharts@3/dist/apexcharts.min.js');
    $builder->css()->add('https://cdn.jsdelivr.net/npm/apexcharts@3/dist/apexcharts.min.css');
    $builder->appendTo($e->args->assets);
}
```

## Lavorare con articoli

### frontModes

> aggiunta di modalità personalizzate per il frontpage

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    $e->args->currentMode = $this->mode;
}
```

### frontLayouts

> aggiunta di logica personalizzata per il frontpage

```php
public function frontLayouts(Event $e): void
{
    if (! str_contains($e->args->layout, $this->extension))
        return;

    $e->args->renderer = new LatteRenderer();
}
```

### layoutExtensions

> aggiunta di estensioni di layout personalizzate

```php
public function layoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> aggiunta di script e stili personalizzati sul frontpage

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

> aggiunta di colonne e tabelle personalizzate, in base ai parametri ed ordinamenti della funzione _init_

```php
public function frontTopics(Event $e): void
{
    $e->args->wheres[] = ['t.num_replies > ?' => 1];
}
```

### frontTopicsRow

> varie manipolazioni con i risultati della query sulla funzione _getData_

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

> varie manipolazioni con i risultati della query sulla funzione _getData_

```php
public function frontPagesRow(Event $e): void
{
    $e->args->articles[$e->args->row['id']]['comments'] = $e->args->row['num_comments'] ?? 0;
}
```

### frontBoards

> aggiunta di colonne e tabelle personalizzate, in base ai parametri ed ordinamenti della funzione _init_

```php
public function frontBoards(Event $e): void
{
    $e->args->columns['num_topics'] = new Expression('MIN(b.num_topics)');

    $e->args->wheres[] = fn(Select $select) => $select->where->greaterThan('b.num_topics', 5);
}
```

### frontBoardsRow

> varie manipolazioni con i risultati della query sulla funzione _getData_

```php
public function frontBoardsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_board']]['custom_field'] = 'value';
}
```

## Lavorare con le icone

### prepareIconList

> aggiunta di un elenco personalizzato di icone (invece di FontAwesome)

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

> aggiunta di template personalizzato per la visualizzazione delle icone

```php
public function prepareIconTemplate(Event $e): void
{
    $e->args->template = "<i class=\"custom-class {$e->args->icon}\" aria-hidden=\"true\"></i>";
}
```

### changeIconSet

> possibilità di estendere l'interfaccia delle icone disponibili tramite l'array `$this->context['lp_icon_set']`

```php
public function changeIconSet(Event $e): void
{
    $e->args->set['snowman'] = 'fa-solid fa-snowman';
}
```

## Impostazioni Portale

### extendBasicConfig

> aggiunta di configurazioni personalizzate nell'area delle impostazioni di base del portale

```php
public function extendBasicConfig(Event $e): void
{
    $e->args->configVars[] = ['text', 'option_key', 'subtext' => $this->txt['my_mod_description']];
}
```

### extendAdminAreas

> aggiunta di aree personalizzate del portale nel Centro amministrativo

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

> aggiunta di schede personalizzate nelle impostazioni nell'area del blocco

```php
public function extendBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### extendPageAreas

> aggiunta di schede personalizzate nelle impostazioni nell'area della pagina

```php
public function extendPageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### extendCategoryAreas

> aggiunta di schede personalizzate nelle impostazioni dell'area Categoria

```php
public function extendCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### extendTagAreas

> aggiunta di schede personalizzate nelle impostazioni dell'area Tag

### extendPluginAreas

> aggiunta di schede personalizzate nelle impostazioni dei Plugin

```php
public function extendPluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Varie

### credits

> aggiunta dei diritti d'autore delle librerie/script utilizzati, ecc.

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
