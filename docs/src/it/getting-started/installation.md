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
- PHP 8.2 o superiore
- Estensione PHP `intl` per localizzare correttamente alcune stringhe di lingua
- Estensioni PHP `dom` e `simplexml` per esportare/importare pagine e blocchi
- Estensione PHP `zip` per esportare/importare plugin
- MySQL 5.7+ / MariaDB 10.5+ / PostgreSQL 12+

:::info Nota

È sufficiente scaricare il pacchetto con i file del portale dal [catalogo ufficiale](https://custom.simplemachines.org/mods/index.php?mod=4244) e caricarlo tramite il gestore pacchetti sul tuo forum.

:::

## Testing

Puoi provare il [file Docker](https://github.com/dragomano/Light-Portal/tree/d1074c8486ed9eb2f9e89e3afebce2b914d4d570/_docker) o se preferisci le applicazioni LAMP/WAMP/MAMP.
