---
sidebar_position: 1
---

# Installeren
Er zijn geen subtiliteiten. Licht Portaal kan geïnstalleerd worden zoals elke andere wijziging voor SMF - via de pakketbeheerder.

:::info

Het is genoeg om het archief te downloaden met de portalbestanden (in SMF dit heet een pakket) uit de [officiële catalogus](https://custom.simplemachines.org/mods/index.php?mod=4244) en te uploaden via de pakketmanager op uw forum.

:::

## Probleemoplossing
Als uw hosting te slim is, met rechten en de portalbestanden werden niet uitgepakt tijdens de installatie, u moet de mappen `Thema's` en `Bronnen` handmatig uitpakken uit het wijzigingsarchief in uw forummap (waar de mappen met dezelfde thema's en bronnen zich al bevinden, evenals bestanden `cron. hp`, `SSI.php`, `Settings.php`, etc) en stel de juiste missers in. Meestal is het `644`, `664` of `666` voor bestanden, en `755`, `775` of `777` voor mappen.

U moet ook de database met bestand `uitpakken. hp` van modificatie archief naar de hoofdmap van uw forum, stel uitvoeringsrechten hiervoor in (`666`) en toegang via de browser (u moet ingelogd zijn als forumbeheerder). Dit bestand bevat instructies voor het maken van de tabellen die door de portal worden gebruikt.

Als u na het voltooien van alle bovenstaande stappen de sectie nog steeds niet ziet met de portalinstellingen in het beheerscherm, controleer op regel `$sourcedir/LightPortal/app. hp` (variabele `integrate_pre_include`) in de tabel `<your_prefix>instellingen` van de database. Om dit te doen, gebruik de ingebouwde zoekfunctie van de phpMyAdmin of een ander vergelijkbaar hulpmiddel.
