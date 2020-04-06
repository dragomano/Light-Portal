<?php

/**
 * .english language file
 *
 * @package Light Portal
 * @author Bugo https://dragomano.ru/mods/light-portal
 */

$txt['lp_portal'] = 'Portal';
$txt['lp_forum']  = 'Forum';

// Settings
$txt['lp_settings']                            = 'Portal settings';
$txt['lp_php_mysql_info']                      = 'The mod version: <strong>%1$s</strong>, PHP version: <strong>%2$s</strong>, %3$s version: <strong>%4$s</strong>.';
$txt['lp_new_version_is_available']            = 'A new version is available!';
$txt['lp_frontpage_title']                     = 'The frontpage title';
$txt['lp_frontpage_mode']                      = 'The portal frontpage';
$txt['lp_frontpage_mode_set']                  = array('Disabled', 'Specified page', 'All topics from selected boards', 'All active pages', 'Selected boards');
$txt['lp_frontpage_id']                        = 'Portal page to display as the main page';
$txt['lp_frontpage_boards']                    = 'Boards as sources of articles for the frontpage';
$txt['lp_frontpage_layout']                    = 'Number of columns for displaying articles';
$txt['lp_frontpage_layout_set']                = array('1 column', '2 columns', '3 columns', '4 columns', '6 columns');
$txt['lp_show_images_in_articles']             = 'Show images that found in articles';
$txt['lp_subject_size']                        = 'The max size of article titles (in symbols)';
$txt['lp_teaser_size']                         = 'The max size of article teasers (in symbols)';
$txt['lp_num_items_per_page']                  = 'Number of items per page (for pagination)';
$txt['lp_standalone']                          = 'Standalone mode';
$txt['lp_standalone_help']                     = 'Everything except portal pages and ignored areas will be disabled.';
$txt['lp_standalone_excluded_actions']         = 'Ignored actions';
$txt['lp_standalone_excluded_actions_subtext'] = 'Specify the areas that should remain available offline.';
$txt['lp_show_tags_on_page']                   = 'Display keywords at the top of the page';
$txt['lp_show_comment_block']                  = 'Display comments block';
$txt['lp_show_comment_block_set']              = array('none' => 'None', 'default' => 'Integrated');
$txt['lp_num_comments_per_page']               = 'Number of parent comments per page';
$txt['lp_page_editor_type_default']            = 'The type of page editor by default';
$txt['lp_hide_blocks_in_admin_section']        = 'Hide active blocks in the admin area';
$txt['lp_use_block_icons']                     = 'Allow icons for block titles';
$txt['lp_use_block_icons_set']                 = array('none' => 'None', 'fontawesome' => 'Font Awesome');
$txt['lp_open_graph']                          = 'Open Graph';
$txt['lp_page_og_image']                       = 'Use an image from the page content';
$txt['lp_page_og_image_set']                   = array('None', 'First found', 'Last found');
$txt['lp_page_itemprop_address']               = 'Address of your organization';
$txt['lp_page_itemprop_phone']                 = 'Phone of your organization';
$txt['groups_light_portal_view']               = 'Who can view the portal elements';
$txt['groups_light_portal_manage_blocks']      = 'Who can manage blocks';
$txt['groups_light_portal_manage_own_pages']   = 'Who can manage own pages';
$txt['lp_manage_permissions']                  = 'Note: some pages and blocks may contain dangerous HTML/PHP content, so do not grant this right to everyone!';
$txt['lp_extra_settings']                      = 'Extra settings';
$txt['lp_no_items']                            = 'There is no items to show.';

// Actions
$txt['lp_title']        = 'Title';
$txt['lp_actions']      = 'Actions';
$txt['lp_action_on']    = 'Enable';
$txt['lp_action_off']   = 'Disable';
$txt['lp_action_clone'] = 'Clone';
$txt['lp_action_move']  = 'Move';
$txt['lp_read_more']    = 'Read more...';

// Blocks
$txt['lp_blocks']                        = 'Blocks';
$txt['lp_blocks_manage']                 = 'Manage blocks';
$txt['lp_blocks_manage_tab_description'] = 'All created portal blocks are listed here. To add a block, use the corresponding button.';
$txt['lp_blocks_add']                    = 'Add block';
$txt['lp_blocks_add_title']              = 'Adding block';
$txt['lp_blocks_add_tab_description']    = 'There are not many blocks yet, but the most universal ones exist - play with them :)';
$txt['lp_blocks_add_instruction']        = 'Select the desired block by clicking on it.';
$txt['lp_blocks_edit_title']             = 'Editing block';
$txt['lp_blocks_edit_tab_description']   = $txt['lp_blocks_add_tab_description'];
$txt['lp_block_content']                 = 'Content';
$txt['lp_block_icon_cheatsheet']         = 'List of icons';
$txt['lp_block_type']                    = 'Block type';
$txt['lp_block_priority']                = 'Priority';
$txt['lp_block_icon_type']               = 'Icon type';
$txt['lp_block_icon_type_set']           = array('fas' => 'Solid', 'far' => 'Regular', 'fab' => 'Brands');
$txt['lp_block_placement']               = 'Placement';
$txt['lp_block_placement_set']           = array(
	'header' => 'Header',
	'top'    => 'Center (top)',
	'left'   => 'Left side',
	'right'  => 'Right side',
	'bottom' => 'Center (bottom)',
	'footer' => 'Footer'
);

$txt['lp_block_areas']         = 'Actions';
$txt['lp_block_areas_subtext'] = 'Specify one or more areas (separate by comma) to display the block in:<br>
<ul>
	<li><strong>all</strong> — display everywhere</li>
	<li><strong>forum</strong> — display only on the forum area</li>
	<li><strong>portal</strong> — display only on the portal area (including pages)</li>
	<li><strong>custom_action</strong> — display on area <em>index.php?action</em>=<strong>custom_action</strong></li>
	<li><strong>page=alias</strong> — display on page <em>index.php?page</em>=<strong>alias</strong></li>
</ul>';
$txt['lp_block_title_class']   = 'CSS title class';
$txt['lp_block_title_style']   = 'CSS title style';
$txt['lp_block_content_class'] = 'CSS content class';
$txt['lp_block_content_style'] = 'CSS content style';

$txt['lp_block_types'] = array(
	'bbc'  => 'Custom BBC',
	'html' => 'Custom HTML',
	'php'  => 'Custom PHP'
);
$txt['lp_block_types_descriptions'] = array(
	'bbc'  => 'In this block, any BBC tags of the forum can be used as content.',
	'html' => 'In this block, you can use any HTML tags as content.',
	'php'  => 'In this block, you can use any PHP code as content.'
);

// Pages
$txt['lp_pages']                        = 'Pages';
$txt['lp_pages_manage']                 = 'Manage pages';
$txt['lp_pages_manage_tab_description'] = 'All created portal pages are listed here. To add a new page, use the corresponding button.';
$txt['lp_pages_add']                    = 'Add page';
$txt['lp_pages_add_title']              = 'Adding page';
$txt['lp_pages_add_tab_description']    = 'Fill the page title and alias. After that, you can change its type, use preview and save.';
$txt['lp_pages_edit_title']             = 'Editing page';
$txt['lp_pages_edit_tab_description']   = $txt['lp_pages_add_tab_description'];
$txt['lp_extra_pages']                  = 'Portal pages';
$txt['lp_page_types']                   = array('bbc' => 'BBC', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                   = 'Alias';
$txt['lp_page_alias_subtext']           = 'The page name must begin with a Latin letter and consist of lowercase Latin letters, numbers, and underscore.';
$txt['lp_page_content']                 = $txt['lp_block_content'];
$txt['lp_page_type']                    = 'Page type';
$txt['lp_page_description']             = 'Description';
$txt['lp_page_keywords']                = 'Keywords';
$txt['lp_permissions']                  = array('Show to admins', 'Show to guests', 'Show to members', 'Show to everybody');

$txt['lp_page_options'] = array(
	'show_author_and_date' => 'Show the author and creation date',
	'allow_comments'       => 'Allow comments'
);

// Import and Export
$txt['lp_pages_export']                  = 'Page export';
$txt['lp_pages_import']                  = 'Page import';
$txt['lp_pages_export_tab_description']  = 'Here you can export the pages you need to create a backup or import them to another forum.';
$txt['lp_pages_import_tab_description']  = 'Here you can import previously saved portal pages from a backup.';
$txt['lp_blocks_export']                 = 'Block export';
$txt['lp_blocks_import']                 = 'Block import';
$txt['lp_blocks_export_tab_description'] = 'Here you can export the blocks you need to create a backup or import them to another forum.';
$txt['lp_blocks_import_tab_description'] = 'Here you can import previously saved portal blocks from a backup.';
$txt['lp_export_run']                    = 'Export selection';
$txt['lp_import_run']                    = 'Run import';
$txt['lp_export_all']                    = 'Export all';

// Tags
$txt['lp_all_page_tags']    = 'All portal page tags';
$txt['lp_all_tags_by_key']  = 'All pages with the "%1$s" tag';
$txt['lp_no_selected_tag']  = 'The specified tag was not found.';
$txt['lp_no_tags']          = 'There is no tags yet.';
$txt['lp_keyword_column']   = 'Keyword';
$txt['lp_frequency_column'] = 'Frequency';

// Comments
$txt['lp_comments']            = 'Comments';
$txt['lp_comment_placeholder'] = 'Leave a comment...';

$txt['alert_group_light_portal']           = LP_NAME;
$txt['alert_page_comment']                 = 'When my page gets a comment';
$txt['alert_new_comment_page_comment']     = '{member_link} left a comment <a href="{comment_link}">{comment_title}</a>';
$txt['alert_page_comment_reply']           = 'When my comment gets a reply';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} left a reply on your comment <a href="{comment_link}">{comment_title}</a>';

// Errors
$txt['lp_page_not_found']             = 'Page not found!';
$txt['lp_page_not_activated']         = 'The requested page is disabled!';
$txt['lp_block_not_found']            = 'Block not found!';
$txt['lp_post_error_no_title']        = 'The <strong>title</strong> field was not filled out. It is required.';
$txt['lp_post_error_no_alias']        = 'The <strong>alias</strong> field was not filled out. It is required.';
$txt['lp_post_error_no_valid_alias']  = 'The specified alias is not correct!';
$txt['lp_post_error_no_unique_alias'] = 'A page with this alias already exists!';
$txt['lp_post_error_no_content']      = 'The content not specified! It is required.';
$txt['lp_post_error_no_areas']        = 'The <strong>areas</strong> field was not filled out. It is required.';
$txt['lp_page_not_editable']          = 'You are not allowed to edit this page!';
$txt['lp_addon_not_installed']        = 'Plugin %1$s not installed';

// Who
$txt['lp_who_viewing_frontpage'] = 'Viewing <a href="%1$s">the portal frontpage</a>.';
$txt['lp_who_viewing_page']      = 'Viewing <a href="%1$s">the portal page</a>.';
$txt['lp_who_viewing_tags']      = 'Viewing <a href="%1$s">the portal page tags</a>.';
$txt['lp_who_viewing_the_tag']   = 'Viewing the page list with <a href="%1$s" class="bbc_link">%2$s</a> tag.';

// Permissions
$txt['permissiongroup_light_portal']                 = LP_NAME;
$txt['permissionname_light_portal_view']             = $txt['group_perms_name_light_portal_view']             = 'View the portal elements';
$txt['permissionname_light_portal_manage_blocks']    = $txt['group_perms_name_light_portal_manage_blocks']    = 'Manage blocks';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Manage own pages';
$txt['permissionhelp_light_portal_view']             = 'Ability to view portal pages and blocks.';
$txt['permissionhelp_light_portal_manage_blocks']    = 'Access to manage portal blocks.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Access to manage own pages.';
$txt['cannot_light_portal_view']                     = 'Sorry, you are not allowed to view the portal!';
$txt['cannot_light_portal_manage_blocks']            = 'Sorry, you are not allowed to manage blocks!';
$txt['cannot_light_portal_manage_own_pages']         = 'Sorry, you are not allowed to manage pages!';
$txt['cannot_light_portal_view_page']                = 'Sorry, you are not allowed to view this page!';

// Time units
$txt['lp_days_set']       = array('day','days');
$txt['lp_hours_set']      = array('an hour','hours');
$txt['lp_minutes_set']    = array('a minute','minutes');
$txt['lp_seconds_set']    = array('second','seconds');
$txt['lp_tomorrow']       = '<strong>Tomorrow</strong> at ';
$txt['lp_just_now']       = 'Just now';
$txt['lp_time_label_in']  = 'In %1$s';
$txt['lp_time_label_ago'] = ' ago';

// Social units
$txt['lp_posts_set']    = array('post', 'posts');
$txt['lp_replies_set']  = array('reply', 'replies');
$txt['lp_views_set']    = array('view', 'views');
$txt['lp_comments_set'] = array('comment', 'comments');

// Other units
$txt['lp_users_set']   = array('user', 'users');
$txt['lp_guests_set']  = array('guest', 'guests');
$txt['lp_spiders_set'] = array('spider', 'spiders');
$txt['lp_hidden_set']  = array('hidden', 'hidden');
$txt['lp_buddies_set'] = array('buddy', 'buddies');

// Copyrights
$txt['lp_credits']         = 'Credits';
$txt['lp_used_components'] = 'The portal components';

// Script execution time and memory usage
$txt['lp_load_page_stats'] = 'Loaded for %1$.3f seconds. Memory used: %2$d ' . $txt['megabyte'] . '.';