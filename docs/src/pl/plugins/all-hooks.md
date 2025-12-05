---
description: Lista wszystkich dostępnych haków portalu
order: 4
---

# Haki portalu

Light Portal jest świetnie rozbudowany dzięki wtyczkom. Haki umożliwiają wtyczkom interakcję z różnymi komponentami portalu.

## Podstawowe haki

### init

> re definiowanie zmiennych $txt , uruchamianie haków SMF itp.

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

> analizowanie zawartości niestandardowych typów bloków/stron

```php
public function parseContent(Event $e): void
{
    $e->args->content = Content::parse($e->args->content, 'html');
}
```

### prepareContent

> dodawanie niestandardowej zawartości wtyczki

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

> dodawanie dowolnego kodu w obszarze edycji bloku/strony

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

> pomaga przy wstępnym wczytywaniu arkuszy stylów, których potrzebujesz

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

## Pracuj z blokami

### prepareBlockParams

> dodawanie parametrów bloku

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

> dodawanie niestandardowych reguł walidacji podczas dodawania/edycji bloków

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

> dodawanie niestandardowych błędów podczas dodawania/edycji bloku

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

> dodawanie pól niestandardowych do obszaru postów bloków

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

> niestandardowe akcje przy zapisywaniu/edycji bloków

### onBlockRemoving

> niestandardowe akcje przy usuwaniu bloków

```php
public function onBlockRemoving(Event $e): void
{
    foreach ($e->args->items as $item) {
        $this->cache()->forget('block_' . $item . '_cache');
    }
}
```

## Praca ze stronami

### preparePageParams

> dodawanie parametrów strony

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> dodawanie niestandardowych reguł sprawdzania poprawności podczas dodawania/edycji strony

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> dodawanie niestandardowych błędów podczas dodawania/edycji strony

### preparePageFields

> dodawanie pól niestandardowych do obszaru wpisu na stronie

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

> niestandardowe akcje na stronach zapisywania/edycji

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

> niestandardowe akcje przy usuwaniu stron

```php
public function onPageRemoving(Event $e): void
{
    foreach ($e->args->items as $item) {
        $this->cache()->forget('page_' . $item . '_cache');
    }
}
```

### preparePageData

> dodatkowe przygotowanie aktualnych danych strony portalu

```php
public function preparePageData(Event $e): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> możliwość wyświetlania czegoś przed zawartością strony portalu

### afterPageContent

> możliwość wyświetlania czegoś po zawartości strony portalu

### comments

> dodawanie własnego skryptu komentarza do bieżącego widoku strony

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

> dodawanie własnych przycisków poniżej każdego komentarza

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

## Pracuj ze wtyczkami

### addSettings

> dodawanie niestandardowych ustawień wtyczki

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

> dodatkowe działania po zapisaniu ustawień wtyczki

```php
public function saveSettings(Event $e): void
{
    $this->cache()->flush();
}
```

### prepareAssets

> zapisywanie zewnętrznych stylów, skryptów i obrazów w celu poprawy szybkości ładowania zasobów

```php
public function prepareAssets(Event $e): void
{
    $builder = new AssetBuilder($this);
    $builder->scripts()->add('https://cdn.jsdelivr.net/npm/apexcharts@3/dist/apexcharts.min.js');
    $builder->css()->add('https://cdn.jsdelivr.net/npm/apexcharts@3/dist/apexcharts.min.css');
    $builder->appendTo($e->args->assets);
}
```

## Pracuj z artykułami

### frontModes

> dodawanie trybów niestandardowych dla strony głównej

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    $e->args->currentMode = $this->mode;
}
```

### frontLayouts

> dodawanie niestandardowej logiki na stronie głównej

```php
public function frontLayouts(Event $e): void
{
    if (! str_contains($e->args->layout, $this->extension))
        return;

    $e->args->renderer = new LatteRenderer();
}
```

### layoutExtensions

> pozwala na dodawanie własnych rozszerzeń układu

```php
public function layoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> dodawanie własnych skryptów i stylów na stronie głównej

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

> dodawanie niestandardowych kolumn, tabel, kół, parametry i polecenia do funkcji _init_

```php
public function frontTopics(Event $e): void
{
    $e->args->wheres[] = ['t.num_replies > ?' => 1];
}
```

### frontTopicsRow

> różne manipulacje z wynikami zapytania do funkcji _getData_

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

> różne manipulacje z wynikami zapytania do funkcji _getData_

```php
public function frontPagesRow(Event $e): void
{
    $e->args->articles[$e->args->row['id']]['comments'] = $e->args->row['num_comments'] ?? 0;
}
```

### frontBoards

> dodawanie niestandardowych kolumn, tabel, kół, parametry i polecenia do funkcji _init_

```php
public function frontBoards(Event $e): void
{
    $e->args->columns['num_topics'] = new Expression('MIN(b.num_topics)');

    $e->args->wheres[] = fn(Select $select) => $select->where->greaterThan('b.num_topics', 5);
}
```

### frontBoardsRow

> różne manipulacje z wynikami zapytania do funkcji _getData_

```php
public function frontBoardsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_board']]['custom_field'] = 'value';
}
```

## Pracuj z ikonami

### prepareIconList

> dodawanie niestandardowej listy ikon (zamiast FontAwesome)

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

> dodawanie niestandardowego szablonu do wyświetlania ikon

```php
public function prepareIconTemplate(Event $e): void
{
    $e->args->template = "<i class=\"custom-class {$e->args->icon}\" aria-hidden=\"true\"></i>";
}
```

### changeIconSet

> możliwość rozszerzenia ikon interfejsu dostępnych przez tablicę "Utils::$context['lp_icon_set']"

```php
public function changeIconSet(Event $e): void
{
    $e->args->set['snowman'] = 'fa-solid fa-snowman';
}
```

## Ustawienia portalu

### extendBasicConfig

> dodawanie niestandardowych konfiguracji w obszarze podstawowych ustawień portalu

```php
public function extendBasicConfig(Event $e): void
{
    $e->args->configVars[] = ['text', 'option_key', 'subtext' => $this->txt['my_mod_description']];
}
```

### extendAdminAreas

> dodawanie niestandardowych obszarów portalu w Centrum Administracji

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

> dodawanie niestandardowych kart do ustawień obszaru bloku

```php
public function extendBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### extendPageAreas

> dodawanie niestandardowych kart do ustawień obszaru strony

```php
public function extendPageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### extendCategoryAreas

> dodawanie niestandardowych kart do ustawień obszaru kategorii

```php
public function extendCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### extendTagAreas

> dodawanie niestandardowych kart do ustawień obszaru tagów

### extendPluginAreas

> dodawanie własnych kart do ustawień obszaru wtyczek

```php
public function extendPluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Inne

### credits

> dodawanie praw autorskich używanych biblioteki/skryptów itp.

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
