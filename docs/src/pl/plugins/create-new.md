---
description: Krótki opis interfejsu tworzenia wtyczek
order: 2
---

# Dodaj wtyczkę

Wtyczki to rozszerzenia, które rozszerzają możliwości Light Portal. Aby utworzyć własną wtyczkę, postępuj zgodnie z poniższymi instrukcjami.

:::info Uwaga

Możesz użyć **PluginMaker** jako pomocnika do tworzenia własnych wtyczek. Pobierz i włącz na stronie _Admin -> Ustawienia portalu -> Wtyczki_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Wybór typu wtyczki

Obecnie dostępne są następujące typy wtyczek:

| Typ                             |                                                                                                                       Opis |
| ------------------------------- | -------------------------------------------------------------------------------------------------------------------------: |
| `block`                         |                                                         Wtyczki, które dodają nowy typ bloków dla portalu. |
| `ssi`                           |              Wtyczki (zazwyczaj bloki), które używają funkcji SSI do pobierania danych. |
| `editor`                        |                                          Wtyczki, które dodają zewnętrzny edytor dla różnych typów treści. |
| `comment`                       |                           Wtyczki dodające komentarz firm trzecich zamiast wbudowanego widżetu komentarza. |
| `parser`                        |                                        Wtyczki, które zaimplementują parser dla zawartości stron i bloków. |
| `article`                       |                                     Wtyczki do przetwarzania zawartości kart artykułów na stronie głównej. |
| `frontpage`                     |                                                                  Wtyczki do zmiany strony głównej portalu. |
| `impex`                         |                                                   Wtyczki do importu i eksportu różnych elementów portalu. |
| `block_options`, `page_options` | Wtyczki, które dodają dodatkowe parametry dla odpowiedniej jednostki (blok lub strona). |
| `icons`                         |          Wtyczki, które dodają nowe biblioteki ikon do zastępowania elementów interfejsu lub do użytku w nagłówkach bloków |
| `seo`                           |                                        Wtyczki, które w jakiś sposób wpływają na widoczność forum w sieci. |
| `other`                         |                                             Wtyczki, które nie są związane z żadną z powyższych kategorii. |

## Tworzenie katalogu wtyczek

Utwórz osobny folder dla swoich plików wtyczek w katalogu "/Sources/LightPortal/Plugins". Na przykład, jeśli wtyczka jest nazywana "HelloWorld", struktura folderów powinna wyglądać tak:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

Plik "index.php" może być skopiowany z folderów innych wtyczek. Plik "HelloWorld.php" zawiera logikę wtyczki:

```php:line-numbers {17}
<?php declare(strict_types=1);

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
        echo 'Hello world!';
    }

    // Hookable and custom methods
}

```

## Używanie SSI

Jeśli wtyczka musi pobrać jakiekolwiek dane za pomocą funkcji SSI, użyj wbudowanej metody "getFromSsi(string $function, ...$params)". Jako parametr `$function` musisz podać nazwę jednej z funkcji zawartych w pliku **SSI.php**, bez prefiksu `ssi_`. Na przykład:

```php
$data = $this->getFromSSI('topTopics', 'views', 10, 'array');
```

## Używanie kompozytora

Wtyczka może korzystać z bibliotek firm trzecich zainstalowanych przez Composer. Upewnij się, że plik `composer.json`, który zawiera niezbędne zależności, znajduje się w katalogu wtyczki. Przed opublikowaniem wtyczki, otwórz katalog wtyczek w wierszu poleceń i uruchom polecenie: "composer install --no-dev -o". Następnie cała zawartość katalogu wtyczek może być zapakowana jako oddzielna modyfikacja dla SMF (na przykład patrz pakiet **PluginMaker**).
