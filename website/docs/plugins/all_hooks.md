---
sidebar_position: 3
---

# Portal hooks
Light Portal is wonderfully extensible thanks to plugins. And hooks help to plugins interact with various components of the portal.

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
(`&$content, $type, $block_id, $type`)
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
> adding custom validating data when block adding/edition

### findBlockErrors
(`$data, &$post_errors`)
> adding custom error handling when block adding/edition

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
> adding custom validating data when page adding/edition

### findPageErrors
(`$data, &$post_errors`)
> adding custom error handling when page adding/edition

### onPageSaving
(`$item`)
> custom actions on saving/edition pages

### onPageRemoving
(`$items`)
> custom actions on removing pages

### preparePageData
(`&$data`, `$is_author`)
> additional preparing the portal current page data

### comments
> adding custom comment script to the portal current page view

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
> ability to add or override current interface icons

Not so much, Carl?