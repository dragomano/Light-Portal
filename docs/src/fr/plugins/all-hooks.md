---
description: Liste de tous les crochets de portail disponibles
order: 4
---

# Portal hooks

Light Portal est merveilleusement extensible grâce aux plugins. Les crochets permettent aux plugins d'interagir avec divers composants du portail.

## Crochets basiques

### init

> redéfinissant les variables $txt , exécutant des crochets SMF, etc.

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

> analyse du contenu des types de blocs/pages personnalisés

```php
public function parseContent(Event $e): void
{
    $e->args->content = Content::parse($e->args->content, 'html');
}
```

### prepareContent

> ajout de contenu personnalisé de votre plugin

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

> ajouter n'importe quel code sur la zone d'édition de blocs/pages

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

> aide à précharger les feuilles de style dont vous avez besoin

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

## Travailler avec des blocs

### prepareBlockParams

> ajout de vos paramètres de blocage

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

> ajout de règles de validation personnalisées lors de l'ajout/édition de blocs

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

> ajout d'une gestion personnalisée des erreurs lors de l'ajout/édition de blocs

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

> ajout de champs personnalisés à la zone de publication du bloc

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

> actions personnalisées sur les blocs d'enregistrement/édition

### onBlockRemoving

> actions personnalisées sur la suppression de blocs

## Travailler avec les pages

### preparePageParams

> ajout des paramètres de votre page

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> ajout de règles de validation personnalisées lors de l'ajout/modification de la page

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> ajout d'une gestion personnalisée des erreurs lors de l'ajout/modification de la page

### preparePageFields

> ajout de champs personnalisés à la zone de publication de la page

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

> actions personnalisées sur les pages d'enregistrement/édition

### onPageRemoving

> actions personnalisées sur la suppression de pages

### preparePageData

> préparation supplémentaire des données de la page actuelle du portail

```php
public function preparePageData(Event $e): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> capacité d'afficher quelque chose avant le contenu de la page du portail

### afterPageContent

> capacité d'afficher quelque chose après le contenu de la page du portail

### comments

> ajout d'un script de commentaire personnalisé à la vue actuelle de la page du portail

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

> ajouter des boutons personnalisés sous chaque commentaire

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

## Travailler avec les plugins

### addSettings

> ajout de paramètres personnalisés de votre plugin

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

> actions supplémentaires après l'enregistrement des paramètres du plugin

### prepareAssets

> sauvegarde des styles externes, des scripts et des images pour améliorer la vitesse de chargement des ressources

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Travailler avec des articles

### frontModes

> ajout de modes personnalisés pour la page d'accueil

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> ajout d'une logique personnalisée sur la page d'accueil

### customLayoutExtensions

> permet d'ajouter des extensions de mise en page personnalisée

```php
public function customLayoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> ajout de scripts et de styles personnalisés sur la page d'accueil

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

> ajout de colonnes personnalisées, tableaux, quand, params et ordres à la fonction _init_

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

> diverses manipulations avec les résultats de la requête à la fonction _getData_

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> ajout de colonnes personnalisées, tableaux, quand, params et ordres à la fonction _init_

### frontPagesRow

> diverses manipulations avec les résultats de la requête à la fonction _getData_

### frontBoards

> ajout de colonnes personnalisées, tableaux, quand, params et ordres à la fonction _init_

### frontBoardsRow

> diverses manipulations avec les résultats de la requête à la fonction _getData_

## Travailler avec les icônes

### prepareIconList

> ajout d'une liste personnalisée d'icônes (au lieu de FontAwesome)

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

> ajout d'un modèle personnalisé pour afficher les icônes

### changeIconSet

> possibilité d'étendre les icônes d'interface disponibles via le tableau `Utils::$context['lp_icon_set']`

## Paramètres du portail

### extendBasicConfig

> ajout de configurations personnalisées dans la zone de configuration de base du portail

### updateAdminAreas

> ajout des zones personnalisées du portail dans le centre d'administration

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

> ajout d'onglets personnalisés dans les paramètres de la zone de blocage

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> ajout d'onglets personnalisés dans les paramètres de la zone de page

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> ajout d'onglets personnalisés dans les paramètres de la zone de catégorie

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> ajouter des onglets personnalisés dans les paramètres de la zone de tags

### updatePluginAreas

> ajout d'onglets personnalisés dans les paramètres de la zone de plugin

```php
public function updatePluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Divers

### credits

> ajout de droits d'auteur sur les bibliothèques/scripts, etc.

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
