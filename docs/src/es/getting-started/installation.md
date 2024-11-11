---
description: Lista de requisitos para la instalación del portal, así como soluciones a posibles problemas
order: 1
---

# Instalación

Aquí no hay sutilezas. Light Portal se puede instalar como cualquier otra modificación para SMF, a través del administrador de paquetes.

## Requisitos

- [SMF 2.1.x](https://download.simplemachines.org)
- Navegador moderno con JavaScript activado
- Internet (el portal y muchos plugins cargan scripts y estilos desde CDN)
- PHP 8.1 o superior
- Extensión PHP `intl` para localizar correctamente algunas cadenas de idioma
- Extensiones PHP `dom` y `simplexml` para exportar/importar páginas y bloques
- Extensión PHP `zip` para exportar/importar plugins

:::info Nota

Basta con descargar el paquete con los archivos del portal del [catálogo oficial](https://custom.simplemachines.org/mods/index.php?mod=4244) y subirlo a través del administrador de paquetes en tu foro.

:::

## Solución de problemas

Si tu alojamiento es demasiado "inteligente" con permisos y los archivos del portal no fueron desempaquetados durante la instalación, necesitas extraer manualmente los directorios `Temas` y `Fuentes` del archivo de modificaciones en la carpeta de tu foro (donde ya están ubicadas las mismas carpetas de temas y orígenes, así como los archivos `cron.php`, `SSI.php`, `Settings.php`, etc) y establezca los permisos apropiados. La mayoría de las veces es `644`, `664` o `666` para archivos, y `755`, `775` o `777` para carpetas.

También necesita desempaquetar el archivo `database.php` desde el archivo de modificaciones a la raíz de tu foro, establecer derechos de ejecución para él (`666`) y acceder a él a través del navegador (debe estar conectado como administrador del foro). Este archivo contiene instrucciones para crear las tablas utilizadas por el portal.
