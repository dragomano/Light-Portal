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
- PHP 8.1 lub wyższy
- Rozszerzenie PHP `intl` do poprawnej lokalizacji niektórych ciągów językowych
- Rozszerzenia PHP `dom` i `simplexml` do eksportu/importu stron i bloków
- Rozszerzenie PHP `zip` do eksportu/importu wtyczek

:::info Notatka

Wystarczy pobrać pakiet z plikami portalu z [oficjalnego katalogu](https://custom.simplemachines.org/mods/index.php?mod=4244) i przesłać go za pomocą menedżera pakietów na swoim forum.

:::

## Rozwiązywanie problemów

Jeśli twój hosting jest zbyt "mądry" z uprawnieniami i pliki portalu nie zostały rozpakowane podczas instalacji, musisz ręcznie wyodrębnić katalogi `Themes` i `Sources` z archiwum modyfikacji do folderu forum (gdzie te same motywy i foldery źródłowe są już zlokalizowane, jak również pliki `cron.php`, `SSI.php`, `Settings.php`, etc) i ustaw odpowiednie uprawnienia. Najczęściej jest to `644`, `664` lub `666` dla plików i `755`, `775` lub `777` dla folderów.

Należy również rozpakować plik `database.php` z archiwum modyfikacji do katalogu głównego forum, ustaw prawa do wykonania (`666`) i uzyskaj dostęp przez przeglądarkę (musisz być zalogowany jako administrator forum). Ten plik zawiera instrukcje dotyczące tworzenia tabel używanych przez portal.
