---
sidebar_position: 3
---

# Configuración del portal
Use el acceso rápido a través del elemento en el menú principal del foro o la sección correspondiente en el panel de administración para abrir la configuración del portal.

## Configuración general
En esta sección, puede personalizar completamente la página principal del portal, habilitar el modo independiente y cambiar los permisos de usuario para acceder a los elementos del portal.

### Configuraciones para la portada y los artículos

* La portada del portal: elija qué mostrar en la página principal del portal:
    * Desactivado
    * Página especificada (solo se mostrará la página seleccionada)
    * Todas las páginas de las categorías seleccionadas
    * Páginas seleccionadas
    * Todos los temas de foros seleccionados
    * Temas seleccionados
    * Foros seleccionados
* El título de la página principal: puede cambiar el nombre del portal utilizado como título de la página y el título de la pestaña del navegador.
* Mostrar imágenes que se encuentran en artículos: compruebe si desea mostrar imágenes que se encuentran en el texto de páginas o temas.
* URL de la imagen de marcador de posición predeterminada: si la opción anterior está activada, pero la imagen no se encuentra en el texto, se utilizará la especificada aquí.
* Mostrar el resumen del artículo
* Mostrar el autor del artículo
* Muestra el número de vistas y comentarios.
* Primero para mostrar los artículos con la mayor cantidad de comentarios: puede mostrar los artículos más comentados primero, independientemente del tipo de clasificación seleccionado.
* Clasificación de artículos: puede elegir el tipo de clasificación de artículos en la página principal.
* Diseño de plantilla para tarjetas de artículos: para agregar sus propias plantillas, cree un archivo separado _[CustomFrontPage.template.php](/how-to/create-layout)_.
* Número de columnas para mostrar artículos: especifique el número de columnas en las que se mostrarán las fichas de artículos.
* Mostrar la paginación: especifique dónde se debe mostrar la paginación de la página.
* Use paginación simple: muestre enlaces de "página siguiente" y "página anterior" en lugar de navegación completa.
* Número de elementos por página (para paginación): especifique el número máximo de tarjetas que se mostrarán en una página.

### Modo independiente

* Activar — selector de modo independiente, muestra u oculta las siguientes configuraciones.
* La URL de la página principal en el modo independiente: especifique la URL donde estará disponible la página principal del portal.
* Acciones desactivadas: puede especificar áreas del foro que no deben mostrarse en el modo independiente.

### Permisos

* Prohíba a todos, excepto a los administradores, crear páginas PHP y bloques PHP.
* Quién puede ver los elementos del portal: por "elementos" nos referimos a bloques y páginas.
* Quién puede administrar sus propios bloques: puede elegir grupos de usuarios que pueden crear, editar y eliminar bloques, visibles solo para ellos.
* Who can manage own pages — you can choose user groups who can create, edit and delete own pages.
* Who can manage any pages — you can choose user groups who can create, edit and delete any pages.
* Quién puede publicar las páginas del portal sin aprobación: puede elegir grupos de usuarios que podrán publicar páginas del portal sin moderación.

## Páginas y bloques
En esta sección, puede cambiar la configuración general de las páginas y los bloques utilizados tanto al crearlos como al mostrarlos.

* Mostrar palabras clave en la parte superior de la página: si se especifican palabras clave para una página, aparecerán en la parte superior de la página.
* Use una imagen del contenido de la página: seleccione una imagen para compartir en las redes sociales
* Mostrar enlaces a las páginas anterior y siguiente: actívelo si desea ver enlaces a páginas creadas antes y después de la página actual.
* Mostrar páginas relacionadas — si una página tiene páginas similares (por título y alias), se mostrarán en la parte inferior de la página.
* Mostrar comentarios de la página — si tiene permitido comentar una página, se mostrará un formulario de comentarios en la parte inferior de la página.
* BBCode permitido en comentarios — puede especificar etiquetas que se pueden usar al comentar páginas.
* Tiempo máximo después de comentar para permitir la edición — después del tiempo especificado (después de crear un comentario), no podrá cambiar los comentarios.
* Número de comentarios principales por página — especifique el número máximo de comentarios que no son secundarios para mostrar en una sola página.
* Clasificación de comentarios de forma predeterminada — seleccione el tipo de clasificación deseado para los comentarios en las páginas del portal.
* Permitir votar por comentarios — los botones "Me gusta" y "No me gusta" aparecerán debajo de cada comentario. El fondo de los comentarios cambiará según la cantidad de calificaciones positivas o negativas.
* Muestre elementos en páginas de etiquetas/categorías como tarjetas — puede mostrar elementos como una tabla o como tarjetas.
* El tipo de editor de página predeterminado — si crea constantemente páginas del mismo tipo, puede establecer este tipo como predeterminado.
* El número máximo de palabras clave que se pueden agregar a una página — al crear páginas de portal, no podrá especificar el número de palabras clave mayor que el número especificado.
* Permisos para páginas y bloques de forma predeterminada — si constantemente crea páginas y bloques con los mismos permisos, puede configurar estos permisos como predeterminados.
* Oculte los bloques activos en el área de administración — si los bloques le molestan en el panel de administración, puede ocultarlos.

### Usar los íconos de FontAwesome
* Fuente para la biblioteca FontAwesome — seleccione cómo se debe cargar la hoja de estilo para mostrar los íconos FA.

## Categorías
En esta sección, puede administrar categorías para categorizar las páginas del portal. Si lo necesitas, por supuesto.

## Paneles
En esta sección, puede cambiar algunas de las configuraciones para los paneles del portal existentes y personalizar la dirección de los bloques en estos paneles.

![Paneles](panels.png)

Aquí puede reorganizar rápidamente algunos paneles sin arrastrar bloques de un panel a otro:
* Cambia el encabezado y el pie de página
* Cambia el panel izquierdo y el panel derecho
* Cambie el centro (arriba) y el centro (abajo)

## Varios
En esta sección, puede cambiar varias configuraciones auxiliares del portal, que pueden ser útiles para los desarrolladores de plantillas y complementos.

### Depuración y almacenamiento en caché

* Muestre el tiempo de carga y el número de consultas del portal — información útil para administradores y creadores de complementos.
* El intervalo de actualización de la memoria caché - después de un período de tiempo específico (en segundos), se borrará la memoria caché de cada elemento del portal.

### Modo de compatibilidad
* El valor del parámetro **action** del portal: puede cambiar esta configuración para usar el Portal de luz junto con otras modificaciones similares. Luego se abrirá la página de inicio en la dirección especificada.
* El parámetro **page** para las páginas del portal: consulte más arriba. De manera similar, para las páginas del portal, cambie el parámetro y se abrirán con diferentes URL.

### Mantenimiento
* Optimización semanal de las tablas del portal: active esta opción para que una vez a la semana se eliminen las filas con valores vacíos en las tablas del portal de la base de datos y se optimicen las tablas.