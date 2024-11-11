---
description: Lista wszystkich dostępnych hooków portalu
order: 4
---

# Portal hooks

Lekki portal jest świetnie rozbudowany dzięki wtyczkom. Haki umożliwiają wtyczkom interakcję z różnymi komponentami portalu.

## Podstawowe haki

### init

> redefiniowanie zmiennych $txt , uruchamianie haczyków SMF itp.

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
    if ($e->args->type === 'markdown') {
        $e->args->content = $this->getParsedContent($e->args->content);
    }
}
```

### prepareContent

> dodawanie niestandardowej zawartości wtyczki

```php
public function prepareContent(Event $e): void
{
    if ($e->args->data->type !== 'user_info')
        return;

    $this->setTemplate();

    $userData = $this->cache('user_info_addon_u' . Utils::$context['user']['id'])
        ->setLifeTime($e->args->data->cache_time)
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
    if (Utils::$context['current_block']['type'] !== 'article_list')
        return;

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

> dodawanie niestandardowych reguł walidacji podczas dodawania/edycji bloków

```php
public function validateBlockParams(Event $e): void
{
    if (Utils::$context['current_block']['type'] !== 'article_list')
        return;

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

    Lang::$txt['lp_post_error_no_ads_placement'] = Lang::$txt['lp_ads_block']['no_ads_placement'];

    if (empty($e->args->data['parameters']['ads_placement'])) {
        $e->args->errors[] = 'no_ads_placement';
    }
}
```

### prepareBlockFields

> dodawanie pól niestandardowych do obszaru postów bloków

```php
public function prepareBlockFields(): void
{
    if (Utils::$context['current_block']['type'] !== 'article_list')
        return;

    RadioField::make('display_type', Lang::$txt['lp_article_list']['display_type'])
        ->setTab(BlockArea::TAB_CONTENT)
        ->setOptions(Lang::$txt['lp_article_list']['display_type_set'])
        ->setValue(Utils::$context['lp_block']['options']['display_type']);

    CheckboxField::make('seek_images', Lang::$txt['lp_article_list']['seek_images'])
        ->setValue(Utils::$context['lp_block']['options']['seek_images']);
}
```

### onBlockSaving

> niestandardowe akcje przy zapisywaniu/edycji bloków

### onBlockRemoving

> niestandardowe akcje przy usuwaniu bloków

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
public function preparePageFields(): void
{
    VirtualSelectField::make('meta_robots', Lang::$txt['lp_extended_meta_tags']['meta_robots'])
        ->setTab(PageArea::TAB_SEO)
        ->setOptions(array_combine($this->meta_robots, Lang::$txt['lp_extended_meta_tags']['meta_robots_set']))
        ->setValue(Utils::$context['lp_page']['options']['meta_robots']);
}
```

### onPageSaving

> niestandardowe akcje na stronach zapisywania/edycji

### onPageRemoving

> niestandardowe akcje przy usuwaniu stron

### preparePageData

> dodatkowe przygotowanie aktualnych danych strony portalu

```php
public function preparePageData(): void
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

## Pracuj z wtyczkami

### addSettings

> dodawanie niestandardowych ustawień wtyczki

```php
public function addSettings(Event $e): void
{
    $e->args->settings['disqus'][] = [
        'text',
        'shortname',
        'subtext' => Lang::$txt['lp_disqus']['shortname_subtext'],
        'required' => true,
    ];
}
```

### saveSettings

> dodatkowe działania po zapisaniu ustawień wtyczki

### prepareAssets

> zapisywanie zewnętrznych stylów, skryptów i obrazów w celu poprawy szybkości ładowania zasobów

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Pracuj z artykułami

### frontModes

> dodawanie trybów niestandardowych dla strony głównej

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> dodawanie niestandardowej logiki na stronie głównej

### customLayoutExtensions

> pozwala na dodawanie własnych rozszerzeń układu

```php
public function customLayoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> dodawanie własnych skryptów i stylów na stronie głównej

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

> dodawanie niestandardowych kolumn, tabel, kół, paramów i zamówień do funkcji _init_

```php
public function frontTopics(Event $e): void
{
    if (! class_exists('TopicRatingBar'))
        return;

    $e->args->columns[] = 'tr.total_votes, tr.total_value';
    $e->args->tables[]  = 'LEFT JOIN {db_prefix}topic_ratings AS tr ON (t.id_topic = tr.id)';
}
```

### frontTopicsRow

> różne manipulacje z wynikami zapytania do funkcji _getData_

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> dodawanie niestandardowych kolumn, tabel, kół, paramów i zamówień do funkcji _init_

### frontPagesRow

> różne manipulacje z wynikami zapytania do funkcji _getData_

### frontBoards

> dodawanie niestandardowych kolumn, tabel, kół, paramów i zamówień do funkcji _init_

### frontBoardsRow

> różne manipulacje z wynikami zapytania do funkcji _getData_

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

### changeIconSet

> możliwość rozszerzenia ikon interfejsu dostępnych przez tablicę `Utils::$context['lp_icon_set']`

## Ustawienia portalu

### extendBasicConfig

> dodawanie niestandardowych konfiguracji w obszarze podstawowych ustawień portalu

### updateAdminAreas

> dodawanie niestandardowych obszarów portalu w Centrum Administracji

```php
public function updateAdminAreas(Event $e): void
{
    if (User::$info['is_admin']) {
        $e->args->areas['lp_pages']['subsections']['import_from_ep'] = [
            Utils::$context['lp_icon_set']['import'] . Lang::$txt['lp_eh_portal']['label_name']
        ];
    }
}
```

### updateBlockAreas

> dodawanie niestandardowych kart do ustawień obszaru bloku

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> dodawanie niestandardowych kart do ustawień obszaru strony

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> dodawanie niestandardowych kart do ustawień obszaru kategorii

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> dodawanie niestandardowych kart do ustawień obszaru tagów

### updatePluginAreas

> dodawanie własnych kart do ustawień obszaru wtyczek

```php
public function updatePluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Miscellaneous

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
