<?php

/**
 * Polish translation by Adrek (https://adrek.pl)
 *
 * @package Light Portal
 */

$txt['lp_portal'] = 'Portal';
$txt['lp_forum']  = 'Forum';

$txt['lp_new_version_is_available'] = 'Dostępna jest nowa wersja!';

$txt['lp_article']  = 'Artykuł';
$txt['lp_no_items'] = 'Nic nie zostało dodane.';
$txt['lp_example']  = 'Przykład: ';
$txt['lp_content']  = 'Zawartość';

// Settings
$txt['lp_settings']  = 'Ustawienia portalu';
$txt['lp_base']      = 'Ustawienia strony głównej i artykułów';
$txt['lp_base_info'] = 'Wersja modyfikacji: <strong>%1$s</strong>, Wersja PHP: <strong>%2$s</strong>, Wersja %3$s: <strong>%4$s</strong>.<br>One can discuss bugs and features of the portal at <a class="bbc_link" href="https://www.simplemachines.org/community/index.php?topic=572393.0">simplemachines.com</a>.<br>You can also <a class="bbc_link" href="https://www.patreon.com/bugo">become a sponsor on Patreon</a> or <a class="bbc_link" href="https://ko-fi.com/U7U41XD2G">buy a cup of coffee as a thank</a>.';

$txt['lp_frontpage_title']                = 'Tytuł strony głównej';
$txt['lp_frontpage_mode']                 = 'Strona główna portalu';
$txt['lp_frontpage_mode_set']             = array('Wyłącz', 'Wybrana strona', 'Wszystkie wątki z wybranych działów', 'Wszystkie aktywne strony', 'Wybrane działy');
$txt['lp_frontpage_alias']                = 'Strona główna portalu';
$txt['lp_frontpage_alias_subtext']        = 'Podaj alias strony.';
$txt['lp_frontpage_boards']               = 'Działy artykułów strony głównej';
$txt['lp_frontpage_card_alt_layout']      = 'Alternatywny układ elementów kart';
$txt['lp_frontpage_order_by_num_replies'] = 'First to display articles with the highest number of comments';
$txt['lp_frontpage_article_sorting']      = 'Sorting articles';
$txt['lp_frontpage_article_sorting_set']  = array('By the last comment (default)', 'By the date of creation (new first)', 'By the date of creation (old first)');
$txt['lp_frontpage_layout']               = 'Liczba kolumn artykułów';
$txt['lp_frontpage_layout_set']           = array('1 kolumna', '2 kolumny', '3 kolumny', '4 kolumny', '6 kolumn');
$txt['lp_show_images_in_articles']        = 'Wyświetlaj obrazy z artykułów';
$txt['lp_image_placeholder']              = 'Adres domyślnego obrazu';
$txt['lp_teaser_size']                    = 'Długość podglądu artykułów';
$txt['lp_num_items_per_page']             = 'Ilość artykułów na stronę';

$txt['lp_standalone_mode']     = $txt['lp_standalone_mode_title'] = 'Tryb portalu';
$txt['lp_standalone_url']      = 'Adres strony głównej w trybie portalu osobnym';
$txt['lp_standalone_url_help'] = 'Możesz ustawić własny adres strony głównej portalu (np., <strong>https://twojastrona/portal.php</strong>).<br>W tym przypadku strona główna forum pozostanie pod adresem <strong>https://twojastrona/index.php</strong>.<br><br>Skopiuj i wklej ten kod do pliku <em>portal.php</em>:<br><pre><code class="bbc_code">
require(dirname(__FILE__) . \'/SSI.php\');
<br>
Bugo\LightPortal\FrontPage::show();
<br>
obExit(true);</code></pre><br>
Wyłącz opcję "<strong>Włącz lokalne przechowywanie plików cookies</strong>" jeśli plik <em>portal.php</em> jest zlokalizowany poza katalogiem forum (Konserwacja => Ustawienia serwera => Ciasteczka i Sesje).';
$txt['lp_standalone_mode_disabled_actions']         = 'Wyłączone akcje';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Wybierz akcje, które powinny być wyłączone w trybie osobnym.';
$txt['lp_standalone_mode_disabled_actions_help']    = 'Na przykład, jeśli chcesz wyłączyć akcję wyszukiwania (index.php?action=<strong>search</strong>), dodaj w polu <strong>search</strong>.';

$txt['groups_light_portal_view']             = 'Kto może widzieć portal';
$txt['groups_light_portal_manage_blocks']    = 'Kto może zarządzać blokami';
$txt['groups_light_portal_manage_own_pages'] = 'Kto może zarządzać stronami';
$txt['groups_light_portal_approve_pages']    = 'Who can post the portal pages without approval';
$txt['lp_manage_permissions']                = 'Uwaga: niektóre strony i bloki mogą zawierać szkodliwą zawartość HTML/PHP, przyznaj te uprawnienia tylko zaufanym użytkownikom!';

$txt['lp_debug_and_caching']       = 'Debugowanie i pamięć podręczna';
$txt['lp_show_debug_info']         = 'Wyświetl czas ładowania portalu oraz ilość zapytań do bazy danych';
$txt['lp_show_debug_info_subtext'] = 'Informacja ta widoczna jest tylko dla administratorów!';
$txt['lp_cache_update_interval']   = 'Interwał aktualizacji pamięci podręcznej';

// Pages and blocks
$txt['lp_extra']      = 'Strony i bloki';
$txt['lp_extra_info'] = 'Tutaj znajdują się ogólne ustawienia stron i bloków.';

$txt['lp_show_tags_on_page']            = 'Wyświetlaj słowa kluczowe na górze strony';
$txt['lp_show_tags_as_articles']        = 'Display lists of articles with the same tag as cards';
$txt['lp_show_related_pages']           = 'Display related pages block';
$txt['lp_show_comment_block']           = 'Wyświetlaj blok komentarzy';
$txt['lp_disabled_bbc_in_comments']     = 'Dozwolone tagi BBC';
$txt['lp_show_comment_block_set']       = array('none' => 'Brak', 'default' => 'Zintegrowany');
$txt['lp_time_to_change_comments']      = 'Maximum time after commenting to allow edit';
$txt['lp_num_comments_per_page']        = 'Ilość komentarzy na stronę';
$txt['lp_page_editor_type_default']     = 'Rodzaj domyślnego edytora ';
$txt['lp_hide_blocks_in_admin_section'] = 'Ukryj aktywne bloki w centrum administracji';

$txt['lp_open_graph']                   = 'Open Graph';
$txt['lp_page_og_image']                = 'Użyj obrazu z treści';
$txt['lp_page_og_image_set']            = array('Brak', 'Pierwszy', 'Ostatni');
$txt['lp_page_itemprop_address']        = 'Adres Twojej organizacji';
$txt['lp_page_itemprop_phone']          = 'Numer telefonu';

$txt['lp_permissions'] = array('Pokaż administratorom', 'Pokaż gościom', 'Pokaż użytkownikom', 'Pokaż wszystkim');

// Panels
$txt['lp_panels']               = 'Panele';
$txt['lp_panels_info']          = 'Możesz spersolanizować szerokość niektórych panelów oraz kierunek bloków.<br><strong>%1$s</strong> wykorzystuje <a class="bbc_link" href="%2$s" target="_blank" rel="noopener">12 kolumnowy układ siatki</a> do wyświetlania bloków w 6 panelach.';
$txt['lp_swap_header_footer']   = 'Zamień miejscami nagłówek ze stopką';
$txt['lp_swap_left_right']      = 'Zamień miejscami panel lewy z prawym';
$txt['lp_swap_top_bottom']      = 'Zamień miejscami panele centralne';
$txt['lp_panel_layout_note']    = 'Zmień szerokość przeglądarki i sprawdź która klasa jest używana.';
$txt['lp_browser_width']        = 'Szerokość okna przeglądarki';
$txt['lp_used_class']           = 'Uzywana klasa';
$txt['lp_panel_layout_preview'] = 'Możesz ustawić ilość kolumn dla niektórych panelów w zależnośc iod szerokości okna przeglądarki.';
$txt['lp_left_panel_sticky']    = $txt['lp_right_panel_sticky'] = 'Sticky';
$txt['lp_panel_direction_note'] = 'Możesz zmienić kierunek bloków dla każdego panelu.';
$txt['lp_panel_direction']      = 'Kierunek bloków w panelach';
$txt['lp_panel_direction_set']  = array('Pionowy', 'Poziomy');

// Plugins
$txt['lp_plugins']      = 'Wtyczki';
$txt['lp_plugins_desc'] = 'Możesz włączyć lub wyłączyć dowolne wtyczki, niektóre z nich są również edytowalne!';
$txt['lp_plugins_info'] = 'Tutaj znajdują się zainstalowane wtyczki portalu.';

$txt['lp_plugins_hooks_types'] = array(
	'block'     => 'Blok',
	'editor'    => 'Edytor',
	'comment'   => 'Widget komentarzy',
	'parser'    => 'Parser zawartości',
	'article'   => 'Przetwarzanie artykułów',
	'frontpage' => 'The layout of the frontpage',
	'other'     => 'Inne'
);

// Actions
$txt['lp_title']        = 'Tytuł';
$txt['lp_actions']      = 'Akcje';
$txt['lp_action_on']    = 'Włącz';
$txt['lp_action_off']   = 'Wyłącz';
$txt['lp_action_clone'] = 'Klonuj';
$txt['lp_action_move']  = 'Przenieś';
$txt['lp_read_more']    = 'Czytaj dalej...';

// Blocks
$txt['lp_blocks']                        = 'Bloki';
$txt['lp_blocks_manage']                 = 'Zarządzaj blokami';
$txt['lp_blocks_manage_tab_description'] = 'Tutaj znajdują się wszystkie utworzone bloki. W celu dodania nowego bloku kliknij w odpowiednią opcję.';
$txt['lp_blocks_add']                    = 'Dodaj blok';
$txt['lp_blocks_add_title']              = 'Dodawanie nowego bloku';
$txt['lp_blocks_add_tab_description']    = 'Nie ma jeszcze zbyt wielu bloków, ale najbardziej uniwersalne już istnieją :)';
$txt['lp_blocks_add_instruction']        = 'Wybierz blok przez kliknięcie.';
$txt['lp_blocks_edit_title']             = 'Edytowanie bloku';
$txt['lp_blocks_edit_tab_description']   = $txt['lp_blocks_add_tab_description'];
$txt['lp_block_icon_cheatsheet']         = 'Ikony';
$txt['lp_block_type']                    = 'Typ bloku';
$txt['lp_block_priority']                = 'Priorytet';
$txt['lp_block_icon_type']               = 'Typ ikony';
$txt['lp_block_icon_type_set']           = array('fas' => 'Wypełnione', 'far' => 'Normalne', 'fab' => 'Marki');
$txt['lp_block_placement']               = 'Umieszczenie';
$txt['lp_block_placement_set']           = array(
	'header' => 'Nagłówek',
	'top'    => 'Centralny (na górze)',
	'left'   => 'Lewa strona',
	'right'  => 'Prawa strona',
	'bottom' => 'Centralny (na dole)',
	'footer' => 'Stopka'
);

$txt['lp_block_areas']            = 'Akcje';
$txt['lp_block_areas_subtext']    = 'Wybierz jedną lub kilka akcji do wyświetlania bloku (oddziel akcje przecinkami):';
$txt['lp_block_areas_area_th']    = 'Area';
$txt['lp_block_areas_display_th'] = 'Display';
$txt['lp_block_areas_values']     = array(
	'wszędzie',
	'tylko na "własnej akcji" np. <em>index.php?action</em>=<strong>custom_action</strong> (for example: portal,forum,search)',
	'on all portal pages',
	'na stronie <em>index.php?page</em>=<strong>alias</strong>',
	'we wszystkich działach',
	'tylko w dziale o wskazanym <strong>id</strong> (włączając w to wszystkie wątki w dziale)',
	'w działach id1, id2, id3',
	'w działach id3 i id7',
	'we wszystkich wątkach',
	'tylko w wątku o wskazanym <strong>id</strong>',
	'w wątkach id1, id2, id3',
	'w wątkach id3 i id7'
);

$txt['lp_block_title_class']   = 'Klasa CSS tytułu';
$txt['lp_block_title_style']   = 'Styl CSS tytułu';
$txt['lp_block_content_class'] = 'Klasa CSS zawartości';
$txt['lp_block_content_style'] = 'Styl CSS zawartości';

$txt['lp_block_types'] = array(
	'bbc'  => 'Własny BBC',
	'html' => 'Własny HTML',
	'php'  => 'Własny PHP'
);
$txt['lp_block_types_descriptions'] = array(
	'bbc'  => 'W tym bloku można wykorzystać kody BBC do tworzenia zawartości.',
	'html' => 'W tym bloku można wykorzystać kod HTML do tworzenia zawartości.',
	'php'  => 'W tym bloku można wykorzystać kod PHP do tworzenia zawartości.'
);

// Pages
$txt['lp_pages']                        = 'Strony';
$txt['lp_pages_manage']                 = 'Zarządzaj stronami';
$txt['lp_pages_manage_tab_description'] = 'Tutaj znajdują się wszystkie utworzone strony. W celu dodania nowej strony kliknij w odpowiednią opcję.';
$txt['lp_pages_add']                    = 'Dodaj stronę';
$txt['lp_pages_add_title']              = 'Dodawanie strony';
$txt['lp_pages_add_tab_description']    = 'Podaj nazwę strony i jej alias. Po tym będzie można zmienić jej typ i użyć podglądu.';
$txt['lp_pages_edit_title']             = 'Edytowanie strony';
$txt['lp_pages_edit_tab_description']   = $txt['lp_pages_add_tab_description'];
$txt['lp_extra_pages']                  = 'Strony portalu';
$txt['lp_search_pages']                 = 'Alias lub tytuł';
$txt['lp_page_types']                   = array('bbc' => 'BBC', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                   = 'Alias';
$txt['lp_page_alias_subtext']           = 'Alias strony musi zaczynać się od litery, może zawierać małe litery, cyfry oraz podkreślenie.';
$txt['lp_page_type']                    = 'Typ strony';
$txt['lp_page_description']             = 'Opis';
$txt['lp_page_keywords']                = 'Słowa kluczowe';
$txt['lp_page_keywords_only_unique']    = 'Only unique items can be added';
$txt['lp_page_keywords_enter_to_add']   = 'Press Enter to add <b>"${value}"</b>';
$txt['lp_page_publish_datetime']        = 'Data i czas publikacji';

$txt['lp_page_options'] = array(
	'show_author_and_date' => 'Wyświetlaj datę oraz nazwę autora',
	'show_related_pages'   => 'Show related pages',
	'allow_comments'       => 'Włącz komentarze'
);

// Tabs
$txt['lp_tab_content']          = 'Zawartość';
$txt['lp_tab_seo']              = 'SEO';
$txt['lp_tab_access_placement'] = 'Dostęp i rozmieszczenie';
$txt['lp_tab_appearance']       = 'Wygląd';
$txt['lp_tab_tuning']           = 'Tuning';

// Import and Export
$txt['lp_pages_export']                  = 'Eksport stron';
$txt['lp_pages_import']                  = 'Import stron';
$txt['lp_pages_export_tab_description']  = 'W tym miejscu możesz eksportować strony w celu wykonania kopii zapasowej lub w celu wykorzystania ich na innym forum.';
$txt['lp_pages_import_tab_description']  = 'W tym miejscu możesz importować wcześniej utworzone kopie zapasowe stron.';
$txt['lp_blocks_export']                 = 'Eksport bloków';
$txt['lp_blocks_import']                 = 'Import bloków';
$txt['lp_blocks_export_tab_description'] = 'W tym miejscu możesz eksportować bloki w celu wykonania kopii zapasowej lub w celu wykorzystania ich na innym forum.';
$txt['lp_blocks_import_tab_description'] = 'W tym miejscu możesz importować wcześniej utworzone kopie zapasowe bloków.';
$txt['lp_export_run']                    = 'Wybór eksportu';
$txt['lp_import_run']                    = 'Importuj';
$txt['lp_export_all']                    = 'Eksportuj wszystko';

// Tags
$txt['lp_all_page_tags']          = 'Wszystkie tagi stron portalu';
$txt['lp_all_tags_by_key']        = 'Strony z tagiem "%1$s"';
$txt['lp_no_selected_tag']        = 'Nie znaleziono podanego tagu.';
$txt['lp_no_tags']                = 'Nie dodano żadnych tagów.';
$txt['lp_keyword_column']         = 'Słowa kluczowe';
$txt['lp_frequency_column']       = 'Częstotliwość';
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
$txt['lp_comments']            = 'Komentarze';
$txt['lp_comment_placeholder'] = 'Dodaj komentarz...';

// Comment alerts
$txt['alert_group_light_portal']           = LP_NAME;
$txt['alert_page_comment']                 = 'Po otrzymaniu komentarza na stronie';
$txt['alert_new_comment_page_comment']     = '{member_link} napisał komentarz {page_comment_new_comment}';
$txt['alert_page_comment_reply']           = 'Po otrzymaniu odpowiedzi na mój komentarz';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} napisał odpowiedź pod Twoim komentarzem {page_comment_reply_new_reply}';

// Errors
$txt['lp_page_not_found']             = 'Nie znaleziono strony!';
$txt['lp_page_not_activated']         = 'The requested page is disabled!';
$txt['lp_page_not_editable']          = 'Nie posiadasz uprawnień do edytowania tej strony!';
$txt['lp_page_visible_but_disabled']  = 'The page is visible to you, but not activated!';
$txt['lp_block_not_found']            = 'Nie znaleziono bloku!';
$txt['lp_post_error_no_title']        = 'Pole <strong>Tytuł</strong> nie zostało wypełnione.';
$txt['lp_post_error_no_alias']        = 'Pole <strong>Alias</strong> nie zostało wypełnione.';
$txt['lp_post_error_no_valid_alias']  = 'Podany alias nie jest poprawny!';
$txt['lp_post_error_no_unique_alias'] = 'Alias jest używany już przez inną stronę!';
$txt['lp_post_error_no_content']      = 'Nie wpisano zawartości!';
$txt['lp_post_error_no_areas']        = 'Pole <strong>Akcje</strong> nie zostało wypełnione.';
$txt['lp_post_error_no_valid_areas']  = 'W polu <strong>Akcje</strong> podano niewłaściwą akcję!';
$txt['lp_addon_not_installed']        = 'Wtyczka %1$s nie jest zainstalowana';
$txt['lp_wrong_import_file']          = 'Nie można importować tego pliku...';
$txt['lp_import_failed']              = 'Wystąpił błąd podczas importowania...';

// Who
$txt['lp_who_viewing_frontpage']       = 'Przegląda <a href="%1$s">stronę główną portalu</a>.';
$txt['lp_who_viewing_index']           = 'Przegląda <a href="%1$s">stronę główną portalu</a> lub <a href="%2$s">stronę główną forum</a>.';
$txt['lp_who_viewing_page']            = 'Przegląda <a href="%1$s">stronę na portalu</a>.';
$txt['lp_who_viewing_tags']            = 'Przegląda <a href="%1$s">tagi stron portalu</a>.';
$txt['lp_who_viewing_the_tag']         = 'Przegląda listę stron z tagiem <a href="%1$s" class="bbc_link">%2$s</a>.';
$txt['lp_who_viewing_portal_settings'] = 'Przegląda lub zmienia <a href="%1$s">ustawienia portalu</a>.';
$txt['lp_who_viewing_portal_blocks']   = 'Przegląda <a href="%1$s">bloki portalu</a> w centrum administracji.';
$txt['lp_who_viewing_editing_block']   = 'Edytuje blok (#%1$d).';
$txt['lp_who_viewing_adding_block']    = 'Dodaje nowy blok.';
$txt['lp_who_viewing_portal_pages']    = 'Przegląda <a href="%1$s">strony portalu</a> w centrum administracji.';
$txt['lp_who_viewing_editing_page']    = 'Edytuje stronę (#%1$d).';
$txt['lp_who_viewing_adding_page']     = 'Dodaje nową stronę portalu.';

// Permissions
$txt['permissiongroup_light_portal']                 = LP_NAME;
$txt['permissionname_light_portal_view']             = $txt['group_perms_name_light_portal_view']             = 'Oglądanie elementów portalu';
$txt['permissionname_light_portal_manage_blocks']    = $txt['group_perms_name_light_portal_manage_blocks']    = 'Zarządzanie blokami';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Zarządzanie własnymi stronami';
$txt['permissionname_light_portal_approve_pages']    = $txt['group_perms_name_light_portal_approve_pages']    = 'Post pages without approval';
$txt['permissionhelp_light_portal_view']             = 'Możliwość do przeglądania portalu oraz stron.';
$txt['permissionhelp_light_portal_manage_blocks']    = 'Dostęp do zarządzania blokami.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Dostęp do zarządzania własnymi stronami portalu.';
$txt['permissionhelp_light_portal_approve_pages']    = 'Ability to post portal pages without approval.';
$txt['cannot_light_portal_view']                     = 'Przepraszamy, nie posiadasz uprawnień do przeglądania portalu!';
$txt['cannot_light_portal_manage_blocks']            = 'Przepraszamy, nie posiadasz uprawnień do zarządzania blokami!';
$txt['cannot_light_portal_manage_own_pages']         = 'Przepraszamy, nie posiadasz uprawnień do zarządzania stronami!';
$txt['cannot_light_portal_approve_pages']            = 'Sorry, you are not allowed to post pages without approval!';
$txt['cannot_light_portal_view_page']                = 'Przepraszamy, nie posiadasz uprawnień do przeglądania tej strony!';

// Time units
$txt['lp_days_set']       = array('dzień','dni');
$txt['lp_hours_set']      = array('godzina','godzin');
$txt['lp_minutes_set']    = array('minuta','minut');
$txt['lp_seconds_set']    = array('sekunda','sekund');
$txt['lp_tomorrow']       = '<strong>Jutro</strong> ';
$txt['lp_just_now']       = 'Przed chwilą';
$txt['lp_time_label_in']  = ' %1$s';
$txt['lp_time_label_ago'] = ' temu';

// Social units
$txt['lp_posts_set']    = array('wiadomość', 'wiadomości');
$txt['lp_replies_set']  = array('odpowiedź', 'odpowiedzi');
$txt['lp_views_set']    = array('wyświetleń', 'wyświetlenia');
$txt['lp_comments_set'] = array('komentarz', 'komentarze', 'komentarzy');

// Other units
$txt['lp_users_set']   = array('użytkownik', 'użytkowników');
$txt['lp_guests_set']  = array('gość', 'gości');
$txt['lp_spiders_set'] = array('bot', 'botów');
$txt['lp_hidden_set']  = array('ukryty', 'ukrytych');
$txt['lp_buddies_set'] = array('znajomy', 'znajomych');

// Credits
$txt['lp_used_components'] = 'Składniki portalu';

// Debug info
$txt['lp_load_page_stats'] = 'Załadowano w %1$.3f sekund. Zapytania do bazy danych: %2$d.';
