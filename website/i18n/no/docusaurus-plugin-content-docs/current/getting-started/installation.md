---
sidebar_position: 1
---

# Installerer
Det er ingen undertekster her. Lys Portal kan installeres, som alle andre endringer for SMF - gjennom pakkebehandleren.

:::info

Det er nok til å laste ned arkivet med portalfiler (i SMF dette kalles en pakke) fra [den offisielle katalogen](https://custom.simplemachines.org/mods/index.php?mod=4244) og laste opp via pakkeadministratoren på forumet ditt.

:::

## Feilsøking
Hvis din hosting er for "smart" med tillatelser og portalfiler ikke ble pakket ut under installasjon, du må trekke ut mappene `Temaer` og `Kilder` fra modifikasjonsarkivet til mappen forum (der de samme temaer og kilder mappene allerede ligger, så vel som filer `cron. hp`, `SSI.php`, `Settings.php`, etc) og angi de riktige tillatelsene. Oftest er det `644`, `664` eller `666` for filer, og `755`, `775` eller `777` for mapper.

Du må også pakke ut filen `databasen. hp` fra modifikasjonsarkiv til roten av forumet ditt, angi utførelsesrettigheter for den (`666`) og få tilgang til den gjennom nettleseren (du må være innlogget som en forumadministrator). Denne filen inneholder instruksjoner for å opprette tabellene som brukes av portalen. Melding `Databasens endringer er fullført! Vennligst vent...` vil bekrefte vellykket kjøring av skriptet.

Hvis du etter å ha fullført alle trinnene ovenfor, ikke ser du seksjonen med portalinnstillingene i admin-panelet. Sjekk etter linjen `$sourcedir/LightPortal/app. hp` (variabel `integrate_pre_include`) i tabellen `<your_prefix>innstillinger` i databasen. For å gjøre dette må du bruke det innebygde søket på phpMyAdmin eller andre lignende nytte.
