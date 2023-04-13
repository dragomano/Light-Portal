---
sidebar_position: 1
---

# Instalación
Aquí no hay sutilezas. Light Portal se puede instalar como cualquier otra modificación para SMF, a través del administrador de paquetes.

:::info

Basta con descargar el archivo con los archivos del portal (en SMF esto se llama paquete) del [catálogo oficial ](https://custom.simplemachines.org/mods/index.php?mod=4244) y súbalo a través del administrador de paquetes en su foro.

:::

## Solución de problemas
Si su alojamiento es demasiado "inteligente" con los permisos y los archivos del portal no se desempaquetaron durante la instalación, debe extraer manualmente los directorios `Themes` y `Sources` del archivo de modificación a su carpeta del foro (donde ya se encuentran las mismas carpetas de Themes y Sources, así como los archivos `cron.php`, `SSI.php`, `Settings.php` , etc) y establecer los permisos apropiados. La mayoría de las veces es `644`, `664` o `666` para archivos, y `755`, `775` o `777` para carpetas.

También necesita descomprimir el archivo `database.php` del archivo de modificación a la raíz de su foro, establecer los derechos de ejecución para él (`666`) y acceder a él a través del navegador (usted debe iniciar sesión como administrador del foro). Este archivo contiene instrucciones para crear las tablas utilizadas por el portal. El mensaje `Database changes are complete! Please wait...` confirmará la ejecución exitosa del script.

Si, después de completar todos los pasos anteriores, aún no ve la sección con la configuración del portal en el panel de administración, busque la línea `$sourcedir/LightPortal/app.php` (variable `integrate_pre_include`) en la tabla `<your_prefix>settings` de tu base de datos. Para hacer esto, use la búsqueda integrada de phpMyAdmin u otra utilidad similar.
