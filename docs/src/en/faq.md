---
title: FAQ
description: Frequently Asked Questions about Light Portal
---

# Frequently Asked Questions

Here are answers to the most popular questions about Light Portal.

## General questions

### Which versions of SMF are supported?

See [Installation](./getting-started/installation.md).

### Where can I download Light Portal?

See [Installation](./getting-started/installation.md).

---

## Installation and Setup

### How do I install Light Portal?

See [Installation](./getting-started/installation.md).

### How do I make the portal the front page?

See [Portal Settings](./getting-started/configuration#settings-for-the-front-page-and-articles).

### Can I use Light Portal alongside another portal?

Yes, you can try combining two portals.

1. Install Light Portal without removing the previous portal
2. Go to **Settings** → **Miscellaneous** and change the `action`/`page` parameters to differ from the other portal

---

## Pages

### How do I create a new page?

See [Add Page](./pages/create-new.md).

### How do I configure SEO for pages?

See [SEO Tab](./pages/create-new#seo-tab).

### What are categories and tags?

See [Glossary](./glossary.md)

You can create categories in **Portal** → **Categories** and tags in **Portal** → **Tags**.

---

## Blocks

### How do I add a block?

See [Add Block](./blocks/create-new.md).

### How do I reorder blocks?

In the block management section, drag blocks to the desired order.

### Can I use JavaScript in blocks?

Yes, use an HTML-type block for this.

:::warning Warning

Be careful with external scripts — they can slow down page loading or create security vulnerabilities.

:::

---

## Plugins

### What are plugins?

Plugins extend Light Portal functionality. They can add new block types, integrate with other modifications, and provide additional features.

See [Manage Plugins](./plugins/manage.md) for details.

### How do I install an additional plugin?

See [Installing additional plugins](./plugins/manage#installing-additional-plugins).

---

## Design and Themes

### How do I change the portal appearance?

Light Portal uses the same theme as the rest of the forum. However, you can change the front page layout.

1. **CSS**: Create a file `portal_custom.css` in the `Themes/default/css` folder
2. **Layouts**: Create a custom front page layout in the `Themes/default/portal_layouts` folder

See [Create Custom Layouts](./how-to/create-layout.md) for details.

---

## Troubleshooting

### Page isn't displaying

Check:

1. Page status (enabled/disabled)
2. Correct URL (slug)
3. Visibility settings (Access and placement tab in page settings)

### Block isn't displaying

Check:

1. Whether the block is enabled
2. Which panel it is assigned to
3. Visibility settings (Access and placement tab in block settings)

### Errors after update

1. Clear the forum cache and browser cache
2. Enable weekly table optimization in the **Miscellaneous** tab in portal settings
3. Reinstall/update plugins if necessary

### Where can I find error logs?

Light Portal logs are in the standard SMF logs. You can also enable debug mode in the portal settings.

---

## Development

### How do I create my own plugin?

See [Add Plugin](./plugins/create-new.md).

### Where can I find documentation on hooks?

See [Portal Hooks](./plugins/all-hooks.md).

---

## Need Help?

If you didn't find the answer to your question:

1. Search the [support forum](https://www.simplemachines.org/community/index.php?topic=572393.0)
2. Create a new post describing your problem
3. Attach screenshots and error logs

Or use the comment section right on this page.
