---
title: Najczęściej zadawane pytania
description: Najczęściej zadawane pytania dotyczące Light Portal
---

# Najczęściej zadawane pytania

Oto odpowiedzi na najczęściej zadawane pytania dotyczące Light Portal.

## Pytania ogólne

### Które wersje SMF są obsługiwane?

Zobacz [Installation](./getting-started/installation.md).

### Gdzie mogę pobrać program Light Portal?

Zobacz [Installation](./getting-started/installation.md).

---

## Instalacja i konfiguracja

### Jak zainstalować Light Portal?

Zobacz [Installation](./getting-started/installation.md).

### Jak ustawić portal jako stronę główną?

Zobacz [Portal Settings](./getting-started/configuration#settings-for-the-front-page-and-articles).

### Czy mogę korzystać z Light Portal równolegle z innym portalem?

Tak, możesz spróbować połączyć dwa portale.

1. Zainstaluj Light Portal bez usuwania poprzedniej wersji portalu
2. Przejdź do **Ustawienia** → **Różne** i zmień parametry `action`/`page` tak, aby różniły się od tych w pozostałych portalach

---

## Strony

### Jak utworzyć nową stronę?

Zobacz [Add Page](./pages/create-new.md).

### Jak skonfigurować SEO dla stron?

Zobacz [SEO Tab](./pages/create-new#seo-tab).

### Czym są kategorie i tagi?

Zobacz [Glossary](./glossary.md)

Kategorie można tworzyć w sekcji **Portal** → **Kategorie**, a tagi w sekcji **Portal** → **Tagi**.

---

## Bloki

### Jak dodać blok?

Zobacz [Add Block](./blocks/create-new.md).

### Jak zmienić kolejność bloków?

W sekcji zarządzania blokami przeciągnij bloki, aby ustawić je w żądanej kolejności.

### Czy mogę używać JavaScriptu w blokach?

Tak, w tym celu użyj bloku typu HTML.

:::warning Uwaga

Należy zachować ostrożność w przypadku skryptów zewnętrznych — mogą one spowolnić ładowanie strony lub spowodować luki w zabezpieczeniach.

:::

---

## Wtyczki

### Czym są wtyczki?

Wtyczki rozszerzają funkcjonalność Light Portal. Mogą dodawać nowe typy bloków, integrować się z innymi modyfikacjami oraz zapewniać dodatkowe funkcje.

Zobacz [Manage Plugins](./plugins/manage.md) więcej informacji.

### Jak zainstalować dodatkową wtyczkę?

Zobacz [Installing additional plugins](./plugins/manage#installing-additional-plugins).

---

## Projekt i motywy

### Jak mogę zmienić wygląd portalu?

Light Portal korzysta z tego samego motywu, co reszta forum. Można jednak zmienić układ strony głównej.

1. **CSS**: Utwórz plik `portal_custom.css` w folderze `Themes/default/css`
2. **Układ strony**: Utwórz własny układ strony głównej w folderze  `Themes/default/portal_layouts`

Zobacz [Tworzenie niestandardowych układów](./how-to/create-layout.md) Więcej informacji.

---

## Rozwiązywanie problemów

### Strona się nie wyświetla

Sprawdź:

1. Status strony (włączony/wyłączony)
2. Prawidłowy adres URL (nazwa)
3. Ustawienia widoczności (zakładka „Dostęp i rozmieszczenie” w ustawieniach strony)

### Blok się nie wyświetla

Sprawdź:

1. Czy blok jest włączony
2. Do którego panelu jest przypisany
3. Ustawienia widoczności (zakładka „Dostęp i rozmieszczenie” w ustawieniach bloku)

### Błędy po aktualizacji

1. Wyczyść pamięć podręczną forum i pamięć podręczną przeglądarki
2. Włącz cotygodniową optymalizację tabeli w zakładce **Różne** w ustawieniach portalu
3. W razie potrzeby zainstaluj ponownie lub zaktualizuj wtyczki

### Gdzie mogę znaleźć dzienniki błędów?

Logi serwisu Light Portal znajdują się w standardowych logach SMF. W ustawieniach portalu można również włączyć tryb debugowania.

---

## Projektowanie

### Jak stworzyć własną wtyczkę?

Zobacz [Add Plugin](./plugins/create-new.md).

### Gdzie mogę znaleźć dokumentację dotyczącą haków?

Zobacz [Portal Hooks](./plugins/all-hooks.md).

---

## Potrzebujesz pomocy?

Jeśli nie znalazłeś odpowiedzi na swoje pytanie:

1. Przeszukaj [forum pomocy technicznej](https://www.simplemachines.org/community/index.php?topic=572393.0)
2. Utwórz nowy wpis opisujący swój problem
3. Załącz zrzuty ekranu i pliki dziennika błędów

Możesz też skorzystać z sekcji komentarzy bezpośrednio na tej stronie.
