---
description: Λίστα με όλα τα διαθέσιμα άγκιστρα πύλης
order: 4
---

# Άγκιστρα πύλης

Το Light Portal είναι υπέροχα επεκτάσιμο χάρη στα πρόσθετα. Τα άγκιστρα επιτρέπουν στις προσθήκες να αλληλεπιδρούν με διάφορα στοιχεία της πύλης.

## Βασικοί άγκιστρα

### init

> επαναπροσδιορισμός μεταβλητών $txt, εκτέλεση αγκίστρων SMF κ.λ.π.

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

> ανάλυση περιεχομένου προσαρμοσμένων τύπων μπλοκ/σελίδων

```php
public function parseContent(string &$content, string $type): void
{
    if ($type === 'markdown')
        $content = $this->getParsedContent($content);
}
```

### prepareContent

(`$data, $parameters`)

> προσθήκη προσαρμοσμένου περιεχομένου της προσθήκης σας

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

> προσθήκη οποιουδήποτε κώδικα στην περιοχή επεξεργασίας μπλοκ/σελίδων

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

> βοηθά στην προφόρτωση των φύλλων στυλ που χρειάζεστε

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

## Εργαστείτε με μπλοκ

### prepareBlockParams

(`&$params`)

> προσθέτοντας τις παραμέτρους του μπλοκ σας

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

> προσθήκη προσαρμοσμένων κανόνων επικύρωσης κατά την φραγή προσθήκης/επεξεργασίας

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

> προσθήκη προσαρμοσμένου χειρισμού σφαλμάτων κατά την προσθήκη/επεξεργασία αποκλεισμού

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

> προσθήκη προσαρμοσμένων πεδίων στην περιοχή ανάρτησης μπλοκ

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

> προσαρμοσμένες ενέργειες για αποθήκευση/επεξεργασία μπλοκ

### onBlockRemoving

(`$items`)

> προσαρμοσμένες ενέργειες για την αφαίρεση μπλοκ

## Εργαστείτε με σελίδες

### preparePageParams

(`&$params`)

> προσθέτοντας τις παραμέτρους της σελίδας σας

```php
public function preparePageParams(array &$params): void
{
    $params['meta_robots'] = '';
    $params['meta_rating'] = '';
}
```

### validatePageParams

(`&$params`)

> προσθήκη προσαρμοσμένων κανόνων επικύρωσης κατά την προσθήκη/επεξεργασία σελίδας

```php
public function validatePageParams(array &$params): void
{
    $params['meta_robots'] = FILTER_DEFAULT;
    $params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

(`&$errors, $data`)

> προσθήκη προσαρμοσμένου χειρισμού σφαλμάτων κατά την προσθήκη/επεξεργασία σελίδας

### preparePageFields

> προσθήκη προσαρμοσμένων πεδίων στην περιοχή ανάρτησης σελίδας

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

> προσαρμοσμένες ενέργειες κατά την αποθήκευση/επεξεργασία σελίδων

### onPageRemoving

(`$items`)

> προσαρμοσμένες ενέργειες κατά την κατάργηση σελίδων

### preparePageData

(`&$data`, `$is_author`)

> πρόσθετη προετοιμασία των δεδομένων τρέχουσας σελίδας της πύλης

```php
public function preparePageData(): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> δυνατότητα εμφάνισης κάτι πριν από το περιεχόμενο της σελίδας της πύλης

### afterPageContent

> δυνατότητα εμφάνισης κάτι μετά το περιεχόμενο της σελίδας της πύλης

### comments

> προσθήκη προσαρμοσμένου σεναρίου σχολίων στην τρέχουσα προβολή σελίδας της πύλης

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

> προσθέτοντας προσαρμοσμένα κουμπιά κάτω από κάθε σχόλιο

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

## Εργαστείτε με πρόσθετα

### addSettings

(`&$settings`)

> προσθήκη προσαρμοσμένων ρυθμίσεων της προσθήκης σας

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

> πρόσθετες ενέργειες μετά την αποθήκευση των ρυθμίσεων της προσθήκης

### prepareAssets

(`&$assets`)

> αποθήκευση εξωτερικών στυλ, σεναρίων και εικόνων για τη βελτίωση της ταχύτητας φόρτωσης των πόρων

```php
public function prepareAssets(array &$assets): void
{
    $assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Εργαστείτε με άρθρα

### frontModes

(`&$modes`)

> προσθέτοντας προσαρμοσμένες λειτουργίες για την πρώτη σελίδα

```php
public function frontModes(array &$modes): void
{
    $modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> προσθέτοντας προσαρμοσμένες λειτουργίες για την πρώτη σελίδα

### customLayoutExtensions

(`&$extensions`)

> ας προσθέσουμε επεκτάσεις προσαρμοσμένης διάταξης

```php
public function customLayoutExtensions(array &$extensions): void
{
    $extensions[] = '.twig';
}
```

### frontAssets

> προσθέτοντας προσαρμοσμένα σενάρια και στυλ στην πρώτη σελίδα

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

> προσθήκη προσαρμοσμένων στηλών, πινάκων, Wheres, παραμέτρων και παραγγελιών στη συνάρτηση __init__function

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

> διάφορους χειρισμούς με αποτελέσματα ερωτημάτων στη συνάρτηση _getData_λειτουργίες

```php
public function frontTopicsOutput(array &$topics, array $row): void
{
    $topics[$row['id_topic']]['rating'] = empty($row['total_votes'])
        ? 0 : (number_format($row['total_value'] / $row['total_votes']));
}
```

### frontPages

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> προσθήκη προσαρμοσμένων στηλών, πινάκων, Wheres, παραμέτρων και παραγγελιών στη συνάρτηση __init__λειτουργίες

### frontPagesOutput

(`&$pages, $row`)

> διάφορους χειρισμούς με αποτελέσματα ερωτημάτων στη συνάρτηση _getData_λειτουργίες

### frontBoards

(`&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders`)

> προσθήκη προσαρμοσμένων στηλών, πινάκων, Wheres, παραμέτρων και παραγγελιών στη συνάρτηση __init__function

### frontBoardsOutput

(`&$boards, $row`)

> διάφορους χειρισμούς με αποτελέσματα ερωτημάτων στη συνάρτηση _getData_λειτουργίες

## Εργαστείτε με εικονίδια

### prepareIconList

(`&$icons, &$template`)

> προσθήκη προσαρμοσμένης λίστας εικονιδίων (αντί για FontAwesome)

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

> προσθήκη προσαρμοσμένου προτύπου για την εμφάνιση εικονιδίων

### changeIconSet

(`&$set`)

> δυνατότητα επέκτασης εικονιδίων διεπαφής που είναι διαθέσιμα μέσω του πίνακα "Utils::$context['lp_icon_set']"

## Ρυθμίσεις πόρταλ

### updateAdminAreas

(`&$areas`)

> προσθέτει τις προσαρμοσμένες περιοχές της πύλης στο Κέντρο διαχείρισης

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

> προσθήκη προσαρμοσμένων καρτελών στις ρυθμίσεις περιοχής αποκλεισμού

```php
public function updateBlockAreas(array &$areas): void
{
    $areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

(`&$areas`)

> προσθήκη προσαρμοσμένων καρτελών στις ρυθμίσεις της περιοχής σελίδας

```php
public function updatePageAreas(array &$areas): void
{
    $areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

(`&$areas`)

> προσθήκη προσαρμοσμένων καρτελών στις ρυθμίσεις περιοχής κατηγορίας

```php
public function updateCategoryAreas(array &$areas): void
{
    $areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

(`&$areas`)

> προσθήκη προσαρμοσμένων καρτελών στις ρυθμίσεις της περιοχής ετικετών

### updatePluginAreas

(`&$areas`)

> προσθέτει προσαρμοσμένες καρτέλες στις ρυθμίσεις της περιοχής προσθήκης

```php
public function updatePluginAreas(array &$areas): void
{
    $areas['add'] = [new Handler(), 'add'];
}
```

## Διάφορα

### credits

(`&$links`)

> προσθήκη πνευματικών δικαιωμάτων χρησιμοποιημένων βιβλιοθηκών/σεναρίων κ.λπ.

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
