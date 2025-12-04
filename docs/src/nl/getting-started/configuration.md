---
description: Een korte samenvatting van beschikbare portalinstellingen
order: 3
outline: [ 2, 3 ]
---

# Portaal instellingen

Gebruik de snelle toegang via het item in het hoofdmenu van het forum of de bijbehorende sectie in het beheerpaneel om de portalinstellingen te openen.

We zullen niet in detail op elk van de beschikbare instellingen ingaan, we zullen alleen de belangrijkste noemen.

## Algemene instellingen

In this section, you can fully customize the portal front page, enable standalone mode, and change user permissions to access portal items.

### Settings for the front page and articles

To change the content of the portal home page, select the appropriate "the portal front page" mode:

- Uitgeschakeld
- Opgegeven pagina (alleen de geselecteerde pagina wordt weergegeven)
- Alle pagina's uit geselecteerde categorieën
- Geselecteerde pagina's
- Alle onderwerpen in geselecteerde boards
- Geselecteerde onderwerpen
- Geselecteerde boards

### Alleenstaande modus

This is a mode where you can specify your own home page, and remove unnecessary items from the main menu (user list, calendar, etc.). Zie `portal.php` in de forum hoofdmap voor bijvoorbeeld.

### Machtigingen

Hier ziet u gewoon dat WHO kan en de WAT kan doen met de verschillende elementen (blokken en pagina's) van de portal.

## Pagina's en blokken

In deze sectie kunt u de algemene instellingen van pagina's en blokken wijzigen die zowel worden gebruikt bij het maken als wanneer ze worden weergegeven.

## Panelen

In dit gedeelte kunt u enkele instellingen voor bestaande portalpanelen wijzigen en de richting van blokken in deze panelen aanpassen.

![Panels](panels.png)

## Diversen

In dit gedeelte kunt u verschillende hulpinstellingen van de portal, die nuttig kunnen zijn voor ontwikkelaars van templates en plugins, wijzigen.

### Compatibiliteitsmodus

- De waarde van de **actie** parameter van het portaal - u kunt deze instelling veranderen naar het Light Portal in combinatie met andere soortgelijke wijzigingen. Dan wordt de startpagina geopend op het opgegeven adres.
- De **pagina** parameter voor portalpagina's - zie hierboven. Similarly, for portal pages - change the parameter and they will open with different URLs.

### Onderhoud

- Wekelijkse optimalisatie van portaltabellen - schakel deze optie in zodat de rijen met lege waarden in de portaltabellen in de database worden verwijderd en de tabellen worden geoptimaliseerd.
