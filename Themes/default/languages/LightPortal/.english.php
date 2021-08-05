<?php

/**
 * .english language file
 *
 * @package Light Portal
 */

$txt['lp_portal'] = 'Portal';
$txt['lp_forum'] = 'Forum';

$txt['lp_new_version_is_available'] = 'A new version is available!';

$txt['lp_article'] = 'Article';
$txt['lp_no_items'] = 'There are no items to show.';
$txt['lp_example'] = 'Example: ';
$txt['lp_content'] = 'Content';
$txt['lp_my_pages'] = 'My pages';
$txt['lp_views'] = $txt['views'];
$txt['lp_replies'] = $txt['replies'];
$txt['lp_default'] = 'Default';
$txt['lp_sponsors_only'] = 'For sponsors of the portal';

// Settings
$txt['lp_settings'] = 'Portal settings';
$txt['lp_base'] = 'Settings for the frontpage and articles';
$txt['lp_base_info'] = 'The mod version: <strong>%1$s</strong>, PHP version: <strong>%2$s</strong>, %3$s version: <strong>%4$s</strong>.<br>One can discuss bugs and features of the portal at <a class="bbc_link" href="https://www.simplemachines.org/community/index.php?topic=572393.0">simplemachines.com</a>.<br>You can also <a class="bbc_link" href="https://ko-fi.com/U7U41XD2G">buy a cup of coffee as a thank</a>.';

$txt['lp_frontpage_title'] = 'The frontpage title';
$txt['lp_frontpage_mode'] = 'The portal frontpage';
$txt['lp_frontpage_mode_set'] = array('Disabled', 'Specified page', 'All pages from selected categories', 'Selected pages', 'All topics from selected boards', 'Selected topics', 'Selected boards');
$txt['lp_frontpage_alias'] = 'Portal page to display as the main page';
$txt['lp_frontpage_alias_subtext'] = 'Enter the alias of the page that exist.';
$txt['lp_frontpage_categories'] = 'Categories - sources of articles for the frontpage';
$txt['lp_select_categories_from_list'] = 'Select the desired categories';
$txt['lp_frontpage_boards'] = 'Boards - sources of articles for the frontpage';
$txt['lp_select_boards_from_list'] = 'Select the desired boards';
$txt['lp_frontpage_pages'] = 'Pages - sources of articles for the frontpage';
$txt['lp_frontpage_pages_subtext'] = 'IDs of the required pages, separated by commas.';
$txt['lp_frontpage_topics'] = 'Topics - sources of articles for the frontpage';
$txt['lp_frontpage_topics_subtext'] = 'IDs of the required topics, separated by commas.';
$txt['lp_show_images_in_articles'] = 'Show images that found in articles';
$txt['lp_show_images_in_articles_help'] = 'First, it checks whether the article has an attachment (if the article is based on a forum topic), then — whether the article has an IMG tag with an image.';
$txt['lp_image_placeholder'] = 'URL of the default placeholder image';
$txt['lp_frontpage_time_format'] = 'Time format in the article cards';
$txt['lp_frontpage_time_format_set'] = array('Full (LP style)', 'As in the forum', 'Own format');
$txt['lp_frontpage_custom_time_format'] = 'Own time format';
$txt['lp_frontpage_custom_time_format_help'] = 'See the list of possible parameters in the <a class="bbc_link" href="https://www.php.net/manual/en/datetime.format.php">documentation</a>.';
$txt['lp_show_teaser'] = 'Show the article summary';
$txt['lp_show_author'] = 'Show the article author';
$txt['lp_show_author_help'] = 'If the board card is displayed, it will be information about the category.';
$txt['lp_show_num_views_and_comments'] = 'Show the number of views and comments';
$txt['lp_frontpage_order_by_num_replies'] = 'First to display articles with the highest number of comments';
$txt['lp_frontpage_article_sorting'] = 'Sorting articles';
$txt['lp_frontpage_article_sorting_set'] = array('By the last comment', 'By the date of creation (new first)', 'By the date of creation (old first)', 'By the date of updation (fresh first)');
$txt['lp_frontpage_article_sorting_help'] = 'When you select the first option, the article cards display the dates and the latest commentators (if they available).';
$txt['lp_frontpage_layout'] = 'Template layout for article cards';
$txt['lp_frontpage_num_columns'] = 'Number of columns for displaying articles';
$txt['lp_frontpage_num_columns_set'] = array('1 column', '2 columns', '3 columns', '4 columns', '6 columns');
$txt['lp_show_pagination'] = 'Show the pagination';
$txt['lp_show_pagination_set'] = array('Bottom only', 'Top and bottom', 'Top only');
$txt['lp_use_simple_pagination'] = 'Use simple pagination';
$txt['lp_num_items_per_page'] = 'Number of items per page (for pagination)';

$txt['lp_standalone_mode_title'] = 'Standalone mode';
$txt['lp_standalone_url'] = 'The frontpage URL in the standalone mode';
$txt['lp_standalone_url_help'] = 'You can specify your own URL to display as the portal frontpage (for example, <strong>https://yourforum/portal.php</strong>).<br>In this case, the forum frontpage will remain available at <strong>https://yourforum/index.php</strong>.<br><br>As an example, the <em>portal.php</em> file is included with the portal — you can use it.<br><br>Disable the "<strong>Enable local storage of cookies</strong>" option if you want to place <em>portal.php</em> outside the forum directory (Maintenance => Server Settings => Cookies and Sessions).';
$txt['lp_standalone_mode_disabled_actions'] = 'Disabled actions';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Specify the areas that should be DISABLED in the standalone mode.';
$txt['lp_standalone_mode_disabled_actions_help'] = 'For example, if you need to disable the Search area (index.php?action=<strong>search</strong>), add <strong>search</strong> into the text field.';

$txt['groups_light_portal_view'] = 'Who can view the portal elements';
$txt['groups_light_portal_manage_blocks'] = 'Who can manage blocks';
$txt['groups_light_portal_manage_own_pages'] = 'Who can manage own pages';
$txt['groups_light_portal_approve_pages'] = 'Who can post the portal pages without approval';
$txt['lp_manage_permissions'] = 'Some pages may contain dangerous HTML/PHP content, so do not allow their creation to everyone!';

// Pages and blocks
$txt['lp_extra'] = 'Pages and blocks';
$txt['lp_extra_info'] = 'Here you can find general settings for pages and blocks.';

$txt['lp_show_page_permissions'] = 'Show information about the page permissions';
$txt['lp_show_page_permissions_subtext'] = 'Only those who have the permission to edit the page can see it.';
$txt['lp_show_tags_on_page'] = 'Show keywords at the top of the page';
$txt['lp_show_items_as_articles'] = 'Show items on tag/category pages as cards';
$txt['lp_show_related_pages'] = 'Show related pages block';
$txt['lp_show_comment_block'] = 'Show comments block';
$txt['lp_disabled_bbc_in_comments'] = 'Allowed BBC in comments';
$txt['lp_disabled_bbc_in_comments_subtext'] = 'You can use any tags <a class="bbc_link" href="%1$s">that allowed</a> on the forum.';
$txt['lp_show_comment_block_set'] = array('None', 'Integrated');
$txt['lp_time_to_change_comments'] = 'Maximum time after commenting to allow edit';
$txt['lp_num_comments_per_page'] = 'Number of parent comments per page';
$txt['lp_page_editor_type_default'] = 'The type of page editor by default';
$txt['lp_permissions_default'] = 'Permissions for pages and blocks by default';
$txt['lp_hide_blocks_in_admin_section'] = 'Hide active blocks in the admin area';

$txt['lp_schema_org'] = 'Schema microdata markup for contacts';
$txt['lp_page_og_image'] = 'Use an image from the page content';
$txt['lp_page_og_image_set'] = array('None', 'First found', 'Last found');
$txt['lp_page_itemprop_address'] = 'Address of your organization';
$txt['lp_page_itemprop_phone'] = 'Phone of your organization';

$txt['lp_permissions'] = array('Show to admins', 'Show to guests', 'Show to members', 'Show to everybody');

// Categories
$txt['lp_categories'] = 'Categories';
$txt['lp_categories_info'] = 'Here you can create and edit the portal categories for categorizing pages.<br>Simply drag a category to a new position to change the order.';
$txt['lp_categories_manage'] = 'Manage categories';
$txt['lp_categories_add'] = 'Add category';
$txt['lp_categories_desc'] = 'Description';
$txt['lp_category'] = 'Category';
$txt['lp_no_category'] = 'Uncategorized';
$txt['lp_all_categories'] = 'All categories of the portal';
$txt['lp_all_pages_with_category'] = 'All pages in category "%1$s"';
$txt['lp_all_pages_without_category'] = 'All pages without category';
$txt['lp_category_not_found'] = 'The specified category was not found.';
$txt['lp_no_categories'] = 'There are no categories yet.';
$txt['lp_total_pages_column'] = 'Total pages';

// Panels
$txt['lp_panels'] = 'Panels';
$txt['lp_panels_info'] = 'Here you can customize the width of some panels, as well as the direction of blocks.<br><strong>%1$s</strong> uses <a class="bbc_link" href="%2$s" target="_blank" rel="noopener">12 column grid system</a> to display blocks in 6 panels.';
$txt['lp_swap_header_footer'] = 'Swap the header and the footer';
$txt['lp_swap_left_right'] = 'Swap the left panel and the right panel';
$txt['lp_swap_top_bottom'] = 'Swap the center (top) and the center (bottom)';
$txt['lp_panel_layout_preview'] = 'Here you can set the number of columns for some panels, depending on the width of the browser window.';
$txt['lp_left_panel_sticky'] = $txt['lp_right_panel_sticky'] = 'Sticky';
$txt['lp_panel_direction_note'] = 'Here you can change the direction of blocks for each panel.';
$txt['lp_panel_direction'] = 'The direction of blocks in panels';
$txt['lp_panel_direction_set'] = array('Vertical', 'Horizontal');

// Misc
$txt['lp_misc'] = 'Miscellaneous';
$txt['lp_misc_info'] = 'There are additional portal settings that will be useful for template and plugin developers here.';
$txt['lp_debug_and_caching'] = 'Debugging and caching';
$txt['lp_show_debug_info'] = 'Show the loading time and number of the portal queries';
$txt['lp_show_debug_info_help'] = 'This information will be available to administrators only!';
$txt['lp_cache_update_interval'] = 'The cache update interval';

// Actions
$txt['lp_title'] = 'Title';
$txt['lp_actions'] = 'Actions';
$txt['lp_action_on'] = 'Enable';
$txt['lp_action_off'] = 'Disable';
$txt['lp_action_toggle'] = 'Toggle status';
$txt['lp_action_clone'] = 'Clone';
$txt['lp_action_move'] = 'Move';
$txt['lp_read_more'] = 'Read more...';
$txt['lp_save_and_exit'] = 'Save and Exit';

// Blocks
$txt['lp_blocks'] = 'Blocks';
$txt['lp_blocks_manage'] = 'Manage blocks';
$txt['lp_blocks_manage_description'] = 'All created portal blocks are listed here. To add a block, use the "+" button.';
$txt['lp_blocks_add'] = 'Add block';
$txt['lp_blocks_add_title'] = 'Adding a block';
$txt['lp_blocks_add_description'] = 'Blocks can contain any content, depending on their type.';
$txt['lp_blocks_add_instruction'] = 'Select the desired block by clicking on it.';
$txt['lp_blocks_edit_title'] = 'Editing block';
$txt['lp_blocks_edit_description'] = $txt['lp_blocks_add_description'];
$txt['lp_block_icon_cheatsheet'] = 'List of icons';
$txt['lp_block_type'] = 'Block type';
$txt['lp_block_note'] = 'Note';
$txt['lp_block_priority'] = 'Priority';
$txt['lp_block_icon_type'] = 'Icon type';
$txt['lp_block_icon_type_set'] = array('Solid', 'Regular', 'Brands');
$txt['lp_block_placement'] = 'Placement';
$txt['lp_block_placement_set'] = array('Header', 'Center (top)', 'Left side', 'Right side', 'Center (bottom)', 'Footer');

$txt['lp_block_areas'] = 'Actions';
$txt['lp_block_areas_subtext'] = 'Specify one or more areas (separate by comma) to display the block in:';
$txt['lp_block_areas_area_th'] = 'Area';
$txt['lp_block_areas_display_th'] = 'Display';
$txt['lp_block_areas_values'] = array(
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

$txt['lp_block_title_class'] = 'CSS title class';
$txt['lp_block_title_style'] = 'CSS title style';
$txt['lp_block_content_class'] = 'CSS content class';
$txt['lp_block_content_style'] = 'CSS content style';

// Internal blocks
$txt['lp_bbc']['title'] = 'Custom BBC';
$txt['lp_html']['title'] = 'Custom HTML';
$txt['lp_php']['title'] = 'Custom PHP';
$txt['lp_bbc']['description'] = 'In this block, any BBC tags of the forum can be used as content.';
$txt['lp_html']['description'] = 'In this block, you can use any HTML tags as content.';
$txt['lp_php']['description'] = 'In this block, you can use any PHP code as content.';

// Pages
$txt['lp_pages'] = 'Pages';
$txt['lp_pages_manage'] = 'Manage pages';
$txt['lp_pages_manage_all_pages'] = 'Here you can view all portal pages.';
$txt['lp_pages_manage_own_pages'] = 'Here you can view all your own portal pages.';
$txt['lp_pages_manage_description'] = 'Use the corresponding button to add a new page.';
$txt['lp_pages_add'] = 'Add page';
$txt['lp_pages_add_title'] = 'Adding a page';
$txt['lp_pages_add_description'] = 'Fill the page title. After that, you can change its type, use preview and save.';
$txt['lp_pages_edit_title'] = 'Editing page';
$txt['lp_pages_edit_description'] = 'Make the necessary changes.';
$txt['lp_pages_extra'] = 'Portal pages';
$txt['lp_pages_search'] = 'Alias or title';
$txt['lp_page_types']['bbc'] = 'BBC';
$txt['lp_page_types']['html'] = 'HTML';
$txt['lp_page_types']['php'] = 'PHP';
$txt['lp_page_alias'] = 'Alias';
$txt['lp_page_alias_subtext'] = 'The page alias must begin with a Latin letter and consist of lowercase Latin letters, numbers, and underscore.';
$txt['lp_page_type'] = 'Page type';
$txt['lp_page_description'] = 'Description';
$txt['lp_page_keywords'] = 'Keywords';
$txt['lp_page_keywords_placeholder'] = 'Select tags or add new';
$txt['lp_page_publish_datetime'] = 'Date and time of publication';
$txt['lp_page_author'] = 'Transfer of authorship';
$txt['lp_page_author_placeholder'] = 'Specify a username to transfer rights to the page';
$txt['lp_page_author_search_length'] = 'Please enter at least 3 characters';
$txt['lp_page_options'] = array('Show the author and creation date', 'Show related pages', 'Allow comments', 'Item in main menu');

// Tabs
$txt['lp_tab_content'] = 'Content';
$txt['lp_tab_seo'] = 'SEO';
$txt['lp_tab_access_placement'] = 'Access and placement';
$txt['lp_tab_appearance'] = 'Appearance';
$txt['lp_tab_menu'] = 'Menu';
$txt['lp_tab_tuning'] = 'Tuning';

// Import and Export
$txt['lp_pages_export'] = 'Page export';
$txt['lp_pages_import'] = 'Page import';
$txt['lp_pages_export_description'] = 'Here you can export the selected pages to create a backup or for transfer them to another forum.';
$txt['lp_pages_import_description'] = 'Here you can import previously saved portal pages from a backup.';
$txt['lp_blocks_export'] = 'Block export';
$txt['lp_blocks_import'] = 'Block import';
$txt['lp_blocks_export_description'] = 'Here you can export the selected blocks to create a backup or for transfer them to another forum.';
$txt['lp_blocks_import_description'] = 'Here you can import previously saved portal blocks from a backup.';
$txt['lp_export_run'] = 'Export selection';
$txt['lp_import_run'] = 'Run import';
$txt['lp_export_all'] = 'Export all';

// Plugins
$txt['lp_plugins'] = 'Plugins';
$txt['lp_plugins_manage'] = 'Manage plugins';
$txt['lp_plugins_manage_description'] = 'The installed portal plugins are listed here. You can always create a new one using <a class="bbc_link" href="%1$s" target="_blank" rel="noopener">the instructions</a>.';
$txt['lp_plugins_desc'] = 'Plugins extend the capabilities of the portal and its components, providing additional features that are not available in the core.';
$txt['lp_plugins_type_set'] = array('Block', 'Editor', 'Comment widget', 'Content parser', 'Processing articles', 'The layout of the frontpage', 'Import and export', 'Other');
$txt['lp_plugins_requires'] = 'Required plugins for work';

// Tags
$txt['lp_all_page_tags'] = 'All portal page tags';
$txt['lp_all_tags_by_key'] = 'All pages with the "%1$s" tag';
$txt['lp_tag_not_found'] = 'The specified tag was not found.';
$txt['lp_no_tags'] = 'There are no tags yet.';
$txt['lp_keyword_column'] = 'Keyword';
$txt['lp_frequency_column'] = 'Frequency';
$txt['lp_sorting_label'] = 'Sort by';
$txt['lp_sort_by_title_desc'] = 'Title (desc)';
$txt['lp_sort_by_title'] = 'Title (asc)';
$txt['lp_sort_by_created_desc'] = 'Creation date (new first)';
$txt['lp_sort_by_created'] = 'Creation date (old first)';
$txt['lp_sort_by_updated_desc'] = 'Update date (new first)';
$txt['lp_sort_by_updated'] = 'Update date (old first)';
$txt['lp_sort_by_author_desc'] = 'Author name (desc)';
$txt['lp_sort_by_author'] = 'Author name (asc)';
$txt['lp_sort_by_num_views_desc'] = 'Number of views (desc)';
$txt['lp_sort_by_num_views'] = 'Number of views (asc)';

// Related pages
$txt['lp_related_pages'] = 'Related pages';

// Comments
$txt['lp_comments'] = 'Comments';
$txt['lp_comment_placeholder'] = 'Leave a comment...';

// Comment alerts
$txt['alert_page_comment'] = 'When my page gets a comment';
$txt['alert_new_comment_page_comment'] = '{member_link} left a comment {page_comment_new_comment}';
$txt['alert_page_comment_reply'] = 'When my comment gets a reply';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} left a reply on your comment {page_comment_reply_new_reply}';

// Errors
$txt['lp_page_not_found'] = 'Page not found!';
$txt['lp_page_not_activated'] = 'The requested page is disabled!';
$txt['lp_page_not_editable'] = 'You are not allowed to edit this page!';
$txt['lp_page_visible_but_disabled'] = 'The page is visible to you, but not activated!';
$txt['lp_block_not_found'] = 'Block not found!';
$txt['lp_post_error_no_title'] = 'The <strong>title</strong> field was not filled out. It is required.';
$txt['lp_post_error_no_alias'] = 'The <strong>alias</strong> field was not filled out. It is required.';
$txt['lp_post_error_no_valid_alias'] = 'The specified alias is not correct!';
$txt['lp_post_error_no_unique_alias'] = 'A page with this alias already exists!';
$txt['lp_post_error_no_content'] = 'The content not specified! It is required.';
$txt['lp_post_error_no_areas'] = 'The <strong>areas</strong> field was not filled out. It is required.';
$txt['lp_post_error_no_valid_areas'] = 'The <strong>areas</strong> field was set incorrectly!';
$txt['lp_post_error_no_name'] = 'The <strong>name</strong> field was not filled out. It is required.';
$txt['lp_wrong_import_file'] = 'Wrong file to import...';
$txt['lp_import_failed'] = 'Failed to import...';
$txt['lp_wrong_template'] = 'Wrong template. Choose a template that matches the content.';
$txt['lp_addon_not_installed'] = 'Plugin %1$s is not installed';

// Who
$txt['lp_who_viewing_frontpage'] = 'Viewing <a href="%1$s">the portal frontpage</a>.';
$txt['lp_who_viewing_index'] = 'Viewing <a href="%1$s">the portal frontpage</a> or <a href="%2$s">the forum index</a>.';
$txt['lp_who_viewing_page'] = 'Viewing <a href="%1$s">the portal page</a>.';
$txt['lp_who_viewing_tags'] = 'Viewing <a href="%1$s">the portal page tags</a>.';
$txt['lp_who_viewing_the_tag'] = 'Viewing the page list with <a href="%1$s" class="bbc_link">%2$s</a> tag.';
$txt['lp_who_viewing_portal_settings'] = 'Viewing or changing <a href="%1$s">the portal settings</a>.';
$txt['lp_who_viewing_portal_blocks'] = 'Viewing <a href="%1$s">the portal blocks</a> in the admin area.';
$txt['lp_who_viewing_editing_block'] = 'Editing the portal block (#%1$d).';
$txt['lp_who_viewing_adding_block'] = 'Adding a block for the portal.';
$txt['lp_who_viewing_portal_pages'] = 'Viewing <a href="%1$s">the portal pages</a> in the admin area.';
$txt['lp_who_viewing_editing_page'] = 'Editing the portal page (#%1$d).';
$txt['lp_who_viewing_adding_page'] = 'Adding a page for the portal.';

// Permissions
$txt['permissionname_light_portal_view'] = $txt['group_perms_name_light_portal_view'] = 'View the portal elements';
$txt['permissionname_light_portal_manage_blocks'] = $txt['group_perms_name_light_portal_manage_blocks'] = 'Manage blocks';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Manage own pages';
$txt['permissionname_light_portal_approve_pages'] = $txt['group_perms_name_light_portal_approve_pages'] = 'Post pages without approval';
$txt['permissionhelp_light_portal_view'] = 'Ability to view portal pages and blocks.';
$txt['permissionhelp_light_portal_manage_blocks'] = 'Access to manage portal blocks.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Access to manage own pages.';
$txt['permissionhelp_light_portal_approve_pages'] = 'Ability to post portal pages without approval.';
$txt['cannot_light_portal_view'] = 'Sorry, you are not allowed to view the portal!';
$txt['cannot_light_portal_manage_blocks'] = 'Sorry, you are not allowed to manage blocks!';
$txt['cannot_light_portal_manage_own_pages'] = 'Sorry, you are not allowed to manage pages!';
$txt['cannot_light_portal_approve_pages'] = 'Sorry, you are not allowed to post pages without approval!';
$txt['cannot_light_portal_view_page'] = 'Sorry, you are not allowed to view this page!';

// Time units (see https://github.com/dragomano/Light-Portal/wiki/To-translators)
$txt['lp_days_set'] = 'day, days';
$txt['lp_hours_set'] = 'an hour, hours';
$txt['lp_minutes_set'] = 'a minute, minutes';
$txt['lp_seconds_set'] = 'second, seconds';
$txt['lp_tomorrow'] = '<strong>Tomorrow</strong> at ';
$txt['lp_just_now'] = 'Just now';
$txt['lp_time_label_in'] = 'In %1$s';
$txt['lp_time_label_ago'] = ' ago';

// Social units
$txt['lp_posts_set'] = 'post, posts';
$txt['lp_replies_set'] = 'reply, replies';
$txt['lp_views_set'] = 'view, views';
$txt['lp_comments_set'] = 'comment, comments';
$txt['lp_articles_set'] = 'article, articles';

// Other units
$txt['lp_users_set'] = 'user, users';
$txt['lp_guests_set'] = 'guest, guests';
$txt['lp_spiders_set'] = 'spider, spiders';
$txt['lp_hidden_set'] = 'hidden, hidden';
$txt['lp_buddies_set'] = 'buddy, buddies';

// Credits
$txt['lp_contributors'] = 'Contribution to the development of the portal';
$txt['lp_translators'] = 'Translators';
$txt['lp_testers'] = 'Testers';
$txt['lp_sponsors'] = 'Sponsors';
$txt['lp_used_components'] = 'The portal components';

// Debug info
$txt['lp_load_page_stats'] = 'The portal is loaded in %1$.3f seconds, with %2$d queries.';
