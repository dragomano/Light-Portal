---
description: A brief summary of available portal settings
order: 3
outline: [2, 3]
---

# Portal settings

Use the quick access through the item in the main forum menu or the corresponding section in the admin panel to open the portal settings.

We will not describe in detail each of the available settings, we will only mention the most important ones.

## General settings

In this section, you can fully customize the portal frontpage, enable standalone mode, and change user permissions to access portal items.

### Settings for the frontpage and articles

To change the content of the portal home page, select the appropriate "the portal frontpage" mode:

- Disabled
- Specified page (only the selected page will be displayed)
- All pages from selected categories
- Selected pages
- All topics from selected boards
- Selected topics
- Selected boards

### Standalone mode

This is a mode where you can specify your own home page (even if it is on another site), and remove unnecessary items from the main menu (user list, calendar, etc.). See `portal.php` in the forum root for example.

### Permissions

Here you simply note WHO can and WHAT can do with the various elements (blocks and pages) of the portal.

## Pages and blocks

In this section, you can change the general settings of pages and blocks used both when creating them and when displaying them.

## Panels

In this section, you can change some of the settings for existing portal panels and customize the direction of blocks in these panels.

![Panels](panels.png)

## Miscellaneous

In this section, you can change various auxiliary settings of the portal, which may be useful for developers of templates and plugins.

### Compatibility mode

- The value of the **action** parameter of the portal - you can change this setting to use the Light Portal in conjunction with other similar modifications. Then the home page will open at the specified address.
- The **page** parameter for portal pages - see above. Similarly, for portal pages - change the parameter and they will open with different URls.

### Maintenance

- Weekly optimization of portal tables - enable this option so that once a week the rows with empty values in the portal tables in the database will be deleted and the tables will be optimized.
