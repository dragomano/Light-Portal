---
sidebar_position: 4
---

# Portal-Hooks
Dank Plugins ist Light Portal wundervoll erweiterbar. Hooks helfen Plugins, mit verschiedenen Komponenten des Portals zu interagieren.

## Grundlegende Hooks

### init
> $txt-Variablen überschreiben, SMF-Hooks ausführen, etc.

### prepareEditor
(`$context['lp_block']` for block, `$context['lp_page']` for page)
> Beliebigen Code zum Block-/Seite-Bearbeiten-Bereich hinzufügen

### parseContent
(`&$content, $type`)
> Den Inhalt von benutzerdefinierten Block-/Seitentypen parsen

### prepareContent
(`$data, $parameters`)
> Den benutzerdefinierten Inhalt Ihres Plugins hinzufügen

### credits
(`&$links`)
> Lizenzen der verwendeten Bibliotheken/Scripts etc. hinzufügen

### addAdminAreas
(`&$admin_areas`)
> Benutzerdefinierte Bereiche zu SMFs „integrate_admin_areas“-Hook hinzufügen

## Mit Blöcken arbeiten

### blockOptions
(`&$options`)
> Benutzerdefinierte Blockparameter hinzufügen

### prepareBlockFields
> Benutzerdefinierte Felder zum Block-Erstellungsbereich hinzufügen

### validateBlockData
(`&$parameters, $context['current_block']['type']`)
> Benutzerdefinierte Verifizierungsregeln für das Hinzufügen/Bearbeiten von Blöcken hinzufügen

### findBlockErrors
(`&$post_errors, $data`)
> Benutzerdefinierte Fehlerbehandlung für das Hinzufügen/Bearbeiten von Blöcken hinzufügen

### onBlockSaving
(`$item`)
> Benutzerdefinierte Aktionen beim Speichern/Bearbeiten von Blöcken

### onBlockRemoving
(`$items`)
> Benutzerdefinierte Aktionen beim Löschen von Blöcken

## Mit Seiten arbeiten

### pageOptions
(`&$options`)
> Benutzerdefinierte Seitenparameter hinzufügen

### preparePageFields
> Benutzerdefinierte Felder zum Seiten-Erstellungsbereich hinzufügen

### validatePageData
(`&$parameters`)
> Benutzerdefinierte Verifizierungsregeln für das Hinzufügen/Bearbeiten von Seiten hinzufügen

### findPageErrors
(`&$post_errors, $data`)
> Benutzerdefinierte Fehlerbehandlung für das Hinzufügen/Bearbeiten von Seiten hinzufügen

### onPageSaving
(`$item`)
> Benutzerdefinierte Aktionen beim Speichern/Bearbeiten von Seiten

### onPageRemoving
(`$items`)
> Benutzerdefinierte Aktionen beim Löschen von Seiten

### preparePageData
(`&$data`, `$is_author`)
> Benutzerdefinierte Vorbereitung der Daten der aktuellen Seite des Portals

### beforePageContent
> Bietet die Möglichkeit, etwas vor dem Inhalt der Portalseite anzuzeigen

### afterPageContent
> Bietet die Möglichkeit, etwas nach dem Inhalt der Portalseite anzuzeigen

### comments
> Benutzerdefiniertes Kommentar-Script zur aktuellen Portalseitenansicht hinzufügen

### commentButtons
(`$comment`)
> Benutzerdefinierte Schaltflächen unter jedem Kommentar hinzufügen

## Mit Plugins arbeiten

### addSettings
(`&$config_vars`)
> Benutzerdefinierte Einstellungen zu Ihrem Plugin hinzufügen

### saveSettings
(`&$plugin_options`)
> Zusätzliche Aktionen nach dem Speichern von Plugin-Einstellungen

## Portaleinstellungen

### addBlockAreas
(`&$subActions`)
> Benutzerdefinierte Reiter in den Block-Einstellungen hinzufügen

### addPageAreas
(`&$subActions`)
> Benutzerdefinierte Reiter in den Seiten-Einstellungen hinzufügen

## Mit Artikeln arbeiten

### frontModes
(`&$this->modes`)
> Benutzerdefinierte Modi für die Hauptseite hinzufügen

### frontCustomTemplate
(`$layouts`)
> Benutzerdefinierte Vorlagen für die Hauptseite hinzufügen

### frontAssets
> Benutzerdefinierte Scripts und Stile zur Hauptseite hinzufügen

### frontTopics
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> Benutzerdefinierte Spalten, Tabellen, WHEREs, Parameter und Sortierung zur _init_-Funktion hinzufügen

### frontTopicsOutput
(`&$topics, $row`)
> Verschiedene Manipulationen der Query-Ergebnisse der _getData_-Funktion

### frontPages
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> Benutzerdefinierte Spalten, Tabellen, WHEREs, Parameter und Sortierung zur _init_-Funktion hinzufügen

### frontPagesOutput
(`&$pages, $row`)
> Verschiedene Manipulationen der Query-Ergebnisse der _getData_-Funktion

### frontBoards
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> Benutzerdefinierte Spalten, Tabellen, WHEREs, Parameter und Sortierung zur _init_-Funktion hinzufügen

### frontBoardsOutput
(`&$boards, $row`)
> Verschiedene Manipulationen der Query-Ergebnisse der _getData_-Funktion

## Mit Symbolen arbeiten

### prepareIconList
(`&$all_icons, &$template`)
> Benutzerdefinierte Symbole (statt FontAwesome) hinzufügen

### prepareIconTemplate
(`&$template, $icon`)
> Benutzerdefinierte Vorlage zum Anzeigen von Symbolen hinzufügen

### changeIconSet
(`&$set`)
> Bietet die Möglichkeit, Interface-Symbole, die über das `$this->context['lp_icon_set']`-Array verfügbar sind, zu erweitern
