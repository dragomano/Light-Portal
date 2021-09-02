# Portal hooks
Light Portal is wonderfully extensible thanks to plugins. And hooks help to plugins interact with various components of the portal.

## Basic hooks

### init
> redefining $txt variables, loading scripts, and styles, connection SMF hooks, etc.
### prepareEditor
> adding any code on block/page editing area
### parseContent
> parsing content of custom block/page types
### prepareContent
> adding custom content of your addon
### credits
> adding copyrights of used libraries/scripts, etc.
### addAdminAreas
> adding custom areas to SMF's integrate_admin_areas hook

## Work with blocks

### blockOptions
> adding your block parameters
### prepareBlockFields
> adding custom fields to the block post area
### validateBlockData
> adding custom validating data when block adding/edition
### findBlockErrors
> adding custom error handling when block adding/edition
### onBlockSaving
> custom actions on saving/edition blocks
### onBlockRemoving
> custom actions on removing blocks

## Work with pages

### pageOptions
> adding your page parameters
### preparePageFields
> adding custom fields to the page post area
### validatePageData
> adding custom validating data when page adding/edition
### findPageErrors
> adding custom error handling when page adding/edition
### onPageSaving
> custom actions on saving/edition pages
### onPageRemoving
> custom actions on removing pages

## Work with plugins

## preparePluginFields
> adding custom fields to the plugin post area
### addSettings
> adding custom settings of your addon
### onSettingsSaving
> additional actions after plugin settings saving

## Portal settings

### addPanels
> adding custom settings on the Panels tab
### addMisc
> adding custom settings on the Misc tab
### addBlockAreas
> adding custom tabs into Block area settings
### addPageAreas
> adding custom tabs into Page area settings

## Work with articles

### frontCustomTemplate
> adding custom templates for the frontpage
### frontAssets
> adding custom scripts and styles on the frontpage
### frontTopics
> adding custom columns, tables, wheres, params and orders to _init_ function
### frontTopicsOutput
> various manipulations with query results to _getData_ function
### frontPages
> adding custom columns, tables, wheres, params and orders to _init_ function
### frontPagesOutput
> various manipulations with query results to _getData_ function
### frontBoards
> adding custom columns, tables, wheres, params and orders to _init_ function
### frontBoardsOutput
> various manipulations with query results to _getData_ function

## Work with comments

### comments
> adding custom comment script to the page view

## Miscellaneous

### prepareIconList
> adding custom list of icons (instead of FontAwesome)
### prepareIconTemplate
> Adding custom template for displaying icons