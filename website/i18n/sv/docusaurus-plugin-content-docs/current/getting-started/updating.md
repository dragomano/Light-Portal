---
sidebar_position: 2
---

# Uppdaterar din version
Om det inte finns några anteckningar i ändringsloggen av den senaste versionen, det räcker med att extrahera katalogerna `Teman` och`Källor` från ändringsarkivet till roten av ditt forum, över de befintliga, och uppdateringen kommer att vara korrekt. Men det är bäst att avinstallera den aktuella versionen innan du installerar den nya versionen.

:::info

Since version 2.1.1 you can upgrade without uninstalling the previous version. Simply download the new archive, go to the Package Manager and click "Upgrade" button next to the uploaded package.

![Updating](upgrade.png)

:::

## Felsökning

### Warning: Undefined array key "bla-bla-bla"
Om du såg ett liknande fel i portalblocket efter uppdatering, försök att gå till inställningarna för detta block och spara om inställningarna igen. Detta är inget fel, men en varning om att det saknas (nya) blockinställningar i databasen.
