<?php

/**
 * .spanish latin language file Rev. 4.3
 * Spanish translation by Rock Lee https://www.bombercode.net
 *
 * @package Light Portal
 * @author Bugo https://dragomano.ru/mods/light-portal
 */

$txt['lp_portal'] = 'Portal';
$txt['lp_forum'] = 'Foro';

$txt['lp_new_version_is_available'] = '¡Una nueva version esta disponible!';

$txt['lp_article'] = 'Artículo';
$txt['lp_no_items'] = 'No hay elementos que mostrar.';

// Settings
$txt['lp_settings'] = 'Configuración del portal';
$txt['lp_base'] = 'Configuraciones para la portada y los artículos';
$txt['lp_base_info'] = 'La versión del mod: <strong>%1$s</strong>,Versión PHP: <strong>%2$s</strong>, %3$s versión: <strong>%4$s</strong>.';

$txt['lp_frontpage_title'] = 'El título de la portada';
$txt['lp_frontpage_mode'] = 'La primera página del portal';
$txt['lp_frontpage_mode_set'] = array('Desactivar', 'Página especificada', 'Todos los temas de foros seleccionados', 'Todas las páginas activas', 'Foros seleccionados	');
$txt['lp_frontpage_id'] = 'Página del portal para mostrar como página principal';
$txt['lp_frontpage_boards'] = 'Foros como fuentes de artículos para la portada';
$txt['lp_frontpage_layout'] = 'Número de columnas para mostrar artículos.';
$txt['lp_frontpage_layout_set'] = array('1 columna', '2 columnas', '3 columnas', '4 columnas', '6 columnas');
$txt['lp_show_images_in_articles'] = 'Mostrar las imágenes que se encuentran en los artículos';
$txt['lp_image_placeholder'] = 'URL de la imagen del marcador de posición por defecto';
$txt['lp_subject_size'] = 'El tamaño máximo de los títulos de los artículos (en símbolos)';
$txt['lp_teaser_size'] = 'El tamaño máximo de los avances de artículos (en símbolos)';
$txt['lp_num_items_per_page'] = 'Número de elementos por página (para paginación)';

$txt['lp_standalone_mode'] = $txt['lp_standalone_mode_title'] = 'Modo independiente';
$txt['lp_standalone_url']      = 'La URL de la página principal en el modo independiente';
$txt['lp_standalone_url_help'] = 'Puede especificar su propia URL para mostrar como portada del portal (por ejemplo, <strong>https://miforo/portal.php</strong>).<br>En este caso, la portada del foro permanecerá disponible en <strong>https://miforo/index.php</strong>.<br><br>Pegue este código en el <em>portal.php</em> archivo:<br><pre><code class="bbc_code">
require(dirname(__FILE__) . \'/SSI.php\');
<br>
Bugo\LightPortal\FrontPage::show();
<br>
obExit(true);</code></pre><br>
Deshabilite la opción "<strong>Activar el almacenamiento local de cookies.</strong>" si el archivo <em>portal.php</em> se encuentra fuera del directorio del foro (Mantenimiento => Configuración del servidor => Cookies y sesiones).';
$txt['lp_standalone_mode_disabled_actions'] = 'Acciones desactivadas';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Especifique las áreas que deben DESACTIVARSE en el modo independiente.';

$txt['groups_light_portal_view'] = '¿Quién puede ver los elementos del portal?';
$txt['groups_light_portal_manage_blocks'] = '¿Quién puede administrar los bloques?';
$txt['groups_light_portal_manage_own_pages'] = '¿Quién puede administrar sus propias páginas?';
$txt['lp_manage_permissions'] = 'Nota: algunas páginas y bloques pueden contener contenido HTML/PHP peligroso, ¡así que no otorgue este derecho a todos!';

$txt['lp_debug_and_caching'] = 'Depuración y almacenamiento en caché';
$txt['lp_show_debug_info'] = 'Muestra el tiempo de carga y el número de consultas del portal.';
$txt['lp_show_debug_info_subtext'] = '¡Esta información estará disponible solo para administradores!';
$txt['lp_cache_update_interval'] = 'El intervalo de actualización del caché';

$txt['lp_extra'] = 'Páginas y bloques';
$txt['lp_extra_info'] = 'Aquí puede encontrar configuraciones generales de páginas y bloques.';

$txt['lp_show_tags_on_page'] = 'Mostrar palabras clave en la parte superior de la página';
$txt['lp_show_comment_block'] = 'Mostrar bloque de comentarios';
$txt['lp_show_comment_block_set'] = array('ninguno' => 'Ninguno', 'por defecto' => 'Integrado');
$txt['lp_num_comments_per_page'] = 'Número de comentarios de los padres por página'; // Check meaning
$txt['lp_page_editor_type_default'] = 'El tipo de editor de la página por defecto';
$txt['lp_hide_blocks_in_admin_section'] = 'Ocultar bloques activos en el área de administración';
$txt['lp_open_graph'] = 'Open Graph';
$txt['lp_page_og_image'] = 'Usa una imagen del contenido de la página';
$txt['lp_page_og_image_set'] = array('ninguno', 'Primero encontrado', 'Último encontrado');
$txt['lp_page_itemprop_address'] = 'Dirección de su organización';
$txt['lp_page_itemprop_phone'] = 'Teléfono de su organización';

// Plugins
$txt['lp_plugins'] = 'Plugins';
$txt['lp_plugins_desc'] = 'Puede activar o desactivar cualquiera de los plugins. ¡Y algunos de ellos también están personalizados!';
$txt['lp_plugins_info'] = 'Los plugins instalados portal se enumeran aquí. Siempre puedes crear uno nuevo usando <a href="%1$s" target="_blank" rel="noopener">las instrucciones</a>.';

$txt['lp_plugins_hooks_types'] = array(
	'block' => 'Bloque',
	'editor' => 'Editor',
	'comment' => 'Widget de comentarios',
	'parser' => 'Analizador de contenido',
	'article' => 'Procesamiento de artículos',
	'other' => 'Otro'
);

// Actions
$txt['lp_title'] = 'Título';
$txt['lp_actions'] = 'Acciones'; // Review later
$txt['lp_action_on'] = 'Activar';
$txt['lp_action_off'] = 'Desactivar';
$txt['lp_action_clone'] = 'Clonar';
$txt['lp_action_move'] = 'Mover';
$txt['lp_read_more'] = 'Leer más...';

// Blocks
$txt['lp_blocks'] = 'Bloques';
$txt['lp_blocks_manage'] = 'Administrar bloques';
$txt['lp_blocks_manage_tab_description'] = 'Todos los bloques del portal creados se enumeran aquí. Para agregar un bloque, use el botón correspondiente.';
$txt['lp_blocks_add'] = 'Agregar bloque';
$txt['lp_blocks_add_title'] = 'Adding block'; // Review later
$txt['lp_blocks_add_tab_description'] = 'Todavía no hay muchos bloques, pero existen los más universales ~ juega con ellos :)';
$txt['lp_blocks_add_instruction'] = 'Seleccione el bloque deseado haciendo clic en él.';
$txt['lp_blocks_edit_title'] = 'Edición de bloques';
$txt['lp_blocks_edit_tab_description'] = $txt['lp_blocks_add_tab_description'];
$txt['lp_block_content'] = 'Contenido';
$txt['lp_block_icon_cheatsheet'] = 'Lista de los iconos';
$txt['lp_block_type'] = 'Tipo de bloque';
$txt['lp_block_priority'] = 'Prioridad';
$txt['lp_block_icon_type'] = 'Tipo de icono';
$txt['lp_block_icon_type_set'] = array('fas' => 'Sólido', 'lejos' => 'Regular', 'fabuloso' => 'Marcas'); // Review later
$txt['lp_block_placement'] = 'Colocación';
$txt['lp_block_placement_set'] = array(
	'header' => 'Encabezado',
	'top' => 'Centro (arriba)',
	'left' => 'Lado izquierdo',
	'right' => 'Lado derecho',
	'bottom' => 'Centro (abajo)',
	'footer' => 'Pie de página'
);

$txt['lp_block_areas'] = 'Acciones';
$txt['lp_block_areas_subtext'] = 'Especifique una o más áreas (separadas por comas) para mostrar el bloque en:<br>
<ul class="bbc_list">
	<li><strong>all</strong> — mostrar en todas partes</li>
	<li><strong>forum</strong> — mostrar solo en el área del foro</li>
	<li><strong>portal</strong> — mostrar solo en el área del portal (incluidas las páginas)</li>
	<li><strong>custom_action</strong> — mostrar en el área <em>index.php?action</em>=<strong>custom_action</strong></li>
	<li><strong>page=alias</strong> — mostrar en la página <em>index.php?page</em>=<strong>alias</strong></li>
</ul>';
$txt['lp_block_title_class'] = 'Clase de título CSS';
$txt['lp_block_title_style'] = 'Estilo de título CSS';
$txt['lp_block_content_class'] = 'Clase de contenido CSS';
$txt['lp_block_content_style'] = 'Estilo de contenido CSS';

$txt['lp_block_types'] = array(
	'bbc' => 'BBC personalizado',
	'html' => 'HTML personalizado',
	'php' => 'PHP personalizado'
);
$txt['lp_block_types_descriptions'] = array(
	'bbc' => 'En este bloque, las etiquetas BBC del foro se pueden utilizar como contenido.',
	'html' => 'En este bloque, se puede utilizar cualquier etiqueta HTML como contenido.',
	'php' => 'En este bloque, puede usar cualquier código PHP como contenido.'
);

// Pages
$txt['lp_pages'] = 'Páginas';
$txt['lp_pages_manage'] = 'Administrar páginas';
$txt['lp_pages_manage_tab_description'] = 'Todas las páginas del portal creadas se enumeran aquí. Para agregar una nueva página, use el botón correspondiente.';
$txt['lp_pages_add'] = 'Añadir página';
$txt['lp_pages_add_title'] = 'Añadiendo página';
$txt['lp_pages_add_tab_description'] = 'Rellene el título de la página y el alias. Después de eso, se puede cambiar el tipo, el uso de vista previa y guardar.';
$txt['lp_pages_edit_title'] = 'Página de edición';
$txt['lp_pages_edit_tab_description'] = $txt['lp_pages_add_tab_description'];
$txt['lp_extra_pages'] = 'Páginas del portal';
$txt['lp_page_types'] = array('bbc' => 'BBC', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias'] = 'Alias';
$txt['lp_page_alias_subtext'] = 'El nombre de la página debe comenzar con una letra latina y consistir en letras minúsculas latinas, números y guiones bajos.';
$txt['lp_page_content'] = $txt['lp_block_content'];
$txt['lp_page_type'] = 'Tipo de página';
$txt['lp_page_description'] = 'Descripción';
$txt['lp_page_keywords'] = 'Palabras claves';
$txt['lp_page_keywords_after'] = 'Usa una coma para separar';
$txt['lp_permissions'] = array('Mostrar a los administradores', 'Mostrar a los invitados', 'Mostrar a los usuarios', 'Mostrar a todos');

$txt['lp_page_options'] = array(
	'show_author_and_date' => 'Mostrar el autor y la fecha de creación',
	'allow_comments' => 'Permitir comentarios'
);

// Import and Export
$txt['lp_pages_export'] = 'Exportar página';
$txt['lp_pages_import'] = 'Importar página';
$txt['lp_pages_export_tab_description'] = 'Aquí puede exportar las páginas que necesita para crear una copia de seguridad o importarlas a otro foro.';
$txt['lp_pages_import_tab_description'] = 'Aquí puede importar páginas del portal guardadas previamente desde una copia de seguridad.';
$txt['lp_blocks_export'] = 'Exportar bloque';
$txt['lp_blocks_import'] = 'Importar bloque';
$txt['lp_blocks_export_tab_description'] = 'Aquí puede exportar los bloques que necesita para crear una copia de seguridad o importarlos a otro foro.';
$txt['lp_blocks_import_tab_description'] = 'Aquí puede importar bloques del portal guardados previamente desde una copia de seguridad.';
$txt['lp_export_run'] = 'Exportar selección';
$txt['lp_import_run'] = 'Ejecutar importación';
$txt['lp_export_all'] = 'Exportar todo';

// Tags
$txt['lp_all_page_tags'] = 'Todas las etiquetas de página del portal';
$txt['lp_all_tags_by_key'] = 'Todas las páginas con la etiqueta "%1$s"';
$txt['lp_no_selected_tag'] = 'No se encontró la etiqueta especificada.';
$txt['lp_no_tags'] = 'No hay etiquetas todavía.';
$txt['lp_keyword_column'] = 'Palabra clave';
$txt['lp_frequency_column'] = 'Frecuencia';

// Comments
$txt['lp_comments'] = 'Comentarios';
$txt['lp_comment_placeholder'] = 'Deja un comentario...';

$txt['alert_group_light_portal'] = LP_NAME;
$txt['alert_page_comment'] = 'Cuando mi página recibe un comentario';
$txt['alert_new_comment_page_comment'] = '{member_link} dejó un comentario <a href="{comment_link}">{comment_title}</a>';
$txt['alert_page_comment_reply'] = 'Cuando mi comentario recibe una respuesta';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} dejó una respuesta en tu comentario <a href="{comment_link}">{comment_title}</a>';

// Errors
$txt['lp_page_not_found'] = '¡Página no encontrada!';
$txt['lp_page_not_activated'] = '¡La página solicitada está deshabilitada!';
$txt['lp_block_not_found'] = '¡Bloque no encontrado!';
$txt['lp_post_error_no_title'] = 'El campo <strong>título</strong> no se completó. Es requerido.';
$txt['lp_post_error_no_alias'] = 'El campo <strong>alias</strong> no se completó. Es requerido.';
$txt['lp_post_error_no_valid_alias'] = '¡El alias especificado no es correcto!';
$txt['lp_post_error_no_unique_alias'] = '¡Ya existe una página con este alias!';
$txt['lp_post_error_no_content'] = '¡El contenido no especificado! Es requerido.';
$txt['lp_post_error_no_areas'] = 'El campo <strong>areas</strong> no se completó. Es requerido.';
$txt['lp_page_not_editable'] = '¡No tienes permiso para editar esta página!';
$txt['lp_addon_not_installed'] = 'Plugin %1$s no instalado';

// Who
$txt['lp_who_viewing_frontpage'] = 'Viendo <a href="%1$s">la página principal del portal</a>.';
$txt['lp_who_viewing_page'] = 'Viendo <a href="%1$s">la página del portal</a>.';
$txt['lp_who_viewing_tags'] = 'Viendo <a href="%1$s">las etiquetas de la página del portal</a>.';
$txt['lp_who_viewing_the_tag'] = 'Viendo la lista de páginas con la etiqueta <a href="%1$s" class="bbc_link">%2$s</a>.';

// Permissions
$txt['permissiongroup_light_portal'] = LP_NAME;
$txt['permissionname_light_portal_view'] = $txt['group_perms_name_light_portal_view'] = 'Ver los elementos del portal';
$txt['permissionname_light_portal_manage_blocks'] = $txt['group_perms_name_light_portal_manage_blocks'] = 'Administrar bloques';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Administra tus propias páginas';
$txt['permissionhelp_light_portal_view'] = 'Capacidad para ver páginas y bloques del portal.';
$txt['permissionhelp_light_portal_manage_blocks'] = 'Acceso para gestionar bloques del portal.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Acceso para gestionar páginas propias.';
$txt['cannot_light_portal_view'] = 'Lo sentimos, ¡no tienes permiso para ver el portal!';
$txt['cannot_light_portal_manage_blocks'] = 'Lo sentimos, ¡no tienes permiso para administrar bloques!';
$txt['cannot_light_portal_manage_own_pages'] = 'Lo sentimos, ¡no tienes permiso para administrar páginas!';
$txt['cannot_light_portal_view_page'] = 'Lo sentimos, ¡no puedes ver esta página!';

// Time units
$txt['lp_days_set'] = array('día','días');
$txt['lp_hours_set'] = array('una hora','horas');
$txt['lp_minutes_set'] = array('un minuto','minutos');
$txt['lp_seconds_set'] = array('segundo','segundos');
$txt['lp_tomorrow'] = '<strong>mañana</strong> a las ';
$txt['lp_just_now'] = 'Justo ahora';
$txt['lp_time_label_in'] = 'en %1$s';
$txt['lp_time_label_ago'] = ' hace';

// Social units
$txt['lp_posts_set'] = array('mensaje', 'mensajes');
$txt['lp_replies_set'] = array('respuesta', 'respuestas');
$txt['lp_views_set'] = array('vista', 'vistas');
$txt['lp_comments_set'] = array('comentario', 'comentarios');

// Other units
$txt['lp_users_set'] = array('usuario', 'usuarios');
$txt['lp_guests_set'] = array('invitado', 'invitados');
$txt['lp_spiders_set'] = array('araña', 'arañas');
$txt['lp_hidden_set'] = array('oculto', 'oculto');
$txt['lp_buddies_set'] = array('amigo', 'amigos');

// Credits
$txt['lp_used_components'] = 'Los componentes del portal.';

// Debug info
$txt['lp_load_page_stats'] = 'Cargado en %1$.3f segundos. Consultas a la DB: %2$d.';
