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

> ανάλυση περιεχομένου προσαρμοσμένων τύπων μπλοκ/σελίδων

```php
public function parseContent(Event $e): void
{
    $e->args->content = Content::parse($e->args->content, 'html');
}
```

### prepareContent

> προσθήκη προσαρμοσμένου περιεχομένου της προσθήκης σας

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

> προσθήκη οποιουδήποτε κώδικα στην περιοχή επεξεργασίας μπλοκ/σελίδων

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

> βοηθά στην προφόρτωση των φύλλων στυλ που χρειάζεστε

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

## Εργαστείτε με μπλοκ

### prepareBlockParams

> προσθέτοντας τις παραμέτρους του μπλοκ σας

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

> προσθήκη προσαρμοσμένων κανόνων επικύρωσης κατά την φραγή προσθήκης/επεξεργασίας

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

> προσθήκη προσαρμοσμένου χειρισμού σφαλμάτων κατά την προσθήκη/επεξεργασία αποκλεισμού

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

> προσθήκη προσαρμοσμένων πεδίων στην περιοχή ανάρτησης μπλοκ

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

> προσαρμοσμένες ενέργειες για αποθήκευση/επεξεργασία μπλοκ

### onBlockRemoving

> προσαρμοσμένες ενέργειες για την αφαίρεση μπλοκ

## Εργαστείτε με σελίδες

### preparePageParams

> προσθέτοντας τις παραμέτρους της σελίδας σας

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> προσθήκη προσαρμοσμένων κανόνων επικύρωσης κατά την προσθήκη/επεξεργασία σελίδας

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> προσθήκη προσαρμοσμένου χειρισμού σφαλμάτων κατά την προσθήκη/επεξεργασία σελίδας

### preparePageFields

> προσθήκη προσαρμοσμένων πεδίων στην περιοχή ανάρτησης σελίδας

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

> προσαρμοσμένες ενέργειες κατά την αποθήκευση/επεξεργασία σελίδων

### onPageRemoving

> προσαρμοσμένες ενέργειες κατά την κατάργηση σελίδων

### preparePageData

> πρόσθετη προετοιμασία των δεδομένων τρέχουσας σελίδας της πύλης

```php
public function preparePageData(Event $e): void
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

> προσθέτοντας προσαρμοσμένα κουμπιά κάτω από κάθε σχόλιο

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

## Εργαστείτε με πρόσθετα

### addSettings

> προσθήκη προσαρμοσμένων ρυθμίσεων της προσθήκης σας

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

> πρόσθετες ενέργειες μετά την αποθήκευση των ρυθμίσεων της προσθήκης

### prepareAssets

> αποθήκευση εξωτερικών στυλ, σεναρίων και εικόνων για τη βελτίωση της ταχύτητας φόρτωσης των πόρων

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Εργαστείτε με άρθρα

### frontModes

> προσθέτοντας προσαρμοσμένες λειτουργίες για την πρώτη σελίδα

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> προσθέτοντας προσαρμοσμένες λειτουργίες για την πρώτη σελίδα

```php
public function frontLayouts(Event $e): void
{
    if (! str_contains($e->args->layout, $this->extension))
        return;

    $e->args->renderer = new LatteRenderer();
}
```

### layoutExtensions

> ας προσθέσουμε επεκτάσεις προσαρμοσμένης διάταξης

```php
public function layoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> προσθέτοντας προσαρμοσμένα σενάρια και στυλ στην πρώτη σελίδα

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

> προσθήκη προσαρμοσμένων στηλών, πινάκων, Wheres, παραμέτρων και παραγγελιών στη συνάρτηση __init__function

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

> διάφορους χειρισμούς με αποτελέσματα ερωτημάτων στη συνάρτηση _getData_λειτουργίες

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> προσθήκη προσαρμοσμένων στηλών, πινάκων, Wheres, παραμέτρων και παραγγελιών στη συνάρτηση __init__λειτουργίες

### frontPagesRow

> διάφορους χειρισμούς με αποτελέσματα ερωτημάτων στη συνάρτηση _getData_λειτουργίες

### frontBoards

> προσθήκη προσαρμοσμένων στηλών, πινάκων, Wheres, παραμέτρων και παραγγελιών στη συνάρτηση __init__function

### frontBoardsRow

> διάφορους χειρισμούς με αποτελέσματα ερωτημάτων στη συνάρτηση _getData_λειτουργίες

## Εργαστείτε με εικονίδια

### prepareIconList

> προσθήκη προσαρμοσμένης λίστας εικονιδίων (αντί για FontAwesome)

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

> προσθήκη προσαρμοσμένου προτύπου για την εμφάνιση εικονιδίων

### changeIconSet

> δυνατότητα επέκτασης εικονιδίων διεπαφής που είναι διαθέσιμα μέσω του πίνακα "Utils::$context['lp_icon_set']"

## Ρυθμίσεις πόρταλ

### extendBasicConfig

> προσθέτοντας προσαρμοσμένες ρυθμίσεις παραμέτρων στην περιοχή βασικών ρυθμίσεων της πύλης

### updateAdminAreas

> προσθέτει τις προσαρμοσμένες περιοχές της πύλης στο Κέντρο διαχείρισης

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

> προσθήκη προσαρμοσμένων καρτελών στις ρυθμίσεις περιοχής αποκλεισμού

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> προσθήκη προσαρμοσμένων καρτελών στις ρυθμίσεις της περιοχής σελίδας

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> προσθήκη προσαρμοσμένων καρτελών στις ρυθμίσεις περιοχής κατηγορίας

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> προσθήκη προσαρμοσμένων καρτελών στις ρυθμίσεις της περιοχής ετικετών

### updatePluginAreas

> προσθέτει προσαρμοσμένες καρτέλες στις ρυθμίσεις της περιοχής προσθήκης

```php
public function updatePluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Διάφορα

### credits

> προσθήκη πνευματικών δικαιωμάτων χρησιμοποιημένων βιβλιοθηκών/σεναρίων κ.λπ.

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
