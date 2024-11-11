---
description: Liste aller verfügbaren Portal-Hooks
order: 4
---

# Portal-Hooks

Light Portal ist wunderbar erweiterbar dank Plugins. Hooks erlauben es Plugins mit verschiedenen Komponenten des Portals zu interagieren.

## Grundlegende Hooks

### init

> neu definiert $txt Variablen, laufende SMF Hooks etc.

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

> analysiert Inhalte von benutzerdefinierten Block/Seitentypen

```php
public function parseContent(Event $e): void
{
    if ($e->args->type === 'markdown') {
        $e->args->content = $this->getParsedContent($e->args->content);
    }
}
```

### prepareContent

> benutzerdefinierte Inhalte Ihres Plugins hinzufügen

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

> füge beliebigen Code auf der Block/Seitenbearbeitung hinzu

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

> hilft beim Vorladen der benötigten Stylesheets

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

## Arbeiten mit Blöcken

### prepareBlockParams

> deine Blockparameter hinzufügen

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

> benutzerdefinierte Validierungsregeln beim Hinzufügen/Bearbeiten hinzufügen

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

> benutzerdefinierte Fehlerbehandlung beim Hinzufügen/Bearbeiten hinzufügen

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

> benutzerdefinierte Felder zum Block Beitragsbereich hinzufügen

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

> benutzerdefinierte Aktionen zum Speichern/Bearbeiten von Blöcken

### onBlockRemoving

> benutzerdefinierte Aktionen zum Entfernen von Blöcken

## Mit Seiten arbeiten

### preparePageParams

> füge deine Seitenparameter hinzu

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> benutzerdefinierte Validierungsregeln beim Hinzufügen/Bearbeiten der Seite hinzufügen

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> benutzerdefinierte Fehlerbehandlung beim Hinzufügen/Bearbeiten der Seite hinzufügen

### preparePageFields

> benutzerdefinierte Felder zum Beitragsbereich der Seite hinzufügen

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

> benutzerdefinierte Aktionen zum Speichern/Bearbeiten von Seiten

### onPageRemoving

> benutzerdefinierte Aktionen beim Entfernen von Seiten

### preparePageData

> zusätzliche Vorbereitung der aktuellen Seitendaten des Portals

```php
public function preparePageData(): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> Möglichkeit, etwas vor dem Inhalt der Portalseite anzuzeigen

### afterPageContent

> Möglichkeit, etwas nach dem Inhalt der Portalseite anzuzeigen

### comments

> benutzerdefiniertes Kommentar-Skript zur aktuellen Seitenansicht des Portals hinzufügen

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

> benutzerdefinierte Schaltflächen unter jedem Kommentar hinzufügen

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

## Mit Plugins arbeiten

### addSettings

> benutzerdefinierte Einstellungen des Plugins hinzufügen

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

> zusätzliche Aktionen nach dem Speichern der Plugin-Einstellungen

### prepareAssets

> Speichere externe Stile, Skripte und Bilder, um das Laden von Ressourcen zu verbessern

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Mit Artikeln arbeiten

### frontModes

> benutzerdefinierte Modi für die Startseite hinzufügen

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> benutzerdefinierte Logik auf der Startseite hinzufügen

### customLayoutExtensions

> lässt benutzerdefinierte Layout-Erweiterungen hinzufügen

```php
public function customLayoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> benutzerdefinierte Skripte und Stile auf der Startseite hinzufügen

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

> benutzerdefinierte Spalten, Tabellen, wozu, Parameter und Orders zu _init_-Funktion hinzufügen

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

> verschiedene Manipulationen mit Abfrageergebnissen zu _getData_ Funktion

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> benutzerdefinierte Spalten, Tabellen, wozu, Parameter und Orders zu _init_-Funktion hinzufügen

### frontPagesRow

> verschiedene Manipulationen mit Abfrageergebnissen zu _getData_ Funktion

### frontBoards

> benutzerdefinierte Spalten, Tabellen, wozu, Parameter und Orders zu _init_-Funktion hinzufügen

### frontBoardsRow

> verschiedene Manipulationen mit Abfrageergebnissen zu _getData_ Funktion

## Mit Symbolen arbeiten

### prepareIconList

> benutzerdefinierte Liste von Symbolen hinzufügen (statt FontAwesome)

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

> benutzerdefinierte Vorlage für die Anzeige von Symbolen hinzufügen

### changeIconSet

> Fähigkeit Interface-Icons über `Utils::$context['lp_icon_set']` Array zu erweitern

## Portaleinstellungen

### extendBasicConfig

> benutzerdefinierte Konfigurationen im Basis-Bereich des Portals hinzufügen

### updateAdminAreas

> die benutzerdefinierten Bereiche des Portals im Administrationszentrum hinzufügen

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

> benutzerdefinierte Tabs zu den Blockbereichseinstellungen hinzufügen

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> benutzerdefinierte Tabs zu den Seitenbereichseinstellungen hinzufügen

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> benutzerdefinierte Tabs zu den Kategoriebereichseinstellungen hinzufügen

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> benutzerdefinierte Tabs zu den Tag-Bereich-Einstellungen hinzufügen

### updatePluginAreas

> benutzerdefinierte Tabs zu den Plugin-Gebietseinstellungen hinzufügen

```php
public function updatePluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Sonstiges

### credits

> das Hinzufügen von Urheberrechten von benutzten Bibliotheken/Skripten usw.

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
