---
description: Wykaz wymogów dotyczących instalacji portali oraz rozwiązań możliwych problemów
order: 1
---

# Instalacja

Nie ma tutaj żadnych subtelności. Light Portal może być zainstalowany tak jak każda inna modyfikacja dla SMF, poprzez menedżera pakietów.

## Wymagania

- [SMF 2.1.x](https://download.simplemachines.org)
- Nowoczesna przeglądarka z włączonym JavaScript
- Internet (portal i wiele wtyczek ładuje skrypty i style z CDN)
- PHP 8.2 or higher
- Rozszerzenie PHP `intl` do poprawnej lokalizacji niektórych ciągów językowych
- Rozszerzenia PHP `dom` i `simplexml` do eksportu/importu stron i bloków
- Rozszerzenie PHP `zip` do eksportu/importu wtyczek
- MySQL 5.7+ / MariaDB 10.5+ / PostgreSQL 12+

:::info Notatka

Wystarczy pobrać pakiet z plikami portalu z [oficjalnego katalogu](https://custom.simplemachines.org/mods/index.php?mod=4244) i przesłać go za pomocą menedżera pakietów na swoim forum.

:::

## Testing

You can try our [Docker files](https://github.com/dragomano/Light-Portal/tree/d1074c8486ed9eb2f9e89e3afebce2b914d4d570/_docker) or your preffered LAMP/WAMP/MAMP app.
