---
description: Elenco di tutti gli hook del portale disponibili
order: 4
---

# Hook portale

Light Portal è meravigliosamente estensibile grazie ai plugin. E gli hook aiutano i plugin a interagire con i vari componenti del portale.

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

    $userData = $this->cache('user_info_addon_u' . $this->context['user']['id'])
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

> aiuta a precaricare gli script di cui hai bisogno

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

### blockOptions

(`&$options`)

> aggiunta dei parametri del blocco

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

> aggiunta di campi personalizzati all'area dei blocchi

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

> aggiunta di regole di convalida personalizzate durante l'aggiunta/modifica dei blocchi

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

> aggiunta della gestione personalizzata degli errori durante l'aggiunta/modifica dei blocchi

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

> azioni personalizzate durante il salvataggio/modifica dei blocchi

### onBlockRemoving

(`$items`)

> azioni personalizzate sulla rimozione dei blocchi

## Lavorare con le pagine

### pageOptions

(`&$options`)

> aggiunta dei parametri della pagina

```php
public function pageOptions(array &$options): void
{
    $options['meta_robots'] = '';
    $options['meta_rating'] = '';
}
```

### preparePageFields

> aggiunta di campi personalizzati all'area delle pagine

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

> aggiunta di regole di convalida personalizzate durante l'aggiunta/modifica dele pagine

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

> aggiunta della gestione personalizzata degli errori durante l'aggiunta/modifica delle pagine

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

> aggiunta di pulsanti personalizzati sotto ogni commento

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

## Lavorare con i plugin

### addSettings

(`&$config_vars`)

> aggiunta di impostazioni personalizzate del tuo plugin

```php
public function addSettings(array &$config_vars): void
{
    $config_vars['disqus'][] = ['text', 'shortname', 'subtext' => $this->txt['lp_disqus']['shortname_subtext'], 'required' => true];
}
```

### saveSettings

(`&$plugin_options`)

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

    $this->modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontCustomTemplate

(`$layouts`)

> aggiunta di layout personalizzate per il frontpage

```php
public function frontCustomTemplate(): void
{
    ob_start();

    // Your code

    $this->context['lp_layout'] = ob_get_clean();

    $this->modSettings['lp_frontpage_layout'] = '';
}
```

### frontAssets

> aggiunta di script e stili personalizzati sul frontpage

```php
public function frontAssets(): void
{
    $this->loadExtJS('https://' . $this->context['lp_disqus_plugin']['shortname'] . '.disqus.com/count.js');
}
```

### frontTopics

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> aggiunta di colonne e tabelle personalizzate, in base ai parametri ed ordinamenti della funzione _init_

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

> varie manipolazioni con i risultati della query sulla funzione _getData_

```php
public function frontTopicsOutput(array &$topics, array $row): void
{
    $topics[$row['id_topic']]['rating'] = empty($row['total_votes']) ? 0 : (number_format($row['total_value'] / $row['total_votes']));
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

(`&$all_icons, &$template`)

> aggiunta di un elenco personalizzato di icone (invece di FontAwesome)

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
    if ($this->user_info['is_admin'])
        $areas['lp_pages']['subsections']['import_from_ep'] = [$this->context['lp_icon_set']['import'] . $this->txt['lp_eh_portal']['label_name']];
}
```

### updateBlockAreas

(`&$areas`)

> aggiunta di schede personalizzate nelle impostazioni nell'area del blocco

```php
public function updateBlockAreas(array &$areas): void
{
    if ($this->user_info['is_admin'])
        $areas['import_from_tp'] = [new BlockImport, 'main'];
}
```

### updatePageAreas

(`&$areas`)

> aggiunta di schede personalizzate nelle impostazioni nell'area della pagina

```php
public function updatePageAreas(array &$areas): void
{
    if ($this->user_info['is_admin'])
        $areas['import_from_ep'] = [new Import, 'main'];
}
```

### updatePluginAreas

(`&$areas`)

> aggiunta di schede personalizzate nelle impostazioni dei Plugin

```php
public function updatePluginAreas(array &$areas): void
{
    $areas['add'] = [new Handler, 'add'];
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
