<?php

/**
 * @package Light Portal
 */

$txt['lp_portal'] = 'Portal';
$txt['lp_forum'] = 'Forum';

$txt['lp_article'] = 'Article';
$txt['lp_no_items'] = 'There are no items to show.';
$txt['lp_example'] = 'Example: ';
$txt['lp_content'] = 'Content';
$txt['lp_caution'] = 'Caution';
$txt['lp_my_pages'] = 'My pages';
$txt['lp_page_moderation'] = 'Page moderation';
$txt['lp_views'] = 'Views';
$txt['lp_replies'] = 'Replies';
$txt['lp_default'] = 'Default';
$txt['lp_can_donate'] = 'For sponsors';
$txt['lp_can_download'] = 'Can download';
$txt['lp_min_search_length'] = 'Please enter at least %d characters';
$txt['lp_no_such_members'] = 'Nobody here by that name';
$txt['lp_promote_to_fp'] = 'Promote to frontpage';
$txt['lp_remove_from_fp'] = 'Remove from frontpage';

// Settings
$txt['lp_settings'] = 'Portal settings';
$txt['lp_base'] = 'Settings for the frontpage and articles';
$txt['lp_base_info'] = 'The mod version: <strong>%1$s</strong>, PHP version: <strong>%2$s</strong>, %3$s version: <strong>%4$s</strong>.<br>One can discuss bugs and features of the portal at <a class="bbc_link" href="https://www.simplemachines.org/community/index.php?topic=572393.0">simplemachines.org</a>.<br>The portal always needs new testers, translators, and front-page template makers.';

$txt['lp_frontpage_title'] = 'The frontpage title';
$txt['lp_frontpage_mode'] = 'The portal frontpage';
$txt['lp_frontpage_mode_set'] = ['Disabled', 'Specified page', 'All pages from selected categories', 'Selected pages', 'All topics from selected boards', 'Selected topics', 'Selected boards'];
$txt['lp_frontpage_alias'] = 'Portal page to display as the main page';
$txt['lp_frontpage_categories'] = 'Categories - sources of articles for the frontpage';
$txt['lp_frontpage_categories_select'] = 'Select the desired categories';
$txt['lp_frontpage_boards'] = 'Boards - sources of articles for the frontpage';
$txt['lp_frontpage_boards_select'] = 'Select the desired boards';
$txt['lp_frontpage_pages'] = 'Pages - sources of articles for the frontpage';
$txt['lp_frontpage_pages_select'] = 'Select the desired pages';
$txt['lp_frontpage_pages_no_items'] = 'There are no active pages';
$txt['lp_frontpage_topics'] = 'Topics - sources of articles for the frontpage';
$txt['lp_frontpage_topics_select'] = 'Select the desired topics';
$txt['lp_frontpage_topics_no_items'] = 'There are no approved topics';
$txt['lp_show_images_in_articles'] = 'Show images found in articles';
$txt['lp_show_images_in_articles_help'] = 'First, it checks whether the article has an attachment (if the article is based on a forum topic), then — whether the article has an IMG tag with an image.';
$txt['lp_image_placeholder'] = 'URL of the default placeholder image';
$txt['lp_image_placeholder_subtext'] = 'Used if the option above is disabled.';
$txt['lp_show_teaser'] = 'Show the article summary';
$txt['lp_show_author'] = 'Show the article author';
$txt['lp_show_author_help'] = 'If the board\'s card is displayed, it will show information about the category instead of the author.';
$txt['lp_show_views_and_comments'] = 'Show the number of views and comments';
$txt['lp_frontpage_order_by_replies'] = 'Display articles with the highest number of comments first';
$txt['lp_frontpage_article_sorting'] = 'Sort articles';
$txt['lp_frontpage_article_sorting_set'] = ['By last comment', 'By posting date (new first)', 'By posting date (old first)', 'By last update (fresh first)'];
$txt['lp_frontpage_article_sorting_help'] = 'When you select the first option, the article cards display the dates and the latest commentators (if they are available).';
$txt['lp_frontpage_layout'] = 'Template layout for article cards';
$txt['lp_frontpage_num_columns'] = 'Number of columns for displaying articles';
$txt['lp_frontpage_num_columns_set'] = '{columns, plural, one {# column} other {# columns}}';
$txt['lp_show_pagination'] = 'Show the pagination';
$txt['lp_show_pagination_set'] = ['Bottom only', 'Top and bottom', 'Top only'];
$txt['lp_use_simple_pagination'] = 'Use simple pagination';
$txt['lp_num_items_per_page'] = 'Number of items per page (for pagination)';

$txt['lp_standalone_mode_title'] = 'Standalone mode';
$txt['lp_standalone_url'] = 'The frontpage URL in the standalone mode';
$txt['lp_standalone_url_help'] = 'You can specify your own URL to display as the portal frontpage (for instance, <strong>%1$s</strong>).<br>In this case, the forum frontpage will remain available at <strong>%2$s</strong>.<br><br>As an example, the <em>portal.php</em> file is included with the portal — you can use it.<br><br>Disable the "<strong>Enable local storage of cookies</strong>" option if you want to place <em>portal.php</em> outside the forum directory (Maintenance => Server Settings => Cookies and Sessions).';
$txt['lp_disabled_actions'] = 'Disabled actions';
$txt['lp_disabled_actions_subtext'] = 'Specify the areas that should be DISABLED in the standalone mode.';
$txt['lp_disabled_actions_help'] = 'For example, if you need to disable the Search area (index.php?action=<strong>search</strong>), add <strong>search</strong> into the text field.';

$txt['groups_light_portal_view'] = 'Who can view the portal elements — <span class="new_posts lp_type_other">Tourist</span>';
$txt['groups_light_portal_manage_pages_own'] = 'Who can manage own pages — <span class="new_posts lp_type_editor">Writer</span>';
$txt['groups_light_portal_manage_pages_any'] = 'Who can manage any pages — <span class="new_posts lp_type_article">Page Moderator</span>';
$txt['groups_light_portal_approve_pages'] = 'Who can post the portal pages without approval — <span class="new_posts lp_type_comment">The Chosen One</span>';

// Pages and blocks
$txt['lp_extra'] = 'Pages and blocks';
$txt['lp_extra_info'] = 'Here you can find general settings for pages and blocks.';

$txt['lp_show_tags_on_page'] = 'Show keywords at the top of the page';
$txt['lp_page_og_image'] = 'Use an image from the page content';
$txt['lp_page_og_image_set'] = ['None', 'First found', 'Last found'];
$txt['lp_show_prev_next_links'] = 'Show links to the previous and next pages';
$txt['lp_show_related_pages'] = 'Show related pages';
$txt['lp_show_comment_block'] = 'Show page comments';
$txt['lp_show_comment_block_set'] = ['None', 'Integrated'];
$txt['lp_time_to_change_comments'] = 'Maximum time after commenting to allow edit';
$txt['lp_num_comments_per_page'] = 'Number of parent comments per page';
$txt['lp_comment_sorting'] = 'Sort comments by default';
$txt['lp_show_items_as_articles'] = 'Show items on tag/category pages as cards';
$txt['lp_page_maximum_keywords'] = 'The maximum number of keywords that can be added to a page';
$txt['lp_permissions_default'] = 'Default permissions for pages and blocks';
$txt['lp_hide_blocks_in_acp'] = 'Hide active blocks in the admin area';
$txt['lp_fa_source_title'] = 'Using the FontAwesome icons';
$txt['lp_fa_source'] = 'Source for the FontAwesome library';
$txt['lp_fa_source_css_cdn'] = 'Connecting CSS from jsDelivr CDN';
$txt['lp_fa_source_css_local'] = 'Locally (all.min.css from the theme css folder)';
$txt['lp_fa_custom'] = 'Custom url to the FontAwesome library';
$txt['lp_fa_kit'] = 'Font Awesome Kit';
$txt['lp_permissions'] = ['Show to admins', 'Show to guests', 'Show to members', 'Show to everybody'];

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
$txt['lp_panel_layout_preview'] = 'Here you can set the number of columns for some panels, depending on the width of the browser window. The grid system divides the screen into 12 columns and you can mention how much width can be taken for components in mobile devices, tablets and desktops. The breakpoints for xs, sm, md, lg and xl are 576px, 768px, 992px and 1200px. You can see the difference by resizing the browser window of the page.';
$txt['lp_left_panel_sticky'] = $txt['lp_right_panel_sticky'] = 'Sticky';
$txt['lp_panel_direction_note'] = 'Here you can change the direction of blocks for each panel.';
$txt['lp_panel_direction'] = 'The direction of blocks in panels';
$txt['lp_panel_direction_set'] = ['Vertical', 'Horizontal'];

// Misc
$txt['lp_misc'] = 'Miscellaneous';
$txt['lp_misc_info'] = 'There are additional portal settings that will be useful for template and plugin developers here.';
$txt['lp_debug_and_caching'] = 'Debugging and caching';
$txt['lp_show_debug_info'] = 'Show the loading time and number of the portal queries';
$txt['lp_show_debug_info_help'] = 'This information will be available to administrators only!';
$txt['lp_cache_update_interval'] = 'The cache update interval';
$txt['lp_compatibility_mode'] = 'Compatibility mode';
$txt['lp_portal_action'] = 'The value of the <strong>action</strong> parameter of the portal';
$txt['lp_page_param'] = 'The <strong>page</strong> parameter for portal pages';
$txt['lp_weekly_cleaning'] = 'Weekly optimization of portal tables';

// Feedback
$txt['lp_feedback'] = 'Feedback';
$txt['lp_feedback_info'] = 'Here you can send your suggestions for improving the portal.';
$txt['lp_thanks'] = 'Thank you!';

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
$txt['lp_blocks_add_instruction'] = 'Select the desired block by clicking on it. If the block you need is not on the list, check if the <a class="bbc_link" href="%1$s">corresponding plugin</a> is enabled.';
$txt['lp_blocks_edit_title'] = 'Editing block';
$txt['lp_blocks_edit_description'] = $txt['lp_blocks_add_description'];
$txt['lp_block_type'] = 'Block type';
$txt['lp_block_note'] = 'Note';
$txt['lp_block_priority'] = 'Priority';
$txt['lp_block_placement'] = 'Placement';
$txt['lp_block_placement_select'] = 'Where should the block be displayed?';
$txt['lp_block_placement_set'] = ['Header', 'Center (top)', 'Left side', 'Right side', 'Center (bottom)', 'Footer'];

$txt['lp_block_areas'] = 'Display areas';
$txt['lp_block_areas_subtext'] = 'Specify one or more areas (separate by comma) to display the block in';
$txt['lp_block_areas_th'] = 'Custom areas';
$txt['lp_block_areas_values'][0] = 'on area <em>index.php?action</em>=<strong>custom_action</strong> (for example: <em>%1$s</em>)';
$txt['lp_block_areas_values'][1] = 'everywhere, except <strong>custom_action</strong> (used as an exception, in addition to the preset areas)';
$txt['lp_block_areas_values'][2] = 'on page <em>index.php?page</em>=<strong>alias</strong>';
$txt['lp_block_areas_values'][3] = 'only inside the board with identifier <strong>id</strong> (including all topics inside the board)';
$txt['lp_block_areas_values'][4] = 'in boards with identifiers 1, 2, 3';
$txt['lp_block_areas_values'][5] = 'in boards with identifiers 3, and 7';
$txt['lp_block_areas_values'][6] = 'only inside the topic with identifier <strong>id</strong>';
$txt['lp_block_areas_values'][7] = 'in topics with identifiers 1, 2, 3';
$txt['lp_block_areas_values'][8] = 'in topics with identifiers 3, and 7';
$txt['lp_block_areas_set']['all'] = 'Everywhere';
$txt['lp_block_areas_set']['home'] = 'Main page of the portal';
$txt['lp_block_areas_set']['portal'] = 'Portal area (action=portal)';
$txt['lp_block_areas_set']['forum'] = 'Main page of the forum';
$txt['lp_block_areas_set']['pages'] = 'All portal pages (pages)';
$txt['lp_block_areas_set']['boards'] = 'All boards and topics of the forum';
$txt['lp_block_areas_set']['topics'] = 'All topics of the forum';

$txt['lp_block_select_icon'] = 'Select icon';
$txt['lp_block_title_class'] = 'CSS title class';
$txt['lp_block_content_class'] = 'CSS content class';
$txt['lp_block_hide_header'] = 'Hide header';

// Default types
$txt['lp_bbc']['title'] = 'Custom BBCode';
$txt['lp_html']['title'] = 'Custom HTML';
$txt['lp_php']['title'] = 'Custom PHP';
$txt['lp_bbc']['description'] = 'Here you can use any allowed BBCode tags as content.';
$txt['lp_html']['description'] = 'Here you can use any HTML tags as content.';
$txt['lp_php']['description'] = 'Here you can use any PHP code as content.';

// Pages
$txt['lp_pages'] = 'Pages';
$txt['lp_pages_manage'] = 'Manage pages';
$txt['lp_pages_manage_all_pages'] = 'Here you can view all portal pages.';
$txt['lp_pages_manage_own_pages'] = 'Here you can view all your own portal pages.';
$txt['lp_pages_manage_description'] = 'Use the corresponding button to add a new page.';
$txt['lp_pages_unapproved'] = 'Unapproved pages';
$txt['lp_pages_unapproved_description'] = 'Here you can view all unapproved portal pages. Read them and decide whether to publish or not.';
$txt['lp_pages_internal'] = 'Internal pages';
$txt['lp_pages_internal_description'] = 'Here you can view all internal portal pages. They do not participate in general sampling and searching, but remain accessible by direct links.';
$txt['lp_pages_add'] = 'Add page';
$txt['lp_pages_add_title'] = 'Adding a page';
$txt['lp_pages_add_description'] = 'Pages can contain any content, depending on their type.';
$txt['lp_pages_add_instruction'] = 'Select the desired page type from the list below.';
$txt['lp_pages_edit_title'] = 'Editing page';
$txt['lp_pages_edit_description'] = 'Make the necessary changes.';
$txt['lp_pages_extra'] = 'Portal pages';
$txt['lp_pages_search'] = 'Alias or title';
$txt['lp_page_alias'] = 'Alias';
$txt['lp_page_alias_subtext'] = 'The page alias must begin with a Latin letter and consist of lowercase Latin letters, numbers, and underscores.';
$txt['lp_page_type'] = 'Page type';
$txt['lp_page_description'] = 'Description';
$txt['lp_page_keywords'] = 'Keywords';
$txt['lp_page_keywords_placeholder'] = 'Select tags or add new';
$txt['lp_page_keywords_empty'] = 'No tags yet';
$txt['lp_page_publish_datetime'] = 'Date and time of publication';
$txt['lp_page_author'] = 'Transfer of authorship';
$txt['lp_page_author_placeholder'] = 'Specify a username to transfer rights to the page';
$txt['lp_page_show_title'] = 'Show the title';
$txt['lp_page_show_author_and_date'] = 'Show the author and posting date';
$txt['lp_page_show_related_pages'] = 'Show related pages';
$txt['lp_page_allow_comments'] = 'Allow comments';
$txt['lp_page_show_in_menu'] = 'Show in the Main menu';
$txt['lp_page_status_set'] = ['Inactive', 'Active', 'Unapproved', 'Internal'];

// Modlog
$txt['modlog_ac_update_lp_page'] = 'Updated page "{page}"';
$txt['modlog_ac_remove_lp_page'] = 'Removed page "{page}"';

// Tabs
$txt['lp_tab_content'] = 'Content';
$txt['lp_tab_seo'] = 'SEO';
$txt['lp_tab_access_placement'] = 'Access and placement';
$txt['lp_tab_appearance'] = 'Appearance';
$txt['lp_tab_tuning'] = 'Tuning';

// Import and Export
$txt['lp_pages_export'] = 'Page export';
$txt['lp_pages_import'] = 'Page import';
$txt['lp_pages_export_description'] = 'Here you can export the selected pages to create a backup or transfer them to another forum.';
$txt['lp_pages_import_description'] = 'Here you can import previously saved portal pages from a backup.';
$txt['lp_pages_import_info'] = 'Existing pages with the same identifiers will be overwritten by the pages from the imported file.';
$txt['lp_blocks_export'] = 'Block export';
$txt['lp_blocks_import'] = 'Block import';
$txt['lp_blocks_export_description'] = 'Here you can export the selected blocks to create a backup or transfer them to another forum.';
$txt['lp_blocks_import_description'] = 'Here you can import previously saved portal blocks from a backup.';
$txt['lp_blocks_import_info'] = 'Existing blocks with the same identifiers will be overwritten with the blocks from the imported file.';
$txt['lp_plugins_export'] = 'Plugin export';
$txt['lp_plugins_import'] = 'Plugin import';
$txt['lp_plugins_export_description'] = 'Here you can export the selected plugins to create a backup or transfer them to another forum.';
$txt['lp_plugin_name'] = 'Name';
$txt['lp_plugins_import_description'] = 'Here you can import archived portal plugins.';
$txt['lp_plugins_import_info'] = 'Existing plugins will be overwritten with the plugins from the imported archive.';
$txt['lp_plugins_import_success'] = 'Import of plugins successfully completed';
$txt['lp_export_selection'] = 'Export selection';
$txt['lp_import_selection'] = 'Import selection';
$txt['lp_import_run'] = 'Run import';
$txt['lp_export_all'] = 'Export all';
$txt['lp_import_all'] = 'Import all';
$txt['lp_import_success'] = 'Imported: %1$s';

// Plugins
$txt['lp_plugins'] = 'Plugins';
$txt['lp_plugins_manage'] = 'Manage plugins';
$txt['lp_plugins_manage_description'] = 'The installed portal plugins are listed here. You can always create a new one using <a class="bbc_link" href="%1$s" target="_blank" rel="noopener">the instructions</a>.';
$txt['lp_plugins_desc'] = 'Plugins extend the capabilities of the portal and its components, providing additional features that are not available in the core.';
$txt['lp_plugins_types'][0] = 'Block';
$txt['lp_plugins_types'][1] = 'SSI';
$txt['lp_plugins_types'][2] = 'Editor';
$txt['lp_plugins_types'][3] = 'Comment widget';
$txt['lp_plugins_types'][4] = 'Content parser';
$txt['lp_plugins_types'][5] = 'Processing articles';
$txt['lp_plugins_types'][6] = 'The layout of the frontpage';
$txt['lp_plugins_types'][7] = 'Import and export';
$txt['lp_plugins_types'][8] = 'Block options';
$txt['lp_plugins_types'][9] = 'Page options';
$txt['lp_plugins_types'][10] = 'Icons';
$txt['lp_plugins_types'][11] = 'SEO';
$txt['lp_plugins_types'][12] = 'Other';
$txt['lp_plugins_select'] = 'Make a choice';

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
$txt['lp_sort_by_created_desc'] = 'Posting date (new first)';
$txt['lp_sort_by_created'] = 'Posting date (old first)';
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
$txt['lp_comments_title'] = 'No comments | One comment | {count} comments';
$txt['lp_comment_placeholder'] = 'Leave a comment...';

// Alerts
$txt['alert_page_comment'] = 'When my page gets a comment';
$txt['alert_new_comment_page_comment'] = '{gender, select,
	female {{member_link} left a comment {content_subject}}
	male   {{member_link} left a comment {content_subject}}
	other  {{member_link} left a comment {content_subject}}
}';
$txt['alert_page_comment_reply'] = 'When my comment gets a reply';
$txt['alert_new_reply_page_comment_reply'] = '{gender, select,
	female {{member_link} left a reply to your comment {content_subject}}
	male   {{member_link} left a reply to your comment {content_subject}}
	other  {{member_link} left a reply to your comment {content_subject}}
}';
$txt['alert_page_unapproved'] = 'When a new unapproved page appears';
$txt['alert_new_page_page_unapproved'] = '{gender, select,
	female {{member_link} created a page {content_subject}}
	male   {{member_link} created a page {content_subject}}
	other  {{member_link} created a page {content_subject}}
}';

// Emails
$txt['page_unapproved_subject'] = 'New page from {MEMBERNAME}';
$txt['page_unapproved_body'] = 'There is a new page from {MEMBERNAME} on the portal, check it out.

Member: {PROFILELINK}
Page: {PAGELINK}

{REGARDS}';

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
$txt['lp_addon_not_activated'] = 'Plugin %1$s is not activated';

// Who
$txt['lp_who_viewing_frontpage'] = 'Viewing <a href="%1$s">the portal frontpage</a>.';
$txt['lp_who_viewing_index'] = 'Viewing <a href="%1$s">the portal frontpage</a> or <a href="%2$s">the forum index</a>.';
$txt['lp_who_viewing_page'] = 'Viewing <a href="%1$s">the portal page</a>.';
$txt['lp_who_viewing_tags'] = 'Viewing <a href="%1$s">the portal page tags</a>.';
$txt['lp_who_viewing_the_tag'] = 'Viewing the page list with <a href="%1$s" class="bbc_link">%2$s</a> tag.';
$txt['lp_who_viewing_categories'] = 'Viewing <a href="%1$s">the portal page categories</a>.';
$txt['lp_who_viewing_the_category'] = 'Viewing the page list within <a href="%1$s" class="bbc_link">%2$s</a> category.';

// Permissions
$txt['permissionname_light_portal_view'] = $txt['group_perms_name_light_portal_view'] = 'View the portal elements';
$txt['permissionhelp_light_portal_view'] = 'Ability to view portal pages and blocks.';
$txt['permissionname_light_portal_manage_pages'] = $txt['group_perms_name_light_portal_manage_pages'] = 'Manage pages';
$txt['permissionhelp_light_portal_manage_pages'] = 'Access to manage the portal pages. The <strong>Page Moderator</strong> has access to all portal pages via the menu <em>Moderate - Unapproved pages</em>.';
$txt['permissionname_light_portal_approve_pages'] = $txt['group_perms_name_light_portal_approve_pages'] = 'Post pages without approval';
$txt['permissionhelp_light_portal_approve_pages'] = 'Ability to post portal pages without approval.';
$txt['permissionname_light_portal_manage_pages_own'] = $txt['group_perms_name_light_portal_manage_pages_own'] = 'Manage own pages';
$txt['permissionhelp_light_portal_manage_pages_own'] = 'Access to manage own pages.';
$txt['permissionname_light_portal_manage_pages_any'] = $txt['group_perms_name_light_portal_manage_pages_any'] = 'Manage any pages (moderation)';
$txt['permissionhelp_light_portal_manage_pages_any'] = 'Ability to create and edit any pages on the portal.';
$txt['cannot_light_portal_view'] = 'You aren\'t allowed to view the portal!';
$txt['cannot_light_portal_manage_pages'] = 'You aren\'t allowed to manage pages!';
$txt['cannot_light_portal_approve_pages'] = 'You aren\'t allowed to post pages without approval!';
$txt['cannot_light_portal_view_page'] = 'You aren\'t allowed to view this page!';

// Time units
$txt['lp_months_set'] = '{months, plural, one {a month} other {# months}}';
$txt['lp_days_set'] = '{days, plural, one {a day} other {# days}}';
$txt['lp_hours_set'] = '{hours, plural, one {an hour} other {# hours}}';
$txt['lp_minutes_set'] = '{minutes, plural, one {a minute} other {# minutes}}';
$txt['lp_seconds_set'] = '{seconds, plural, one {a second} other {# seconds}}';
$txt['lp_tomorrow'] = '<strong>Tomorrow</strong> at ';
$txt['lp_just_now'] = 'Just now';
$txt['lp_time_label_in'] = 'In %1$s';
$txt['lp_time_label_ago'] = ' ago';

// Social units
$txt['lp_replies_set'] = '{replies, plural, one {# reply} other {# replies}}';
$txt['lp_views_set'] = '{views, plural, one {# view} other {# views}}';
$txt['lp_comments_set'] = '{comments, plural, one {# comment} other {# comments}}';
$txt['lp_articles_set'] = '{articles, plural, one {# article} other {# articles}}';

// Other units
$txt['lp_pages_set'] = '{pages, plural, one {# page} other {# pages}}';
$txt['lp_blocks_set'] = '{blocks, plural, one {# block} other {# blocks}}';
$txt['lp_users_set'] = '{users, plural, one {# user} other {# users}}';
$txt['lp_guests_set'] = '{guests, plural, one {# guest} other {# guests}}';
$txt['lp_spiders_set'] = '{spiders, plural, one {# spider} other {# spiders}}';
$txt['lp_hidden_set'] = '{hidden, plural, one {# hidden} other {# hidden}}';
$txt['lp_buddies_set'] = '{buddies, plural, one {# buddy} other {# buddies}}';

// Credits
$txt['lp_contributors'] = 'Contribution to the development of the portal';
$txt['lp_translators'] = 'Translators';
$txt['lp_testers'] = 'Testers';
$txt['lp_sponsors'] = 'Sponsors';
$txt['lp_tools'] = 'Tools';
$txt['lp_used_components'] = 'The portal components';

// Debug info
$txt['lp_load_page_stats'] = 'The portal is loaded in %1$.3f seconds, with %2$d queries.';
