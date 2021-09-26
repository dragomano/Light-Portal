<?php

/**
 * Spanish translation by Rock Lee https://www.bombercode.net | Rev. 4.7
 *
 * @package Light Portal
 */

$txt['lp_portal'] = 'Portal';
$txt['lp_forum'] = 'Foro';

$txt['lp_new_version_is_available'] = '¡Una nueva version esta disponible!';

$txt['lp_article'] = 'Artículo';
$txt['lp_no_items'] = 'No hay elementos que mostrar.';
$txt['lp_example'] = 'Ejemplo: ';
$txt['lp_content'] = 'Contenido';
$txt['lp_my_blocks'] = 'Mis bloques';
$txt['lp_my_pages'] = 'Mis paginas';
$txt['lp_views'] = $txt['views'];
$txt['lp_replies'] = $txt['replies'];
$txt['lp_default'] = 'Por Defecto';
$txt['lp_can_donate'] = 'Para patrocinadores';
$txt['lp_can_download'] = 'Puede descargar';
$txt['lp_min_search_length'] = 'Ingrese al menos %d caracteres';

// Settings
$txt['lp_settings'] = 'Configuración del portal';
$txt['lp_base'] = 'Configuraciones para la portada y los artículos';
$txt['lp_base_info'] = 'La versión del mod: <strong>%1$s</strong>,Versión PHP: <strong>%2$s</strong>, %3$s versión: <strong>%4$s</strong>.<br>Se pueden discutir errores y características del portal en <a class="bbc_link" href="https://www.simplemachines.org/community/index.php?topic=572393.0">simplemachines.org</a>.<br>También puedes <a class="bbc_link" href="https://ko-fi.com/U7U41XD2G">comprar una taza de café como agradecimiento</a>.';
$txt['lp_debug_info'] = 'Información de depuración';

$txt['lp_frontpage_title'] = 'El título de la portada';
$txt['lp_frontpage_mode'] = 'La primera página del portal';
$txt['lp_frontpage_mode_set'] = array('Desactivar', 'Página especificada', 'Todas las páginas de las categorías seleccionadas', 'Paginas seleccionadas', 'Todos los temas de foros seleccionados', 'Temas seleccionados', 'Foros seleccionados');
$txt['lp_frontpage_alias'] = 'Página del portal para mostrar como página principal';
$txt['lp_frontpage_alias_subtext'] = 'Ingrese el alias de la página que existen.';
$txt['lp_frontpage_categories'] = 'Categorías: fuentes de artículos para la portada';
$txt['lp_select_categories_from_list'] = 'Seleccione las categorías deseadas';
$txt['lp_frontpage_boards'] = 'Foros como fuentes de artículos para la portada';
$txt['lp_select_boards_from_list'] = 'Seleccione los foros deseados';
$txt['lp_frontpage_pages'] = 'Páginas como fuentes de artículos para la portada';
$txt['lp_frontpage_pages_subtext'] = 'ID de las páginas requeridas, separados por comas.';
$txt['lp_frontpage_topics'] = 'Temas como fuentes de artículos para la portada';
$txt['lp_frontpage_topics_subtext'] = 'ID de los temas obligatorios, separados por comas.';
$txt['lp_show_images_in_articles'] = 'Mostrar las imágenes que se encuentran en los artículos';
$txt['lp_show_images_in_articles_help'] = 'Primero, verifica si el artículo tiene un archivo adjunto (si el artículo se basa en un tema del foro), luego, si el artículo tiene una etiqueta IMG con una imagen.';
$txt['lp_image_placeholder'] = 'URL de la imagen del marcador de posición por defecto';
$txt['lp_frontpage_time_format'] = 'Formato de hora en las fichas de artículos';
$txt['lp_frontpage_time_format_set'] = array('Completo (estilo LP)', 'Como en el foro', 'Formato propio');
$txt['lp_frontpage_custom_time_format'] = 'Formato de tiempo propio';
$txt['lp_frontpage_custom_time_format_help'] = 'Consulte la lista de posibles parámetros en la <a class="bbc_link" href="https://www.php.net/manual/en/datetime.format.php">documentación</a>.';
$txt['lp_show_teaser'] = 'Mostrar el resumen del artículo';
$txt['lp_show_author'] = 'Mostrar el autor del artículo';
$txt['lp_show_author_help'] = 'Si se muestra la tarjeta del tablero, será información sobre la categoría.';
$txt['lp_show_num_views_and_comments'] = 'Muestra el número de vistas y comentarios.';
$txt['lp_frontpage_order_by_num_replies'] = 'Primero en mostrar los artículos con el mayor número de comentarios';
$txt['lp_frontpage_article_sorting'] = 'Clasificación de artículos';
$txt['lp_frontpage_article_sorting_set'] = array('Por el último comentario', 'Por la fecha de creación (nuevo primero)', 'Por la fecha de creación (primero el antiguo)', 'Por la fecha de actualización (fresco primero)');
$txt['lp_frontpage_article_sorting_help'] = 'Cuando selecciona la primera opción, las tarjetas de artículos muestran las fechas y los últimos comentaristas (si están disponibles).';
$txt['lp_frontpage_layout'] = 'Diseño de plantilla para tarjetas de artículos';
$txt['lp_frontpage_num_columns'] = 'Número de columnas para mostrar artículos.';
$txt['lp_frontpage_num_columns_set'] = array('1 columna', '2 columnas', '3 columnas', '4 columnas', '6 columnas');
$txt['lp_show_pagination'] = 'Mostrar la paginación';
$txt['lp_show_pagination_set'] = array('Solo abajo', 'Arriba y abajo', 'Sólo arriba');
$txt['lp_use_simple_pagination'] = 'Usar paginación simple';
$txt['lp_num_items_per_page'] = 'Número de elementos por página (para paginación)';

$txt['lp_standalone_mode_title'] = 'Modo independiente';
$txt['lp_standalone_url'] = 'La URL de la página principal en el modo independiente';
$txt['lp_standalone_url_help'] = 'Puede especificar su propia URL para mostrar como portada del portal (por ejemplo, <strong>https://miforo/portal.php</strong>).<br>En este caso, la portada del foro permanecerá disponible en <strong>https://miforo/index.php</strong>.<br><br>As an example, the <em>portal.php</em> file is included with the portal — you can use it.<br><br>Deshabilite la opción "<strong>Activar el almacenamiento local de cookies.</strong>" if you want to place <em>portal.php</em> outside the forum directory (Mantenimiento => Configuración del servidor => Cookies y sesiones).';
$txt['lp_standalone_mode_disabled_actions'] = 'Acciones desactivadas';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Especifique las áreas que deben DESACTIVARSE en el modo independiente.';
$txt['lp_standalone_mode_disabled_actions_help'] = 'Por ejemplo, si necesita desactivar el área de búsqueda (index.php?action=<strong>search</strong>), Agregar <strong>búsqueda</strong> en el campo de texto.';

$txt['groups_light_portal_view'] = '¿Quién puede ver los elementos del portal?';
$txt['groups_light_portal_manage_blocks'] = '¿Quién puede administrar los bloques?';
$txt['groups_light_portal_manage_own_pages'] = '¿Quién puede administrar sus propias páginas?';
$txt['groups_light_portal_approve_pages'] = 'Quién puede publicar las páginas del portal sin aprobación';
$txt['lp_manage_permissions'] = 'Algunas páginas pueden contener contenido HTML/PHP peligroso, por lo que no permita que todos puedan crearlas';

// Pages and blocks
$txt['lp_extra'] = 'Páginas y bloques';
$txt['lp_extra_info'] = 'Aquí puede encontrar configuraciones generales de páginas y bloques.';

$txt['lp_show_page_permissions'] = 'Mostrar información sobre los permisos de la página';
$txt['lp_show_page_permissions_subtext'] = 'Solo aquellos que tienen permiso para editar la página pueden verla.';
$txt['lp_show_tags_on_page'] = 'Mostrar palabras clave en la parte superior de la página';
$txt['lp_show_items_as_articles'] = 'Mostrar elementos en páginas de etiquetas/categorías como tarjetas';
$txt['lp_show_related_pages'] = 'Mostrar bloque de páginas relacionadas';
$txt['lp_show_comment_block'] = 'Mostrar bloque de comentarios';
$txt['lp_disabled_bbc_in_comments'] = 'BBC permitidos en los comentarios';
$txt['lp_disabled_bbc_in_comments_subtext'] = 'Puede utilizar cualquier etiqueta <a class="bbc_link" href="%1$s">permitida</a> en el foro.';
$txt['lp_show_comment_block_set'] = array('Ninguno', 'Integrado');
$txt['lp_time_to_change_comments'] = 'Tiempo máximo después de comentar para permitir la edición';
$txt['lp_num_comments_per_page'] = 'Número de comentarios de los foros por página';
$txt['lp_page_editor_type_default'] = 'El tipo de editor de página por defecto';
$txt['lp_permissions_default'] = 'Permisos para páginas y bloques por defecto';
$txt['lp_hide_blocks_in_admin_section'] = 'Ocultar bloques activos en el área de administración';

$txt['lp_schema_org'] = 'Marcado de microdatos de esquema para contactos';
$txt['lp_page_og_image'] = 'Usa una imagen del contenido de la página';
$txt['lp_page_og_image_set'] = array('ninguno', 'Primero encontrado', 'Último encontrado');
$txt['lp_page_itemprop_address'] = 'Dirección de su organización';
$txt['lp_page_itemprop_phone'] = 'Teléfono de su organización';

$txt['lp_permissions'] = array('Mostrar a los administradores', 'Mostrar a los invitados', 'Mostrar a los usuarios', 'Mostrar a todos');

// Categories
$txt['lp_categories'] = 'Categorías';
$txt['lp_categories_info'] = 'Aquí puede crear y editar las categorías del portal para categorizar páginas.<br>Simplemente arrastre una categoría a una nueva posición para cambiar el orden.';
$txt['lp_categories_manage'] = 'Administrar categorías';
$txt['lp_categories_add'] = 'Agregar categoría';
$txt['lp_categories_desc'] = 'Descripción';
$txt['lp_category'] = 'Categoría';
$txt['lp_no_category'] = 'Sin categorizar';
$txt['lp_all_categories'] = 'Todas las categorías del portal';
$txt['lp_all_pages_with_category'] = 'Todas las páginas de la categoría "%1$s"';
$txt['lp_all_pages_without_category'] = 'Todas las páginas sin categoría';
$txt['lp_category_not_found'] = 'No se encontró la categoría especificada.';
$txt['lp_no_categories'] = 'Aún no hay categorías.';
$txt['lp_total_pages_column'] = 'Total de páginas';

// Panels
$txt['lp_panels'] = 'Paneles';
$txt['lp_panels_info'] = 'Aquí se puede personalizar el ancho de algunos paneles, así como la dirección de los bloques.<br><strong>%1$s</strong> utiliza <a class="bbc_link" href="%2$s" target="_blank" rel="noopener">un sistema de cuadrícula de 12 columnas</a> to display blocks in 6 panels.';
$txt['lp_swap_header_footer'] = 'Cambia el encabezado y el pie de página';
$txt['lp_swap_left_right'] = 'Cambia el panel izquierdo y el panel derecho';
$txt['lp_swap_top_bottom'] = 'Cambie el centro (arriba) y el centro (abajo)';
$txt['lp_panel_layout_preview'] = 'Aquí puede establecer el número de columnas para algunos paneles, dependiendo del ancho de la ventana del navegador.';
$txt['lp_left_panel_sticky'] = $txt['lp_right_panel_sticky'] = 'Fijado';
$txt['lp_panel_direction_note'] = 'Aquí puede cambiar la dirección de los bloques para cada panel.';
$txt['lp_panel_direction'] = 'La dirección de los bloques en los paneles.';
$txt['lp_panel_direction_set'] = array('Vertical', 'Horizontal');

// Misc
$txt['lp_misc'] = 'Varios';
$txt['lp_misc_info'] = 'Hay configuraciones de portal adicionales que serán útiles para los desarrolladores de plantillas y complementos aquí.';
$txt['lp_debug_and_caching'] = 'Depuración y almacenamiento en caché';
$txt['lp_show_debug_info'] = 'Muestra el tiempo de carga y el número de consultas del portal.';
$txt['lp_show_debug_info_help'] = '¡Esta información solo estará disponible para administradores!';
$txt['lp_show_cache_info'] = 'Muestra la información sobre el portal que trabaja con el caché.';
$txt['lp_cache_update_interval'] = 'El intervalo de actualización del caché ';
$txt['lp_compatibility_mode'] = 'Modo de compatibilidad';
$txt['lp_portal_action'] = 'El valor del parámetro <strong>action</strong> del portal';
$txt['lp_page_param'] = 'El parámetro <strong>page</strong> para las páginas del portal';
$txt['lp_weekly_cleaning'] = 'Optimización semanal de las tablas del portal';
$txt['lp_cache_info'] = 'Portal - usando el caché';
$txt['lp_cache_saving'] = 'Guardando datos en la celda <strong>%1$s</strong> durante %2$d segundos.';
$txt['lp_cache_loading'] = 'Cargando datos de la celda <strong>%1$s</strong>';

// Actions
$txt['lp_title'] = 'Título';
$txt['lp_actions'] = 'Acciones';
$txt['lp_action_on'] = 'Activar';
$txt['lp_action_off'] = 'Desactivar';
$txt['lp_action_toggle'] = 'Cambiar estado';
$txt['lp_action_clone'] = 'Clonar';
$txt['lp_action_move'] = 'Mover';
$txt['lp_read_more'] = 'Leer más...';
$txt['lp_save_and_exit'] = 'Guardar y Salir';

// Blocks
$txt['lp_blocks'] = 'Bloques';
$txt['lp_blocks_manage'] = 'Administrar bloques';
$txt['lp_blocks_manage_description'] = 'Todos los bloques del portal creados se enumeran aquí. Para agregar un bloque, use el botón correspondiente.';
$txt['lp_blocks_add'] = 'Agregar bloque';
$txt['lp_blocks_add_title'] = 'Adición de bloque';
$txt['lp_blocks_add_description'] = 'Todavía no hay muchos bloques, pero existen los más universales ~ juega con ellos :)';
$txt['lp_blocks_add_instruction'] = 'Seleccione el bloque deseado haciendo clic en él.';
$txt['lp_blocks_edit_title'] = 'Edición de bloques';
$txt['lp_blocks_edit_description'] = $txt['lp_blocks_add_description'];
$txt['lp_block_type'] = 'Tipo de bloque';
$txt['lp_block_note'] = 'Nota';
$txt['lp_block_priority'] = 'Prioridad';
$txt['lp_block_placement'] = 'Colocación';
$txt['lp_block_placement_set'] = array('Encabezado', 'Centro (arriba)', 'Lado izquierdo', 'Lado derecho', 'Centro (abajo)', 'Pie de página');

$txt['lp_block_areas'] = 'Acciones';
$txt['lp_block_areas_subtext'] = 'Especifique una o más áreas (separadas por comas) para mostrar el bloque en:';
$txt['lp_block_areas_area_th'] = 'Area';
$txt['lp_block_areas_display_th'] = 'Display';
$txt['lp_block_areas_values'] = array(
	'en todas partes',
	'en el área <em>index.php?action</em>=<strong>custom_action</strong> (por ejemplo:  portal,forum,search)',
	'en todas las páginas del portal',
	'en la página <em>index.php?page</em>=<strong>alias</strong>',
	'en todos los foros',
	'solo dentro del tablero con identificador <strong>id</strong> (incluidos todos los temas dentro del foro)',
	'en foros id1, id2, id3',
	'en foros id3, y id7',
	'en todos los temas',
	'solo dentro del tema con identificador <strong>id</strong>',
	'en temas id1, id2, id3',
	'en temasid3, y id7'
);

$txt['lp_block_select_icon'] = 'Seleccionar icono';
$txt['lp_block_title_class'] = 'Clase de título CSS';
$txt['lp_block_title_style'] = 'Estilo de título CSS';
$txt['lp_block_content_class'] = 'Clase de contenido CSS';
$txt['lp_block_content_style'] = 'Estilo de contenido CSS';

// Internal blocks
$txt['lp_bbc']['title'] = 'BBC personalizado';
$txt['lp_html']['title'] = 'HTML personalizado';
$txt['lp_php']['title'] = 'PHP personalizado';
$txt['lp_bbc']['description'] = 'En este bloque, las etiquetas BBC del foro se pueden utilizar como contenido.';
$txt['lp_html']['description'] = 'En este bloque, se puede utilizar cualquier etiqueta HTML como contenido.';
$txt['lp_php']['description'] = 'En este bloque, puede usar cualquier código PHP como contenido.';

// Pages
$txt['lp_pages'] = 'Páginas';
$txt['lp_pages_manage'] = 'Administrar páginas';
$txt['lp_pages_manage_all_pages'] = 'Todas las páginas del portal creadas se enumeran aquí.';
$txt['lp_pages_manage_own_pages'] = 'Here you can view all your own portal pages.';
$txt['lp_pages_manage_description'] = 'Para agregar una nueva página, use el botón correspondiente.';
$txt['lp_pages_add'] = 'Añadir página';
$txt['lp_pages_add_title'] = 'Añadiendo página';
$txt['lp_pages_add_description'] = 'Rellene el título de la página y el alias. Después de eso, se puede cambiar el tipo, el uso de vista previa y guardar.';
$txt['lp_pages_edit_title'] = 'Página de edición';
$txt['lp_pages_edit_description'] = $txt['lp_pages_add_description'];
$txt['lp_pages_extra'] = 'Páginas del portal';
$txt['lp_pages_search'] = 'Alias o título';
$txt['lp_page_alias'] = 'Alias';
$txt['lp_page_alias_subtext'] = 'El alias de la página debe comenzar con una letra latina y consistir en letras minúsculas latinas, números y guiones bajos.';
$txt['lp_page_type'] = 'Tipo de página';
$txt['lp_page_types'] = array('BBC', 'HTML', 'PHP');
$txt['lp_page_description'] = 'Descripción';
$txt['lp_page_keywords'] = 'Palabras claves';
$txt['lp_page_keywords_placeholder'] = 'Seleccionar etiquetas o añadir nueva';
$txt['lp_page_publish_datetime'] = 'Fecha y hora de publicación';
$txt['lp_page_author'] = 'Transferencia de autoría';
$txt['lp_page_author_placeholder'] = 'Especifique un nombre de usuario para transferir derechos a la página';
$txt['lp_page_options'] = array('Mostrar el autor y la fecha de creación', 'Mostrar páginas relacionadas', 'Permitir comentarios', 'Elemento en el menú principal');

// Tabs
$txt['lp_tab_content'] = 'Contenido';
$txt['lp_tab_seo'] = 'SEO';
$txt['lp_tab_access_placement'] = 'El acceso y la colocación';
$txt['lp_tab_appearance'] = 'Apariencia';
$txt['lp_tab_menu'] = 'Menu';
$txt['lp_tab_tuning'] = 'Extras';

// Import and Export
$txt['lp_pages_export'] = 'Exportar página';
$txt['lp_pages_import'] = 'Importar página';
$txt['lp_pages_export_description'] = 'Aquí puede exportar las páginas que necesita para crear una copia de seguridad o importarlas a otro foro.';
$txt['lp_pages_import_description'] = 'Aquí puede importar páginas del portal guardadas previamente desde una copia de seguridad.';
$txt['lp_blocks_export'] = 'Exportar bloque';
$txt['lp_blocks_import'] = 'Importar bloque';
$txt['lp_blocks_export_description'] = 'Aquí puede exportar los bloques que necesita para crear una copia de seguridad o importarlos a otro foro.';
$txt['lp_blocks_import_description'] = 'Aquí puede importar bloques del portal guardados previamente desde una copia de seguridad.';
$txt['lp_export_run'] = 'Exportar selección';
$txt['lp_import_run'] = 'Ejecutar importación';
$txt['lp_export_all'] = 'Exportar todo';

// Plugins
$txt['lp_plugins'] = 'Complementos';
$txt['lp_plugins_manage'] = 'Administrar complementos';
$txt['lp_plugins_manage_description'] = 'Los plugins instalados portal se enumeran aquí. Siempre puedes crear uno nuevo usando <a class="bbc_link" href="%1$s" target="_blank" rel="noopener">las instrucciones</a>. or the "+" button below.';
$txt['lp_plugins_desc'] = 'Los complementos amplían las capacidades del portal y sus componentes, proporcionando características adicionales que no están disponibles en el núcleo.';
$txt['lp_plugins_types'] = array('Bloque', 'Editor', 'Widget de comentarios', 'Analizador de contenido', 'Procesamiento de artículos', 'El diseño de la página principal', 'Importar y exportar', 'Otros');
$txt['lp_plugins_requires'] = 'Complementos necesarios para trabajar';

// Tags
$txt['lp_all_page_tags'] = 'Todas las etiquetas de página del portal';
$txt['lp_all_tags_by_key'] = 'Todas las páginas con la etiqueta "%1$s"';
$txt['lp_tag_not_found'] = 'No se encontró la etiqueta especificada.';
$txt['lp_no_tags'] = 'No hay etiquetas todavía.';
$txt['lp_keyword_column'] = 'Palabra clave';
$txt['lp_frequency_column'] = 'Frecuencia';
$txt['lp_sorting_label'] = 'Ordenar por';
$txt['lp_sort_by_title_desc'] = 'Título (desc)';
$txt['lp_sort_by_title'] = 'Título (asc)';
$txt['lp_sort_by_created_desc'] = 'Fecha de creación (nuevo primero)';
$txt['lp_sort_by_created'] = 'Fecha de creación (antiguo primero)';
$txt['lp_sort_by_updated_desc'] = 'Fecha de actualización (nuevo primero)';
$txt['lp_sort_by_updated'] = 'Fecha de actualización (antiguo primero)';
$txt['lp_sort_by_author_desc'] = 'Nombre del autor (desc)';
$txt['lp_sort_by_author'] = 'Nombre del autor (asc)';
$txt['lp_sort_by_num_views_desc'] = 'Número de vistas (desc) ';
$txt['lp_sort_by_num_views'] = 'Número de vistas (asc)';

// Related pages
$txt['lp_related_pages'] = 'Páginas relacionadas';

// Comments
$txt['lp_comments'] = 'Comentarios';
$txt['lp_comment_placeholder'] = 'Deja un comentario...';

// Comment alerts
$txt['alert_page_comment'] = 'Cuando mi página recibe un comentario';
$txt['alert_new_comment_page_comment'] = '{member_link} dejó un comentario {page_comment_new_comment}';
$txt['alert_page_comment_reply'] = 'Cuando mi comentario recibe una respuesta';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} dejó una respuesta en tu comentario {page_comment_reply_new_reply}';

// Errors
$txt['lp_page_not_found'] = '¡Página no encontrada!';
$txt['lp_page_not_activated'] = '¡La página solicitada está desactivada!';
$txt['lp_page_not_editable'] = '¡No tienes permiso para editar esta página!';
$txt['lp_page_visible_but_disabled'] = '¡La página está visible para usted, pero no está activada! ';
$txt['lp_block_not_found'] = '¡Bloque no encontrado!';
$txt['lp_block_not_editable'] = '¡No tienes permiso para editar este bloque!';
$txt['lp_post_error_no_title'] = 'El campo <strong>título</strong> no se completó. Es requerido.';
$txt['lp_post_error_no_alias'] = 'El campo <strong>alias</strong> no se completó. Es requerido.';
$txt['lp_post_error_no_valid_alias'] = '¡El alias especificado no es correcto!';
$txt['lp_post_error_no_unique_alias'] = '¡Ya existe una página con este alias!';
$txt['lp_post_error_no_content'] = '¡El contenido no especificado! Es requerido.';
$txt['lp_post_error_no_areas'] = 'El campo <strong>areas</strong> no se completó. Es requerido.';
$txt['lp_post_error_no_valid_areas'] = '¡El campo de las <strong>zonas</strong> se configuró incorrectamente!';
$txt['lp_post_error_no_name'] = 'El campo <strong>nombre</strong> no se completó. Es requerido.';
$txt['lp_wrong_import_file'] = 'Archivo incorrecto para importar...';
$txt['lp_import_failed'] = 'Error al importar...';
$txt['lp_wrong_template'] = 'Plantilla incorrecta. Elija una plantilla que coincida con el contenido.';
$txt['lp_addon_not_installed'] = 'Complemento %1$s no instalado';
$txt['lp_addon_requires_ssi'] = '¡El complemento %1$s depende de SSI.php, que debe estar en la raíz del foro!';

// Who
$txt['lp_who_viewing_frontpage'] = 'Viendo <a href="%1$s">la página principal del portal</a>.';
$txt['lp_who_viewing_index'] = 'Viendo <a href="%1$s">la página principal del portal</a> o <a href="%2$s">el índice del foro</a>.';
$txt['lp_who_viewing_page'] = 'Viendo <a href="%1$s">la página del portal</a>.';
$txt['lp_who_viewing_tags'] = 'Viendo <a href="%1$s">las etiquetas de la página del portal</a>.';
$txt['lp_who_viewing_the_tag'] = 'Viendo la lista de páginas con la etiqueta <a href="%1$s" class="bbc_link">%2$s</a>.';
$txt['lp_who_viewing_portal_settings'] = 'Viendo o cambiando <a href="%1$s">la configuración del portal</a>.';
$txt['lp_who_viewing_portal_blocks'] = 'Viendo <a href="%1$s">the portal blocks</a> in the admin area.';
$txt['lp_who_viewing_editing_block'] = 'Editando el bloque del portal (#%1$d).';
$txt['lp_who_viewing_adding_block'] = 'Agregando un bloque para el portal.';
$txt['lp_who_viewing_portal_pages'] = 'Viendo <a href="%1$s">las páginas del portal</a> en el área de administración.';
$txt['lp_who_viewing_editing_page'] = 'Editando la página del portal (#%1$d).';
$txt['lp_who_viewing_adding_page'] = 'Agregando una página para el portal.';

// Permissions
$txt['permissionname_light_portal_view'] = $txt['group_perms_name_light_portal_view'] = 'Ver los elementos del portal';
$txt['permissionname_light_portal_manage_blocks'] = $txt['group_perms_name_light_portal_manage_blocks'] = 'Administrar bloques';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Administra tus propias páginas';
$txt['permissionname_light_portal_approve_pages'] = $txt['group_perms_name_light_portal_approve_pages'] = 'Publica páginas sin aprobación';
$txt['permissionhelp_light_portal_view'] = 'Capacidad para ver páginas y bloques del portal.';
$txt['permissionhelp_light_portal_manage_blocks'] = 'Acceso para gestionar bloques del portal.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Acceso para gestionar páginas propias.';
$txt['permissionhelp_light_portal_approve_pages'] = 'Posibilidad de publicar páginas del portal sin aprobación.';
$txt['cannot_light_portal_view'] = 'Lo sentimos, ¡no tienes permiso para ver el portal!';
$txt['cannot_light_portal_manage_own_blocks'] = 'Lo sentimos, ¡no tienes permiso para administrar bloques!';
$txt['cannot_light_portal_manage_own_pages'] = 'Lo sentimos, ¡no tienes permiso para administrar páginas!';
$txt['cannot_light_portal_approve_pages'] = 'Lo sentimos, ¡no se le permite publicar páginas sin aprobación!';
$txt['cannot_light_portal_view_page'] = 'Lo sentimos, ¡no puedes ver esta página!';

// Time units (see https://github.com/dragomano/Light-Portal/wiki/To-translators)
$txt['lp_days_set'] = 'día, días';
$txt['lp_hours_set'] = 'una hora, horas';
$txt['lp_minutes_set'] = 'un minuto, minutos';
$txt['lp_seconds_set'] = 'segundo, segundos';
$txt['lp_tomorrow'] = '<strong>mañana</strong> a las ';
$txt['lp_just_now'] = 'Justo ahora';
$txt['lp_time_label_in'] = 'en %1$s';
$txt['lp_time_label_ago'] = ' hace';

// Social units
$txt['lp_posts_set'] = 'mensaje, mensajes';
$txt['lp_replies_set'] = 'respuesta, respuestas';
$txt['lp_views_set'] = 'vista, vistas';
$txt['lp_comments_set'] = 'comentario, comentarios';
$txt['lp_articles_set'] = 'articulo, articulos';

// Other units
$txt['lp_users_set'] = 'usuario, usuarios';
$txt['lp_guests_set'] = 'invitado, invitados';
$txt['lp_spiders_set'] = 'araña, arañas';
$txt['lp_hidden_set'] = 'oculto, ocultos';
$txt['lp_buddies_set'] = 'amigo, amigos';

// Packages
$txt['lp_addon_package'] = 'Complementos Light Portal';
$txt['install_lp_addon'] = 'Instalar complemento';
$txt['uninstall_lp_addon'] = 'Desinstalar complemento';

// Credits
$txt['lp_contributors'] = 'Contribución al desarrollo del portal';
$txt['lp_translators'] = 'Traductores';
$txt['lp_testers'] = 'Testers';
$txt['lp_sponsors'] = 'Patrocinadores';
$txt['lp_used_components'] = 'Los componentes del portal.';

// Debug info
$txt['lp_load_page_stats'] = 'Cargado en %1$.3f segundos. Consultas a la DB: %2$d.';
