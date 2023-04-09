---
sidebar_position: 2
---

# Updating your version
If there are no notes in the changelog of the latest version, it is enough to extract the directories `Themes` and` Sources` from the modification archive to the root of your forum, over the existing ones, and the update will be correct. But it's best to uninstall the current version before installing the new version. At the same time, when deleting the modification package, **DO NOT** check the suggested box if you want to save all the blocks and pages you created in the database. After that, install the new version and continue working.

![Uninstalling](uninstall.png)

## Troubleshooting

### Warning: Undefined array key "bla-bla-bla"
If you saw a similar error in the portal block after updating, try going to the settings of this block and resave settings again. This is not an error, but a warning about missing (new) block settings in the database.