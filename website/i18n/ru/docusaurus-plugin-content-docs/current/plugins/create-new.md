---
sidebar_position: 2
---

# Создание плагина
Плагины — дополнения, расширяющие возможности портала. Чтобы создать собственный плагин для Light Portal, достаточно следовать инструкциям ниже.

:::info

С версии 1.9 функционал для создания плагинов вынесен в отдельный плагин — **PluginMaker**. Скачайте и подключите его на странице _Админка -> Настройки портала -> Плагины_.

:::

## Выбор типа плагина
На данный момент в Light Portal доступны следующие типы дополнений:

* `block` — плагины, добавляющие новый вид блоков для портала
* `ssi` — плагины (как правило, блоки), использующие SSI-функции для получения данных
* `editor` — плагины, добавляющие сторонний редактор для разных типов контента
* `comment` — плагины, добавляющие сторонний виджет комментариев, вместо стандартного
* `parser` — плагины, реализующие парсер контента страниц и блоков
* `article` — плагины для обработки содержимого карточек статей на главной
* `frontpage` — плагины для изменения главной страницы портала
* `impex` — плагины для импорта и экспорта различных элементов портала
* `block_options` и `page_options` — плагины, добавляющие дополнительные параметры для соответствующей сущности (блока или страницы)
* `icons` — плагины, добавляющие новые библиотеки иконок для замены элементов интерфейса или использования в заголовках блоков
* `seo` — плагины, тем или иным образом влияющие на видимость форума в сети
* `other` — плагины, не входящие ни в одну из категорий выше

## Создание директории плагина
Создайте отдельную папку для файлов вашего дополнения, внутри `/Sources/LightPortal/Addons`. Например, если ваш плагин называется `HelloWorld`, структура папки должна выглядеть так:

```
...(Addons)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Файл `index.php` можно скопировать из папок других дополнений. В файле `HelloWorld.php` содержится логика плагина:

```php
<?php

/**
 * HelloWorld.php
 *
 * @package HelloWorld (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Никнейм <email>
 * @copyright 2023 Никнейм
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 23.03.23 (дата создания, а в дальнейшем — обновления кода плагина, в формате дд.мм.гг)
 */

namespace Bugo\LightPortal\Addons\HelloWorld;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
    die('No direct access...');

class HelloWorld extends Plugin
{
    // Используемые свойства и методы
    // Обращение к глобальным переменным: $this->context['user'], $this->modSettings['variable'] и т. д.
    // Обращение к языковым переменным: $this->txt['lp_hello_world']['variable_name']
}

```

## Использование SSI
Если в плагине требуется получить какие-либо данные с помощью SSI-функций, используйте встроенный метод `getFromSsi(string $function, ...$params)`. В качестве параметра `$function` нужно передать имя одной из функций, находящихся в файле **SSI.php**, без приставки `ssi_`. Например:

```php
<?php

    // См. функцию ssi_topTopics в файле SSI.php
    $data = $this->getFromSsi('topTopics', 'views', 10, 'array');
```

:::caution

Без файла SSI.php вышеупомянутый метод работать не будет.

:::

## Использование Composer
Ваш плагин может использовать сторонние библиотеки, устанавливающиеся через Composer. Убедитесь, что в директории плагина расположен файл `composer.json`, в котором указаны необходимые зависимости. Перед публикацией вашего плагина откройте директорию плагина в командной строке и выполните команду: `composer install --no-dev -o`. После этого всё содержимое директории плагина можно упаковать как отдельную модификацию для SMF (для примера см. пакет **PluginMaker**).