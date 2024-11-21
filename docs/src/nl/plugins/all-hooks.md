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
        'seek_images'    => false
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

### onPageRemoving

> aangepaste acties bij het verwijderen van pagina's

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
    $e->args->settings[$this->name][] = [
        'text',
        'shortname',
        'subtext' => $this->txt['shortname_subtext'],
        'required' => true,
    ];
}
```

### saveSettings

> aanvullende acties na het opslaan van de plugin instellingen

### prepareAssets

> externe stijlen, scripts en afbeeldingen opslaan om het laden van bronsnelheid te verbeteren

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Werk met artikelen

### frontModes

> aangepaste modi voor de voorpagina toevoegen

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> aangepaste logica toevoegen aan de voorpagina

### customLayoutExtensions

> Laat aangepaste lay-out extensies toevoegen

```php
public function customLayoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> aangepaste scripts en stijlen toevoegen op de voorpagina

```php
public function frontAssets(): void
{
    Theme::loadJavaScriptFile(
        'https://' . $this->context['shortname'] . '.disqus.com/count.js',
        ['external' => true],
    );
}
```

### frontTopics

> aangepaste kolommen, tabellen, waarmee, parameters en orders worden toegevoegd aan de functie _init_

```php
public function frontTopics(Event $e): void
{
    if (! class_exists('TopicRatingBar'))
        return;

    $e->args->columns[] = 'tr.total_votes, tr.total_value';

    $e->args->tables[] = 'LEFT JOIN {db_prefix}topic_ratings AS tr ON (t.id_topic = tr.id)';
}
```

### frontTopicsRow

> verschillende manipulaties met query resultaten naar _getData_ functie

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> aangepaste kolommen, tabellen, waarmee, parameters en orders worden toegevoegd aan de functie _init_

### frontPagesRow

> verschillende manipulaties met query resultaten naar _getData_ functie

### frontBoards

> aangepaste kolommen, tabellen, waarmee, parameters en orders worden toegevoegd aan de functie _init_

### frontBoardsRow

> verschillende manipulaties met query resultaten naar _getData_ functie

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

### changeIconSet

> mogelijkheid om interface iconen die beschikbaar zijn via `Utils::$context['lp_icon_set']` array uit te breiden

## Portaal instellingen

### extendBasicConfig

> het toevoegen van aangepaste configuraties in het basisinstellingen gebied van de portal

### updateAdminAreas

> het toevoegen van de aangepaste portaalgebieden in het Administratorcentrum

```php
public function updateAdminAreas(Event $e): void
{
    if (User::$info['is_admin']) {
        $e->args->areas['lp_pages']['subsections']['import_from_ep'] = [
            Utils::$context['lp_icon_set']['import'] . $this->txt['label_name']
        ];
    }
}
```

### updateBlockAreas

> Aangepaste tabbladen toevoegen aan instellingen van Blok gebied

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> Aangepaste tabbladen toevoegen aan instellingen van pagina-gebied

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> Aangepaste tabbladen toevoegen aan instellingen voor categoriegebied

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> Aangepaste tabbladen toevoegen aan instellingen van het taggebied

### updatePluginAreas

> eigen tabbladen toevoegen aan Plugin instellingen

```php
public function updatePluginAreas(Event $e): void
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
