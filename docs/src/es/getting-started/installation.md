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
- PHP 8.2 or higher
- Extensión PHP `intl` para localizar correctamente algunas cadenas de idioma
- Extensiones PHP `dom` y `simplexml` para exportar/importar páginas y bloques
- Extensión PHP `zip` para exportar/importar plugins
- MySQL 5.7+ / MariaDB 10.5+ / PostgreSQL 12+

:::info Nota

Basta con descargar el paquete con los archivos del portal del [catálogo oficial](https://custom.simplemachines.org/mods/index.php?mod=4244) y subirlo a través del administrador de paquetes en tu foro.

:::

## Testing

You can try our [Docker files](https://github.com/dragomano/Light-Portal/tree/d1074c8486ed9eb2f9e89e3afebce2b914d4d570/_docker) or your preffered LAMP/WAMP/MAMP app.
