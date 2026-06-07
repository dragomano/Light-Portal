---
description: Guida alla migrazione a Light Portal da altri portali
---

# Migrazione a Light Portal

Passare a un nuovo portale è un passo importante. Questa guida ti aiuterà a migrare i contenuti da altri portali SMF a Light Portal.

## Preparazione

### Backup

Prima di iniziare la migrazione, effettuare un backup completo:

- Database del Forum
- File del Forum (`Themes`, `Sources`)

:::warning Attenzione

Non avviare mai la migrazione su un forum live senza prima testare su un server o su un sito di test locale.

:::

### Verifica il tuo contenuto attuale

Fai un elenco di ciò che deve essere migrato:

- Blocchi
- Pagine
- Categorie

:::info Note

Solo i blocchi e le pagine con PHP/HTML/BBCode sono supportati per l'importazione. Altri tipi di blocchi dovranno essere creati manualmente.

:::

### Rimozione del portale precedente

Lasciare le tabelle create dal portale precedente nel database: sono necessarie per l'importazione.

## Migrazione da TinyPortal

1. Installare e attivare il plugin TinyPortalMigration
2. Vai alla sezione desiderata — **Blocchi**, **Pagine**, o **Categorie**, quindi seleziona **Importa da TinyPortal**

## Migrazione da EhPortal (SimplePortal)

1. Installare e attivare il plugin EhPortalMigration
2. Vai alla sezione desiderata — **Blocchi**, **Pagine**, o **Categorie**, quindi seleziona **Importa da EhPortal**

## Migrazione da EzPortal

1. Installare e attivare il plugin EzPortalMigration
2. Vai alla sezione desiderata — **Blocchi**, **Pagine**, o **Categorie**, quindi seleziona **Importa da EzPortal**

## Aiuto aggiuntivo

Se si incontrano difficoltà durante la migrazione:

1. Cerca una soluzione nel [forum di supporto](https://www.simplemachines.org/community/index.php?topic=572393.0)
2. Crea un nuovo messaggio che descrive il tuo problema
3. Allega screenshot e registri degli errori

Oppure usa la sezione dei commenti direttamente in questa pagina.
