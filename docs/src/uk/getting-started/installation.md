---
description: Список вимог до встановлення порталу, а також вирішення можливих проблем
order: 1
---

# Інсталяція

Тут все запросто. Light Portal може бути встановлений як будь-які інші модификації для SMF - через менеджер пакетів.

## Вимоги

- [SMF 2.1.x](https://download.simplemachines.org)
- Сучасний веб-браузер з увімкненим JavaScript
- Інтернет (портал та багато плагінів завантажують скрипти та стилі з CDN)
- PHP 8.2 or higher
- PHP розширення `intl` для коректної локалізації деяких мовних стрічок
- Розширення PHP `dom` і `simplexml` для експорту/імпорту сторінок і блоків
- Розширення PHP `zip` для експорту/імпорту плагінів
- MySQL 5.7+ / MariaDB 10.5+ / PostgreSQL 12+

:::info Примітка

Досить завантажити пакет з файлами порталу з [офіційного каталогу](https://custom.simplemachines.org/mods/index.php?mod=4244) і завантажити через менеджер пакетів на вашому форумі.

:::

## Testing

You can try our [Docker files](https://github.com/dragomano/Light-Portal/tree/d1074c8486ed9eb2f9e89e3afebce2b914d4d570/_docker) or your preffered LAMP/WAMP/MAMP app.
