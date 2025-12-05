---
description: Короткий опис інтерфейсу створення плагінів
order: 2
---

# Додати плагін

Щоб додати блок, просто натисніть на нього. Спочатку ви можете створювати блоки трьох типів: PHP, HTML та BBCode. Якщо вам потрібні інші, спочатку [увімкніть необхідні плагіни](../plugins/manage) типу `block`.

В залежності від типу блоку, будуть доступні різні параметри, поширюються на різні вкладки.

## Block types

### Built-in content types

- **BBC**: Allows BBCode markup for content
- **HTML**: Raw HTML content
- **PHP**: Executable PHP code (admin only)

### Plugin-based blocks

Blocks from plugins extend functionality. Examples:

- **Markdown**: Enables Markdown syntax for content
- **ArticleList**: Displays articles from topics/pages with customizable display options
- **Calculator**: Interactive calculator widget
- **BoardStats**: Forum board statistics
- **News**: Latest announcements
- **Polls**: Active forum polls
- **RecentPosts**: Recent forum activity
- **UserInfo**: Current user details
- **WhosOnline**: Online users list

## Вкладка "Вміст"

Тут ви можете налаштувати:

- заголовок
- примітка
- вміст (тільки для деяких блоків)

![Content tab](content_tab.png)

## Вкладка доступу і розміщення

Тут ви можете налаштувати:

- публікація
- дозволи
- ділянки

![Access tab](access_tab.png)

## Зовнішня вкладка

Here you can configure the block appearance options.

![Appearance tab](appearance_tab.png)

## Вкладка "Настройки"

Тунери для блокування зазвичай доступні на вкладці **Настройки**.

![Tuning tab](tuning_tab.png)

Плагіни можуть додавати власні налаштування у будь-який з цих розділів, в залежності від намірів розробників.
