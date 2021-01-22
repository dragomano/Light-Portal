<?php

/**
 * Spanish translation by Rock Lee https://www.bombercode.net | Rev. 4.5
 *
 * @package Light Portal
 */

$txt['lp_portal'] = 'Portal';
$txt['lp_forum']  = 'Foro';

$txt['lp_new_version_is_available'] = '¡Una nueva version esta disponible!';

$txt['lp_article']  = 'Artículo';
$txt['lp_no_items'] = 'No hay elementos que mostrar.';
$txt['lp_example']  = 'Ejemplo: ';
$txt['lp_content']  = 'Contenido';
$txt['lp_my_pages'] = 'My pages';
$txt['lp_views']    = $txt['views'];
$txt['lp_replies']  = $txt['replies'];

// Settings
$txt['lp_settings']  = 'Configuración del portal';
$txt['lp_base']      = 'Configuraciones para la portada y los artículos';
$txt['lp_base_info'] = 'La versión del mod: <strong>%1$s</strong>,Versión PHP: <strong>%2$s</strong>, %3$s versión: <strong>%4$s</strong>.<br>One can discuss bugs and features of the portal at <a class="bbc_link" href="https://www.simplemachines.org/community/index.php?topic=572393.0">simplemachines.com</a>.<br>You can also <a class="bbc_link" href="https://www.patreon.com/bugo">become a sponsor on Patreon</a> or <a class="bbc_link" href="https://ko-fi.com/U7U41XD2G">buy a cup of coffee as a thank</a>.';

$txt['lp_frontpage_title']                   = 'El título de la portada';
$txt['lp_frontpage_mode']                    = 'La primera página del portal';
$txt['lp_frontpage_mode_set']                = array(
	'Desactivar',
	'chosen_page'   => 'Página especificada',
	'all_topics'    => 'Todos los temas de foros seleccionados',
	'all_pages'     => 'Todas las páginas activas',
	'chosen_boards' => 'Foros seleccionados',
	'chosen_topics' => 'Selected topics',
	'chosen_pages'  => 'Selected pages'
);
$txt['lp_frontpage_alias']                   = 'Página del portal para mostrar como página principal';
$txt['lp_frontpage_alias_subtext']           = 'Ingrese el alias de la página que existen.';
$txt['lp_frontpage_boards']                  = 'Foros como fuentes de artículos para la portada';
$txt['lp_frontpage_topics']                  = 'Topics as sources of articles for the frontpage';
$txt['lp_frontpage_topics_subtext']          = 'IDs of the required topics, separated by commas.';
$txt['lp_frontpage_pages']                   = 'Pages as sources of articles for the frontpage';
$txt['lp_frontpage_pages_subtext']           = 'IDs of the required pages, separated by commas.';
$txt['lp_show_images_in_articles']           = 'Mostrar las imágenes que se encuentran en los artículos';
$txt['lp_show_images_in_articles_help']      = 'First, it checks whether the article has an attachment (if the article is based on a forum topic), then — whether the article has an IMG tag with an image.';
$txt['lp_image_placeholder']                 = 'URL de la imagen del marcador de posición por defecto';
$txt['lp_frontpage_time_format']             = 'Time format in the article cards';
$txt['lp_frontpage_time_format_set']         = array('Full (LP style)', 'As in the forum', 'Own format');
$txt['lp_frontpage_custom_time_format']      = 'Own time format';
$txt['lp_frontpage_custom_time_format_help'] = 'See the list of possible parameters in the <a class="bbc_link" href="https://www.php.net/manual/en/datetime.format.php">documentation</a>.';
$txt['lp_show_teaser']                       = 'Show the article teaser';
$txt['lp_teaser_size']                       = 'El tamaño máximo de los avances de artículos (en símbolos)';
$txt['lp_show_author']                       = 'Show the article author';
$txt['lp_show_author_help']                  = 'If the board card is displayed, it will be information about the category.';
$txt['lp_show_num_views_and_comments']       = 'Show the number of views and comments';
$txt['lp_frontpage_order_by_num_replies']    = 'First to display articles with the highest number of comments';
$txt['lp_frontpage_article_sorting']         = 'Sorting articles';
$txt['lp_frontpage_article_sorting_set']     = array('By the last comment', 'By the date of creation (new first)', 'By the date of creation (old first)', 'By the date of updation (fresh first)');
$txt['lp_frontpage_article_sorting_help']    = 'When you select the first option, the article cards display the dates and the latest commentators (if they available).';
$txt['lp_frontpage_layout']                  = 'Número de columnas para mostrar artículos.';
$txt['lp_frontpage_layout_set']              = array('1 columna', '2 columnas', '3 columnas', '4 columnas', '6 columnas');
$txt['lp_num_items_per_page']                = 'Número de elementos por página (para paginación)';

$txt['lp_standalone_mode_title']                    = 'Modo independiente';
$txt['lp_standalone_url']                           = 'La URL de la página principal en el modo independiente';
$txt['lp_standalone_url_help']                      = 'Puede especificar su propia URL para mostrar como portada del portal (por ejemplo, <strong>https://miforo/portal.php</strong>).<br>En este caso, la portada del foro permanecerá disponible en <strong>https://miforo/index.php</strong>.<br><br>As an example, the <em>portal.php</em> file is included with the portal — you can use it.<br><br>Deshabilite la opción "<strong>Activar el almacenamiento local de cookies.</strong>" if you want to place <em>portal.php</em> outside the forum directory (Mantenimiento => Configuración del servidor => Cookies y sesiones).';
$txt['lp_standalone_mode_disabled_actions']         = 'Acciones desactivadas';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Especifique las áreas que deben DESACTIVARSE en el modo independiente.';
$txt['lp_standalone_mode_disabled_actions_help']    = 'Por ejemplo, si necesita desactivar el área de búsqueda (index.php?action=<strong>search</strong>), Agregar <strong>búsqueda</strong> en el campo de texto.';

$txt['groups_light_portal_view']             = '¿Quién puede ver los elementos del portal?';
$txt['groups_light_portal_manage_blocks']    = '¿Quién puede administrar los bloques?';
$txt['groups_light_portal_manage_own_pages'] = '¿Quién puede administrar sus propias páginas?';
$txt['groups_light_portal_approve_pages']    = 'Who can post the portal pages without approval';
$txt['lp_manage_permissions']                = 'Some pages may contain dangerous HTML/PHP content, so do not allow their creation to everyone';

// Pages and blocks
$txt['lp_extra']      = 'Páginas y bloques';
$txt['lp_extra_info'] = 'Aquí puede encontrar configuraciones generales de páginas y bloques.';

$txt['lp_show_tags_on_page']                = 'Mostrar palabras clave en la parte superior de la página';
$txt['lp_show_tags_as_articles']            = 'Display lists of articles with the same tag as cards';
$txt['lp_show_related_pages']               = 'Display related pages block';
$txt['lp_show_comment_block']               = 'Mostrar bloque de comentarios';
$txt['lp_disabled_bbc_in_comments']         = 'BBC permitidos en los comentarios';
$txt['lp_disabled_bbc_in_comments_subtext'] = 'You can use any tags <a class="bbc_link" href="%1$s">that allowed</a> on the forum.';
$txt['lp_show_comment_block_set']           = array('none' => 'None', 'default' => 'Integrated');
$txt['lp_time_to_change_comments']          = 'Maximum time after commenting to allow edit';
$txt['lp_num_comments_per_page']            = 'Número de comentarios de los foros por página';
$txt['lp_page_editor_type_default']         = 'El tipo de editor de página por defecto';
$txt['lp_permissions_default']              = 'Permissions for pages and blocks by default';
$txt['lp_hide_blocks_in_admin_section']     = 'Ocultar bloques activos en el área de administración';

$txt['lp_schema_org']            = 'Schema microdata markup for contacts';
$txt['lp_page_og_image']         = 'Usa una imagen del contenido de la página';
$txt['lp_page_og_image_set']     = array('ninguno', 'Primero encontrado', 'Último encontrado');
$txt['lp_page_itemprop_address'] = 'Dirección de su organización';
$txt['lp_page_itemprop_phone']   = 'Teléfono de su organización';

$txt['lp_permissions'] = array('Mostrar a los administradores', 'Mostrar a los invitados', 'Mostrar a los usuarios', 'Mostrar a todos');

// Panels
$txt['lp_panels']               = 'Paneles';
$txt['lp_panels_info']          = 'Aquí se puede personalizar el ancho de algunos paneles, así como la dirección de los bloques.<br><strong>%1$s</strong> utiliza <a class="bbc_link" href="%2$s" target="_blank" rel="noopener">un sistema de cuadrícula de 12 columnas</a> to display blocks in 6 panels.';
$txt['lp_swap_header_footer']   = 'Cambia el encabezado y el pie de página';
$txt['lp_swap_left_right']      = 'Cambia el panel izquierdo y el panel derecho';
$txt['lp_swap_top_bottom']      = 'Cambie el centro (arriba) y el centro (abajo)';
$txt['lp_panel_layout_note']    = 'Cambia el ancho de la ventana del navegador y ver qué clase se utiliza.';
$txt['lp_browser_width']        = 'Ancho de la ventana del navegador';
$txt['lp_used_class']           = 'Clase utilizada';
$txt['lp_panel_layout_preview'] = 'Aquí puede establecer el número de columnas para algunos paneles, dependiendo del ancho de la ventana del navegador.';
$txt['lp_left_panel_sticky']    = $txt['lp_right_panel_sticky'] = 'Sticky';
$txt['lp_panel_direction_note'] = 'Aquí puede cambiar la dirección de los bloques para cada panel.';
$txt['lp_panel_direction']      = 'La dirección de los bloques en los paneles.';
$txt['lp_panel_direction_set']  = array('Vertical', 'Horizontal');

// Misc
$txt['lp_misc']                           = 'Miscellaneous';
$txt['lp_misc_info']                      = 'There are additional portal settings that will be useful for template and plugin developers here.';
$txt['lp_fontawesome_compat_themes']      = 'Check themes those using Font Awesome icons';
$txt['lp_fontawesome_compat_themes_help'] = 'Compatibility option for templates that use Font Awesome icons.';
$txt['lp_debug_and_caching']              = 'Depuración y almacenamiento en caché';
$txt['lp_show_debug_info']                = 'Muestra el tiempo de carga y el número de consultas del portal.';
$txt['lp_show_debug_info_help']           = '¡Esta información estará disponible solo para administradores!';
$txt['lp_cache_update_interval']          = 'El intervalo de actualización del caché';

// Actions
$txt['lp_title']        = 'Título';
$txt['lp_actions']      = 'Acciones';
$txt['lp_action_on']    = 'Activar';
$txt['lp_action_off']   = 'Desactivar';
$txt['lp_action_clone'] = 'Clonar';
$txt['lp_action_move']  = 'Mover';
$txt['lp_read_more']    = 'Leer más...';

// Blocks
$txt['lp_blocks']                    = 'Bloques';
$txt['lp_blocks_manage']             = 'Administrar bloques';
$txt['lp_blocks_manage_description'] = 'Todos los bloques del portal creados se enumeran aquí. Para agregar un bloque, use el botón correspondiente.';
$txt['lp_blocks_add']                = 'Agregar bloque';
$txt['lp_blocks_add_title']          = 'Adición de bloque';
$txt['lp_blocks_add_description']    = 'Todavía no hay muchos bloques, pero existen los más universales ~ juega con ellos :)';
$txt['lp_blocks_add_instruction']    = 'Seleccione el bloque deseado haciendo clic en él.';
$txt['lp_blocks_edit_title']         = 'Edición de bloques';
$txt['lp_blocks_edit_description']   = $txt['lp_blocks_add_description'];
$txt['lp_block_icon_cheatsheet']     = 'Lista de los iconos';
$txt['lp_block_type']                = 'Tipo de bloque';
$txt['lp_block_note']                = 'Note';
$txt['lp_block_priority']            = 'Prioridad';
$txt['lp_block_icon_type']           = 'Tipo de icono';
$txt['lp_block_icon_type_set']       = array('fas' => 'Sólido', 'far' => 'Regular', 'fab' => 'Marcas'); // Review later
$txt['lp_block_placement']           = 'Colocación';
$txt['lp_block_placement_set'] = array(
	'header' => 'Encabezado',
	'top'    => 'Centro (arriba)',
	'left'   => 'Lado izquierdo',
	'right'  => 'Lado derecho',
	'bottom' => 'Centro (abajo)',
	'footer' => 'Pie de página'
);

$txt['lp_block_areas']            = 'Acciones';
$txt['lp_block_areas_subtext']    = 'Especifique una o más áreas (separadas por comas) para mostrar el bloque en:';
$txt['lp_block_areas_area_th']    = 'Area';
$txt['lp_block_areas_display_th'] = 'Display';
$txt['lp_block_areas_values']     = array(
	'en todas partes',
	'en el área <em>index.php?action</em>=<strong>custom_action</strong> (for example: portal,forum,search)',
	'on all portal pages',
	'en la página <em>index.php?page</em>=<strong>alias</strong>',
	'in all boards',
	'only inside the board with identificator <strong>id</strong> (including all topics inside the board)',
	'in boards id1, id2, id3',
	'in boards id3, and id7',
	'in all topics',
	'only inside the topic with identificator <strong>id</strong>',
	'in topics id1, id2, id3',
	'in topics id3, and id7'
);

$txt['lp_block_title_class']   = 'Clase de título CSS';
$txt['lp_block_title_style']   = 'Estilo de título CSS';
$txt['lp_block_content_class'] = 'Clase de contenido CSS';
$txt['lp_block_content_style'] = 'Estilo de contenido CSS';

$txt['lp_block_types'] = array(
	'bbc'  => 'BBC personalizado',
	'html' => 'HTML personalizado',
	'php'  => 'PHP personalizado'
);
$txt['lp_block_types_descriptions'] = array(
	'bbc'  => 'En este bloque, las etiquetas BBC del foro se pueden utilizar como contenido.',
	'html' => 'En este bloque, se puede utilizar cualquier etiqueta HTML como contenido.',
	'php'  => 'En este bloque, puede usar cualquier código PHP como contenido.'
);

// Pages
$txt['lp_pages']                     = 'Páginas';
$txt['lp_pages_manage']              = 'Administrar páginas';
$txt['lp_pages_manage_all_pages']    = 'Todas las páginas del portal creadas se enumeran aquí.';
$txt['lp_pages_manage_own_pages']    = 'Here you can view all your own portal pages.';
$txt['lp_pages_manage_description']  = 'Para agregar una nueva página, use el botón correspondiente.';
$txt['lp_pages_add']                 = 'Añadir página';
$txt['lp_pages_add_title']           = 'Añadiendo página';
$txt['lp_pages_add_description']     = 'Rellene el título de la página y el alias. Después de eso, se puede cambiar el tipo, el uso de vista previa y guardar.';
$txt['lp_pages_edit_title']          = 'Página de edición';
$txt['lp_pages_edit_description']    = $txt['lp_pages_add_description'];
$txt['lp_pages_extra']               = 'Páginas del portal';
$txt['lp_pages_search']              = 'Alias o título';
$txt['lp_page_types']                = array('bbc' => 'BBC', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                = 'Alias';
$txt['lp_page_alias_subtext']        = 'El alias de la página debe comenzar con una letra latina y consistir en letras minúsculas latinas, números y guiones bajos.';
$txt['lp_page_type']                 = 'Tipo de página';
$txt['lp_page_description']          = 'Descripción';
$txt['lp_page_keywords']             = 'Palabras claves';
$txt['lp_page_keywords_placeholder'] = 'Select tags or add new';
$txt['lp_page_publish_datetime']     = 'Fecha y hora de publicación';
$txt['lp_page_author_subtext']       = 'Name of member to post as. Leave blank to post as yourself.';

$txt['lp_page_options'] = array(
	'show_author_and_date' => 'Mostrar el autor y la fecha de creación',
	'show_related_pages'   => 'Show related pages',
	'allow_comments'       => 'Permitir comentarios'
);

// Tabs
$txt['lp_tab_content']          = 'Contenido';
$txt['lp_tab_seo']              = 'SEO';
$txt['lp_tab_access_placement'] = 'El acceso y la colocación';
$txt['lp_tab_appearance']       = 'Apariencia';
$txt['lp_tab_tuning']           = 'Extras';

// Import and Export
$txt['lp_pages_export']              = 'Exportar página';
$txt['lp_pages_import']              = 'Importar página';
$txt['lp_pages_export_description']  = 'Aquí puede exportar las páginas que necesita para crear una copia de seguridad o importarlas a otro foro.';
$txt['lp_pages_import_description']  = 'Aquí puede importar páginas del portal guardadas previamente desde una copia de seguridad.';
$txt['lp_blocks_export']             = 'Exportar bloque';
$txt['lp_blocks_import']             = 'Importar bloque';
$txt['lp_blocks_export_description'] = 'Aquí puede exportar los bloques que necesita para crear una copia de seguridad o importarlos a otro foro.';
$txt['lp_blocks_import_description'] = 'Aquí puede importar bloques del portal guardados previamente desde una copia de seguridad.';
$txt['lp_export_run']                = 'Exportar selección';
$txt['lp_import_run']                = 'Ejecutar importación';
$txt['lp_export_all']                = 'Exportar todo';

// Plugins
$txt['lp_plugins']                    = 'Plugins';
$txt['lp_plugins_manage']             = 'Manage plugins';
$txt['lp_plugins_manage_description'] = 'Los plugins instalados portal se enumeran aquí. Siempre puedes crear uno nuevo usando <a class="bbc_link" href="%1$s" target="_blank" rel="noopener">las instrucciones</a>. or the "+" button below.';
$txt['lp_plugins_desc']               = 'Plugins extend the capabilities of the portal and its components, providing additional features that are not available in the core.';
$txt['lp_plugins_add']                = 'Add plugin';
$txt['lp_plugins_add_title']          = 'Adding a plugin';
$txt['lp_plugins_add_description']    = 'The plugin maker wizard will help you prepare the addon skeleton for further changes. Fill in the suggested fields carefully.';
$txt['lp_plugins_add_information']    = 'The plugin files will be saved in the directory %1$s<br>Be sure to look there and check/edit the necessary files.';

$txt['lp_plugins_tab_content']    = 'Basic information';
$txt['lp_plugins_tab_copyrights'] = 'Copyrights';
$txt['lp_plugins_tab_settings']   = 'Settings';
$txt['lp_plugins_tab_tuning']     = 'Additional';

$txt['lp_plugins_hooks_types'] = array(
	'block'     => 'Bloque',
	'editor'    => 'Editor',
	'comment'   => 'Widget de comentarios',
	'parser'    => 'Analizador de contenido',
	'article'   => 'Procesamiento de artículos',
	'frontpage' => 'The layout of the frontpage',
	'impex'     => 'Import and export',
	'other'     => 'Otro'
);

$txt['lp_plugin_name']              = 'The plugin name';
$txt['lp_plugin_name_subtext']      = 'In Latin letters, without spaces!';
$txt['lp_plugin_type']              = 'The plugin type';
$txt['lp_plugin_site_subtext']      = 'Website where users can download new versions of this plugin.';
$txt['lp_plugin_license']           = 'The plugin license';
$txt['lp_plugin_license_own']       = 'Own license';
$txt['lp_plugin_license_name']      = 'The license name';
$txt['lp_plugin_license_link']      = 'The license link';
$txt['lp_plugin_smf_hooks']         = 'Are you using SMF hooks?';
$txt['lp_plugin_components']        = 'Are you using third-party scripts?';
$txt['lp_plugin_components_name']   = 'Component name';
$txt['lp_plugin_components_link']   = 'Link to component site';
$txt['lp_plugin_components_author'] = 'Component author';

$txt['lp_plugin_option_name']  = 'Option name (Latin)';
$txt['lp_plugin_option_type']  = 'Option type';
$txt['lp_plugin_option_types'] = array(
	'text'       => 'Text field',
	'url'        => 'Web address',
	'color'      => 'Input color',
	'int'        => 'Input number',
	'check'      => 'Checkbox',
	'multicheck' => 'Multiple select',
	'select'     => 'Select'
);

$txt['lp_plugin_option_default_value']        = 'Default value';
$txt['lp_plugin_option_variants']             = 'Possible values';
$txt['lp_plugin_option_variants_placeholder'] = 'Multiple options separated by commas';
$txt['lp_plugin_option_translations']         = 'Localization';
$txt['lp_plugin_new_option']                  = 'Add option';

// Tags
$txt['lp_all_page_tags']          = 'Todas las etiquetas de página del portal';
$txt['lp_all_tags_by_key']        = 'Todas las páginas con la etiqueta "%1$s"';
$txt['lp_no_selected_tag']        = 'No se encontró la etiqueta especificada.';
$txt['lp_no_tags']                = 'No hay etiquetas todavía.';
$txt['lp_keyword_column']         = 'Palabra clave';
$txt['lp_frequency_column']       = 'Frecuencia';
$txt['lp_sorting_label']          = 'Sort by';
$txt['lp_sort_by_created_desc']   = 'Creation date (new first)';
$txt['lp_sort_by_created']        = 'Creation date (old first)';
$txt['lp_sort_by_updated_desc']   = 'Update date (new first)';
$txt['lp_sort_by_updated']        = 'Update date (old first)';
$txt['lp_sort_by_author_desc']    = 'Author name (desc)';
$txt['lp_sort_by_author']         = 'Author name (asc)';
$txt['lp_sort_by_num_views_desc'] = 'Number of views (desc)';
$txt['lp_sort_by_num_views']      = 'Number of views (asc)';

// Comments
$txt['lp_comments']            = 'Comentarios';
$txt['lp_comment_placeholder'] = 'Deja un comentario...';

// Related pages
$txt['lp_related_pages'] = 'Related pages';

// Comment alerts
$txt['alert_group_light_portal']       = LP_NAME;
$txt['alert_page_comment']             = 'Cuando mi página recibe un comentario';
$txt['alert_new_comment_page_comment'] = '{member_link} dejó un comentario {page_comment_new_comment}';
$txt['alert_page_comment_reply']       = 'Cuando mi comentario recibe una respuesta';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} dejó una respuesta en tu comentario {page_comment_reply_new_reply}';

// Errors
$txt['lp_page_not_found']             = '¡Página no encontrada!';
$txt['lp_page_not_activated']         = 'The requested page is disabled!';
$txt['lp_page_not_editable']          = '¡No tienes permiso para editar esta página!';
$txt['lp_page_visible_but_disabled']  = 'The page is visible to you, but not activated!';
$txt['lp_block_not_found']            = '¡Bloque no encontrado!';
$txt['lp_post_error_no_title']        = 'El campo <strong>título</strong> no se completó. Es requerido.';
$txt['lp_post_error_no_alias']        = 'El campo <strong>alias</strong> no se completó. Es requerido.';
$txt['lp_post_error_no_valid_alias']  = '¡El alias especificado no es correcto!';
$txt['lp_post_error_no_unique_alias'] = '¡Ya existe una página con este alias!';
$txt['lp_post_error_no_content']      = '¡El contenido no especificado! Es requerido.';
$txt['lp_post_error_no_areas']        = 'El campo <strong>areas</strong> no se completó. Es requerido.';
$txt['lp_post_error_no_valid_areas']  = '¡El campo de las <strong>zonas</strong> se configuró incorrectamente!';
$txt['lp_post_error_no_name']         = 'The <strong>name</strong> field was not filled out. It is required.';
$txt['lp_post_error_no_valid_name']   = 'The specified name does not match the rules!';
$txt['lp_post_error_no_unique_name']  = 'A plugin with this name already exists!';
$txt['lp_post_error_no_description']  = 'The description not specified! It is required.';
$txt['lp_addon_not_installed']        = 'Plugin %1$s no instalado';
$txt['lp_addon_add_failed']           = 'The <strong>/Sources/LightPortal/addons</strong> directory must be writable!';
$txt['lp_wrong_import_file']          = 'Archivo incorrecto para importar...';
$txt['lp_import_failed']              = 'Error al importar...';

// Who
$txt['lp_who_viewing_frontpage']       = 'Viendo <a href="%1$s">la página principal del portal</a>.';
$txt['lp_who_viewing_index']           = 'Viendo <a href="%1$s">la página principal del portal</a> o <a href="%2$s">el índice del foro</a>.';
$txt['lp_who_viewing_page']            = 'Viendo <a href="%1$s">la página del portal</a>.';
$txt['lp_who_viewing_tags']            = 'Viendo <a href="%1$s">las etiquetas de la página del portal</a>.';
$txt['lp_who_viewing_the_tag']         = 'Viendo la lista de páginas con la etiqueta <a href="%1$s" class="bbc_link">%2$s</a>.';
$txt['lp_who_viewing_portal_settings'] = 'Viendo o cambiando <a href="%1$s">la configuración del portal</a>.';
$txt['lp_who_viewing_portal_blocks']   = 'Viendo <a href="%1$s">the portal blocks</a> in the admin area.';
$txt['lp_who_viewing_editing_block']   = 'Editando el bloque del portal (#%1$d).';
$txt['lp_who_viewing_adding_block']    = 'Agregando un bloque para el portal.';
$txt['lp_who_viewing_portal_pages']    = 'Viendo <a href="%1$s">las páginas del portal</a> en el área de administración.';
$txt['lp_who_viewing_editing_page']    = 'Editando la página del portal (#%1$d).';
$txt['lp_who_viewing_adding_page']     = 'Agregando una página para el portal.';

// Permissions
$txt['permissiongroup_light_portal']                 = LP_NAME;
$txt['permissionname_light_portal_view']             = $txt['group_perms_name_light_portal_view'] = 'Ver los elementos del portal';
$txt['permissionname_light_portal_manage_blocks']    = $txt['group_perms_name_light_portal_manage_blocks'] = 'Administrar bloques';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Administra tus propias páginas';
$txt['permissionname_light_portal_approve_pages']    = $txt['group_perms_name_light_portal_approve_pages']    = 'Post pages without approval';
$txt['permissionhelp_light_portal_view']             = 'Capacidad para ver páginas y bloques del portal.';
$txt['permissionhelp_light_portal_manage_blocks']    = 'Acceso para gestionar bloques del portal.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Acceso para gestionar páginas propias.';
$txt['permissionhelp_light_portal_approve_pages']    = 'Ability to post portal pages without approval.';
$txt['cannot_light_portal_view']                     = 'Lo sentimos, ¡no tienes permiso para ver el portal!';
$txt['cannot_light_portal_manage_blocks']            = 'Lo sentimos, ¡no tienes permiso para administrar bloques!';
$txt['cannot_light_portal_manage_own_pages']         = 'Lo sentimos, ¡no tienes permiso para administrar páginas!';
$txt['cannot_light_portal_approve_pages']            = 'Sorry, you are not allowed to post pages without approval!';
$txt['cannot_light_portal_view_page']                = 'Lo sentimos, ¡no puedes ver esta página!';

// Time units
$txt['lp_days_set']       = array('día','días');
$txt['lp_hours_set']      = array('una hora','horas');
$txt['lp_minutes_set']    = array('un minuto','minutos');
$txt['lp_seconds_set']    = array('segundo','segundos');
$txt['lp_tomorrow']       = '<strong>mañana</strong> a las ';
$txt['lp_just_now']       = 'Justo ahora';
$txt['lp_time_label_in']  = 'en %1$s';
$txt['lp_time_label_ago'] = ' hace';

// Social units
$txt['lp_posts_set']    = array('mensaje', 'mensajes');
$txt['lp_replies_set']  = array('respuesta', 'respuestas');
$txt['lp_views_set']    = array('vista', 'vistas');
$txt['lp_comments_set'] = array('comentario', 'comentarios');

// Other units
$txt['lp_users_set']   = array('usuario', 'usuarios');
$txt['lp_guests_set']  = array('invitado', 'invitados');
$txt['lp_spiders_set'] = array('araña', 'arañas');
$txt['lp_hidden_set']  = array('oculto', 'oculto');
$txt['lp_buddies_set'] = array('amigo', 'amigos');

// Credits
$txt['lp_used_components'] = 'Los componentes del portal.';

// Debug info
$txt['lp_load_page_stats'] = 'Cargado en %1$.3f segundos. Consultas a la DB: %2$d.';
