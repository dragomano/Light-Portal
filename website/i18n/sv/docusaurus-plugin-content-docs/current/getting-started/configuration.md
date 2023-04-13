---
sidebar_position: 3
---

# Portalen inställningar
Använd snabbåtkomst genom objektet i huvudforummenyn eller motsvarande avsnitt i administratörspanelen för att öppna portalinställningarna.

## Allmänna inställningar
I det här avsnittet kan du anpassa portalens framsida, aktivera fristående läge och ändra användarbehörigheter för att komma åt portalobjekt.

### Inställningar för startsidan och artiklar

* Portalen framsida — välj vad som ska visas på huvudsidan i portalen:
    * Inaktiverad
    * Angiven sida (endast den valda sidan kommer att visas)
    * Alla sidor från valda kategorier
    * Valda sidor
    * Alla ämnen från valda tavlor
    * Valda ämnen
    * Valda tavlor
* Huvudsidans titel — du kan ändra namnet på portalen som används som sidtitel och titeln på webbläsarfliken.
* Visa bilder som finns i artiklar — kontrollera om du vill visa bilder som finns i texten på sidor eller ämnen.
* URL för standard platshållare bild — om alternativet ovan är aktiverat, men bilden finns inte i texten, den som anges här kommer att användas.
* Visa sammanfattningen av artikeln
* Visa artikelns författare
* Visa antalet visningar och kommentarer
* Först att visa artiklar med det högsta antalet kommentarer — du kan visa de mest kommenterade artiklarna först, oavsett vilken sorteringstyp.
* Sortering av artiklar – du kan välja vilken typ av sortering av artiklar som finns på startsidan.
* Mall layout för artikelkort — för att lägga till egna mallar skapa en separat fil _[CustomFrontPage.template.php](/how-to/create-layout)_.
* Antal kolumner för att visa artiklar — ange antalet kolumner där artikelkorten kommer att visas.
* Visa sidnumrering — ange var sidnumreringen ska visas.
* Använd enkel sidnumrering – visa "nästa sida" och "föregående sida" länkar istället för fullständig navigering.
* Antal objekt per sida (för sidnumrering) — ange det maximala antalet kort som ska visas på en sida.

### Fristående läge

* Aktivera — Fristående läge växlare, visar eller döljer följande inställningar.
* Den frontpage URL i fristående läge — ange den URL där huvudsidan för portalen kommer att vara tillgänglig.
* Inaktiverade åtgärder — du kan ange områden i forumet som inte ska visas i fristående läge.

### Behörigheter

* Förbjud alla utom administratörer att skapa PHP-sidor och PHP-block.
* Vem kan se portalelementen – med "element" menar vi block och sidor.
* Vem kan hantera egna block – du kan välja användargrupper som kan skapa, redigera och ta bort block, endast synliga för dem.
* Who can manage own pages — you can choose user groups who can create, edit and delete own pages.
* Who can manage any pages — you can choose user groups who can create, edit and delete any pages.
* Vem kan posta portalsidorna utan godkännande — du kan välja användargrupper som kommer att kunna posta portalsidor utan moderation.

## Sidor och block
I det här avsnittet kan du ändra de allmänna inställningarna för sidor och block som används både när du skapar dem och när du visar dem.

* Visa nyckelord högst upp på sidan — om nyckelord anges för en sida, kommer de att visas högst upp på sidan
* Använd en bild från sidans innehåll — välj en bild för delning i sociala nätverk
* Visa länkar till föregående och nästa sidor — aktivera om du vill se länkar till sidor som skapats före och efter den aktuella sidan.
* Visa relaterade sidor — om en sida har liknande sidor (av titel och alias), kommer de att visas längst ner på sidan.
* Visa sidkommentarer — om du har tillåtelse att kommentera en sida, visas ett kommentarsformulär längst ner på sidan.
* Tillåten BBCode i kommentarer — du kan ange taggar som är tillåtna att användas när du kommenterar sidor.
* Maximal tid efter att du kommenterat för att tillåta redigering — efter den angivna tiden (efter att du skapat en kommentar), kommer du inte att kunna ändra kommentarer.
* Antal överordnade kommentarer per sida — ange maximalt antal icke-underordnade kommentarer att visa på en sida.
* Sortering kommentarer som standard — välj önskad sorteringstyp för kommentarer på portalsidor.
* Tillåt röstning för kommentarer – "Gilla" och "Ogilla" knappar visas under varje kommentar. Bakgrunden till kommentarer kommer att förändras beroende på antalet positiva eller negativa betyg.
* Visa objekt på taggar/kategorisidor som kort — du kan visa objekt som tabell, eller som kort.
* Den typ av sidredigerare som standard — om du ständigt skapar sidor av samma typ, kan du ställa in denna typ som standard.
* Det maximala antalet sökord som kan läggas till en sida — när du skapar portalsidor, du kommer inte att kunna ange antalet sökord större än det angivna antalet.
* Behörigheter för sidor och block som standard — om du ständigt skapar sidor och block med samma behörigheter, du kan ställa in dessa behörigheter som standard.
* Dölj aktiva block i admin-området — om block stör dig i admin-panelen, kan du dölja dem.

### Använda FontAwesome ikoner
* Källa för FontAwesome bibliotek — välj hur stilmallen ska laddas för att visa FA-ikonerna.

## Kategorier
I det här avsnittet kan du hantera kategorier för kategorisering av portalsidor. Om du behöver det, naturligtvis.

## Paneler
I det här avsnittet kan du ändra några av inställningarna för befintliga portalpaneler och anpassa riktningen av block i dessa paneler.

![Paneler](panels.png)

Här kan du snabbt ordna om några paneler utan att dra block från en panel till en annan:
* Byt sidhuvud och sidfot
* Byt ut vänster panel och höger panel
* Byt centrum (överst) och centrum (underst)

## Diverse
I detta avsnitt kan du ändra olika extra inställningar för portalen, vilket kan vara användbart för utvecklare av mallar och plugins.

### Felsökning och caching

* Visa laddningstiden och antalet portalfrågor — användbar information för administratörer och plugin-skapare.
* cache uppdateringsintervall - efter en viss tid (i sekunder) kommer cachen för varje portalobjekt att rensas.

### Kompatibilitetsläge
* Värdet av **-åtgärden** i portalen - du kan ändra den här inställningen för att använda Light Portal i samband med andra liknande ändringar. Därefter öppnas startsidan på angiven adress.
* Parametern **page** för portalsidor - se ovan. På samma sätt, för portalsidor - ändra parametern och de kommer att öppna med olika URL-adresser.

### Underhåll
* Veckans optimering av portaltabeller - aktivera detta alternativ så att raderna med tomma värden i portaltabellerna i databasen raderas och tabellerna optimeras.