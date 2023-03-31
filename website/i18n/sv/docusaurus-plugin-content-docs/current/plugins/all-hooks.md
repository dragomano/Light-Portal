---
sidebar_position: 3
---

# Portal hooks
Light Portal är underbart utbyggbar tack vare plugins. Och krokar hjälper till att plugins interagerar med olika komponenter i portalen.

## Grundläggande krokar

### init
> omdefinierar $txt variabler, kör SMF-krokar etc.

### prepareEditor
(`$context['lp_block']` for block, `$context['lp_page']` for page)
> lägger till valfri kod på block/sidredigeringsområde

### parseContent
(`&$content, $type`)
> parsa innehåll av anpassade block/sidtyper

### prepareContent
(`&$content, $type, $block_id, $type`)
> lägger till anpassat innehåll i din plugin

### credits
(`&$links`)
> lägga till upphovsrätt till använda bibliotek/skript, etc.

### addAdminAreas
(`&$admin_areas`)
> lägga till anpassade områden till SMF:s integrate_admin_areas krok

## Arbeta med block

### blockOptions
(`&$options`)
> lägger till dina block-parametrar

### prepareBlockFields
> lägga till anpassade fält till blockets postområde

### validateBlockData
(`&$parameters, $context['current_block']['type']`)
> lägga till anpassade valideringsregler när block lägger till/utgåva

### findBlockErrors
(`$data, &$post_errors`)
> lägga till anpassad felhantering när block lägger till/utgåva

### onBlockSaving
(`$item`)
> anpassade åtgärder vid sparande/redigeringsblock

### onBlockRemoving
(`$items`)
> anpassade åtgärder vid borttagning av block

## Arbeta med sidor

### pageOptions
(`&$options`)
> lägger till dina sidparametrar

### preparePageFields
> lägger till anpassade fält till sidinlägget

### validatePageData
(`&$parameters`)
> lägga till anpassade valideringsregler när sidan läggs till/utgåva

### findPageErrors
(`$data, &$post_errors`)
> lägger till anpassad felhantering när sidan läggs till/utgåva

### onPageSaving
(`$item`)
> anpassade åtgärder på sidor för sparande/utgåvor

### onPageRemoving
(`$items`)
> anpassade åtgärder för att ta bort sidor

### preparePageData
(`&$data`, `$is_author`)
> ytterligare förbereda portalen nuvarande siddata

### comments
> lägger till anpassat kommentarsskript till portalens nuvarande sidvy

## Arbeta med plugins

### addSettings
(`&$config_vars`)
> lägga till anpassade inställningar för din plugin

### saveSettings
(`&$plugin_options`)
> ytterligare åtgärder efter plugin-inställningar sparar

## Portalen inställningar

### addBlockAreas
(`&$subActions`)
> lägga till anpassade flikar i Block områdesinställningar

### addPageAreas
(`&$subActions`)
> lägga till anpassade flikar i sidområdets inställningar

## Arbeta med artiklar

### frontModes
(`&$this->modes`)
> lägger till anpassade lägen för startsidan

### frontCustomTemplate
(`$layouts`)
> lägger till anpassade mallar för startsidan

### frontAssets
> lägger till anpassade skript och stilar på framsidan

### frontTopics
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> lägger till anpassade kolumner, tabeller, var, params och order till _init_ funktion

### frontTopicsOutput
(`&$topics, $row`)
> olika manipulationer med frågeresultat till _getData_ funktion

### frontPages
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> lägger till anpassade kolumner, tabeller, var, params och order till _init_ funktion

### frontPagesOutput
(`&$pages, $row`)
> olika manipulationer med frågeresultat till _getData_ funktion

### frontBoards
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> lägger till anpassade kolumner, tabeller, var, params och order till _init_ funktion

### frontBoardsOutput
(`&$boards, $row`)
> olika manipulationer med frågeresultat till _getData_ funktion

## Arbeta med ikoner

### prepareIconList
(`&$all_icons, &$template`)
> lägga till anpassad lista över ikoner (i stället för FontAwesome)

### prepareIconTemplate
(`&$template, $icon`)
> lägga till anpassad mall för att visa ikoner

### changeIconSet
(`&$set`)
> möjlighet att lägga till eller åsidosätta nuvarande gränssnittsikoner

Inte så mycket, Carl?