---
sidebar_position: 1
---

# Instalace
Nejsou zde žádné předměty. Lehký portál může být nainstalován jako jakákoli jiná úprava SMF - prostřednictvím správce balíčků.

:::info

Stačí stáhnout archiv s portálními soubory (v SMF se to nazývá balíček) z [oficiálního katalogu](https://custom.simplemachines.org/mods/index.php?mod=4244) a nahrát přes správce balíčků do vašeho fóra.

:::

## Řešení problémů
Pokud je váš hosting příliš "smart" s oprávněním a portálové soubory nebyly během instalace rozbaleny, musíte ručně rozbalit adresáře `šablony` a `zdroje` z archivu úprav do složky fóra (kde jsou již umístěny stejné šablony a zdrojové složky, stejně jako soubory `cron. hp`, `SSI.php`, `Settings.php`, atd.) a nastavit příslušná oprávnění. Nejčastěji je to `644`, `664` nebo `666` pro soubory, a `755`, `775` nebo `777` pro složky.

Také je třeba rozbalit soubor `databáze. hp` od archivu modifikací do kořene vašeho fóra, nastavit prováděcí práva pro něj (`666`) a přístup k němu prostřednictvím prohlížeče (musíte být přihlášeni jako správce fóra). Tento soubor obsahuje pokyny pro vytvoření tabulek používaných portálem. Zpráva `Database changes are complete! Please wait...` potvrdí úspěšné provedení skriptu.

Pokud po dokončení všech výše uvedených kroků stále nevidíte sekci s nastavením portálu v admin panelu, zkontrolujte pro řádek `$sourcedir/LightPortal/app. hp` (proměnná `integrate_pre_include`) v tabulce `<your_prefix>nastavení` vaší databáze. Chcete-li to provést, použijte vestavěné vyhledávání phpMyAdmin nebo jiný podobný nástroj.
