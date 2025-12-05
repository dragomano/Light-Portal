---
description: Eine kurze Zusammenfassung der verfügbaren Portaleinstellungen
order: 3
outline: [ 2, 3 ]
---

# Portaleinstellungen

Benutzen Sie den Schnellzugriff über den Eintrag im Hauptmenü des Forums oder den entsprechenden Abschnitt im Administrationsbereich, um die Portaleinstellungen zu öffnen.

Wir werden nicht im Detail jedes der verfügbaren Einstellungen beschreiben, wir werden nur die wichtigsten nennen.

## Allgemeine Einstellungen

In this section, you can fully customize the portal front page, enable standalone mode, and change user permissions to access portal items.

### Settings for the front page and articles

To change the content of the portal home page, select the appropriate "the portal front page" mode:

- Deaktiviert
- Spezifische Seite (nur die ausgewählte Seite wird angezeigt)
- Alle Seiten aus ausgewählten Kategorien
- Ausgewählte Seiten
- Alle Themen aus ausgewählten Boards
- Ausgewählte Themen
- Ausgewählte Boards

### Autonomer Modus

This is a mode where you can specify your own home page, and remove unnecessary items from the main menu (user list, calendar, etc.). Siehe zum Beispiel `portal.php` im Forum root .

### Berechtigungen

Aquí simplemente nota que la OMS puede y que puede hacer con los diversos elementos (bloques y páginas) del portal.

## Seiten und Blöcke

In diesem Abschnitt können Sie die allgemeinen Einstellungen von Seiten und Blöcken ändern, die verwendet werden, wenn diese erzeugt oder angezeigt werden.

## Paneles

In diesem Abschnitt können Sie einige der Einstellungen für existierende Portal-Felder ändern und die Richtung der Blöcke in diesen Feldern an Ihre Bedürfnisse anpassen.

![Panels](panels.png)

## Sonstiges

Ihn diesem Abschnitt können Sie diverse zusätzliche Einstellungen des Portals anpassen, die bei der Entwicklung von Vorlagen und Plugins nützlich sein können.

### Kompatibilitätsmodus

- Der Wert des **action**-Parameters des Portals – Sie können diese Einstellung ändern, um Light Portal zusammen mit anderen, ähnlichen Modifikationen zu nutzen. Die Hauptseite ist dann unter der angegebenen Adresse erreichbar.
- Der **page**-Parameter für Portalseiten – siehe oben. Similarly, for portal pages - change the parameter and they will open with different URLs.

### Wartung

- Wöchentliche Optimierung der Portalseiten – aktivieren Sie diese Option, damit einmal wöchentlich leere Zeilen aus den Datenbanktabellen des Portals entfernt und die Tabellen optimiert werden.
