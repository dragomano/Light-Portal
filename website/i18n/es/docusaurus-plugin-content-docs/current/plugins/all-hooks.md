---
sidebar_position: 3
---

# Ganchos de portal
Light Portal es maravillosamente extensible gracias a los complementos. Y los ganchos ayudan a que los complementos interactúen con varios componentes del portal.

## Ganchos basicos

### init
> redefiniendo variables $txt, ejecutando ganchos SMF, etc.

### prepareEditor
(`$context['lp_block']` for block, `$context['lp_page']` for page)
> agregando cualquier código en el área de edición de bloque/página

### parseContent
(`&$content, $type`)
> análisis del contenido de bloques personalizados/tipos de página

### prepareContent
(`&$content, $type, $block_id, $type`)
> agregando contenido personalizado de su complemento

### credits
(`&$links`)
> agregar derechos de autor de bibliotecas/scripts usados, etc.

### addAdminAreas
(`&$admin_areas`)
> agregando áreas personalizadas al gancho de integrate_admin_areas de SMF

## Trabajar con bloques

### blockOptions
(`&$options`)
> agregando sus parámetros de bloque

### prepareBlockFields
> agregar campos personalizados al área de publicación de bloques

### validateBlockData
(`&$parameters, $context['current_block']['type']`)
> agregar reglas de validación personalizadas al agregar/editar bloques

### findBlockErrors
(`$data, &$post_errors`)
> agregar manejo de errores personalizado al agregar/editar bloques

### onBlockSaving
(`$item`)
> acciones personalizadas en bloques de guardado/edición

### onBlockRemoving
(`$items`)
> acciones personalizadas al eliminar bloques

## Trabajar con paginas

### pageOptions
(`&$options`)
> agregando los parámetros de tu página

### preparePageFields
> agregar campos personalizados al área de publicación de la página

### validatePageData
(`&$parameters`)
> agregar reglas de validación personalizadas al agregar/editar páginas

### findPageErrors
(`$data, &$post_errors`)
> agregando un manejo de errores personalizado al agregar/editar una página

### onPageSaving
(`$item`)
> acciones personalizadas en páginas de guardado/edición

### onPageRemoving
(`$items`)
> acciones personalizadas al eliminar páginas

### preparePageData
(`&$data`, `$is_author`)
> preparación adicional de los datos de la página actual del portal

### comments
> agregando un script de comentario personalizado a la vista de página actual del portal

## Trabajar con complementos

### addSettings
(`&$config_vars`)
> agregando configuraciones personalizadas de su complemento

### saveSettings
(`&$plugin_options`)
> acciones adicionales después de guardar la configuración del complemento

## Configuración del portal

### addBlockAreas
(`&$subActions`)
> agregar pestañas personalizadas en la configuración del área de bloqueo

### addPageAreas
(`&$subActions`)
> agregar pestañas personalizadas en la configuración del área de la página

## Trabajar con artículos

### frontModes
(`&$this->modes`)
> agregando modos personalizados para la página principal

### frontCustomTemplate
(`$layouts`)
> agregar plantillas personalizadas para la portada

### frontAssets
> agregando scripts y estilos personalizados en la página principal

### frontTopics
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> agregar columnas personalizadas, tablas, dónde, parámetros y órdenes a la función _init_

### frontTopicsOutput
(`&$topics, $row`)
> varias manipulaciones con resultados de consultas a la función _getData_

### frontPages
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> agregar columnas personalizadas, tablas, dónde, parámetros y órdenes a la función _init_

### frontPagesOutput
(`&$pages, $row`)
> varias manipulaciones con resultados de consultas a la función _getData_

### frontBoards
(`&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders`)
> agregar columnas personalizadas, tablas, dónde, parámetros y órdenes a la función _init_

### frontBoardsOutput
(`&$boards, $row`)
> varias manipulaciones con resultados de consultas a la función _getData_

## Trabajar con iconos

### prepareIconList
(`&$all_icons, &$template`)
> agregando una lista personalizada de íconos (en lugar de FontAwesome)

### prepareIconTemplate
(`&$template, $icon`)
> agregar una plantilla personalizada para mostrar iconos

### changeIconSet
(`&$set`)
> posibilidad de agregar o anular los íconos de la interfaz actual

¿No tanto, Carlos?