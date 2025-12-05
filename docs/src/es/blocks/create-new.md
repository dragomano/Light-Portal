---
description: Breve descripción de la interfaz de creación de bloques
order: 2
---

# Agregar bloque

Para agregar un bloque, simplemente haz clic en él. Inicialmente, puedes crear bloques de tres tipos: PHP, HTML y BBCode. Si necesitas otros, primero [habilita los complementos necesarios](../plugins/manage) del tipo `block`.

Dependiendo del tipo de bloque, varias configuraciones estarán disponibles, diseminadas a través de diferentes pestañas.

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

Aquí puede configurar:

- título
- nota
- contenido (sólo para algunos bloques)

![Content tab](content_tab.png)

## Pestaña de acceso y colocación

Aquí puede configurar:

- colocación
- permisos
- áreas

![Access tab](access_tab.png)

## Pestaña Apariencia

Here you can configure the block appearance options.

![Appearance tab](appearance_tab.png)

## Tuning tab

Los sintonizadores específicos de bloques están normalmente disponibles en la pestaña **Ajuste**.

![Tuning tab](tuning_tab.png)

Los plugins pueden añadir sus propias personalizaciones a cualquiera de estas secciones, dependiendo de las intenciones de los desarrolladores.
