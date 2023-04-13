---
sidebar_position: 2
---

# Actualizando tu versión
Si no hay notas en el registro de cambios de la última versión, basta con extraer los directorios `Themes` y `Sources` del archivo de modificaciones a la raíz de su foro, sobre el existentes, y la actualización será correcta. Pero es mejor desinstalar la versión actual antes de instalar la nueva versión.

:::info

Since version 2.1.1 you can upgrade without uninstalling the previous version. Simply download the new archive, go to the Package Manager and click "Upgrade" button next to the uploaded package.

![Updating](upgrade.png)

:::

## Solución de problemas

### Warning: Undefined array key "bla-bla-bla"
Si vio un error similar en el bloque del portal después de la actualización, intente ir a la configuración de este bloque y vuelva a guardar la configuración. Esto no es un error, sino una advertencia sobre la falta de configuraciones de bloque (nuevas) en la base de datos.
