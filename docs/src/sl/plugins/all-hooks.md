---
description: Seznam vseh razpoložljivih portalnih kavljev.
order: 4
---

# Kavlji portala

Light Portal je izjemno razširljiv zahvaljujoč vtičnikom. Kavlji omogočajo vtičnikom, da se povežejo z različnimi komponentami portala.

## Osnovni kavlji.

### init

> Predefiniranje $txt spremenljivk, izvajanje SMF kavljev itd.

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

> Obdelava vsebine lastnih vrst blokov/strani

```php
public function parseContent(Event $e): void
{
    $e->args->content = Content::parse($e->args->content, 'html');
}
```

### prepareContent

> dodajanje prilagojene vsebine tvojega vtičnika

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

> Dodajanje poljubne kode v urejevalno območje bloka/strani

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

> omogoča prednaložitev potrebnih slogovnih datotek.

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

## Delo z bloki

### prepareBlockParams

> dodajanje parametrov tvojega bloka.

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

> dodajanje prilagojenih pravil za preverjanje pri dodajanju/urejanju bloka.

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

> dodajanje prilagojenega ravnanja z napakami pri dodajanju/urejanju bloka.

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

> dodajanje prilagojenih polj v območje objave bloka.

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

> prilagojene akcije ob shranjevanju/urejanju blokov.

### onBlockRemoving

> prilagojene akcije ob odstranjevanju blokov.

## Delo s stranmi

### preparePageParams

> dodajanje parametrov tvoje strani.

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> dodajanje prilagojenih pravil za preverjanje pri dodajanju/urejanju strani.

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> dodajanje prilagojenega ravnanja z napakami pri dodajanju/urejanju strani.

### preparePageFields

> dodajanje prilagojenih polj v območje objave stani.

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

> prilagojene akcije ob shranjevanju/urejanju strani.

### onPageRemoving

> prilagojene akcije ob odstranjevanju strani.

### preparePageData

> dodatno pripravljanje podatkov trenutne strani portala

```php
public function preparePageData(Event $e): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> možnost prikaza nečesa pred vsebino strani portala

### afterPageContent

> možnost prikaza nečesa po vsebini strani portala

### comments

> dodajanje lastne skripte za komentarje v prikaz trenutne strani portala

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

> dodajanje lastnih gumbov pod vsak komentar

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

## Delo z vtičniki

### addSettings

> dodajanje prilagojenih nastavitev tvojega vtičnika

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

> dodatna dejanja po shranjevanju nastavitev vtičnika

### prepareAssets

> shranjevanje zunanjih stilov, skript in slik za izboljšanje hitrosti nalaganja virov

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Delo z članki

### frontModes

> dodajanje lastnih načinov za začetno stran

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> dodajanje lastne logike na začetno stran

```php
public function frontLayouts(Event $e): void
{
    if (! str_contains($e->args->layout, $this->extension))
        return;

    $e->args->renderer = new LatteRenderer();
}
```

### layoutExtensions

> dodajanje razširitev za prilagojeno postavitev

```php
public function layoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> dodajanje lastnih skript in stilov na začetno stran

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

> dodajanje lastnih stolpcev, tabel, pogojev (wheres), parametrov in razvrščanj (orders) v _init_ funkcijo

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

> različne manipulacije z rezultati poizvedb v _getData _ funkcijo

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> dodajanje lastnih stolpcev, tabel, pogojev (wheres), parametrov in razvrščanj (orders) v _init_ funkcijo

### frontPagesRow

> različne manipulacije z rezultati poizvedb v _getData _ funkcijo

### frontBoards

> dodajanje lastnih stolpcev, tabel, pogojev (wheres), parametrov in razvrščanj (orders) v _init_ funkcijo

### frontBoardsRow

> različne manipulacije z rezultati poizvedb v _getData _ funkcijo

## Delo z ikonami

### prepareIconList

> dodajanje lastnega seznama ikon (namesto FontAwesome)

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

> dodajanje lastne predloge za prikazovanje ikon

### changeIconSet

> možnost razširitve naborov ikon vmesnika preko polja Utils::$context['lp_icon_set']

## Nastavitve portala

### extendBasicConfig

> dodajanje prilagojenih nastavitev v osnovno nastavitveno območje portala

### updateAdminAreas

> dodajanje lastnih nastavitev v osnovni konfiguraciji portala

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

> dodajanje prilagojenih zavihkov v nastavitve blokov

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> dodajanje prilagojenih zavihkov v nastavitve strani

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> dodajanje prilagojenih zavihkov v nastavitve kategorij

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> dodajanje prilagojenih zavihkov v nastavitve oznak

### updatePluginAreas

> dodajanje prilagojenih zavihkov v nastavitve območja vtičnika

```php
public function updatePluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Razno

### credits

> dodajanje avtorskih pravic, uporabljenih knjižnic/skriptov itd.

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
