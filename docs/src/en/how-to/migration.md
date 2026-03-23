---
description: Guide to migrating to Light Portal from other portals
---

# Migrating to Light Portal

Moving to a new portal is an important step. This guide will help you migrate content from other SMF portals to Light Portal.

## Preparation

### Backup

Before starting migration, make a full backup:

- Forum database
- Forum files (`Themes`, `Sources`)

:::warning Warning

Never start migration on a live forum without first testing on a local server or test site.

:::

### Audit your current content

Make a list of what needs to be migrated:

- Blocks
- Pages
- Categories

:::info Note

Only blocks and pages with PHP/HTML/BBCode content types are supported for import. Other block types will need to be created manually.

:::

### Removing previous portal

Leave the tables created by the previous portal in the database — they are needed for import.

## Migrating from TinyPortal

1. Install and activate the TinyPortalMigration plugin
2. Go to the desired section — **Blocks**, **Pages**, or **Categories**, then select **Import from TinyPortal**

## Migrating from EhPortal (SimplePortal)

1. Install and activate the EhPortalMigration plugin
2. Go to the desired section — **Blocks**, **Pages**, or **Categories**, then select **Import from EhPortal**

## Migrating from EzPortal

1. Install and activate the EzPortalMigration plugin
2. Go to the desired section — **Blocks** or **Pages**, then select **Import from EzPortal**

## Additional Help

If you encounter difficulties during migration:

1. Search for a solution in the [support forum](https://www.simplemachines.org/community/index.php?topic=572393.0)
2. Create a new post describing your problem
3. Attach screenshots and error logs

Or use the comment section right on this page.
