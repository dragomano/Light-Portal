---
description: Krótkie podsumowanie dostępnych ustawień portalu
order: 3
outline: [ 2, 3 ]
---

# Ustawienia portalu

Użyj szybkiego dostępu przez element w głównym menu forum lub odpowiedniej sekcji w panelu administratora, aby otworzyć ustawienia portalu.

Nie opiszemy szczegółowo każdego z dostępnych ustawień, wymienimy tylko te najważniejsze.

## Ustawienia ogólne

In this section, you can fully customize the portal front page, enable standalone mode, and change user permissions to access portal items.

### Settings for the front page and articles

To change the content of the portal home page, select the appropriate "the portal front page" mode:

- Wyłącz
- Określona strona (tylko wybrana strona będzie wyświetlana)
- Wszystkie strony z wybranych kategorii
- Wybrane strony
- Wszystkie wątki z wybranych działów
- Wybrane wątki
- Wybrane działy

### Tryb autonomiczny

W tym trybie możesz określić własną stronę główną i usunąć niepotrzebne elementy z menu głównego (listy użytkowników, kalendarza itp.). Na przykład zobacz `portal.php` w katalogu głównym forum.

### Uprawnienia

Tutaj po prostu zauważysz, że WHO może i co może zrobić z różnymi elementami (blokami i stronami) portalu.

## Strony i bloki

W tej sekcji można zmienić ogólne ustawienia stron i bloków używanych zarówno podczas ich tworzenia, jak i wyświetlania.

## Panele

W tej sekcji możesz zmienić niektóre ustawienia istniejących paneli portalowych i dostosować kierunek bloków w tych panelach.

![Panels](panels.png)

## Inne

W tej sekcji możesz zmienić różne ustawienia pomocnicze portalu, które mogą być przydatne dla twórców szablonów i wtyczek.

### Tryb kompatybilności

- Wartość parametru **akcji** portalu, można zmienić to ustawienie na Portal Światła w połączeniu z innymi podobnymi modyfikacjami. Następnie strona główna zostanie otwarta pod podanym adres.
- Parametr **strony** dla stron portalu, patrz powyżej. Similarly, for portal pages - change the parameter and they will open with different URLs.

### Konserwacja

- Tygodniowa optymalizacja tabel portalu, włącz tę opcję, aby raz w tygodniu wiersze z pustymi wartościami w tabelach portalu w bazie danych zostały usunięte i tabele zostaną zoptymalizowane.
