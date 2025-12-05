---
description: Seznam zahtev za namestitev portala ter rešitve morebitnih težav
order: 1
---

# Namestitev

Tukaj ni nobenih posebnosti. Light Portal se lahko namesti kot katera koli druga modifikacija za SMF - preko upravitelja paketov.

## Pogoji

- [SMF 2.1.x](https://download.simplemachines.org)
- Sodoben brskalnik z omogočenim JavaScript-om
- Internet (portal in mnogi vtičniki nalagajo skripte in sloge iz CDN)
- PHP 8.2 ali novejša verzija
- PHP razširitev intl za pravilno lokalizacijo nekaterih jezikovnih nizov
- PHP razširitvi dom in simplexml za izvoz/uvoz strani in blokov
- PHP razširitev zip za izvoz/uvoz vtičnikov
- MySQL 5.7+ / MariaDB 10.5+ / PostgreSQL 12+

:::info Opomba

Dovolj je, da preneseš paket z datotekami portala iz [uradnega kataloga](https://custom.simplemachines.org/mods/index.php?mod=4244) in ga naložiš preko upravitelja paketov na svojem forumu.

:::

## Testiranje

Lahko preizkusiš naše [Docker datoteke](https://github.com/dragomano/Light-Portal/tree/d1074c8486ed9eb2f9e89e3afebce2b914d4d570/_docker) ali svojo priljubljeno LAMP/WAMP/MAMP aplikacijo.
