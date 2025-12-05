---
description: Un breve resumen de la configuración del portal disponible
order: 3
outline: [ 2, 3 ]
---

# Configuración del portal

Use el acceso rápido a través del elemento en el menú principal del foro o la sección correspondiente en el panel de administración para abrir la configuración del portal.

No describiremos en detalle cada una de las configuraciones disponibles, sólo mencionaremos las más importantes.

## Configuración general

In this section, you can fully customize the portal front page, enable standalone mode, and change user permissions to access portal items.

### Settings for the front page and articles

To change the content of the portal home page, select the appropriate "the portal front page" mode:

- Desactivado
- Página especificada (solo se mostrará la página seleccionada)
- Todas las páginas de las categorías seleccionadas
- Páginas seleccionadas
- Todos los temas de foros seleccionados
- Temas seleccionados
- Foros seleccionados

### Modo independiente

This is a mode where you can specify your own home page, and remove unnecessary items from the main menu (user list, calendar, etc.). Vea `portal.php` en la raíz del foro por ejemplo.

### Permisos

Aquí simplemente nota que la OMS puede y que puede hacer con los diversos elementos (bloques y páginas) del portal.

## Páginas y bloques

En esta sección, puede cambiar la configuración general de las páginas y los bloques utilizados tanto al crearlos como al mostrarlos.

## Paneles

En esta sección, puede cambiar algunas de las configuraciones para los paneles del portal existentes y personalizar la dirección de los bloques en estos paneles.

![Panels](panels.png)

## Varios

En esta sección, puede cambiar varias configuraciones auxiliares del portal, que pueden ser útiles para los desarrolladores de plantillas y complementos.

### Modo de compatibilidad

- El valor del parámetro **acción** del portal - puedes cambiar esta opción para usar el Portal de Luz junto con otras modificaciones similares. Luego se abrirá la página de inicio en la dirección especificada.
- El parámetro **página** para las páginas del portal - ver arriba. Similarly, for portal pages - change the parameter and they will open with different URLs.

### Mantenimiento

- Optimización semanal de las tablas del portal: active esta opción para que una vez a la semana se eliminen las filas con valores vacíos en las tablas del portal de la base de datos y se optimicen las tablas.
