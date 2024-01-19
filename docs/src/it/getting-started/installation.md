---
description: Elenco dei requisiti per l'installazione del portale, nonché soluzioni a possibili problemi
order: 1
---

# Installazione

Non c'è molto da dire qui. Light Portal puoi installarlo come qualsiasi altra modifica di SMF - attraverso il gestore pacchetti.

## Requisiti

- [SMF 2.1.x](https://download.simplemachines.org)
- Browser moderno con JavaScript abilitato
- Internet (il portale e molti plugin caricano script e stili dalla CDN)
- PHP 8.0 o superiore
- Estensione PHP `intl` per localizzare correttamente alcune stringhe di lingua
- Estensioni PHP `dom` e `simplexml` per esportare/importare pagine e blocchi
- Estensione PHP `zip` per esportare/importare plugin

:::info

È sufficiente scaricare l'archivio con i file del portale (in SMF si chiama pacchetto) dal [catalogo ufficiale](https://custom.simplemachines.org/mods/index.php?mod=4244) e caricarlo tramite il gestore pacchetti sul tuo forum.

:::

## Risoluzione dei problemi

Se il tuo hosting è troppo "intelligente" con i permessi e i file del portale non sono stati decompressi durante l'installazione, devi estrarre manualmente le cartelle `Themes` e `Sources` dall'archivio della mod nella cartella del forum (dove si trovano le stesse cartelle Themes e Sources, così come i file `cron.php`, `SSI.php`, `Settings.php`, ecc) ed impostare le autorizzazioni appropriate. Molto spesso è "644", "664" o "666" per i file e "755", "775" o "777" per le cartelle.

Inoltre devi estrarre il file `database.php` dall'archivio della mod alla radice del tuo forum, impostarne i diritti di esecuzione (`666`) e accedervi tramite il browser (devi aver effettuato l'accesso come amministratore del forum) Questo file contiene le istruzioni per la creazione delle tabelle utilizzate dal portale.
