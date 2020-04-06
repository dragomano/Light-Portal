<?php

/**
 * .polish language file
 *
 * @package Light Portal
 * @author Adrek https://adrek.pl
 */

$txt['lp_portal'] = 'Portal';
$txt['lp_forum']  = 'Forum';

// Settings
$txt['lp_settings']                            = 'Ustawienia portalu';
$txt['lp_php_mysql_info']                      = 'Wersja modyfikacji: <strong>%1$s</strong>, Wersja PHP: <strong>%2$s</strong>, Wersja %3$s: <strong>%4$s</strong>.';
$txt['lp_new_version_is_available']            = 'Dostępna jest nowa wersja!';
$txt['lp_frontpage_title']                     = 'Tytuł strony głównej';
$txt['lp_frontpage_mode']                      = 'Strona główna portalu';
$txt['lp_frontpage_mode_set']                  = array('Wyłącz', 'Wybrana strona', 'Wszystkie wątki z wybranych działów', 'Wszystkie aktywne strony', 'Wybrane działy');
$txt['lp_frontpage_id']                        = 'Strona główna portalu';
$txt['lp_frontpage_boards']                    = 'Działy artykułów strony głównej';
$txt['lp_frontpage_layout']                    = 'Liczba kolumn artykułów';
$txt['lp_frontpage_layout_set']                = array('1', '2', '3', '4', '6');
$txt['lp_show_images_in_articles']             = 'Wyświetlaj obrazy z artykułów';
$txt['lp_subject_size']                        = 'Długość tytułów artykułów';
$txt['lp_teaser_size']                         = 'Długość podglądu artykułów';
$txt['lp_num_items_per_page']                  = 'Ilość artykułów na stronę';
$txt['lp_standalone']                          = 'Tryb portalu';
$txt['lp_standalone_help']                     = 'Wszystko poza portalem i ignorowanymi akcjami będzie wyłączone.';
$txt['lp_standalone_excluded_actions']         = 'Ignorowane akcje';
$txt['lp_standalone_excluded_actions_subtext'] = 'Wybierz akcje, które powinny być dostępne w trybie portalu.';
$txt['lp_show_tags_on_page']                   = 'Wyświetlaj słowa kluczowe na górze strony';
$txt['lp_show_comment_block']                  = 'Wyświetlaj blok komentarzy';
$txt['lp_show_comment_block_set']              = array('none' => 'Brak', 'default' => 'Zintegrowany');
$txt['lp_num_comments_per_page']               = 'Ilość komentarzy na stronę';
$txt['lp_page_editor_type_default']            = 'Rodzaj domyślnego edytora ';
$txt['lp_hide_blocks_in_admin_section']        = 'Ukryj aktywne bloki w centrum administracji';
$txt['lp_open_graph']                          = 'Open Graph';
$txt['lp_page_og_image']                       = 'Użyj obrazu z treści';
$txt['lp_page_og_image_set']                   = array('Brak', 'Pierwszy', 'Ostatni');
$txt['lp_page_itemprop_address']               = 'Adres Twojej organizacji';
$txt['lp_page_itemprop_phone']                 = 'Numer telefonu';
$txt['groups_light_portal_view']               = 'Kto może widzieć portal';
$txt['groups_light_portal_manage_blocks']      = 'Kto może zarządzać blokami';
$txt['groups_light_portal_manage_own_pages']   = 'Kto może zarządzać stronami';
$txt['lp_manage_permissions']                  = 'Uwaga: niektóre strony i bloki mogą zawierać szkodliwą zawartość HTML/PHP, przyznaj te uprawnienia tylko zaufanym użytkownikom!';
$txt['lp_extra_settings']                      = 'Dodatkowe ustawienia';

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
$txt['lp_block_content']                 = 'Zawartość';
$txt['lp_block_icon_cheatsheet']         = 'Ikony';
$txt['lp_block_type']                    = 'Typ bloku';
$txt['lp_block_priority']                = 'Priorytet';
$txt['lp_block_icon_type']               = 'Typ ikony';
$txt['lp_block_icon_type_set']           = array('fas' => 'Wypełnione', 'far' => 'Normalne', 'fab' => 'Marki');
$txt['lp_block_placement']               = 'Umieszczenie';
$txt['lp_block_placement_set']           = array(
	'header' => 'Nagłówek',
	'top'    => 'Wyśrodkowane (na górze)',
	'left'   => 'Lewa strona',
	'right'  => 'Prawa strona',
	'bottom' => 'Wyśrodkowane (na dole)',
	'footer' => 'Stopka'
);

$txt['lp_block_areas']         = 'Akcje';
$txt['lp_block_areas_subtext'] = 'Wybierz jedną lub kilka akcji do wyświetlania bloku (oddziel akcje przecinkami):<br>
<ul>
	<li><strong>all</strong> — wyświetl wszędzie</li>
	<li><strong>forum</strong> — wyświetl tylko na forum</li>
	<li><strong>portal</strong> — wyświetl tylko na portalu (włącznie ze stronami)</li>
	<li><strong>custom_action</strong> — wyświetl tylko na "własnej akcji" np. <em>index.php?action</em>=<strong>custom_action</strong></li>
	<li><strong>page=alias</strong> — wyświetl na stronie <em>index.php?page</em>=<strong>alias</strong></li>
</ul>';
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
$txt['lp_page_types']                   = array('bbc' => 'BBC', 'html' => 'HTML', 'php' => 'PHP');
$txt['lp_page_alias']                   = 'Alias';
$txt['lp_page_alias_subtext']           = 'Alias strony musi zaczynać się od litery, może zawierać małe litery, cyfry oraz podkreślenie.';
$txt['lp_page_content']                 = $txt['lp_block_content'];
$txt['lp_page_type']                    = 'Typ strony';
$txt['lp_page_description']             = 'Opis';
$txt['lp_page_keywords']                = 'Słowa kluczowe';
$txt['lp_permissions']                  = array('Wyświetl dla administratorów', 'Wyświetl dla gości', 'Wyświetl dla użytkowników', 'Wyświetl wszystkim');
$txt['lp_no_items']                     = 'Nic nie dodano.';

$txt['lp_page_options'] = array(
	'show_author_and_date' => 'Wyświetlaj datę oraz nazwę autora',
	'allow_comments'       => 'Włącz komentarze'
);

// Tags
$txt['lp_all_page_tags']    = 'Wszystkie tagi stron portalu';
$txt['lp_all_tags_by_key']  = 'Strony z tagiem "%1$s"';
$txt['lp_no_selected_tag']  = 'Nie znaleziono podanego tagu.';
$txt['lp_no_tags']          = 'Nie dodano żadnych tagów.';
$txt['lp_keyword_column']   = 'Słowa kluczowe';
$txt['lp_frequency_column'] = 'Częstotliwość';

// Comments
$txt['lp_comments']            = 'Komentarze';
$txt['lp_comment_placeholder'] = 'Dodaj komentarz...';

$txt['alert_group_light_portal']           = LP_NAME;
$txt['alert_page_comment']                 = 'Po otrzymaniu komentarza na stronie';
$txt['alert_new_comment_page_comment']     = '{member_link} napisał komentarz <a href="{comment_link}">{comment_title}</a>';
$txt['alert_page_comment_reply']           = 'Po otrzymaniu odpowiedzi na mój komentarz';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} napisał odpowiedź pod Twoim komentarzem <a href="{comment_link}">{comment_title}</a>';

// Errors
$txt['lp_page_not_found']             = 'Nie znaleziono strony!';
$txt['lp_page_not_activated']         = 'Strona jest wyłączona!';
$txt['lp_block_not_found']            = 'Nie znaleziono bloku!';
$txt['lp_post_error_no_title']        = 'Pole <strong>Tytuł</strong> nie zostało wypełnione.';
$txt['lp_post_error_no_alias']        = 'Pole <strong>Alias</strong> nie zostało wypełnione.';
$txt['lp_post_error_no_valid_alias']  = 'Podany alias nie jest poprawny!';
$txt['lp_post_error_no_unique_alias'] = 'Alias jest używany już przez inną stronę!';
$txt['lp_post_error_no_content']      = 'Nie wpisano zawartości!';
$txt['lp_post_error_no_areas']        = 'Pole <strong>Akcje</strong> nie zostało wypełnione.';
$txt['lp_page_not_editable']          = 'Nie posiadasz uprawnień do edytowania tej strony!';
$txt['lp_addon_not_installed']        = 'Wtyczka %1$s nie jest zainstalowana';

// Who
$txt['lp_who_viewing_frontpage'] = 'Przegląda <a href="%1$s">stronę główną portalu</a>.';
$txt['lp_who_viewing_page']      = 'Przegląda <a href="%1$s">stronę na portalu</a>.';
$txt['lp_who_viewing_tags']      = 'Przegląda <a href="%1$s">tagi stron portalu</a>.';
$txt['lp_who_viewing_the_tag']   = 'Przegląda listę stron z tagiem <a href="%1$s" class="bbc_link">%2$s</a>.';

// Permissions
$txt['permissiongroup_light_portal']                 = LP_NAME;
$txt['permissionname_light_portal_view']             = $txt['group_perms_name_light_portal_view']             = 'Oglądanie elementów portalu';
$txt['permissionname_light_portal_manage_blocks']    = $txt['group_perms_name_light_portal_manage_blocks']    = 'Zarządzanie blokami';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Zarządzanie własnymi stronami';
$txt['permissionhelp_light_portal_view']             = 'Możliwość do przeglądania portalu oraz stron.';
$txt['permissionhelp_light_portal_manage_blocks']    = 'Dostęp do zarządzania blokami.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Dostęp do zarządzania własnymi stronami portalu.';
$txt['cannot_light_portal_view']                     = 'Przepraszamy, nie posiadasz uprawnień do przeglądania portalu!';
$txt['cannot_light_portal_manage_blocks']            = 'Przepraszamy, nie posiadasz uprawnień do zarządzania blokami!';
$txt['cannot_light_portal_manage_own_pages']         = 'Przepraszamy, nie posiadasz uprawnień do zarządzania stronami!';
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
$txt['lp_comments_set'] = array('komentarz', 'komentarzy');

// Other units
$txt['lp_users_set']   = array('użytkownik', 'użytkowników');
$txt['lp_guests_set']  = array('gość', 'gości');
$txt['lp_spiders_set'] = array('bot', 'botów');
$txt['lp_hidden_set']  = array('ukryty', 'ukrytych');
$txt['lp_buddies_set'] = array('znajomy', 'znajomych');

// Copyrights
$txt['lp_credits']         = 'Autorzy';
$txt['lp_used_components'] = 'Składniki portalu';

// Script execution time and memory usage
$txt['lp_load_page_stats'] = 'Załadowano w %1$.3f sekund. Wykorzystana pamięć: %2$d ' . $txt['megabyte'] . '.';
