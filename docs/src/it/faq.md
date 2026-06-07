---
title: FAQ
description: Domande frequenti riguardo Light Portal
---

# Domande frequenti

Ecco le risposte alle domande più popolari su Light Portal.

## Domande generali

### Quali versioni di SMF sono supportate?

Guarda [Installazione](./getting-started/installation.md).

### Dove posso scaricare Light Portal?

Guarda [Installazione](./getting-started/installation.md).

---

## Installazione e configurazione

### Come si installa Light Portal?

Guarda [Installazione](./getting-started/installation.md).

### Come imposto il portale come prima pagina?

Guarda [Impostazioni portale](./getting-started/configuration#settings-for-the-front-page-and-articles).

### Posso usare Light Portal insieme a un altro portale?

Sì, puoi provare a combinare due portali.

1. Installare Light Portal senza rimuovere il portale precedente
2. Vai su **Impostazioni** → **Vari** e modifica i parametri `action`/`page` per differire dall'altro portale

---

## Pagine

### Come si crea una nuova pagina?

Guarda [Aggiungi pagina](./pages/create-new.md).

### Come faccio a configurare la SEO per le pagine?

Guarda [Scheda SEO](./pages/create-new#seo-tab).

### Quali sono le categorie e i tag?

Guarda [Glossario](./glossary.md)

Puoi creare categorie in **Portale** → **Categorie** e tag in **Portale** → **Tags**.

---

## Blocchi

### Come faccio ad aggiungere un blocco?

Guarda [Aggiungi blocco](./blocks/create-new.md).

### Come faccio a riordinare i blocchi?

Nella sezione di gestione del blocco, trascinare i blocchi nell'ordine desiderato.

### Posso usare JavaScript nei blocchi?

Sì, utilizzare un blocco di tipo HTML per questo.

:::warning Attenzione

Fai attenzione agli script esterni: possono rallentare il caricamento delle pagine o creare vulnerabilità di sicurezza.

:::

---

## Plugins

### Cosa sono i plugin?

I plugin estendono le funzionalità di Light Portal. Possono aggiungere nuovi tipi di blocchi, integrarsi con altre modifiche e fornire funzionalità aggiuntive.

Guarda [Gestisci Plugins](./plugins/manage.md) per dettagli.

### Come si installa un plugin aggiuntivo?

Guarda [Installazione di plugin aggiuntivi](./plugins/manage#installing-additional-plugins).

---

## Design e temi

### Come faccio a cambiare l'aspetto del portale?

Light Portal utilizza lo stesso tema del resto del forum. Tuttavia, è possibile modificare il layout della prima pagina.

1. **CSS**: Crea un file `portal_custom.css` nella cartella `Themes/default/css`
2. **Layout**: crea un layout personalizzato per la prima pagina nella cartella `Themes/default/portal_layouts`

Guarda [Crea layout personalizzati](./how-to/create-layout.md) per dettagli.

---

## Risoluzione dei problemi

### La pagina non viene visualizzata

Controlla:

1. Stato della pagina (abilitato/disabilitato)
2. URL corretto (slug)
3. Impostazioni di visibilità (scheda accesso e posizionamento nelle impostazioni della pagina)

### Il blocco non viene visualizzato

Controlla:

1. Se il blocco è abilitato
2. A quale pannello è assegnato
3. Impostazioni di visibilità (scheda accesso e posizionamento nelle impostazioni del blocco)

### Errori dopo l'aggiornamento

1. Cancella la cache del forum e la cache del browser
2. Abilita l'ottimizzazione settimanale delle tabelle del portale nella scheda **Varie** nelle impostazioni del portale
3. Reinstallare/aggiornare i plugin se necessario

### Dove posso trovare i registri degli errori?

I registri Light Portal sono nei registri standard di SMF. È inoltre possibile abilitare la modalità di debug nelle impostazioni del portale.

---

## Sviluppo

### Come posso creare il mio plugin?

Guarda [Aggiungi Plugin](./plugins/create-new.md).

### Dove posso trovare la documentazione per gli Hook?

Guarda [Hook Portale](./plugins/all-hooks.md).

---

## Serve aiuto?

Se non hai trovato la risposta alla tua domanda:

1. Cerca nel [forum di supporto](https://www.simplemachines.org/community/index.php?topic=572393.0)
2. Crea un nuovo messaggio che descrive il tuo problema
3. Allega screenshot e registri degli errori

Oppure usa la sezione dei commenti direttamente in questa pagina.
