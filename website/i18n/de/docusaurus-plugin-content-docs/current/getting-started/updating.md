---
sidebar_position: 2
---

# Version aktualisieren
Falls im Änderungsprotokoll der neuesten Version keine weiteren Hinweise enthalten sind, reicht es aus, die Verzeichnisse `Themes` und `Sources` aus dem Modifikations-Archiv in das Wurzelverzeichnis Ihres Forums zu entpacken, dabei existierende Dateien zu überschreiben, und das Update ist erledigt. Allerdings ist es am besten, die aktuelle Version zu deinstallieren bevor Sie die neue Version installieren.

:::info

Seit Version 2.2.1 können Sie upgraden ohne die vorherige Version zu deinstallieren. Laden Sie einfach das neue Archiv herunter, rufen Sie die Paketverwaltung auf und klicken Sie auf den „Upgrade“-Button neben dem hochgeladenen Paket.

![Aktualisieren](upgrade.png)

:::

## Problembehebung

### Warning: Undefined array key "bla-bla-bla"
Falls Sie nach dem Update einen vergleichbaren Fehler im Portalblock sehen, versuchen Sie die Einstellungen des Blocks aufzurufen und Sie erneut zu speichern. Dies ist kein Fehler, sondern eine Warnung über fehlende (neue) Blockeinstellungen in der Datenbank.