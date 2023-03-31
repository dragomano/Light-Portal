---
sidebar_position: 3
---

# Portal innstillinger
Bruk hurtigtilgang gjennom elementet i hovedforummenyen eller tilsvarende del i admin-panelet for å åpne portalinnstillinger.

## Generelle innstillinger
I denne delen kan du fullt ut tilpasse portalens forsideside, aktiver hvilemodus, og endre brukerrettigheter til å bruke portalelementer.

### Innstillinger for forsiden og artikler

* Portalen forside — velg hva du skal vise på hovedsiden i portalen:
    * Deaktivert
    * Spesifisert side (bare den valgte siden vil bli viset)
    * Alle sider fra valgte kategorier
    * Valgte sider
    * Alle emner fra valgte tavler
    * Valgte emner
    * Valgte tavler
* Forsidetittel — du kan endre navnet på portalen som brukes som sidetittel og tittelen på nettleserfanen.
* Kategorier - kilder til artikler for forsiden — lar deg velge portalkategorier for disse forsideinnstillingene: "Alle sider fra valgte kategorier".
* Tavler - kilder til artikler for frontsiden — lar deg velge tavler til disse frontside-modusene: "Alle emner fra valgte tavler", og "Valgt tavle".
* Vis bilder som finnes i artikler — sjekk om det skal vises bilder i siders tekst eller emner.
* URL-adressen til standard plassholderbilde — hvis alternativet ovenfor er aktivert. men bildet finnes ikke i teksten, den er angitt her og vil bli brukt.
* Vis artikkelsammendrag
* Vis forfatteren av artikkelen
* Vis antall visninger og kommentarer
* Først kan du vise artikler med høyest antall kommentarer - du kan vise de mest kommenterte artiklene først, uavhengig av den valgte sorteringstypen.
* Sorterer artikler – du kan velge type sortering av artikler på forsiden.
* Oppsett av designmal for artikkelkort - opprette en egen fil _[CustomtPage.template.php](/how-to/create-layout)_
* Antall kolonner for artikler - angir antall kolonner som artikkelkort skal vises i
* Vis paginering - spesifiser hvor sidens paginasjon skal vises.
* Bruk enkel paginering — visning av koblinger for "neste side" og "forrige side" istedenfor full navigering.
* Antall elementer per side (for side) - angi maksimalt antall kort som skal vises på en side.

### Frittstående modus

* Aktiver – Frittstående modus skifter, vis eller skjul følgende innstillinger.
* Forsiden URL-adressen i hviletilstand — angi URL-adressen der hovedsiden i portalen skal være tilgjengelig.
* Deaktiverte handlinger – du kan angi områder på forumet som ikke bør vises i frittstående modus.

### Tillatelser

* Send alle unntatt administratorer fra å lage PHP-sider og PHP-blokker.
* Hvem kan se portalelementene – etter "elementer" mener vi blokker og sider.
* Hvem kan administrere egne blokker - du kan velge brukergrupper som kan opprette, redigere og slette blokker, bare synlig for dem.
* Hvem kan administrere egne sider - kan du velge brukergrupper som kan opprette, redigere og slette sider.
* Hvem kan poste portalen sider uten godkjenning - kan du velge brukergrupper som vil kunne legge inn portalsider uten moderasjon.

## Sider og blokker
I denne delen kan du endre de generelle innstillingene til sider og blokker som brukes både når du oppretter dem, og når du viser dem.

* Vis nøkkelord på toppen av siden — hvis nøkkelord er angitt for en side, vil de vises øverst på siden
* Bruk et bilde fra innholdet på siden - velg et bilde for deling i sosiale nettverk
* Vis linker til forrige og neste side - aktiver hvis du vil se linker til sider som er opprettet før og etter gjeldende side.
* Vis relaterte sider — dersom en side har lignende sider (med tittel og alias), vil de bli vist nederst på siden.
* Vis sidekommentarer — hvis du har lov til å kommentere en side, vil det bli vist et kommentarskjema nederst på siden.
* Tillatt BBCode i kommentarer — du kan angi koder som kan brukes når du kommenterer sider.
* Maksimal tid etter å ha kommentert for å tillate redigering — etter den angitte tiden (etter å ha opprettet en kommentar), vil du ikke kunne endre kommentarer.
* Antall overordnede kommentarer per side — angi maksimalt antall ikke-barn-kommentarer som skal vises på en enkelt side.
* Sortering av kommentarer som standard - velg ønsket sorteringstype for kommentarer på portalsidene.
* Tillat stemmegivning for kommentarer - "Like" og "Like"-knapper vil dukke opp under hver kommentar. Bakgrunnen for merknadene vil endres avhengig av antall positive eller negative vurderinger.
* Vis elementer på etikett/kategorisider som oppgavelapper - du kan vise elementene som en tabell, eller som oppgavelapper.
* Type sideeditor som standard - hvis du hele tiden oppretter sider av samme type, kan du angi denne typen som standard.
* Det maksimale antall nøkkelord som kan legges til en side – når du oppretter portalsider, du vil ikke kunne angi antall søkeord større enn det angitte nummeret.
* Tillatelser for sider og blokker som standard - hvis du konstant lager sider og blokker med samme tillatelser, du kan stille inn disse rettighetene som standard.
* Skjul aktive blokker i adminområdet - hvis blokker plager deg i admin-panelet, kan du skjule dem.

### Ved å bruke FontAwesome ikoner
* Kilde til FontAwesome biblioteket - velg hvordan stilark skal lastes for å vise FA-ikonene.

## Kategorier
I denne delen kan du behandle kategorier for kategorisere portalsider. Hvis du trenger det, selvfølgelig.

## Paneler
I denne delen kan du endre noen av innstillingene for eksisterende portalpaneler og tilpasse retningen til blokkene i disse rutene.

![Paneler](panels.png)

Her kan du raskt omorganisere noen paneler uten å dra blokker fra det ene panelet til det andre:
* Bytt topptekst og bunntekst
* Bytt venstre panel og høyre panel
* Bytt sentrum (topp) og senteret (bunn)

## Diverse
I denne delen kan du endre forskjellige tilleggsinnstillinger i portalen, som kan være nyttig for utviklere av maler og utvidelser.

### Feilsøking og hurtigbufring

* Vis lastetid og antall portaler - nyttig informasjon for administratorer og pluginutviklere.
* Mellomlagerets oppdateringsintervall - etter et spesifisert tidsrom (i sekunder), fjernes cachen for hvert portalelement.

### Kompatibilitetsmodus
* Verdien i **handling** parameteret i portalen - du kan endre denne innstillingen til å bruke lysportalen i forbindelse med andre lignende endringer. Deretter vil hjemmesiden åpne på den angitte adressen.
* **side** parameteren for portalsider - se ovenfor. På samme måte blir det for portalsider - endre parameteret og vil åpne med forskjellige URls.

### Vedlikehold
* Ukentlig optimalisering av portaltabeller - aktiver dette valget slik at en gang i uken blir radene med tomme verdier i portaltabellene i databasen slettet og tabellene optimalisert.