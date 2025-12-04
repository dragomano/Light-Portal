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
- PHP 8.2 or higher
- PHP extensie `intl` om sommige taalstrings correct te lokaliseren
- PHP extensies `dom` en `simplexml` om pagina's en blokken te exporteren/importeren
- PHP extensie `zip` voor export/import plugins
- MySQL 5.7+ / MariaDB 10.5+ / PostgreSQL 12+

:::info Opmerking

Het is voldoende om het pakket met de portalbestanden te downloaden van de [officiële catalogus](https://custom.simplemachines.org/mods/index.php?mod=4244) en het via de pakketbeheerder op je forum te uploaden.

:::

## Testing

You can try our [Docker files](https://github.com/dragomano/Light-Portal/tree/d1074c8486ed9eb2f9e89e3afebce2b914d4d570/_docker) or your preffered LAMP/WAMP/MAMP app.
