---
sidebar_position: 3
---

# Portal hooks
Licht Portaal is geweldig uitbreidbaar dankzij plugins. En haks helpen plugins om te communiceren met verschillende componenten van de portal.

## Basis haken

### init
> $txt variabelen herdefiniëren, SMF hooks uitvoeren, etc.

### prepareEditor
(`$context['lp_block']` for block, `$context['lp_page']` for page)
> elke code toevoegen aan blok/pagina bewerken gebied

### parseContent
(`&$content, $type`)
> verwerking van inhoud van aangepaste block/pagina types

### prepareContent
(`&$content, $type, $block_id, $type`)
> aangepaste inhoud van uw plugin toevoegen

### credits
(`&$links`)
> copyrights van gebruikte bibliotheken/scripts, etc. toe te voegen.

### addAdminAreas
(`&$admin_areas`)
> eigen gebieden toevoegen aan SMF's integrate_admin_area hook

## Werk met blokken

### blockOptions
(`&$options`)
> blokparameters toevoegen

### prepareBlockFields
> extra velden toevoegen aan het blok berichtgebied

### validateBlockData
(`&$parameters, $context['current_block']['type']`)
> aangepaste validatieregels toevoegen wanneer blok toevoegen/editie

### findBlockErrors
(`$data, &$post_errors`)
> eigen foutmelding toevoegen bij het blokkeren van toevoegen/editie

### onBlockSaving
(`$item`)
> aangepaste acties bij het opslaan / bewerken blokken

### onBlockRemoving
(`$items`)
> aangepaste acties voor het verwijderen van blokken

## Werk met pagina's

### pageOptions
(`&$options`)
> paginanameters toevoegen

### preparePageFields
> extra velden toevoegen aan het berichtgebied van de pagina

### validatePageData
(`&$parameters`)
> aangepaste validatieregels toevoegen wanneer pagina toevoegen/editie

### findPageErrors
(`$data, &$post_errors`)
> aangepaste foutverwerking toevoegen wanneer pagina toevoegt/editie

### onPageSaving
(`$item`)
> aangepaste acties voor opslaan / bewerken pagina's

### onPageRemoving
(`$items`)
> aangepaste acties bij het verwijderen van pagina's

### preparePageData
(`&$data`, `$is_author`)
> aanvullende voorbereiding van huidige pagina gegevens van de portal

### comments
> eigen commentaar script toevoegen aan de huidige pagina weergave van het portaal

## Werk met plugins

### addSettings
(`&$config_vars`)
> aangepaste instellingen van uw plugin toevoegen

### saveSettings
(`&$plugin_options`)
> aanvullende acties na het opslaan van de plugin instellingen

## Portaal instellingen

### addBlockAreas
(`&$subActions`)
> Aangepaste tabbladen toevoegen aan instellingen van Blok gebied

### addPageAreas
(`&$subActions`)
> Aangepaste tabbladen toevoegen aan instellingen van pagina-gebied

## Werk met artikelen

### frontModes
(`&$this->modes`)
> aangepaste modi voor de voorpagina toevoegen

### frontCustomTemplate
(`$layouts`)
> aangepaste sjablonen voor de voorpagina toevoegen

### frontAssets
> aangepaste scripts en stijlen toevoegen op de voorpagina

### frontTopics
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> aangepaste kolommen, tabellen, waarmee, parameters en orders worden toegevoegd aan de functie _init_

### frontTopicsOutput
(`&$topics, $row`)
> verschillende manipulaties met query resultaten naar _getData_ functie

### frontPages
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> aangepaste kolommen, tabellen, waarmee, parameters en orders worden toegevoegd aan de functie _init_

### frontPagesOutput
(`&$pages, $row`)
> verschillende manipulaties met query resultaten naar _getData_ functie

### frontBoards
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> aangepaste kolommen, tabellen, waarmee, parameters en orders worden toegevoegd aan de functie _init_

### frontBoardsOutput
(`&$boards, $row`)
> verschillende manipulaties met query resultaten naar _getData_ functie

## Werken met pictogrammen

### prepareIconList
(`&$all_icons, &$template`)
> aangepaste lijst met pictogrammen toevoegen (in plaats van FontAwesom)

### prepareIconTemplate
(`&$template, $icon`)
> aangepaste sjabloon voor het weergeven van pictogrammen toevoegen

### changeIconSet
(`&$set`)
> mogelijkheid om huidige interface iconen toe te voegen of te overschrijven

Niet zo veel, Carl?