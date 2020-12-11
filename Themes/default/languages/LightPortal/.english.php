<?php

/**
 * .english language file
 *
 * @package Light Portal
 */

$txt['lp_portal'] = 'Portal';
$txt['lp_forum']  = 'Forum';

$txt['lp_new_version_is_available'] = 'A new version is available!';

$txt['lp_article']  = 'Article';
$txt['lp_no_items'] = 'There is no items to show.';
$txt['lp_example']  = 'Example: ';
$txt['lp_content']  = 'Content';

// Settings
$txt['lp_settings']  = 'Portal settings';
$txt['lp_base']      = 'Settings for the frontpage and articles';
$txt['lp_base_info'] = 'The mod version: <strong>%1$s</strong>, PHP version: <strong>%2$s</strong>, %3$s version: <strong>%4$s</strong>.<br>One can discuss bugs and features of the portal at <a class="bbc_link" href="https://www.simplemachines.org/community/index.php?topic=572393.0">simplemachines.com</a>.<br>You can also <a class="bbc_link" href="https://www.patreon.com/bugo">become a sponsor on Patreon</a> or <a class="bbc_link" href="https://ko-fi.com/U7U41XD2G">buy a cup of coffee as a thank</a>.';

$txt['lp_frontpage_title']                = 'The frontpage title';
$txt['lp_frontpage_mode']                 = 'The portal frontpage';
$txt['lp_frontpage_mode_set']             = array('Disabled', 'Specified page', 'All topics from selected boards', 'All active pages', 'Selected boards');
$txt['lp_frontpage_alias']                = 'Portal page to display as the main page';
$txt['lp_frontpage_alias_subtext']        = 'Enter the alias of the page that exist.';
$txt['lp_frontpage_boards']               = 'Boards as sources of articles for the frontpage';
$txt['lp_frontpage_card_alt_layout']      = 'Alternative arrangement of the elements in the cards';
$txt['lp_frontpage_card_alt_layout_help'] = 'The date and author are reversed.';
$txt['lp_frontpage_order_by_num_replies'] = 'First to display articles with the highest number of comments';
$txt['lp_frontpage_article_sorting']      = 'Sorting articles';
$txt['lp_frontpage_article_sorting_set']  = array('By the last comment', 'By the date of creation (new first)', 'By the date of creation (old first)', 'By the date of updation (fresh first)');
$txt['lp_frontpage_article_sorting_help'] = 'When you select the first option, the article cards display the dates and the latest commentators (if they available).';
$txt['lp_frontpage_layout']               = 'Number of columns for displaying articles';
$txt['lp_frontpage_layout_set']           = array('1 column', '2 columns', '3 columns', '4 columns', '6 columns');
$txt['lp_show_images_in_articles']        = 'Show images that found in articles';
$txt['lp_show_images_in_articles_help']   = 'First, it checks whether the article has an attachment (if the article is based on a forum topic), then — whether the article has an IMG tag with an image.';
$txt['lp_image_placeholder']              = 'URL of the default placeholder image';
$txt['lp_teaser_size']                    = 'The max size of article teasers (in symbols)';
$txt['lp_teaser_size_help']               = 'Used in some addons and can also be used in custom frontpage layouts.';
$txt['lp_num_items_per_page']             = 'Number of items per page (for pagination)';

$txt['lp_standalone_mode_title'] = 'Standalone mode';
$txt['lp_standalone_url']        = 'The frontpage URL in the standalone mode';
$txt['lp_standalone_url_help']   = 'You can specify your own URL to display as the portal frontpage (for example, <strong>https://yourforum/portal.php</strong>).<br>In this case, the forum frontpage will remain available at <strong>https://yourforum/index.php</strong>.<br><br>Paste this code into the <em>portal.php</em> file:<br><pre><code class="bbc_code">
require(dirname(__FILE__) . \'/SSI.php\');
<br>
(new Bugo\LightPortal\FrontPage)->show();</code></pre><br>
Disable the "<strong>Enable local storage of cookies</strong>" option if the <em>portal.php</em> file is located outside the forum directory (Maintenance => Server Settings => Cookies and Sessions).';
$txt['lp_standalone_mode_disabled_actions']         = 'Disabled actions';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Specify the areas that should be DISABLED in the standalone mode.';
$txt['lp_standalone_mode_disabled_actions_help']    = 'For example, if you need to disable the Search area (index.php?action=<strong>search</strong>), add <strong>search</strong> into the text field.';

$txt['groups_light_portal_view']             = 'Who can view the portal elements';
$txt['groups_light_portal_manage_blocks']    = 'Who can manage blocks';
$txt['groups_light_portal_manage_own_pages'] = 'Who can manage own pages';
$txt['groups_light_portal_approve_pages']    = 'Who can post the portal pages without approval';
$txt['lp_manage_permissions']                = 'Note: some pages and blocks may contain dangerous HTML/PHP content, so do not grant this right to everyone!';

// Pages and blocks
$txt['lp_extra']      = 'Pages and blocks';
$txt['lp_extra_info'] = 'Here you can find general settings for pages and blocks.';

$txt['lp_show_tags_on_page']            = 'Show keywords at the top of the page';
$txt['lp_show_tags_as_articles']        = 'Show lists of articles with the same tag as cards';
$txt['lp_show_related_pages']           = 'Show related pages block';
$txt['lp_show_comment_block']           = 'Show comments block';
$txt['lp_disabled_bbc_in_comments']     = 'Allowed BBC in comments';
$txt['lp_show_comment_block_set']       = array('none' => 'None', 'default' => 'Integrated');
$txt['lp_time_to_change_comments']      = 'Maximum time after commenting to allow edit';
$txt['lp_num_comments_per_page']        = 'Number of parent comments per page';
$txt['lp_page_editor_type_default']     = 'The type of page editor by default';
$txt['lp_hide_blocks_in_admin_section'] = 'Hide active blocks in the admin area';

$txt['lp_open_graph']            = 'Open Graph';
$txt['lp_page_og_image']         = 'Use an image from the page content';
$txt['lp_page_og_image_set']     = array('None', 'First found', 'Last found');
$txt['lp_page_itemprop_address'] = 'Address of your organization';
$txt['lp_page_itemprop_phone']   = 'Phone of your organization';

$txt['lp_permissions'] = array('Show to admins', 'Show to guests', 'Show to members', 'Show to everybody');

// Panels
$txt['lp_panels']               = 'Panels';
$txt['lp_panels_info']          = 'Here you can customize the width of some panels, as well as the direction of blocks.<br><strong>%1$s</strong> uses <a class="bbc_link" href="%2$s" target="_blank" rel="noopener">12 column grid system</a> to display blocks in 6 panels.';
$txt['lp_swap_header_footer']   = 'Swap the header and the footer';
$txt['lp_swap_left_right']      = 'Swap the left panel and the right panel';
$txt['lp_swap_top_bottom']      = 'Swap the center (top) and the center (bottom)';
$txt['lp_panel_layout_note']    = 'Change the width of the browser window and see which class is used.';
$txt['lp_browser_width']        = 'Width of the browser window';
$txt['lp_used_class']           = 'Class used';
$txt['lp_panel_layout_preview'] = 'Here you can set the number of columns for some panels, depending on the width of the browser window.';
$txt['lp_left_panel_sticky']    = $txt['lp_right_panel_sticky'] = 'Sticky';
$txt['lp_panel_direction_note'] = 'Here you can change the direction of blocks for each panel.';
$txt['lp_panel_direction']      = 'The direction of blocks in panels';
$txt['lp_panel_direction_set']  = array('Vertical', 'Horizontal');

// Plugins
$txt['lp_plugins']      = 'Plugins';
$txt['lp_plugins_desc'] = 'You can enable or disable any of the plugins. And some of them are also customized!';
$txt['lp_plugins_info'] = 'The installed portal plugins are listed here. You can always create a new one using <a class="bbc_link" href="%1$s" target="_blank" rel="noopener">the instructions</a>.';

$txt['lp_plugins_hooks_types'] = array(
	'block'     => 'Block',
	'editor'    => 'Editor',
	'comment'   => 'Comment widget',
	'parser'    => 'Content parser',
	'article'   => 'Processing articles',
	'frontpage' => 'The layout of the frontpage',
	'impex'     => 'Import and export',
	'other'     => 'Other'
);

// Misc
$txt['lp_misc']                           = 'Miscellaneous';
$txt['lp_misc_info']                      = 'There are additional portal settings that will be useful for template and plugin developers here.';
$txt['lp_fontawesome_compat_themes']      = 'Check themes those using Font Awesome icons';
$txt['lp_fontawesome_compat_themes_help'] = 'Compatibility option for templates that use Font Awesome icons.';
$txt['lp_debug_and_caching']              = 'Debugging and caching';
$txt['lp_show_debug_info']                = 'Show the loading time and number of the portal queries';
$txt['lp_show_debug_info_help']           = 'This information will be available to administrators only!';
$txt['lp_cache_update_interval']          = 'The cache update interval';

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
$txt['lp_blocks_add_tab_description']    = 'Blocks can contain any content, depending on their type.';
$txt['lp_blocks_add_instruction']        = 'Select the desired block by clicking on it.';
$txt['lp_blocks_edit_title']             = 'Editing block';
$txt['lp_blocks_edit_tab_description']   = $txt['lp_blocks_add_tab_description'];
$txt['lp_block_icon_cheatsheet']         = 'List of icons';
$txt['lp_block_type']                    = 'Block type';
$txt['lp_block_note']                    = 'Note';
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

$txt['lp_block_areas']            = 'Actions';
$txt['lp_block_areas_subtext']    = 'Specify one or more areas (separate by comma) to display the block in:';
$txt['lp_block_areas_area_th']    = 'Area';
$txt['lp_block_areas_display_th'] = 'Display';
$txt['lp_block_areas_values']     = array(
	'everywhere',
	'on area <em>index.php?action</em>=<strong>custom_action</strong> (for example: portal,forum,search)',
	'on all portal pages',
	'on page <em>index.php?page</em>=<strong>alias</strong>',
	'in all boards',
	'only inside the board with identifier <strong>id</strong> (including all topics inside the board)',
	'in boards id1, id2, id3',
	'in boards id3, and id7',
	'in all topics',
	'only inside the topic with identifier <strong>id</strong>',
	'in topics id1, id2, id3',
	'in topics id3, and id7'
);

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
$txt['lp_pages_manage_all_pages']       = 'Here you can view all portal pages.';
$txt['lp_pages_manage_own_pages']       = 'Here you can view all your own portal pages.';
$txt['lp_pages_manage_tab_description'] = 'Use the corresponding button to add a new page.';
$txt['lp_pages_add']                    = 'Add page';
$txt['lp_pages_add_title']              = 'Adding page';
$txt['lp_pages_add_tab_description']    = 'Fill the page title. After that, you can change its type, use preview and save.';
$txt['lp_pages_edit_title']             = 'Editing page';
$txt['lp_pages_edit_tab_description']   = 'Make the necessary changes.';
$txt['lp_extra_pages']                  = 'Portal pages';
$txt['lp_search_pages']                 = 'Alias or title';
$txt['lp_page_types']                   = array('bbc' => 'BBC', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                   = 'Alias';
$txt['lp_page_alias_subtext']           = 'The page alias must begin with a Latin letter and consist of lowercase Latin letters, numbers, and underscore.';
$txt['lp_page_type']                    = 'Page type';
$txt['lp_page_description']             = 'Description';
$txt['lp_page_keywords']                = 'Keywords';
$txt['lp_page_keywords_only_unique']    = 'Only unique items can be added';
$txt['lp_page_keywords_enter_to_add']   = 'Press Enter to add <b>"${value}"</b>';
$txt['lp_page_publish_datetime']        = 'Date and time of publication';

$txt['lp_page_options'] = array(
	'show_author_and_date' => 'Show the author and creation date',
	'show_related_pages'   => 'Show related pages',
	'allow_comments'       => 'Allow comments'
);

// Tabs
$txt['lp_tab_content']          = 'Content';
$txt['lp_tab_seo']              = 'SEO';
$txt['lp_tab_access_placement'] = 'Access and placement';
$txt['lp_tab_appearance']       = 'Appearance';
$txt['lp_tab_tuning']           = 'Tuning';

// Import and Export
$txt['lp_pages_export']                  = 'Page export';
$txt['lp_pages_import']                  = 'Page import';
$txt['lp_pages_export_tab_description']  = 'Here you can export the selected pages to create a backup or for transfer them to another forum.';
$txt['lp_pages_import_tab_description']  = 'Here you can import previously saved portal pages from a backup.';
$txt['lp_blocks_export']                 = 'Block export';
$txt['lp_blocks_import']                 = 'Block import';
$txt['lp_blocks_export_tab_description'] = 'Here you can export the selected blocks to create a backup or for transfer them to another forum.';
$txt['lp_blocks_import_tab_description'] = 'Here you can import previously saved portal blocks from a backup.';
$txt['lp_export_run']                    = 'Export selection';
$txt['lp_import_run']                    = 'Run import';
$txt['lp_export_all']                    = 'Export all';

// Tags
$txt['lp_all_page_tags']          = 'All portal page tags';
$txt['lp_all_tags_by_key']        = 'All pages with the "%1$s" tag';
$txt['lp_no_selected_tag']        = 'The specified tag was not found.';
$txt['lp_no_tags']                = 'There is no tags yet.';
$txt['lp_keyword_column']         = 'Keyword';
$txt['lp_frequency_column']       = 'Frequency';
$txt['lp_sorting_label']          = 'Sort by';
$txt['lp_sort_by_created_desc']   = 'Creation date (new first)';
$txt['lp_sort_by_created']        = 'Creation date (old first)';
$txt['lp_sort_by_updated_desc']   = 'Update date (new first)';
$txt['lp_sort_by_updated']        = 'Update date (old first)';
$txt['lp_sort_by_author_desc']    = 'Author name (desc)';
$txt['lp_sort_by_author']         = 'Author name (asc)';
$txt['lp_sort_by_num_views_desc'] = 'Number of views (desc)';
$txt['lp_sort_by_num_views']      = 'Number of views (asc)';

// Related pages
$txt['lp_related_pages'] = 'Related pages';

// Comments
$txt['lp_comments']            = 'Comments';
$txt['lp_comment_placeholder'] = 'Leave a comment...';

// Comment alerts
$txt['alert_group_light_portal']           = LP_NAME;
$txt['alert_page_comment']                 = 'When my page gets a comment';
$txt['alert_new_comment_page_comment']     = '{member_link} left a comment {page_comment_new_comment}';
$txt['alert_page_comment_reply']           = 'When my comment gets a reply';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} left a reply on your comment {page_comment_reply_new_reply}';

// Errors
$txt['lp_page_not_found']             = 'Page not found!';
$txt['lp_page_not_activated']         = 'The requested page is disabled!';
$txt['lp_page_not_editable']          = 'You are not allowed to edit this page!';
$txt['lp_page_visible_but_disabled']  = 'The page is visible to you, but not activated!';
$txt['lp_block_not_found']            = 'Block not found!';
$txt['lp_post_error_no_title']        = 'The <strong>title</strong> field was not filled out. It is required.';
$txt['lp_post_error_no_alias']        = 'The <strong>alias</strong> field was not filled out. It is required.';
$txt['lp_post_error_no_valid_alias']  = 'The specified alias is not correct!';
$txt['lp_post_error_no_unique_alias'] = 'A page with this alias already exists!';
$txt['lp_post_error_no_content']      = 'The content not specified! It is required.';
$txt['lp_post_error_no_areas']        = 'The <strong>areas</strong> field was not filled out. It is required.';
$txt['lp_post_error_no_valid_areas']  = 'The <strong>areas</strong> field was set incorrectly!';
$txt['lp_addon_not_installed']        = 'Plugin %1$s not installed';
$txt['lp_wrong_import_file']          = 'Wrong file to import...';
$txt['lp_import_failed']              = 'Failed to import...';

// Who
$txt['lp_who_viewing_frontpage']       = 'Viewing <a href="%1$s">the portal frontpage</a>.';
$txt['lp_who_viewing_index']           = 'Viewing <a href="%1$s">the portal frontpage</a> or <a href="%2$s">the forum index</a>.';
$txt['lp_who_viewing_page']            = 'Viewing <a href="%1$s">the portal page</a>.';
$txt['lp_who_viewing_tags']            = 'Viewing <a href="%1$s">the portal page tags</a>.';
$txt['lp_who_viewing_the_tag']         = 'Viewing the page list with <a href="%1$s" class="bbc_link">%2$s</a> tag.';
$txt['lp_who_viewing_portal_settings'] = 'Viewing or changing <a href="%1$s">the portal settings</a>.';
$txt['lp_who_viewing_portal_blocks']   = 'Viewing <a href="%1$s">the portal blocks</a> in the admin area.';
$txt['lp_who_viewing_editing_block']   = 'Editing the portal block (#%1$d).';
$txt['lp_who_viewing_adding_block']    = 'Adding a block for the portal.';
$txt['lp_who_viewing_portal_pages']    = 'Viewing <a href="%1$s">the portal pages</a> in the admin area.';
$txt['lp_who_viewing_editing_page']    = 'Editing the portal page (#%1$d).';
$txt['lp_who_viewing_adding_page']     = 'Adding a page for the portal.';

// Permissions
$txt['permissiongroup_light_portal']                 = LP_NAME;
$txt['permissionname_light_portal_view']             = $txt['group_perms_name_light_portal_view']             = 'View the portal elements';
$txt['permissionname_light_portal_manage_blocks']    = $txt['group_perms_name_light_portal_manage_blocks']    = 'Manage blocks';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Manage own pages';
$txt['permissionname_light_portal_approve_pages']    = $txt['group_perms_name_light_portal_approve_pages']    = 'Post pages without approval';
$txt['permissionhelp_light_portal_view']             = 'Ability to view portal pages and blocks.';
$txt['permissionhelp_light_portal_manage_blocks']    = 'Access to manage portal blocks.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Access to manage own pages.';
$txt['permissionhelp_light_portal_approve_pages']    = 'Ability to post portal pages without approval.';
$txt['cannot_light_portal_view']                     = 'Sorry, you are not allowed to view the portal!';
$txt['cannot_light_portal_manage_blocks']            = 'Sorry, you are not allowed to manage blocks!';
$txt['cannot_light_portal_manage_own_pages']         = 'Sorry, you are not allowed to manage pages!';
$txt['cannot_light_portal_approve_pages']            = 'Sorry, you are not allowed to post pages without approval!';
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

// Credits
$txt['lp_used_components'] = 'The portal components';

// Debug info
$txt['lp_load_page_stats'] = 'The portal is loaded in %1$.3f seconds, with %2$d queries.';
