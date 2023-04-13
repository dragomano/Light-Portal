---
sidebar_position: 3
---

# Portal indstillinger
Brug hurtig adgang gennem elementet i forummenuen eller den tilsvarende sektion i administrationspanelet til at åbne portalens indstillinger.

## Generelle indstillinger
I dette afsnit kan du fuldt ud tilpasse portalens forside, aktivere standalone tilstand og ændre brugertilladelser for at få adgang til portalelementer.

### Indstillinger for forsiden og artikler

* Portalens forside - vælg hvad der skal vises på portalens forside:
    * Deaktiveret
    * Angivne side (kun den valgte side vil blive vist)
    * Alle sider fra valgte kategorier
    * Valgte sider
    * Alle emner fra valgte tavler
    * Valgte emner
    * Valgte tavler
* Forsiden titel - du kan ændre navnet på portalen, der bruges som sidetitel og titlen på browseren fanen.
* Vis billeder, der findes i artikler - tjek om billeder fundet i teksten på sider eller emner.
* URL for standardpladsholder-billedet — hvis indstillingen ovenfor er aktiveret, men billedet er ikke fundet i teksten, den der er angivet her vil blive brugt.
* Vis artikeloversigt
* Vis artikelforfatteren
* Vis antallet af visninger og kommentarer
* Først vises artikler med det højeste antal kommentarer - du kan først vise de mest kommenterede artikler uanset den valgte sorteringstype.
* Sortering af artikler - du kan vælge sortering af artikler på forsiden.
* Skabelon layout for artikelkort - for at tilføje dine egne skabeloner oprette en separat fil _[CustomFrontPage.template.php](/how-to/create-layout)_.
* Antal kolonner til visning af artikler — angiv antallet af kolonner, i hvilke artikelkort vil blive vist.
* Vis sideinddelingen — angiv hvor sideinddelingen skal vises.
* Brug simpel paginering - vise "næste side" og "forrige side" links i stedet for fuld navigation.
* Antal elementer pr. side (til side) — angiv det maksimale antal kort, der skal vises på en side.

### Standalone tilstand

* Aktiver — Standalone mode switcher, skærme eller skjuler følgende indstillinger.
* Webadressen på forsiden i standalone tilstand — angiv URL'en, hvor portalens hovedside vil være tilgængelig.
* Deaktiverede handlinger - du kan angive områder af forummet, der ikke bør vises i standalone tilstand.

### Rettigheder

* Forvis alle undtagen administratorer fra at oprette PHP sider og PHP blokke.
* Hvem kan se portalelementerne — ved "elementer" mener vi blokke og sider.
* Hvem kan administrere egne blokke - du kan vælge brugergrupper, der kan oprette, redigere og slette blokke, kun synlige for dem.
* Who can manage own pages — you can choose user groups who can create, edit and delete own pages.
* Who can manage any pages — you can choose user groups who can create, edit and delete any pages.
* Hvem kan sende portalsider uden godkendelse - du kan vælge brugergrupper, der vil være i stand til at sende portalsider uden moderation.

## Sider og blokke
I dette afsnit kan du ændre de generelle indstillinger for sider og blokke, der bruges både ved oprettelse af dem og ved visning af dem.

* Vis søgeord øverst på siden - hvis søgeord er angivet for en side, vil de vises øverst på siden
* Brug et billede fra sidens indhold - vælg et billede til deling på sociale netværk
* Vis links til de forrige og næste sider — aktivér hvis du ønsker at se links til sider oprettet før og efter den aktuelle side.
* Vis relaterede sider — hvis en side har lignende sider (efter titel og alias), vil de blive vist nederst på siden.
* Vis sidekommentarer — hvis du har lov til at kommentere en side, vil en kommentarformular blive vist nederst på siden.
* Tilladt BBCode i kommentarer - du kan angive tags, der må bruges, når du kommenterer sider.
* Maksimal tid efter kommentering for at tillade redigering — efter den angivne tid (efter oprettelse af en kommentar), vil du ikke kunne ændre kommentarer.
* Antal forældrekommentarer pr. side — angiv det maksimale antal ikke-børn kommentarer der skal vises på en enkelt side.
* Sortering af kommentarer som standard - vælg den ønskede sortering type for kommentarer på portal sider.
* Tillad at stemme på kommentarer — "Like" og "Dislike" knapper vises under hver kommentar. Baggrunden for kommentarer vil ændre sig afhængigt af antallet af positive eller negative vurderinger.
* Vis elementer på tag-/kategorisider som kort - du kan vise elementer som et bord, eller som kort.
* Typen af sideeditor som standard - hvis du konstant opretter sider af samme type, kan du indstille denne type som standard.
* Det maksimale antal søgeord, der kan tilføjes til en side — når du opretter portalsider, du vil ikke være i stand til at angive antallet af nøgleord større end det angivne tal.
* Tilladelser til sider og blokke som standard — hvis du konstant opretter sider og blokke med de samme tilladelser, du kan indstille disse tilladelser som standard.
* Skjul aktive blokke i admin-området - hvis blokke generer dig i admin-panelet, kan du skjule dem.

### Brug af FontAwesome ikoner
* Kilde til biblioteket FontAwesome - vælg hvordan stilarket skal indlæses for at vise FA ikonerne.

## Kategorier
I dette afsnit kan du administrere kategorier for kategorisering af portalsider. Hvis du har brug for det, naturligvis.

## Paneler
I dette afsnit kan du ændre nogle af indstillingerne for eksisterende portalpaneler og tilpasse retningen af blokke i disse paneler.

![Paneler](panels.png)

Her kan du hurtigt omarrangere nogle paneler uden at trække blokke fra et panel til et andet.
* Ombyt headeren og sidefoden
* Byt det venstre panel og det højre panel
* Byt midten (øverst) og midten (nederst)

## Diverse
I dette afsnit kan du ændre forskellige hjælpeindstillinger for portalen, som kan være nyttige for udviklere af skabeloner og plugins.

### Fejlfinding og caching

* Vis indlæsningstiden og antallet af portalforespørgsler — nyttig information for administratorer og plugin skabere.
* Opdateringsintervallet for cache - efter et angivet tidsforbrug (i sekunder), vil cachen for hvert portal element blive ryddet.

### Forenelighed tilstand
* Værdien af **handlingen** parameteren for portalen - du kan ændre denne indstilling for at bruge Lysportalen i forbindelse med andre lignende ændringer. Så vil startsiden åbne på den angivne adresse.
* Parameteren **side** for portalsider - se ovenfor. Tilsvarende for portalsider - ændre parameteren og de vil åbne med forskellige URL.

### Vedligeholdelse
* Ugentlig optimering af portaltabeller - aktiver denne indstilling, så en gang om ugen vil rækkerne med tomme værdier i portaltabellerne i databasen blive slettet og tabellerne vil blive optimeret.