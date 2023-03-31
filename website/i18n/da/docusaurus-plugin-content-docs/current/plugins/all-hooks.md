---
sidebar_position: 3
---

# Portal hooks
Light Portal er vidunderligt udvidelig takket være plugins. Og kroge hjælp til plugins interagere med forskellige komponenter af portalen.

## Grundlæggende kroge

### init
> omdefinerer $txt variabler, kører SMF hooks, osv.

### prepareEditor
(`$context['lp_block']` for block, `$context['lp_page']` for page)
> tilføje en kode på blok/sideredigeringsområde

### parseContent
(`&$content, $type`)
> parsing indhold af brugerdefinerede blok/sidetyper

### prepareContent
(`&$content, $type, $block_id, $type`)
> tilføje brugerdefineret indhold af dit plugin

### credits
(`&$links`)
> tilføje ophavsret til brugte biblioteker/scripts osv.

### addAdminAreas
(`&$admin_areas`)
> tilføje brugerdefinerede områder til SMF's integrate_admin_areas krog

## Arbejd med blokke

### blockOptions
(`&$options`)
> tilføje din blok parametre

### prepareBlockFields
> tilføje brugerdefinerede felter til blokken indlæg område

### validateBlockData
(`&$parameters, $context['current_block']['type']`)
> tilføje brugerdefinerede valideringsregler, når blok tilføjer/udgave

### findBlockErrors
(`$data, &$post_errors`)
> tilføjelse af brugerdefineret fejlhåndtering, når blok tilføjer/udgave

### onBlockSaving
(`$item`)
> tilpassede handlinger på gemme/udgave-blokke

### onBlockRemoving
(`$items`)
> tilpassede handlinger ved fjernelse af blokke

## Arbejd med sider

### pageOptions
(`&$options`)
> tilføje dine sideparametre

### preparePageFields
> tilføje brugerdefinerede felter til siden indlæg område

### validatePageData
(`&$parameters`)
> tilføje brugerdefinerede valideringsregler, når side tilføjer/udgave

### findPageErrors
(`$data, &$post_errors`)
> tilføjelse af brugerdefineret fejlhåndtering, når side tilføjer/udgave

### onPageSaving
(`$item`)
> tilpassede handlinger på gemme/redigeringssider

### onPageRemoving
(`$items`)
> tilpassede handlinger ved fjernelse af sider

### preparePageData
(`&$data`, `$is_author`)
> yderligere forberedelse af portalens aktuelle sidedata

### comments
> tilføj brugerdefineret kommentarscript til portalens aktuelle sidevisning

## Arbejde med plugins

### addSettings
(`&$config_vars`)
> tilføje brugerdefinerede indstillinger for dit plugin

### saveSettings
(`&$plugin_options`)
> yderligere handlinger efter plugin indstillinger gemmes

## Portal indstillinger

### addBlockAreas
(`&$subActions`)
> tilføje brugerdefinerede faner i blok område indstillinger

### addPageAreas
(`&$subActions`)
> tilføje brugerdefinerede faner i sideområde indstillinger

## Arbejde med artikler

### frontModes
(`&$this->modes`)
> tilføje brugerdefinerede tilstande til forsiden

### frontCustomTemplate
(`$layouts`)
> tilføje brugerdefinerede skabeloner til forsiden

### frontAssets
> tilføje brugerdefinerede scripts og stilarter på forsiden

### frontTopics
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> tilføje brugerdefinerede kolonner, tabeller, wheres, params og ordrer til _init_ funktion

### frontTopicsOutput
(`&$topics, $row`)
> forskellige manipulationer med forespørgselsresultater til _getData_ -funktion

### frontPages
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> tilføje brugerdefinerede kolonner, tabeller, wheres, params og ordrer til _init_ funktion

### frontPagesOutput
(`&$pages, $row`)
> forskellige manipulationer med forespørgselsresultater til _getData_ -funktion

### frontBoards
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> tilføje brugerdefinerede kolonner, tabeller, wheres, params og ordrer til _init_ funktion

### frontBoardsOutput
(`&$boards, $row`)
> forskellige manipulationer med forespørgselsresultater til _getData_ -funktion

## Arbejde med ikoner

### prepareIconList
(`&$all_icons, &$template`)
> tilføje brugerdefineret liste over ikoner (i stedet for FontAwesome)

### prepareIconTemplate
(`&$template, $icon`)
> tilføje brugerdefineret skabelon til visning af ikoner

### changeIconSet
(`&$set`)
> evne til at tilføje eller tilsidesætte aktuelle grænseflade-ikoner

Ikke så meget, Carl?