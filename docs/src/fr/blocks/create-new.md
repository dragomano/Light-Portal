---
description: Brève description de l'interface de création de plugin
order: 2
---

# Ajouter un plugin

Pour ajouter un bloc, il suffit de cliquer dessus. Au départ, vous pouvez créer des blocs de trois types : PHP, HTML et BBCode. Si vous avez besoin d'autres, activez d'abord [les plugins nécessaires](../plugins/manage) de type `block`.

Selon le type de bloc, divers paramètres seront disponibles, répartis entre différents onglets.

## Block types

### Built-in content types

- **BBC**: Allows BBCode markup for content
- **HTML**: Raw HTML content
- **PHP**: Executable PHP code (admin only)

### Plugin-based blocks

Blocks from plugins extend functionality. Examples:

- **Markdown**: Enables Markdown syntax for content
- **ArticleList**: Displays articles from topics/pages with customizable display options
- **Calculator**: Interactive calculator widget
- **BoardStats**: Forum board statistics
- **News**: Latest announcements
- **Polls**: Active forum polls
- **RecentPosts**: Recent forum activity
- **UserInfo**: Current user details
- **WhosOnline**: Online users list

## Content tab

Ici, vous pouvez configurer :

- titre
- note
- contenu (pour certains blocs seulement)

![Content tab](content_tab.png)

## Onglet d'accès et de placement

Ici, vous pouvez configurer :

- placement
- Permissions
- Zones

![Access tab](access_tab.png)

## Onglet Apparence

Here you can configure the block appearance options.

![Appearance tab](appearance_tab.png)

## Tuning tab

Les tuners spécifiques aux blocs sont généralement disponibles sur l'onglet **Réglage**.

![Tuning tab](tuning_tab.png)

Les plugins peuvent ajouter leurs propres personnalisations à chacune de ces sections, selon les intentions des développeurs.
