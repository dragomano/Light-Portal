---
sidebar_position: 1
---

# Installerar
Det finns inga nyanser här. Light Portal kan installeras som alla andra ändringar för SMF - via pakethanteraren.

:::info

Det räcker med att ladda ner arkivet med portalfilerna (i SMF kallas detta ett paket) från [officiella katalog](https://custom.simplemachines.org/mods/index.php?mod=4244) och ladda upp via pakethanteraren på ditt forum.

:::

## Felsökning
Om ditt webbhotell är för "smart" med behörigheter och portalfilerna inte packades upp under installationen, du behöver manuellt extrahera katalogerna `Teman` och `Källor` från ändringsarkivet till din forummapp (där samma teman och källor redan finns, såväl som filer `cron. hk`, `SSI.php`, `Settings.php`, etc) och ange lämpliga tillstånd. Oftast är det `644`, `664` eller `666` för filer, och `755`, `775` eller `777` för mappar.

Också du behöver för att packa upp filen `databas.` från ändringsarkiv till roten av ditt forum, ange utföranderättigheter för det (`666`) och komma åt det via webbläsaren (du måste vara inloggad som forumadministratör). Denna fil innehåller instruktioner för att skapa de tabeller som används av portalen. Meddelandet `Database changes are complete! Please wait...` kommer att bekräfta det lyckade utförandet av skriptet.

Om du efter att ha slutfört alla ovanstående steg fortfarande inte ser avsnittet med portalinställningarna i adminpanelen, kontrollera raden `$sourcedir/LightPortal/app. hp` (variabel `integrate_pre_include`) i tabellen `<your_prefix>inställningar` i din databas. För att göra detta, använd den inbyggda sökningen av phpMyAdmin eller ett annat liknande verktyg.
