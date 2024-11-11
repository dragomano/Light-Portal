---
description: Lista de todos los ganchos de portal disponibles
order: 4
---

# Portal hooks

Light Portal es maravillosamente extensible gracias a los plugins. Los Hooks permiten que los plugins interactúen con varios componentes del portal.

## Hooks básicos

### init

> redefiniendo variables $txt , ejecutando ganchos SMF, etc.

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

> analizando contenido de tipos de bloque/página personalizados

```php
public function parseContent(Event $e): void
{
    if ($e->args->type === 'markdown') {
        $e->args->content = $this->getParsedContent($e->args->content);
    }
}
```

### prepareContent

> añadir contenido personalizado de tu plugin

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

> añadir cualquier código en el área de edición de bloque/página

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

> ayuda a precargar las hojas de estilo que necesita

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

## Trabajar con bloques

### prepareBlockParams

> añadir parámetros de bloque

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

> añadir reglas de validación personalizadas al añadir o editar bloques

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

> añadir manejo de errores personalizado al añadir o editar bloque

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

> añadir campos personalizados al área de post del bloque

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

> acciones personalizadas al guardar/editar bloques

### onBlockRemoving

> acciones personalizadas al eliminar bloques

## Trabajar con páginas

### preparePageParams

> añadiendo parámetros de la página

```php
public function preparePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = '';
    $e->args->params['meta_rating'] = '';
}
```

### validatePageParams

> añadir reglas de validación personalizadas al añadir o editar página

```php
public function validatePageParams(Event $e): void
{
    $e->args->params['meta_robots'] = FILTER_DEFAULT;
    $e->args->params['meta_rating'] = FILTER_DEFAULT;
}
```

### findPageErrors

> añadir gestión de errores personalizada al añadir o editar la página

### preparePageFields

> añadir campos personalizados al área de publicación de la página

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

> acciones personalizadas al guardar/editar páginas

### onPageRemoving

> acciones personalizadas al eliminar páginas

### preparePageData

> preparación adicional de los datos de la página actual del portal

```php
public function preparePageData(): void
{
    $this->setTemplate()->withLayer('ads_placement_page');
}
```

### beforePageContent

> capacidad para mostrar algo antes del contenido de la página del portal

### afterPageContent

> capacidad para mostrar algo después del contenido de la página del portal

### comments

> añadir script de comentario personalizado a la vista de página actual del portal

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

> añadir botones personalizados debajo de cada comentario

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

## Trabajar con plugins

### addSettings

> añadir ajustes personalizados de su plugin

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

> acciones adicionales después de guardar la configuración del plugin

### prepareAssets

> guardar estilos externos, scripts e imágenes para mejorar la velocidad de carga de recursos

```php
public function prepareAssets(Event $e): void
{
    $e->args->assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
    $e->args->assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
}
```

## Trabajar con artículos

### frontModes

> añadir modos personalizados para la página principal

```php
public function frontModes(Event $e): void
{
    $$e->args->modes[$this->mode] = CustomArticle::class;

    Config::$modSettings['lp_frontpage_mode'] = $this->mode;
}
```

### frontLayouts

> añadir lógica personalizada en la página principal

### customLayoutExtensions

> permite añadir extensiones de diseño personalizado

```php
public function customLayoutExtensions(Event $e): void
{
    $e->args->extensions[] = '.twig';
}
```

### frontAssets

> añadir scripts y estilos personalizados en la página principal

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

> añadir columnas personalizadas, tablas, donde, parámetros y órdenes a la función _init_

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

> varias manipulaciones con resultados de consultas a la función _getData_

```php
public function frontTopicsRow(Event $e): void
{
    $e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
        ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
}
```

### frontPages

> añadir columnas personalizadas, tablas, donde, parámetros y órdenes a la función _init_

### frontPagesRow

> varias manipulaciones con resultados de consultas a la función _getData_

### frontBoards

> añadir columnas personalizadas, tablas, donde, parámetros y órdenes a la función _init_

### frontBoardsRow

> varias manipulaciones con resultados de consultas a la función _getData_

## Trabajar con iconos

### prepareIconList

> añadir lista personalizada de iconos (en lugar de FontAwesome)

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

> añadir plantilla personalizada para mostrar iconos

### changeIconSet

> habilidad para extender los iconos de la interfaz disponibles a través de `Utils::$context['lp_icon_set']` array

## Configuración del portal

### extendBasicConfig

> añadir configuraciones personalizadas en el área de configuración básica del portal

### updateAdminAreas

> añadiendo las áreas personalizadas del portal en el Centro de Administración

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

> añadir pestañas personalizadas en la configuración del área de bloque

```php
public function updateBlockAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new BlockImport(), 'main'];
}
```

### updatePageAreas

> añadiendo pestañas personalizadas en la configuración del área de página

```php
public function updatePageAreas(Event $e): void
{
    $e->args->areas['import_from_ep'] = [new Import(), 'main'];
}
```

### updateCategoryAreas

> añadiendo pestañas personalizadas en la configuración del área de categoría

```php
public function updateCategoryAreas(Event $e): void
{
    $e->args->areas['import_from_tp'] = [new Import(), 'main'];
}
```

### updateTagAreas

> añadiendo pestañas personalizadas en la configuración del área de Tag

### updatePluginAreas

> añadiendo pestañas personalizadas en la configuración del área de plugins

```php
public function updatePluginAreas(Event $e): void
{
    $e->args->areas['add'] = [new Handler(), 'add'];
}
```

## Varios

### credits

> añadir derechos de autor de bibliotecas/scripts usados, etc.

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
