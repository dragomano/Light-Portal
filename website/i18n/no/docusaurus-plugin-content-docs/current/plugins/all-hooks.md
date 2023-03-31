---
sidebar_position: 3
---

# Portal hooks
Lys Portal er utrolig overkommelig takket være utvidelser. Og kroker hjelper plugins med å interagere med ulike komponenter i portalen.

## Grunnleggende kroker

### init
> omdefiner $txt variabler, kjører SMF-hooks, osv.

### prepareEditor
(`$context['lp_block']` for block, `$context['lp_page']` for page)
> legge til en kode i redigeringsområdet for blokker/side

### parseContent
(`&$content, $type`)
> analyserer innholdet av tilpassede blokker/sidetyper

### prepareContent
(`&$content, $type, $block_id, $type`)
> legge til egendefinert innhold i programtillegget

### credits
(`&$links`)
> legge til opphavsrett for brukte biblioteker/skript osv.

### addAdminAreas
(`&$admin_areas`)
> legge til tilpassede områder i SMFs integrate_admin_areas krok

## Jobb med blokker

### blockOptions
(`&$options`)
> legge til dine blokkparametre

### prepareBlockFields
> legge til egendefinerte felt i blokkpostområdet

### validateBlockData
(`&$parameters, $context['current_block']['type']`)
> legge til tilpassede valideringsregler når legge til/utgave

### findBlockErrors
(`$data, &$post_errors`)
> legge til egendefinert feilbehandling når legge til/utgave

### onBlockSaving
(`$item`)
> egendefinerte handlinger på lagre/utgave blokker

### onBlockRemoving
(`$items`)
> egendefinerte handlinger for å fjerne blokker

## Arbeid med sider

### pageOptions
(`&$options`)
> legge til sideparametere

### preparePageFields
> legge til egendefinerte felt i postområdet for siden

### validatePageData
(`&$parameters`)
> legge til egendefinerte valideringsregler når side legges til

### findPageErrors
(`$data, &$post_errors`)
> legge til egendefinert feilhåndtering når side legges til

### onPageSaving
(`$item`)
> egendefinerte handlinger på lagre/utgave sider

### onPageRemoving
(`$items`)
> Egendefinerte handlinger på fjerning av sider

### preparePageData
(`&$data`, `$is_author`)
> i tillegg klargjør for gjeldende sidedata for portalen

### comments
> legge til et egendefinert kommentar-skript i portalens nåværende sidevisning

## Arbeid med plugins

### addSettings
(`&$config_vars`)
> legge til tilpassede innstillinger i programtillegget

### saveSettings
(`&$plugin_options`)
> ekstra handlinger etter at programtilleggets innstillinger lagrer

## Portal innstillinger

### addBlockAreas
(`&$subActions`)
> legge til egendefinerte faner i instillinger for blokkområde

### addPageAreas
(`&$subActions`)
> legge til tilpassede faner i innstillinger for sideområde

## Arbeid med artikler

### frontModes
(`&$this->modes`)
> legge til tilpassede moduser for forsiden

### frontCustomTemplate
(`$layouts`)
> legge til tilpassede maler for frontsiden

### frontAssets
> legge til egendefinerte skript og stiler på forsiden

### frontTopics
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> legge til egendefinerte kolonner, tabeller, kniver, parametere og ordre til _init_ funksjonen

### frontTopicsOutput
(`&$topics, $row`)
> ulike manipulasjoner med søkeresultater for å _getData_ -funksjonen

### frontPages
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> legge til egendefinerte kolonner, tabeller, kniver, parametere og ordre til _init_ funksjonen

### frontPagesOutput
(`&$pages, $row`)
> ulike manipulasjoner med søkeresultater for å _getData_ -funksjonen

### frontBoards
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> legge til egendefinerte kolonner, tabeller, kniver, parametere og ordre til _init_ funksjonen

### frontBoardsOutput
(`&$boards, $row`)
> ulike manipulasjoner med søkeresultater for å _getData_ -funksjonen

## Arbeid med ikoner

### prepareIconList
(`&$all_icons, &$template`)
> legge til egendefinert liste over ikoner (i stedet for FontAwesome)

### prepareIconTemplate
(`&$template, $icon`)
> legge til egendefinert mal for visning av ikoner

### changeIconSet
(`&$set`)
> mulighet til å legge til eller overstyre gjeldende grensesnitt ikoner

Ikke så mye, Carl?