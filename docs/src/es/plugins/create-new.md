---
description: Breve descripción de la interfaz de creación de plugins
order: 2
---

# Añadir complemento

Plugins son las extensiones que amplían las capacidades del Portal de Luz. Para crear su propio plugin, siga las siguientes instrucciones.

:::info Nota

Puedes usar el **PluginMaker** como un ayudante para crear tus propios plugins. Descargue y habilítelo en la página _Admin -> Configuración del portal -> Plugins_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Elegir el tipo de plugin

Actualmente, los siguientes tipos de plugins están disponibles:

| Type                            |                                                                                                                                    Description |
| ------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------: |
| `block`                         |                                                                    Plugins que añaden un nuevo tipo de bloques para el portal. |
| `ssi`                           |                              Plugins (normalmente bloques) que utilizan funciones SSI para recuperar datos. |
| `editor`                        |                                                   Plugins que añaden un editor de terceros para diferentes tipos de contenido. |
| `comment`                       |                                          Plugins que añaden un widget de comentarios de terceros en lugar de los incorporados. |
| `parser`                        |                                                  Plugins que implementan el analizador para el contenido de páginas y bloques. |
| `article`                       |                                        Plugins para procesar el contenido de las tarjetas de artículos en la página principal. |
| `frontpage`                     |                                                                           Plugins para cambiar la página principal del portal. |
| `impex`                         |                                                                  Plugins para importar y exportar varios elementos del portal. |
| `block_options`, `page_options` | Plugins que añaden parámetros adicionales para la entidad correspondiente (bloque o .page). |
| `icons`                         |                    Plugins que añaden nuevas librerías de iconos para reemplazar elementos de interfaz o para usar en las cabeceras de bloques |
| `seo`                           |                                                      Plugins que de alguna manera afectan a la visibilidad del foro en la red. |
| `other`                         |                                                    Plugins que no están relacionados con ninguna de las categorías anteriores. |

## Creando un directorio de plugins

Crea una carpeta separada para tus archivos de plugin, dentro de `/Sources/LightPortal/Plugins`. Por ejemplo, si tu plugin se llama `HelloWorld`, la estructura de la carpeta debería verse así:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

El archivo `index.php` puede ser copiado de carpetas de otros plugins. El archivo `HelloWorld.php` contiene la lógica del plugin:

```php:line-numbers {17}
<?php declare(strict_types=1);

namespace Bugo\LightPortal\Plugins\HelloWorld;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
    die('No direct access...');

class HelloWorld extends Plugin
{
    // FA icon (for blocks only)
    public string $icon = 'fas fa-globe';

    // Your plugin's type
    public string $type = 'other';

    // Optional init method
    public function init(): void
    {
        echo 'Hello world!';
    }

    // Hookable and custom methods
}

```

## Usando SSI

Si el plugin necesita recuperar cualquier dato usando funciones SSI, utilice el método integrado `getFromSsi(string $function, ...$params)`. Como parámetro `$function` debes pasar el nombre de una de las funciones contenidas en el archivo **SSI.php**, sin prefijo `ssi_`. Por ejemplo:

```php
$data = $this->getFromSSI('topTopics', 'views', 10, 'array');
```

## Usando Composer

Su plugin puede utilizar librerías de terceros instaladas a través de Composer. Asegúrese de que el archivo `composer.json` que contiene las dependencias necesarias, se encuentra en el directorio de plugins. Antes de publicar tu plugin, abre el directorio de plugins en la línea de comandos y ejecuta el comando: `composer install --no-dev -o`. Después de eso, todo el contenido del directorio de plugins puede ser empaquetado como una modificación separada para SMF (por ejemplo el paquete **PluginMaker**).
