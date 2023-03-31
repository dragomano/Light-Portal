---
sidebar_position: 3
---

# Portal hooks
Light Portal je nádherně rozšiřitelný díky pluginům. A háčky pomáhají s pluginy komunikovat s různými komponenty portálu.

## Základní háčky

### init
> redefinování $txt proměnných, běh SMF háčků atd.

### prepareEditor
(`$context['lp_block']` for block, `$context['lp_page']` for page)
> přidat libovolný kód na oblast úpravy stránky

### parseContent
(`&$content, $type`)
> analyzuji obsah vlastních typů bloků/stránek

### prepareContent
(`&$content, $type, $block_id, $type`)
> přidat vlastní obsah vašeho pluginu

### credits
(`&$links`)
> přidávání autorských práv použitých knihoven a skriptů atd.

### addAdminAreas
(`&$admin_areas`)
> přidávání vlastních oblastí do háčku integrace_admin_area SMF

## Práce s bloky

### blockOptions
(`&$options`)
> přidejte parametry bloku

### prepareBlockFields
> přidat vlastní pole do oblasti příspěvku

### validateBlockData
(`&$parameters, $context['current_block']['type']`)
> přidat vlastní validační pravidla při přidávání/editaci bloku

### findBlockErrors
(`$data, &$post_errors`)
> přidat vlastní chybu při přidávání/edici bloku

### onBlockSaving
(`$item`)
> vlastní akce při ukládání/editaci bloků

### onBlockRemoving
(`$items`)
> vlastní akce při odebírání bloků

## Pracovat se stránkami

### pageOptions
(`&$options`)
> přidejte parametry stránky

### preparePageFields
> přidat vlastní pole do oblasti příspěvku stránky

### validatePageData
(`&$parameters`)
> přidání vlastních ověřovacích pravidel při přidávání/úpravě stránky

### findPageErrors
(`$data, &$post_errors`)
> přidávání vlastní chyby při přidávání/úpravě stránky

### onPageSaving
(`$item`)
> vlastní akce na stránkách uložení/editace

### onPageRemoving
(`$items`)
> vlastní akce na odstranění stránek

### preparePageData
(`&$data`, `$is_author`)
> další příprava portálu aktuálních dat stránky

### comments
> přidat vlastní komentář skript do portálu aktuálního zobrazení stránky

## Pracovat s pluginy

### addSettings
(`&$config_vars`)
> přidat vlastní nastavení vašeho pluginu

### saveSettings
(`&$plugin_options`)
> další akce po uložení nastavení pluginu

## Nastavení portálu

### addBlockAreas
(`&$subActions`)
> přidat vlastní panely do nastavení oblasti blokování

### addPageAreas
(`&$subActions`)
> přidat vlastní panely do nastavení oblasti stránky

## Práce s články

### frontModes
(`&$this->modes`)
> přidat vlastní režimy pro webovou stránku

### frontCustomTemplate
(`$layouts`)
> přidat vlastní šablony pro webovou stránku

### frontAssets
> přidávání vlastních skriptů a stylů na frontpage

### frontTopics
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> přidání vlastních sloupců, tabulek, kde, parametrů a příkazů do funkce _init_

### frontTopicsOutput
(`&$topics, $row`)
> různé manipulace s výsledky dotazu na funkci _getData_

### frontPages
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> přidání vlastních sloupců, tabulek, kde, parametrů a příkazů do funkce _init_

### frontPagesOutput
(`&$pages, $row`)
> různé manipulace s výsledky dotazu na funkci _getData_

### frontBoards
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> přidání vlastních sloupců, tabulek, kde, parametrů a příkazů do funkce _init_

### frontBoardsOutput
(`&$boards, $row`)
> různé manipulace s výsledky dotazu na funkci _getData_

## Pracovat s ikonami

### prepareIconList
(`&$all_icons, &$template`)
> přidat vlastní seznam ikon (místo FontAwesome)

### prepareIconTemplate
(`&$template, $icon`)
> přidat vlastní šablonu pro zobrazení ikon

### changeIconSet
(`&$set`)
> schopnost přidávat nebo přepsat ikony aktuálního rozhraní

Ne tak mnoho, Carl?