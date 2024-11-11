---
description: Короткий опис інтерфейсу створення плагінів
order: 2
---

# Додати плагін

Плагіни - це розширення, які розширюють можливості Світлового порталу. Щоб створити свій власний плагін, дотримуйтесь інструкцій нижче.

:::info Примітка

Ви можете використовувати **PluginMaker** в якості помічника для створення своїх власних плагін. Завантажте та увімкніть їх на сторінці _Admin -> Налаштування порталу -> Plugins_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Вибір типу плагіна

Доступні наступні типи плагінів:

### `block`

Плагіни, які додають новий тип блоків для порталу.

### `ssi`

Плагіни (як правило, блоки), які використовують функції SSI для отримання даних.

### `editor`

Плагіни, які додають сторонній редактор для різних типів вмісту.

### `comment`

Плагіни, що додають віджет коментарів третіх сторін замість вбудованої.

### `parser`

Плагіни, що реалізують аналізатор сторінок і блоків.

### `article`

Плагіни для обробки вмісту карток статей на головній сторінці.

### `frontpage`

Плагіни для зміни головної сторінки порталу.

### `impex`

Плагіни для імпорту та експорту різних елементів порталу.

### `block_options` | `page_options`

Плагини, які додають додаткові параметри для відповідного об'єкта (блок або .page).

### `icons`

Плагіни, що додають нові бібліотеки іконок, щоб замінити елементи інтерфейсу або використовувати в заголовках блоків

### `seo`

Плагіни які якимось чином впливають на видимість форуму в мережі.

### `other`

Плагіни, які не пов'язані з жодною з категорій вище.

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

```php:line-numbers
<?php

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
        // Access to global variables: Utils::$context['user'], Config::$modSettings['variable'], etc.
        // Access to language variables: Lang::$txt['lp_hello_world']['variable_name']
    }

    // Custom properties and methods
}

```

## Використання SSI

Якщо плагін потребує отримання будь-яких даних використовуючи функції SSI, використовуйте метод `getFromSsi(string $function, ...$params)` для цього методу. Як параметр `$function` ви повинні передати ім'я однієї з функцій, що містяться у файлі **SSI.php**, без префікса `ssi_`. Наприклад:

```php
<?php

// See ssi_topTopics function in the SSI.php file
$data = $this->getFromSsi('topTopics', 'views', 10, 'array');
```

## Використання композитора

Ваш плагін може використовувати сторонні бібліотеки, встановлені через Composer. Переконайтеся, що файл `composer.json`, який містить необхідні залежності, розташований в каталозі плагінів. Перед публікацією вашого плагіна відкрийте каталог плагінів у командному рядку і запустіть команду: `composer install --no-dev -o`. Після цього весь вміст каталогу плагінів можна упакувати як окрему модифікацію для SMF (наприклад, див. **PluginMaker** пакет).