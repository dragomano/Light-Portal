---
description: Kratek povzetek razpoložljivih nastavitev portala
order: 3
outline:
  - 2
  - 3
---

# Nastavitve portala

Uporabi hiter dostop preko elementa v glavnem meniju foruma ali ustreznega razdelka v administratorskem panelu za odpiranje nastavitev portala.

Vsake razpoložljive nastavitve ne bomo podrobno opisali, omenili bomo samo najpomembnejše.

## Splošne nastavitve

V tem razdelku lahko popolnoma prilagodiš začetno stran portala, omogočiš samostojni način in spremeniš dovoljenja uporabnikov za dostop do elementov portala.

### Nastavitve za začetno stran in članke

Za spremembo vsebine začetne strani portala izberi ustrezen način "začetna stran portala":

- Onemogočeno
- Določena stran (prikazana bo samo izbrana stran)
- Vse strani iz izbranih kategorij
- Izbrane strani
- Vse teme iz izbranih desk
- Izbrane teme
- Izbrane deske

### Samostojni način

To je način, kjer lahko določiš svojo lastno začetno stran in odstraniš nepotrebne elemente iz glavnega menija (seznam uporabnikov, koledar itd.). Poglej portal.php v korenski mapi foruma za primer.

### Dovoljenja

Tukaj preprosto opredeliš, KDO lahko in KAJ lahko počne z različnimi elementi (bloki in stranmi) portala.

## Strani in bloki

V tem razdelku lahko spremeniš splošne nastavitve strani in blokov, ki se uporabljajo tako pri njihovem ustvarjanju kot tudi pri njihovem prikazu.

## Plošče

V tem razdelku lahko spremeniš nekatere nastavitve obstoječih portalnih plošč in prilagodiš smer blokov v teh ploščah.

![Panels](panels.png)

## Razno

V tem delu lahko spremeniš različne dodatne nastavitve portala, ki so lahko koristne za razvijalce predlog in vtičnikov.

### Združljivostni način

- Vrednost parametra **dejanje** portala - to nastavitev lahko spremeniš, da uporabiš Light Portal v kombinaciji z drugimi podobnimi modifikacijami. Potem se bo začetna stran odprla na določenem naslovu.
- Parameter \*\*stran \*\* za strani portala - glej zgoraj. Podobno za strani portala - spremeni parameter in odpirale se bodo z različnimi URL-ji.

### Vzdrževanje

- Tedenska optimizacija portalnih tabel - omogoči to možnost, da se enkrat na teden izbrišejo vrstice s praznimi vrednostmi v tabelah portala v podatkovni bazi in da se tabele optimizirajo.
