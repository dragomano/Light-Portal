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
$txt['lp_new_version_is_available']            = '<a href="%1$s" target="_blank" rel="noopener">A new version</a> is available!';
$txt['lp_frontpage_title']                     = 'The frontpage title';
$txt['lp_frontpage_disable']                   = 'Disable the frontpage';
$txt['lp_frontpage_mode']                      = 'What is should be displayed as the frontpage';
$txt['lp_frontpage_mode_set']                  = array('The portal main page', 'All topics from selected boards', 'All pages (except the first)', 'Boards with descriptions');
$txt['lp_frontpage_boards']                    = 'Boards as sources of articles for the frontpage';
$txt['lp_frontpage_layout']                    = 'Number of columns for displaying articles';
$txt['lp_frontpage_layout_set']                = array('1 column', '2 columns', '3 columns', '4 columns', '6 columns');
$txt['lp_show_images_in_articles']             = 'Show images that found in articles';
$txt['lp_subject_size']                        = 'The max size of article titles (in symbols)';
$txt['lp_teaser_size']                         = 'The max size of article teasers (in symbols)';
$txt['lp_num_per_page']                        = 'Maximum number of items (for pagination)';
$txt['lp_standalone']                          = 'Standalone mode';
$txt['lp_standalone_help']                     = 'Everything except portal pages and ignored areas will be disabled.';
$txt['lp_standalone_excluded_actions']         = 'Ignored actions';
$txt['lp_standalone_excluded_actions_subtext'] = 'Specify the areas that should remain available offline.';
$txt['lp_page_editor_type_default']            = 'The type of page editor by default';
$txt['lp_hide_blocks_in_admin_section']        = 'Hide active blocks in the admin area';
$txt['lp_open_graph']                          = 'Open Graph';
$txt['lp_page_og_image']                       = 'Use an image from the page content';
$txt['lp_page_og_image_set']                   = array('None', 'First found', 'Last found');
$txt['lp_page_itemprop_address']               = 'Address of your organization';
$txt['lp_page_itemprop_phone']                 = 'Phone of your organization';
$txt['groups_light_portal_view']               = 'Who can view the portal elements';
$txt['groups_light_portal_manage_blocks']      = 'Who can manage blocks';
$txt['groups_light_portal_manage_own_pages']   = 'Who can manage own pages';
$txt['lp_extra_settings']                      = 'Extra settings';

// Actions
$txt['lp_title']       = 'Title';
$txt['lp_actions']     = 'Actions';
$txt['lp_action_on']   = 'Enable';
$txt['lp_action_off']  = 'Disable';
$txt['lp_action_move'] = 'Move';
$txt['lp_read_more']   = 'Read more...';

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
$txt['lp_block_icon_cheatsheet']         = '<br><span class="smalltext"><a href="https://fontawesome.com/cheatsheet/free/solid" target="_blank" rel="noopener">More icons</a></span>';
$txt['lp_block_type']                    = 'Block type';
$txt['lp_block_priority']                = 'Priority';
$txt['lp_block_placement']               = 'Placement';

$txt['lp_block_placement_set'] = array(
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
$txt['lp_pages_main']                   = 'The main page';
$txt['lp_pages_manage']                 = 'Manage pages';
$txt['lp_pages_manage_tab_description'] = 'All created portal pages are listed here. To add a new page, use the corresponding button.';
$txt['lp_pages_add']                    = 'Add page';
$txt['lp_pages_add_title']              = 'Adding page';
$txt['lp_pages_add_tab_description']    = 'Pay special attention to the <strong>page alias</strong> — it is used in the address bar and can only contain Latin characters and numbers!<br>The main page always has an alias equal to "/".';
$txt['lp_pages_edit_title']             = 'Editing page';
$txt['lp_pages_edit_tab_description']   = $txt['lp_pages_add_tab_description'];
$txt['lp_extra_pages']                  = 'Additional pages';
$txt['lp_page_types']                   = array('bbc' => 'BBC', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                   = 'Alias';
$txt['lp_page_content']                 = $txt['lp_block_content'];
$txt['lp_page_type']                    = 'Page type';
$txt['lp_page_description']             = 'Description';
$txt['lp_page_keywords']                = 'Keywords';
$txt['lp_permissions']                  = array('Show to admins', 'Show to guests', 'Show to members', 'Show to everybody');
$txt['lp_no_items']                     = 'There is nothing yet. Let\'s add?';

$txt['lp_page_options'] = array(
	'show_author_and_date' => 'Show the author and creation date'
);

// Errors
$txt['lp_page_not_found']             = 'Page not found!';
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
$txt['lp_minutes_set']    = array('minute','minutes');
$txt['lp_seconds_set']    = array('second','seconds');
$txt['lp_remained']       = '%1$s left';
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
