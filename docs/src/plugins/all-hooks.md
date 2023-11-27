---
description: List of all available portal hooks
order: 4
outline: [2, 3]
---

# Portal hooks

Light Portal is wonderfully extensible thanks to plugins. And hooks help plugins to interact with various components of the portal.

## Basic hooks

### init

> redefining $txt variables, running SMF hooks, etc.

### prepareEditor

(`$context['lp_block']` for block, `$context['lp_page']` for page)

> adding any code on block/page editing area

### parseContent

(`&$content, $type`)

> parsing content of custom block/page types

### prepareContent

(`$data, $parameters`)

> adding custom content of your plugin

### credits

(`&$links`)

> adding copyrights of used libraries/scripts, etc.

### addAdminAreas

(`&$admin_areas`)

> adding custom areas to SMF's integrate_admin_areas hook

## Work with blocks

### blockOptions

(`&$options`)

> adding your block parameters

### prepareBlockFields

> adding custom fields to the block post area

### validateBlockData

(`&$parameters, $context['current_block']['type']`)

> adding custom validating rules when block adding/editing

### findBlockErrors

(`&$post_errors, $data`)

> adding custom error handling when block adding/editing

### onBlockSaving

(`$item`)

> custom actions on saving/edition blocks

### onBlockRemoving

(`$items`)

> custom actions on removing blocks

## Work with pages

### pageOptions

(`&$options`)

> adding your page parameters

### preparePageFields

> adding custom fields to the page post area

### validatePageData

(`&$parameters`)

> adding custom validating rules when page adding/editing

### findPageErrors

(`&$post_errors, $data`)

> adding custom error handling when page adding/editing

### onPageSaving

(`$item`)

> custom actions on saving/edition pages

### onPageRemoving

(`$items`)

> custom actions on removing pages

### preparePageData

(`&$data`, `$is_author`)

> additional preparing the portal current page data

### beforePageContent

> ability to display something before the portal page content

### afterPageContent

> ability to display something after the portal page content

### comments

> adding custom comment script to the portal current page view

### commentButtons

(`$comment`, `&$buttons`)

> adding custom buttons below each comment

## Work with plugins

### addSettings

(`&$config_vars`)

> adding custom settings of your plugin

### saveSettings

(`&$plugin_options`)

> additional actions after plugin settings saving

## Portal settings

### addBlockAreas

(`&$subActions`)

> adding custom tabs into Block area settings

### addPageAreas

(`&$subActions`)

> adding custom tabs into Page area settings

## Work with articles

### frontModes

(`&$this->modes`)

> adding custom modes for the frontpage

### frontCustomTemplate

(`$layouts`)

> adding custom templates for the frontpage

### frontAssets

> adding custom scripts and styles on the frontpage

### frontTopics

(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)

> adding custom columns, tables, wheres, params and orders to _init_ function

### frontTopicsOutput

(`&$topics, $row`)

> various manipulations with query results to _getData_ function

### frontPages

(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)

> adding custom columns, tables, wheres, params and orders to _init_ function

### frontPagesOutput

(`&$pages, $row`)

> various manipulations with query results to _getData_ function

### frontBoards

(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)

> adding custom columns, tables, wheres, params and orders to _init_ function

### frontBoardsOutput

(`&$boards, $row`)

> various manipulations with query results to _getData_ function

## Work with icons

### prepareIconList

(`&$all_icons, &$template`)

> adding custom list of icons (instead of FontAwesome)

### prepareIconTemplate

(`&$template, $icon`)

> adding custom template for displaying icons

### changeIconSet

(`&$set`)

> ability to extend interface icons available via `$this->context['lp_icon_set']` array