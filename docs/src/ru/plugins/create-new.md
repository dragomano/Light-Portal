---
description: Краткое описание интерфейса создания плагина
order: 2
---

# Создание плагина

Плагины — дополнения, расширяющие возможности портала. Чтобы создать собственный плагин для Light Portal, достаточно следовать инструкциям ниже.

## Перечисление PluginType

Для лучшей типобезопасности и поддержки IDE вы можете использовать перечисление `PluginType` вместо строковых значений для параметра `type`:

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

Доступные значения PluginType:

- `PluginType::ARTICLE` — Для обработки контента статей
- `PluginType::BLOCK` — Для блоков
- `PluginType::BLOCK_OPTIONS` — Для параметров блоков
- `PluginType::COMMENT` — Для систем комментариев
- `PluginType::EDITOR` — Для редакторов
- `PluginType::FRONTPAGE` — Для модификаций главной страницы
- `PluginType::GAMES` — Для игр
- `PluginType::ICONS` — Для библиотек иконок
- `PluginType::IMPEX` — Для импорта/экспорта
- `PluginType::OTHER` — Тип по умолчанию (можно опустить)
- `PluginType::PAGE_OPTIONS` — Для параметров страниц
- `PluginType::PARSER` — Для парсеров
- `PluginType::SEO` — Для SEO
- `PluginType::SSI` — Для блоков с SSI-функциями

Для плагинов, расширяющих классы `Block`, `Editor`, `GameBlock` или `SSIBlock`, тип наследуется автоматически и не требует явного указания.

:::info Примечание

Вы можете использовать **PluginMaker** в качестве помощника при создании своих плагинов. Скачайте и подключите его на странице _Админка -> Настройки портала -> Плагины_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Выбор типа плагина

На данный момент в Light Portal доступны следующие типы дополнений:

| Тип                             |                                                                                                                            Описание |
| ------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------: |
| `block`                         |                                                                  Плагины, добавляющие новый вид блоков для портала. |
| `ssi`                           |                     Плагины (как правило, блоки), использующие SSI-функции для получения данных. |
| `editor`                        |                                                  Плагины, добавляющие сторонний редактор для разных типов контента. |
| `comment`                       |                                            Плагины, добавляющие сторонний виджет комментариев, вместо стандартного. |
| `parser`                        |                                                              Плагины, реализующие парсер контента страниц и блоков. |
| `article`                       |                                                       Плагины для обработки содержимого карточек статей на главной. |
| `frontpage`                     |                                                                     Плагины для изменения главной страницы портала. |
| `impex`                         |                                                         Плагины для импорта и экспорта различных элементов портала. |
| `block_options`, `page_options` | Плагины, добавляющие дополнительные параметры для соответствующей сущности (блока или страницы). |
| `icons`                         | Плагины, добавляющие новые библиотеки иконок для замены элементов интерфейса или использования в заголовках блоков. |
| `seo`                           |                                                  Плагины, тем или иным образом влияющие на видимость форума в сети. |
| `other`                         |                                                                   Плагины, не входящие ни в одну из категорий выше. |
| `games`                         |                                                          Плагины, которые обычно добавляют блок с какой-либо игрой. |

## Создание директории плагина

Создайте отдельную папку для файлов вашего дополнения, внутри `/Sources/LightPortal/Plugins` Например, если ваш плагин называется `HelloWorld`, структура папки должна выглядеть так:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Файл `index.php` можно скопировать из папок других дополнений. В файле `HelloWorld.php` содержится логика плагина:

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

Если в плагине требуется получить какие-либо данные с помощью SSI-функций, используйте метод `getFromSsi(string $function, ...$params)`. В качестве параметра `$function` нужно передать имя одной из функций, находящихся в файле **SSI.php**, без приставки `ssi_`. Например:

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

## Шаблоны Blade

Ваш плагин может использовать шаблон с разметкой Blade. Например:

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

**Инструкции:**

1. Создайте поддиректорию `views` внутри директории вашего плагина, если она не существует.
2. Создайте файл `default.blade.php` со следующим содержимым:

```blade
<div class="some-class-{{ $id }}">
    {{-- Ваша разметка Blade --}}
</div>

<style>
// Ваши CSS-стили
</style>

<script>
// Ваш JS-код
</script>
```

## Composer

Ваш плагин может использовать сторонние библиотеки, устанавливающиеся через Composer. Убедитесь, что в директории плагина расположен файл `composer.json`, в котором указаны необходимые зависимости. Перед публикацией вашего плагина откройте директорию плагина в командной строке и выполните команду: `composer install --no-dev -o`. После этого всё содержимое директории плагина можно упаковать как отдельную модификацию для SMF (для примера см. пакет **PluginMaker**).

Например:

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
