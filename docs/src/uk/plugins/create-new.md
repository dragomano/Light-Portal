---
description: Короткий опис інтерфейсу створення плагінів
order: 2
---

# Додати плагін

Плагіни - це розширення, які розширюють можливості Світлового порталу. Щоб створити свій власний плагін, дотримуйтесь інструкцій нижче.

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

:::info Примітка

Ви можете використовувати **PluginMaker** в якості помічника для створення своїх власних плагін. Завантажте та увімкніть їх на сторінці _Admin -> Налаштування порталу -> Plugins_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Вибір типу плагіна

Доступні наступні типи плагінів:

| Type                            |                                                                                                                                   Опис |
| ------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------: |
| `block`                         |                                                                     Плагіни, які додають новий тип блоків для порталу. |
| `ssi`                           |                    Плагіни (як правило, блоки), які використовують функції SSI для отримання даних. |
| `editor`                        |                                                       Плагіни, які додають сторонній редактор для різних типів вмісту. |
| `comment`                       |                                                Плагіни, що додають віджет коментарів третіх сторін замість вбудованої. |
| `parser`                        |                                                                   Плагіни, що реалізують аналізатор сторінок і блоків. |
| `article`                       |                                                         Плагіни для обробки вмісту карток статей на головній сторінці. |
| `frontpage`                     |                                                                           Плагіни для зміни головної сторінки порталу. |
| `impex`                         |                                                              Плагіни для імпорту та експорту різних елементів порталу. |
| `block_options`, `page_options` | Плагини, які додають додаткові параметри для відповідного об'єкта (блок або .page). |
| `icons`                         |                   Плагіни, що додають нові бібліотеки іконок, щоб замінити елементи інтерфейсу або використовувати в заголовках блоків |
| `seo`                           |                                                      Плагіни які якимось чином впливають на видимість форуму в мережі. |
| `other`                         |                                                                   Плагіни, які не пов'язані з жодною з категорій вище. |
| `games`                         |                                                             Plugins that typically add a block with some kind of game. |

## Створення каталогу плагінів

Створіть окрему папку для файлів плагінів всередині `/Sources/LightPortal/Plugins`. Наприклад, якщо ваш плагін називається "HelloWorld", структура папки повинна виглядати наступним чином:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Файл `index.php` може бути скопійований з папок інших плагінів. Файл `HelloWorld.php` містить логіку плагіна:

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

Якщо плагін потребує отримання будь-яких даних використовуючи функції SSI, використовуйте метод `getFromSsi(string $function, ...$params)` для цього методу. Як параметр `$function` ви повинні передати ім'я однієї з функцій, що містяться у файлі **SSI.php**, без префікса `ssi_`. Наприклад:

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

Your plugin can use a template with Blade markup. Наприклад:

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

Ваш плагін може використовувати сторонні бібліотеки, встановлені через Composer. Переконайтеся, що файл `composer.json`, який містить необхідні залежності, розташований в каталозі плагінів. Перед публікацією вашого плагіна відкрийте каталог плагінів у командному рядку і запустіть команду: `composer install --no-dev -o`. Після цього весь вміст каталогу плагінів можна упакувати як окрему модифікацію для SMF (наприклад, див. **PluginMaker** пакет).

Наприклад:

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
