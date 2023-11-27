---
description: List of all available portal settings, with a brief description
order: 3
---

# Portal settings

Use the quick access through the item in the main forum menu or the corresponding section in the admin panel to open the portal settings.

## General settings

In this section, you can fully customize the portal frontpage, enable standalone mode, and change user permissions to access portal items.

### Settings for the frontpage and articles

- The portal frontpage — choose what to display on the main page of the portal:
  - Disabled
  - Specified page (only the selected page will be displayed)
  - All pages from selected categories
  - Selected pages
  - All topics from selected boards
  - Selected topics
  - Selected boards
- The frontpage title — you can change the name of the portal used as the page title and the title of the browser tab.
- Show images found in articles — check whether to display images found in the text of pages or topics.
- URL of the default placeholder image — if the option above is enabled, but no image is found in the text, the one specified here will be used.
- Show the article summary
- Show the article author
- Show the number of views and comments
- First to display articles with the highest number of comments — you can display the most commented articles first, regardless of the selected sorting type.
- Sorting articles — you can choose the type of sorting of articles on the frontpage.
- Template layout for article cards — you can add _[your own template](../how-to/create-layout)_, if you want.
- Number of columns for displaying articles — specify the number of columns in which the article cards will be displayed.
- Show the pagination — specify where the pagination should be displayed.
- Use simple pagination — displaying "next page" and "previous page" links instead of full navigation.
- Number of items per page (for pagination) — specify the maximum number of cards to display on one page.

### Standalone mode

- Enable — Standalone mode switcher, displays or hides the following settings.
- The frontpage URL in the standalone mode — specify the URL where the main page of the portal will be available.
- Disabled actions — you can specify areas of the forum that should not be displayed in the standalone mode.

### Permissions

- Who can view the portal elements — blocks and pages.
- Who can manage own pages — you can choose user groups who can create, edit and delete own pages.
- Who can manage any pages — you can choose user groups who can create, edit and delete any pages.
- Who can post the portal pages without approval — you can choose user groups who will be able to post portal pages without moderation.

## Pages and blocks

In this section, you can change the general settings of pages and blocks used both when creating them and when displaying them.

- Show keywords at the top of the page — if keywords are specified for a page, they will appear at the top of the page
- Use an image from the page content — select an image for a sharing in social networks
- Show links to the previous and next pages — enable if you want to see links to pages created before and after the current page.
- Show related pages — if a page has similar pages (by title and alias), they will be displayed at the bottom of the page.
- Show page comments — if you are allowed to comment a page, a comment form will be displayed at the bottom of the page.
- Maximum time after commenting to allow edit — after the specified time (after creating a comment), you will not be able to change comments.
- Number of parent comments per page — specify the maximum number of non-children comments to display on a single page.
- Sorting comments by default — select the desired sorting type for comments on portal pages.
- Show items on tag/category pages as cards — you can display items as a table, or as cards.
- The maximum number of keywords that can be added to a page — when creating portal pages, you will not be able to specify the number of keywords greater than the specified number.
- Permissions for pages and blocks by default — if you constantly create pages and blocks with the same permissions, you can set these permissions as default.
- Hide active blocks in the admin area — if blocks bother you in the admin panel, you can hide them.

### Using the FontAwesome icons

- Source for the FontAwesome library — select how the stylesheet should be loaded to display the FA icons.

## Categories

In this section, you can manage categories for categorizing portal pages. If you need it, of course.

## Panels

In this section, you can change some of the settings for existing portal panels and customize the direction of blocks in these panels.

![Panels](panels.png)

Here you can quickly rearrange some panels without dragging blocks from one panel to another:

- Swap the header and the footer
- Swap the left panel and the right panel
- Swap the center (top) and the center (bottom)

## Miscellaneous

In this section, you can change various auxiliary settings of the portal, which may be useful for developers of templates and plugins.

### Debugging and caching

- Show the loading time and number of the portal queries — useful information for administrators and plugin creators.
- The cache update interval - after a specified amount of time (in seconds), the cache of each portal item will be cleared.

### Compatibility mode

- The value of the **action** parameter of the portal - you can change this setting to use the Light Portal in conjunction with other similar modifications. Then the home page will open at the specified address.
- The **page** parameter for portal pages - see above. Similarly, for portal pages - change the parameter and they will open with different URls.

### Maintenance

- Weekly optimization of portal tables - enable this option so that once a week the rows with empty values in the portal tables in the database will be deleted and the tables will be optimized.
