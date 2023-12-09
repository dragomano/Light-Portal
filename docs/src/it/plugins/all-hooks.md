---
description: Elenco di tutti gli hook del portale disponibili
order: 4
---

# Hook portale

Light Portal è meravigliosamente estensibile grazie ai plugin. E gli hook aiutano i plugin a interagire con i vari componenti del portale.

## Hook base

### init

> ridefinizione delle variabili $txt, eseguendo hook SMF, ecc.

### parseContent

(`&$content, $type`)

> analisi del contenuto dei tipi di blocco/pagina personalizzati

### prepareContent

(`$data, $parameters`)

> aggiunta di contenuto personalizzato del tuo plugin

### addAdminAreas

(`&$admin_areas`)

> aggiunta di aree personalizzate all'hook integral\_admin\_areas di SMF

### prepareEditor

(`$context['lp_block']` for block, `$context['lp_page']` for page)

> aggiunta di qualsiasi codice nell'area di modifica del blocco/pagina

### preloadLinks

(`&$links`)

> aiuta a precaricare i fogli di stile di cui hai bisogno

### credits

(`&$links`)

> aggiunta dei diritti d'autore delle librerie/script utilizzati, ecc.

## Lavorare con i blocchi

### blockOptions

(`&$options`)

> aggiunta dei parametri del blocco

### prepareBlockFields

> aggiunta di campi personalizzati all'area dei blocchi

### validateBlockData

(`&$parameters, $context['current_block']['type']`)

> aggiunta di regole di convalida personalizzate durante l'aggiunta/modifica dei blocchi

### findBlockErrors

(`&$post_errors, $data`)

> aggiunta della gestione personalizzata degli errori durante l'aggiunta/modifica dei blocchi

### onBlockSaving

(`$item`)

> azioni personalizzate durante il salvataggio/modifica dei blocchi

### onBlockRemoving

(`$items`)

> azioni personalizzate sulla rimozione dei blocchi

## Lavorare con le pagine

### pageOptions

(`&$options`)

> aggiunta dei parametri della pagina

### preparePageFields

> aggiunta di campi personalizzati all'area delle pagine

### validatePageData

(`&$parameters`)

> aggiunta di regole di convalida personalizzate durante l'aggiunta/modifica dele pagine

### findPageErrors

(`&$post_errors, $data`)

> aggiunta della gestione personalizzata degli errori durante l'aggiunta/modifica delle pagine

### onPageSaving

(`$item`)

> azioni personalizzate durante il salvataggio/modifica delle pagine

### onPageRemoving

(`$items`)

> azioni personalizzate sulla rimozione dele pagine

### preparePageData

(`&$data`, `$is_author`)

> ulteriore preparazione dei dati della pagina corrente del portale

### beforePageContent

> capacità di visualizzare qualcosa prima del contenuto della pagina del portale

### afterPageContent

> capacità di visualizzare qualcosa dopo il contenuto della pagina del portale

### comments

> aggiunta di commenti personalizzati alla visualizzazione della pagina corrente del portale

### commentButtons

(`$comment`, `&$buttons`)

> aggiunta di pulsanti personalizzati sotto ogni commento

## Lavorare con i plugin

### addSettings

(`&$config_vars`)

> aggiunta di impostazioni personalizzate del tuo plugin

### saveSettings

(`&$plugin_options`)

> azioni aggiuntive dopo il salvataggio delle impostazioni del plugin

## Impostazioni Portale

### addBlockAreas

(`&$subActions`)

> aggiunta di schede personalizzate nelle impostazioni nell'area del blocco

### addPageAreas

(`&$subActions`)

> aggiunta di schede personalizzate nelle impostazioni nell'area della pagina

## Lavorare con articoli

### frontModes

(`&$this->modes`)

> aggiunta di modalità personalizzate per il frontpage

### frontCustomTemplate

(`$layouts`)

> aggiunta di layout personalizzate per il frontpage

### frontAssets

> aggiunta di script e stili personalizzati sul frontpage

### frontTopics

(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)

> aggiunta di colonne e tabelle personalizzate, in base ai parametri ed ordinamenti della funzione _init_

### frontTopicsOutput

(`&$topics, $row`)

> varie manipolazioni con i risultati della query sulla funzione _getData_

### frontPages

(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)

> aggiunta di colonne e tabelle personalizzate, in base ai parametri ed ordinamenti della funzione _init_

### frontPagesOutput

(`&$pages, $row`)

> varie manipolazioni con i risultati della query sulla funzione _getData_

### frontBoards

(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)

> aggiunta di colonne e tabelle personalizzate, in base ai parametri ed ordinamenti della funzione _init_

### frontBoardsOutput

(`&$boards, $row`)

> varie manipolazioni con i risultati della query sulla funzione _getData_

## Lavorare con le icone

### prepareIconList

(`&$all_icons, &$template`)

> aggiunta di un elenco personalizzato di icone (invece di FontAwesome)

### prepareIconTemplate

(`&$template, $icon`)

> aggiunta di template personalizzato per la visualizzazione delle icone

### changeIconSet

(`&$set`)

> possibilità di estendere l'interfaccia delle icone disponibili tramite l'array `$this->context['lp_icon_set']`
