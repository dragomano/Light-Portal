---
sidebar_position: 2
---

# Oppdaterer versjonen
Hvis det er ingen merknader i endringsloggen til den siste versjonen, det er nok til å trekke ut katalogene `temaer` og`kilder` fra modifikasjonsarkivet til roten av ditt forum, over de eksisterende, og oppdateringen vil være riktig. Men det er best å avinstallere denne versjonen før du installerer den nye versjonen.

:::info

Since version 2.1.1 you can upgrade without uninstalling the previous version. Simply download the new archive, go to the Package Manager and click "Upgrade" button next to the uploaded package.

![Updating](upgrade.png)

:::

## Feilsøking

### Warning: Undefined array key "bla-bla-bla"
Hvis du så en lignende feil i portalblokken etter oppdatering, kan du prøve å gå til innstillingene for denne blokken og gjenopprette innstillingene. Dette er ikke en feil, men en advarsel om at det mangler (nye) blokkeringsinnstillinger i databasen.
