---
sidebar_position: 1
---

# Installerer
Der er ingen finesser her. Light Portal kan installeres som enhver anden ændring til SMF - gennem pakkehåndteringen.

:::info

Det er nok at downloade arkivet med portalfilerne (i SMF kaldes dette en pakke) fra det [officielle katalog](https://custom.simplemachines.org/mods/index.php?mod=4244) og uploade via pakkehåndteringen på dit forum.

:::

## Fejlfinding
Hvis din hosting er for "smart" med tilladelser og portalfilerne ikke blev pakket ud under installationen, du skal manuelt udtrække mapperne `Temaer` og `Kilder` fra modifikationsarkivet til din forummappe (hvor de samme temaer og Kilder mapper allerede er placeret, samt filer `cron. hp`, `SSI.php`, `Settings.php`, etc) og indstil de relevante tilladelser. Oftest er det `644`, `664` eller `666` for filer, og `755`, `775` eller `777` for mapper.

Du skal også udpakke filen `databasen. hp` fra ændringsarkiv til roden af dit forum, sæt udførelsesrettigheder for det (`666`) og få adgang til det via browseren (du skal være logget ind som forum administrator). Denne fil indeholder instruktioner til at oprette de tabeller, der bruges af portalen.

Hvis du efter at have gennemført alle ovenstående trin stadig ikke kan se afsnittet med portalindstillingerne i administrationspanelet, tjekke for linjen `$sourcedir/LightPortal/app. hp` (variabel `integrate_pre_include`) i tabellen `<your_prefix>indstillinger` i din database. For at gøre dette, skal du bruge den indbyggede søgning af phpMyAdmin eller en anden lignende værktøj.
