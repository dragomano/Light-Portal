---
description: Breve descripción de la interfaz de creación de plugins
order: 2
---

# Añadir complemento

Plugins son las extensiones que amplían las capacidades del Portal de Luz. Para crear su propio plugin, siga las siguientes instrucciones.

## PluginType enum

For better type safety and IDE support, you can use the `PluginType` enum instead of string values for the `type` parameter:

```php
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\PluginAttribute;

// Instead of: #[PluginAttribute(type: 'editor')]
#[PluginAttribute(type: PluginType::EDITOR)]

// Instead of: #[PluginAttribute(type: 'block')]
#[PluginAttribute(type: PluginType::BLOCK)]

// Instead of: #[PluginAttribute(type: 'other')]
#[PluginAttribute(type: PluginType::OTHER)]

// Or simply omit the type parameter since OTHER is default:
#[PluginAttribute]
```

Available PluginType values:

- `PluginType::ARTICLE` - For processing article content
- `PluginType::BLOCK` - For blocks
- `PluginType::BLOCK_OPTIONS` - For block options
- `PluginType::COMMENT` - For comment systems
- `PluginType::EDITOR` - For editors
- `PluginType::FRONTPAGE` - For frontpage modifications
- `PluginType::GAMES` - For games
- `PluginType::ICONS` - For icon libraries
- `PluginType::IMPEX` - For import/export
- `PluginType::OTHER` - Default type (can be omitted)
- `PluginType::PAGE_OPTIONS` - For page options
- `PluginType::PARSER` - For parsers
- `PluginType::SEO` - For SEO
- `PluginType::SSI` - For blocks with SSI functions

For plugins extending `Block`, `Editor`, `GameBlock`, or `SSIBlock` classes, the type is automatically inherited and doesn't need to be specified explicitly.

:::info Nota

Puedes usar el **PluginMaker** como un ayudante para crear tus propios plugins. Descargue y habilítelo en la página _Admin -> Configuración del portal -> Plugins_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Elegir el tipo de plugin

Actualmente, los siguientes tipos de plugins están disponibles:

| Type                            |                                                                                                                                    Descripción |
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
| `games`                         |                                                                     Plugins that typically add a block with some kind of game. |

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

```php:line-numbers {16}
<?php declare(strict_types=1);

namespace LightPortal\Plugins\HelloWorld;

use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;

if (! defined('LP_NAME'))
    die('No direct access...');

#[PluginAttribute(icon: 'fas fa-globe')]
class HelloWorld extends Plugin
{
    public function init(): void
    {
        echo 'Hello world!';
    }

    // Other hooks and custom methods
}

```

## SSI

Si el plugin necesita recuperar cualquier dato usando funciones SSI, utilice el método integrado `getFromSsi(string $function, ...$params)`. Como parámetro `$function` debes pasar el nombre de una de las funciones contenidas en el archivo **SSI.php**, sin prefijo `ssi_`. Por ejemplo:

```php:line-numbers {17}
<?php declare(strict_types=1);

namespace LightPortal\Plugins\TopTopics;

use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\SsiBlock;

if (! defined('LP_NAME'))
    die('No direct access...');

#[PluginAttribute(icon: 'fas fa-star')]
class TopTopics extends SsiBlock
{
    public function prepareContent(Event $e): void
    {
        $data = $this->getFromSSI('topTopics', 'views', 10, 'array');

        if ($data) {
            var_dump($data);
        } else {
            echo '<p>No top topics found.</p>';
        }
    }
}
```

## Blade templates

Your plugin can use a template with Blade markup. Por ejemplo:

```php:line-numbers {16,20}
<?php declare(strict_types=1);

namespace LightPortal\Plugins\Calculator;

use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\Block;
use LightPortal\Utils\Traits\HasView;

if (! defined('LP_NAME'))
    die('No direct access...');

#[PluginAttribute(icon: 'fas fa-calculator')]
class Calculator extends Block
{
    use HasView;

    public function prepareContent(Event $e): void
    {
        echo $this->view(params: ['id' => $e->args->id]);
    }
}
```

**Instructions:**

1. Create the `views` subdirectory inside your plugin directory if it doesn't exist.
2. Create the file `default.blade.php` with the following content:

```blade
<div class="some-class-{{ $id }}">
    {{-- Your blade markup --}}
</div>

<style>
// Your CSS
</style>

<script>
// Your JS
</script>
```

## Composer

Su plugin puede utilizar librerías de terceros instaladas a través de Composer. Asegúrese de que el archivo `composer.json` que contiene las dependencias necesarias, se encuentra en el directorio de plugins. Antes de publicar tu plugin, abre el directorio de plugins en la línea de comandos y ejecuta el comando: `composer install --no-dev -o`. Después de eso, todo el contenido del directorio de plugins puede ser empaquetado como una modificación separada para SMF (por ejemplo el paquete **PluginMaker**).

Por ejemplo:

::: code-group

```php:line-numbers {15} [CarbonDate.php]
<?php declare(strict_types=1);

namespace LightPortal\Plugins\CarbonDate;

use Carbon\Carbon;
use LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
    die('No direct access...');

class CarbonDate extends Plugin
{
    public function init(): void
    {
        require_once __DIR__ . '/vendor/autoload.php';

        $date = Carbon::now()->format('l, F j, Y \a\t g:i A');

        echo 'Current date and time: ' . $date;
    }
}
```

```json [composer.json]
{
    "require": {
      "nesbot/carbon": "^3.0"
    },
    "config": {
      "optimize-autoloader": true
    }
}
```

:::
