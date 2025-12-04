---
description: Mevcut tüm portal kancalarının listesi
order: 4
---

# Portal kancaları

Light Portal, eklentiler sayesinde harika bir şekilde genişletilebilir. Kancalar, eklentilerin portalın çeşitli bileşenleriyle etkileşimde bulunmasına olanak tanır.

## Temel kancalar

### init

> $txt değişkenlerini yeniden tanımlama, SMF kancalarını çalıştırma vb.

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

> Özel blok/sayfa türlerinin içeriğini ayrıştırma

```php
public function parseContent(Event $e): void
{
    $e->args->content = Content::parse($e->args->content, 'html');
}
```

### prepareContent

> Eklentinizin özel içeriğini ekleme

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

> Blok/sayfa düzenleme alanına herhangi bir kod ekleme

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

> Gerekli stil dosyalarını önceden yüklemenize yardımcı olur

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

## Bloklarla çalışma

### prepareBlockParams

> Blok parametrelerinizi ekleme

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

> Blok ekleme/düzenleme sırasında özel doğrulama kuralları ekleme

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

> Blok ekleme/düzenleme sırasında özel hata işleme ekleme

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

> Blok gönderim alanına özel alanlar ekleme

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

> Blokları kaydetme/düzenleme sırasında özel eylemler

### onBlockRemoving

> Blokları kaldırma sırasında özel eylemler

```php
public function onBlockRemoving(Event $e): void
{
    foreach ($e->args->items as $item) {
        $this->cache()->forget('block_' . $item . '_cache');
    }
}
```

## Sayfalarla çalışma

### preparePageParams

> Sayfa parametrelerinizi ekleme

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> Sayfa ekleme/düzenleme sırasında özel doğrulama kuralları ekleme

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> Sayfa ekleme/düzenleme sırasında özel hata işleme ekleme

### preparePageFields

> Sayfa gönderim alanına özel alanlar ekleme

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

> Sayfaları kaydetme/düzenleme sırasında özel eylemler

### onCustomPageImport

> özel sayfa içeri almada özel eylem

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

> Sayfaları kaldırma sırasında özel eylemler

```php
public function onPageRemoving(Event $e): void
{
    foreach ($e->args->items as $item) {
        $this->cache()->forget('page_' . $item . '_cache');
    }
}
```

### preparePageData

> Portalın mevcut sayfa verilerini ek hazırlama

```php
public function preparePageData(Event $e): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> Portal sayfası içeriğinden önce bir şey görüntüleme yeteneği

### afterPageContent

> Portal sayfası içeriğinden sonra bir şey görüntüleme yeteneği

### comments

> Portalın mevcut sayfa görünümüne özel yorum scripti ekleme

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

> Her yorumun altında özel butonlar ekleme

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

## Eklentilerle çalışma

### addSettings

> Eklentinizin özel ayarlarını ekleme

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

> Eklenti ayarları kaydedildikten sonra ek eylemler

```php
public function saveSettings(Event $e): void
{
    $this->cache()->flush();
}
```

### prepareAssets

> Kaynak hızını artırmak için harici stilleri, scriptleri ve görselleri kaydetme

```php
public function prepareAssets(Event $e): void
{
    $builder = new AssetBuilder($this);
    $builder->scripts()->add('https://cdn.jsdelivr.net/npm/apexcharts@3/dist/apexcharts.min.js');
    $builder->css()->add('https://cdn.jsdelivr.net/npm/apexcharts@3/dist/apexcharts.min.css');
    $builder->appendTo($e->args->assets);
}
```

## Makalelerle çalışma

### frontModes

> Ana sayfa için özel modlar ekleme

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    $e->args->currentMode = $this->mode;
}
```

### frontLayouts

> Ana sayfada özel mantık ekleme

```php
public function frontLayouts(Event $e): void
{
    if (! str_contains($e->args->layout, $this->extension))
        return;

    $e->args->renderer = new LatteRenderer();
}
```

### layoutExtensions

> Özel düzen uzantıları ekleyelim

```php
public function layoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> Ana sayfada özel scriptler ve stiller ekleme

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

> _init_ fonksiyonuna özel sütunlar, tablolar, where'ler, parametreler ve sıralamalar ekleme

```php
public function frontTopics(Event $e): void
{
    $e->args->wheres[] = ['t.num_replies > ?' => 1];
}
```

### frontTopicsRow

> _getData_ fonksiyonuyla sorgu sonuçları üzerinde çeşitli manipülasyonlar

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['replies'] = $e->args->row['num_replies'] ?? 0;
}
```

### frontPages

> _init_ işlevine özel sütunlar, joinler, where şartları, parametreler ve sıralamalar ekleme

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

> _getData_ fonksiyonuyla sorgu sonuçları üzerinde çeşitli manipülasyonlar

```php
public function frontPagesRow(Event $e): void
{
    $e->args->articles[$e->args->row['id']]['comments'] = $e->args->row['num_comments'] ?? 0;
}
```

### frontBoards

> _init_ fonksiyonuna özel sütunlar, tablolar, where'ler, parametreler ve sıralamalar ekleme

```php
public function frontBoards(Event $e): void
{
    $e->args->columns['num_topics'] = new Expression('MIN(b.num_topics)');

    $e->args->wheres[] = fn(Select $select) => $select->where->greaterThan('b.num_topics', 5);
}
```

### frontBoardsRow

> _getData_ fonksiyonuyla sorgu sonuçları üzerinde çeşitli manipülasyonlar

```php
public function frontBoardsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_board']]['custom_field'] = 'value';
}
```

## İkonlarla çalışma

### prepareIconList

> FontAwesome yerine özel ikon listesi ekleme

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

> İkonları görüntülemek için özel şablon ekleme

```php
public function prepareIconTemplate(Event $e): void
{
    $e->args->template = "<i class=\"custom-class {$e->args->icon}\" aria-hidden=\"true\"></i>";
}
```

### changeIconSet

> `Utils::$context['lp_icon_set']` dizisi aracılığıyla mevcut arayüz ikonlarını genişletme yeteneği

```php
public function changeIconSet(Event $e): void
{
    $e->args->set['snowman'] = 'fa-solid fa-snowman';
}
```

## Portal ayarları

### extendBasicConfig

> Portal temel ayarları alanında özel yapılandırmalar ekleme

```php
public function extendBasicConfig(Event $e): void
{
    $e->args->configVars[] = ['text', 'option_key', 'subtext' => $this->txt['my_mod_description']];
}
```

### extendAdminAreas

> Yönetim Merkezi'nde portal özel alanları ekleme

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

> Blok alanı ayarlarına özel sekmeler ekleme

```php
public function extendBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### extendPageAreas

> Sayfa alanı ayarlarına özel sekmeler ekleme

```php
public function extendPageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### extendCategoryAreas

> Kategori alanı ayarlarına özel sekmeler ekleme

```php
public function extendCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### extendTagAreas

> Etiket alanı ayarlarına özel sekmeler ekleme

### extendPluginAreas

> Eklenti alanı ayarlarına özel sekmeler ekleme

```php
public function extendPluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Çeşitli

### credits

> Kullanılan kütüphanelerin/scriptlerin vb. telif haklarını ekleme

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

> portal eklentilerini indirme taleplerini karşılama

```php
public function downloadRequest(Event $e): void
{
    if ($e->args->attachRequest['id'] === (int) $this->request()->get('attach')) {
        // Some handling
    }
}
```
