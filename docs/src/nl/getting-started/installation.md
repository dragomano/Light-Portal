---
description: Lijst van vereisten voor portaalinstallatie en oplossingen voor mogelijke problemen
order: 1
---

# Installeren

Er zijn geen subtiliteiten. Light Portal kan geïnstalleerd worden zoals elke andere wijziging voor SMF - via de pakketbeheerder.

## Vereisten

- [SMF 2.1.x](https://download.simplemachines.org)
- Moderne browser met JavaScript ingeschakeld
- Internet (de portal en vele plugins laden scripts en stijlen van CDN)
- PHP 8.1 of hoger
- PHP extensie `intl` om sommige taalstrings correct te lokaliseren
- PHP extensies `dom` en `simplexml` om pagina's en blokken te exporteren/importeren
- PHP extensie `zip` voor export/import plugins

:::info Opmerking

Het is voldoende om het pakket met de portalbestanden te downloaden van de [officiële catalogus](https://custom.simplemachines.org/mods/index.php?mod=4244) en het via de pakketbeheerder op je forum te uploaden.

:::

## Probleemoplossing

Als uw hosting te slim is, met rechten en de portalbestanden werden niet uitgepakt tijdens de installatie, je moet de mappen `Themes` en `Sources` handmatig uitpakken uit het modificatie archief in je forum map (waar dezelfde thema's en bronnen mappen al aanwezig zijn evenals bestanden `cron.php`, `SSI.php`, `Settings.php`, etc) en stel de juiste permissies in. Meestal is het `644`, `664` of `666` voor bestanden, en `755`, `775` of `777` voor mappen.

U moet ook het bestand `database uitpakken.php` van modificatie archief naar de hoofdmap van uw forum, stel de uitvoeringsrechten in (`666`) en krijg toegang via de browser (je moet ingelogd zijn als een forumbeheerder). Dit bestand bevat instructies voor het maken van de tabellen die door de portal worden gebruikt.
