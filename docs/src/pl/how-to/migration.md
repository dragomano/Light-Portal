---
description: Przewodnik po migracji do serwisu Light Portal z innych portali
---

# Przejście na Light Portal

Przejście na nowy portal to ważny krok. Ten przewodnik pomoże Ci przenieść treści z innych portali SMF do Light Portal.

## Przygotowanie

### Kopia zapasowa

Przed rozpoczęciem migracji należy wykonać pełną kopię zapasową:

- Baza danych forum
- Pliki z forum (`Themes`, `Sources`)

:::warning Ostrzeżenie

Nigdy nie należy rozpoczynać migracji na działającym forum bez uprzedniego przetestowania jej na serwerze lokalnym lub stronie testowej.

:::

### Przeanalizuj swoje obecne treści

Sporządź listę elementów, które należy przenieść:

- Bloki
- Strony
- Kategorie

:::info Uwaga

Importowane mogą być wyłącznie bloki i strony o typach zawartości PHP, HTML lub BBCode. Pozostałe typy bloków trzeba będzie utworzyć ręcznie.

:::

### Usunięcie poprzedniego portalu

Proszę pozostawić w bazie danych tabele utworzone przez poprzedni portal — są one potrzebne do importu.

## Migracja z TinyPortal

1. Zainstaluj i aktywuj wtyczkę TinyPortalMigration
2. Przejdź do wybranej sekcji — **Bloki**, **Strony** lub **Kategorie**, a następnie wybierz opcję **Importuj z TinyPortal**

## Migracja z EhPortal (SimplePortal)

1. Zainstaluj i aktywuj wtyczkę EhPortalMigration
2. Przejdź do wybranej sekcji — **Bloki**, **Strony** lub **Kategorie**, a następnie wybierz opcję **Importuj z EhPortal**

## Migracja z EzPortal

1. Zainstaluj i aktywuj wtyczkę EzPortalMigration
2. Przejdź do wybranej sekcji — **Bloki** lub **Strony**, a następnie wybierz opcję **Importuj z EzPortal**

## Dodatkowa pomoc

Jeśli podczas migracji napotkasz trudności:

1. Poszukaj rozwiązania na [forum pomocy technicznej](https://www.simplemachines.org/community/index.php?topic=572393.0)
2. Utwórz nowy post opisujący swój problem
3. Załącz zrzuty ekranu i logi błędów

Możesz też skorzystać z sekcji komentarzy bezpośrednio na tej stronie.
