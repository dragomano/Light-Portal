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
        'seek_images'    => false
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

### onPageRemoving

> Sayfaları kaldırma sırasında özel eylemler

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
    $e->args->settings[$this->name][] = [
        'text',
        'shortname',
        'subtext' => $this->txt['shortname_subtext'],
        'required' => true,
    ];
}
```

### saveSettings

> Eklenti ayarları kaydedildikten sonra ek eylemler

### prepareAssets

> Kaynak hızını artırmak için harici stilleri, scriptleri ve görselleri kaydetme

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Makalelerle çalışma

### frontModes

> Ana sayfa için özel modlar ekleme

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> Ana sayfada özel mantık ekleme

### customLayoutExtensions

> Özel düzen uzantıları ekleyelim

```php
public function customLayoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> Ana sayfada özel scriptler ve stiller ekleme

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

> _init_ fonksiyonuna özel sütunlar, tablolar, where'ler, parametreler ve sıralamalar ekleme

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

> _getData_ fonksiyonuyla sorgu sonuçları üzerinde çeşitli manipülasyonlar

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> _init_ fonksiyonuna özel sütunlar, tablolar, where'ler, parametreler ve sıralamalar ekleme

### frontPagesRow

> _getData_ fonksiyonuyla sorgu sonuçları üzerinde çeşitli manipülasyonlar

### frontBoards

> _init_ fonksiyonuna özel sütunlar, tablolar, where'ler, parametreler ve sıralamalar ekleme

### frontBoardsRow

> _getData_ fonksiyonuyla sorgu sonuçları üzerinde çeşitli manipülasyonlar

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

### changeIconSet

> `Utils::$context['lp_icon_set']` dizisi aracılığıyla mevcut arayüz ikonlarını genişletme yeteneği

## Portal ayarları

### extendBasicConfig

> Portal temel ayarları alanında özel yapılandırmalar ekleme

### updateAdminAreas

> Yönetim Merkezi'nde portal özel alanları ekleme

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

> Blok alanı ayarlarına özel sekmeler ekleme

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> Sayfa alanı ayarlarına özel sekmeler ekleme

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> Kategori alanı ayarlarına özel sekmeler ekleme

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> Etiket alanı ayarlarına özel sekmeler ekleme

### updatePluginAreas

> Eklenti alanı ayarlarına özel sekmeler ekleme

```php
public function updatePluginAreas(Event $e): void
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
