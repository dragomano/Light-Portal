---
description: قائمة بجميع روابط البوابة المتاحة
order: 4
---

# خطافات البوابة

بوابة الضوء قابلة للتوسع بشكل رائع بفضل الإضافات. الروابط تسمح للملحقات بالتفاعل مع مختلف مكونات البوابة.

## الروابط الأساسية

### init

> إعادة تعريف المتغيرات $txt ، وتشغيل روابط SMF، إلخ.

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

> تحليل محتوى أنواع الكتلة/الصفحات المخصصة

```php
public function parseContent(Event $e): void
{
    $e->args->content = Content::parse($e->args->content, 'html');
}
```

### prepareContent

> إضافة محتوى مخصص للملحقة الخاصة بك

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

> إضافة أي كود في منطقة تحرير الكتل/الصفحة

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

> يساعد في التحميل المسبق لورقات الأنماط التي تحتاجها

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

## العمل مع الكتل البرمجية

### prepareBlockParams

> إضافة معلمات الكتلة الخاصة بك

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

> إضافة قواعد تحقق مخصصة عند حظر الإضافة/التحرير

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

> إضافة خطأ مخصص عند إضافة/تحرير الكتلة

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

> إضافة حقول مخصصة إلى منطقة مشاركة الكتلة

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

> إجراءات مخصصة على حفظ / تحرير الكتل

### onBlockRemoving

> إجراءات مخصصة لإزالة الكتل

## العمل مع الصفحات

### preparePageParams

> إضافة معلمات الصفحة الخاصة بك

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> إضافة قواعد تحقق مخصصة عند إضافة/تحرير الصفحة

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> إضافة خطأ مخصص عند إضافة/تحرير الصفحة

### preparePageFields

> إضافة حقول مخصصة إلى صفحة منطقة النشر

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

> إجراءات مخصصة لصفحات الحفظ/التحرير

### onPageRemoving

> إجراءات مخصصة في إزالة الصفحات

### preparePageData

> إعداد إضافي لبوابة بيانات الصفحة الحالية

```php
public function preparePageData(Event $e): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> القدرة على عرض شيء ما قبل محتوى صفحة البوابة

### afterPageContent

> القدرة على عرض شيء ما بعد محتوى صفحة البوابة

### comments

> إضافة نص تعليق مخصص إلى عرض الصفحة الحالية للبوابة

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

> إضافة أزرار مخصصة أسفل كل تعليق

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

## العمل مع الإضافات

### addSettings

> إضافة إعدادات مخصصة للملحق الخاص بك

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

> إجراءات إضافية بعد حفظ إعدادات البرنامج المساعد

### prepareAssets

> حفظ الأنماط والنصوص والصور الخارجية لتحسين سرعة تحميل الموارد

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## العمل مع المقالات

### frontModes

> إضافة أوضاع مخصصة للصفحة الأمامية

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> إضافة منطق مخصص في الصفحة الأمامية

```php
public function frontLayouts(Event $e): void
{
    if (! str_contains($e->args->layout, $this->extension))
        return;

    $e->args->renderer = new LatteRenderer();
}
```

### layoutExtensions

> يتيح إضافة ملحقات تخطيط مخصصة

```php
public function layoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> إضافة برامج نصية وأنماط مخصصة في الصفحة الأمامية

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

> إضافة أعمدة وجداول وعناوين وأوامر مخصصة إلى دالة _init_

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

> تلاعبات مختلفة مع نتائج الاستعلام إلى دالة _getData_

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> إضافة أعمدة وجداول وعناوين وأوامر مخصصة إلى دالة _init_

### frontPagesRow

> تلاعبات مختلفة مع نتائج الاستعلام إلى دالة _getData_

### frontBoards

> إضافة أعمدة وجداول وعناوين وأوامر مخصصة إلى دالة _init_

### frontBoardsRow

> تلاعبات مختلفة مع نتائج الاستعلام إلى دالة _getData_

## العمل مع الأيقونات

### prepareIconList

> إضافة قائمة مخصصة من الرموز (بدلاً من FontAwesome)

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

> إضافة قالب مخصص لعرض الرموز

### changeIconSet

> القدرة على تمديد أيقونات الواجهة المتاحة عبر `Utils::$context['lp_icon_set']` مصفوفة

## إعدادات البوابة

### extendBasicConfig

> إضافة إعدادات مخصصة في منطقة الإعدادات الأساسية للبوابة

### updateAdminAreas

> إضافة مناطق مخصصة للبوابة في مركز الإدارة

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

> إضافة علامات تبويب مخصصة إلى إعدادات منطقة الحظر

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> إضافة علامات تبويب مخصصة إلى إعدادات منطقة الصفحة

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> إضافة علامات تبويب مخصصة إلى إعدادات منطقة الفئة

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> إضافة علامات تبويب مخصصة إلى إعدادات منطقة الوسم

### updatePluginAreas

> إضافة علامات تبويب مخصصة إلى إعدادات منطقة الإضافات

```php
public function updatePluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## متنوعات

### credits

> إضافة حقوق التأليف والنشر للمكتبات/البرامج النصية المستخدمة، إلخ.

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
