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

> analisi del contenuto dei tipi di blocco/pagina personalizzati

```php
public function parseContent(string &$content, string $type): void
{
    if ($type === 'markdown')
        $content = $this->getParsedContent($content);
}
```

### prepareContent

(`$data, $parameters`)

> aggiunta di contenuto personalizzato del tuo plugin

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

> aggiunta di qualsiasi codice nell'area di modifica del blocco/pagina

```php
public function prepareEditor(array $object): void
{
    if ($object['type'] !== 'markdown')
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

(`&$styles`)

> aiuta a precaricare i fogli di stile di cui hai bisogno

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

## Lavorare con i blocchi

### prepareBlockParams

(`&$params`)

> aggiunta dei parametri del blocco

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

> aggiunta di regole di convalida personalizzate durante l'aggiunta/modifica dei blocchi

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

> aggiunta della gestione personalizzata degli errori durante l'aggiunta/modifica dei blocchi

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

> aggiunta di campi personalizzati all'area dei blocchi

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

> azioni personalizzate sul salvataggio/modifica dei blocchi

### onBlockRemoving

(`$items`)

> azioni personalizzate sulla rimozione dei blocchi

## Lavorare con le pagine

### preparePageParams

(`&$params`)

> aggiunta dei parametri della pagina

```php
public function preparePageParams(array &$params): void
{
    $params['meta_robots'] = '';
    $params['meta_rating'] = '';
}
```

### validatePageParams

(`&$params`)

> aggiunta di regole di convalida personalizzate durante l'aggiunta/modifica dele pagine

```php
public function validatePageParams(array &$params): void
{
    $params['meta_robots'] = FILTER_DEFAULT;
    $params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

(`&$errors, $data`)

> aggiunta della gestione personalizzata degli errori durante l'aggiunta/modifica delle pagine

### preparePageFields

> aggiunta di campi personalizzati all'area delle pagine

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

> azioni personalizzate durante il salvataggio/modifica delle pagine

### onPageRemoving

(`$items`)

> azioni personalizzate sulla rimozione dele pagine

### preparePageData

(`&$data`, `$is_author`)

> ulteriore preparazione dei dati della pagina corrente del portale

```php
public function preparePageData(): void
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

> aggiunta di pulsanti personalizzati sotto ogni commento

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

## Lavorare con i plugin

### addSettings

(`&$settings`)

> aggiunta di impostazioni personalizzate del tuo plugin

```php
public function addSettings(array &$settings): void
{
    $settings['disqus'][] = [
        'text',
        'shortname',
        'subtext' => Lang::$txt['lp_disqus']['shortname_subtext'],
        'required' => true,
    ];
}
```

### saveSettings

(`&$settings`)

> azioni aggiuntive dopo il salvataggio delle impostazioni del plugin

### prepareAssets

(`&$assets`)

> salvataggio di stili, script e immagini esterni per migliorare la velocità di caricamento delle risorse

```php
public function prepareAssets(array &$assets): void
{
    $assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Lavorare con articoli

### frontModes

(`&$modes`)

> aggiunta di modalità personalizzate per il frontpage

```php
public function frontModes(array &$modes): void
{
    $modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> aggiunta di logica personalizzata per il frontpage

### customLayoutExtensions

(`&$extensions`)

> aggiunta di estensioni di layout personalizzate

```php
public function customLayoutExtensions(array &$extensions): void
{
    $extensions[] = '.twig';
}
```

### frontAssets

> aggiunta di script e stili personalizzati sul frontpage

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

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> aggiunta di colonne e tabelle personalizzate, in base ai parametri ed ordinamenti della funzione _init_

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

> varie manipolazioni con i risultati della query sulla funzione _getData_

```php
public function frontTopicsOutput(array &$topics, array $row): void
{
    $topics[$row['id_topic']]['rating'] = empty($row['total_votes'])
        ? 0 : (number_format($row['total_value'] / $row['total_votes']));
}
```

### frontPages

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> aggiunta di colonne e tabelle personalizzate, in base ai parametri ed ordinamenti della funzione _init_

### frontPagesOutput

(`&$pages, $row`)

> varie manipolazioni con i risultati della query sulla funzione _getData_

### frontBoards

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> aggiunta di colonne e tabelle personalizzate, in base ai parametri ed ordinamenti della funzione _init_

### frontBoardsOutput

(`&$boards, $row`)

> varie manipolazioni con i risultati della query sulla funzione _getData_

## Lavorare con le icone

### prepareIconList

(`&$icons, &$template`)

> aggiunta di un elenco personalizzato di icone (invece di FontAwesome)

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

> aggiunta di template personalizzato per la visualizzazione delle icone

### changeIconSet

(`&$set`)

> possibilità di estendere l'interfaccia delle icone disponibili tramite l'array `$this->context['lp_icon_set']`

## Impostazioni Portale

### updateAdminAreas

(`&$areas`)

> aggiunta di aree personalizzate del portale nel Centro amministrativo

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

> aggiunta di schede personalizzate nelle impostazioni nell'area del blocco

```php
public function updateBlockAreas(array &$areas): void
{
    $areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

(`&$areas`)

> aggiunta di schede personalizzate nelle impostazioni nell'area della pagina

```php
public function updatePageAreas(array &$areas): void
{
    $areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

(`&$areas`)

> aggiunta di schede personalizzate nelle impostazioni dell'area Categoria

```php
public function updateCategoryAreas(array &$areas): void
{
    $areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

(`&$areas`)

> aggiunta di schede personalizzate nelle impostazioni dell'area Tag

### updatePluginAreas

(`&$areas`)

> aggiunta di schede personalizzate nelle impostazioni dei Plugin

```php
public function updatePluginAreas(array &$areas): void
{
    $areas['add'] = [new Handler(), 'add'];
}
```

## Varie

### credits

(`&$links`)

> aggiunta dei diritti d'autore delle librerie/script utilizzati, ecc.

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
