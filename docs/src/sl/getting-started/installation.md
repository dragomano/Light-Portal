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
- PHP 8.1 ali novejša verzija
- PHP razširitev intl za pravilno lokalizacijo nekaterih jezikovnih nizov
- PHP razširitvi dom in simplexml za izvoz/uvoz strani in blokov
- PHP razširitev zip za izvoz/uvoz vtičnikov

:::info Opomba

Dovolj je, da preneseš paket z datotekami portala iz [uradnega kataloga](https://custom.simplemachines.org/mods/index.php?mod=4244) in ga naložiš preko upravitelja paketov na svojem forumu.

:::

## Odpravljanje napak

Če je vaš gostitelj preveč "pametno" nastavil dovoljenja in datoteke portala niso bile razpakirane med namestitvijo, je treba ročno razpakirati mape `Themes` in `Sources` iz arhiva modifikacije v svojo mapo foruma (kjer so že prisotne iste mape Themes in Sources, pa tudi datoteke cron.php, SSI.php, Settings.php itd.) ter nastaviti ustrezna dovoljenja. Najpogosteje so to 644, 664 ali 666 za datoteke ter 755, 775 ali 777 za mape.

Prav tako moraš razpakirati datoteko database.php iz arhiva modifikacije v korenski direktorij tvojega foruma, nastaviti izvršilna dovoljenja zanj (666) in jo dostopati preko brskalnika (moraš biti prijavljen kot administrator foruma). Ta datoteka vsebuje navodila za ustvarjanje tabel, ki jih uporablja portal.
