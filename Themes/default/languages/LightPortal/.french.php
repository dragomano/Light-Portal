<?php

/**
 * .french language file Rev. 2.0
 * French translation https://www.atelier-pro-concept.fr/ Copyright 2008-2021
 * @package Light Portal
 */

$txt['lp_portal'] = 'Portail';
$txt['lp_forum'] = 'Forum';

$txt['lp_new_version_is_available'] = 'Une nouvelle version est disponible!';

$txt['lp_article'] = 'Article';
$txt['lp_no_items'] = 'Il n\'y a aucun élément à afficher.';
$txt['lp_example'] = 'Exemple: ';
$txt['lp_content'] = 'Contenu';
$txt['lp_my_pages'] = 'Mes pages';
$txt['lp_views'] = $txt['vues'];
$txt['lp_replies'] = $txt['réponses'];
$txt['lp_default'] = 'Page par défaut';
$txt['lp_sponsors_only'] = 'For sponsors of the portal';

// Settings
$txt['lp_settings'] = 'Paramètres du portail';
$txt['lp_base'] = 'Paramètres de la page d\'accueil et des articles';
$txt['lp_base_info'] = 'La version mod: <strong>%1$s</strong>, version PHP: <strong>%2$s</strong>, %3$s version: <strong>%4$s</strong>.<br>On peut discuter des bogues et des fonctionnalités du portail à <a class="bbc_link" href="https://www.simplemachines.org/community/index.php?topic=572393.0">simplemachines.com</a>.<br>Vous pouvez également <a class="bbc_link" href="https://ko-fi.com/U7U41XD2G">acheter une tasse de café en guise de remerciement</a>.';

$txt['lp_frontpage_title'] = 'Le titre de la page d\'accueil';
$txt['lp_frontpage_mode'] = 'La page d\'accueil du portail';
$txt['lp_frontpage_mode_set'] = array('Désactivée', 'Page spécifiée', 'Toutes les pages des catégories sélectionnées', 'Pages sélectionnées', 'Tous les sujets des tableaux sélectionnés', 'Sujets sélectionnés', 'Sections sélectionnées');
$txt['lp_frontpage_alias'] = 'Page du portail à afficher comme page principale';
$txt['lp_frontpage_alias_subtext'] = 'Entrez l\'alias de la page qui existe.';
$txt['lp_frontpage_categories'] = 'Catégories - sources d\'articles pour la page d\'accueil';
$txt['lp_select_categories_from_list'] = 'Sélectionnez les catégories souhaitées';
$txt['lp_frontpage_boards'] = 'Sections du forum - sources d\'articles pour la page d\'accueil';
$txt['lp_select_boards_from_list'] = 'Sélectionnez les sections souhaitées';
$txt['lp_frontpage_pages'] = 'Pages - sources d\'articles pour la page d\'accueil';
$txt['lp_frontpage_pages_subtext'] = 'ID des pages requises, séparés par des virgules.';
$txt['lp_frontpage_topics'] = 'Sujets - sources d\'articles pour la page d\'accueil';
$txt['lp_frontpage_topics_subtext'] = 'ID des sujets requis, séparés par des virgules.';
$txt['lp_show_images_in_articles'] = 'Afficher les images trouvées dans les articles';
$txt['lp_show_images_in_articles_help'] = 'Tout d\'abord, il vérifie si l\'article a une pièce jointe (si l\'article est basé sur un sujet de forum), puis - si l\'article a une balise IMG avec une image.';
$txt['lp_image_placeholder'] = 'URL de l\'image d\'espace réservé par défaut';
$txt['lp_frontpage_time_format'] = 'Format de l\'heure dans les fiches article';
$txt['lp_frontpage_time_format_set'] = array('Complet (style LP)', 'Comme dans le forum', 'Format propre');
$txt['lp_frontpage_custom_time_format'] = 'Format d\'heure personnalisée';
$txt['lp_frontpage_custom_time_format_help'] = 'Voir la liste des paramètres possibles dans la <a class="bbc_link" href="https://www.php.net/manual/en/datetime.format.php">documentation</a>.';
$txt['lp_show_teaser'] = 'Afficher un extrait de l\'article';
$txt['lp_show_author'] = 'Afficher l\'auteur de l\'article';
$txt['lp_show_author_help'] = 'Si la section du tableau est affichée, ce sera des informations sur la catégorie.';
$txt['lp_show_num_views_and_comments'] = 'Afficher le nombre de vues et de commentaires';
$txt['lp_frontpage_order_by_num_replies'] = 'Premier à afficher les articles avec le plus grand nombre de commentaires';
$txt['lp_frontpage_article_sorting'] = 'Classer les articles';
$txt['lp_frontpage_article_sorting_set'] = array('Par le dernier commentaire', 'À la date de création (nouveau premier)', 'À la date de création (ancien premier)', 'À la date de mise à jour (nouveau premier)');
$txt['lp_frontpage_article_sorting_help'] = 'Lorsque vous sélectionnez la première option, les fiches d\'articles affichent les dates et les derniers commentateurs (s\'ils sont disponibles).';
$txt['lp_frontpage_layout'] = 'Mise en page de modèle pour les fiches d\'articles';
$txt['lp_frontpage_num_columns'] = 'Nombre de colonnes d\'affichage des articles';
$txt['lp_frontpage_num_columns_set'] = array('1 colonne', '2 colonnes', '3 colonnes', '4 colonnes', '6 colonnes');
$txt['lp_num_items_per_page'] = 'Nombre d\'éléments par page (pour la pagination)';

$txt['lp_standalone_mode_title'] = 'Mode autonome';
$txt['lp_standalone_url'] = 'L\'URL de la page d\'accueil en mode autonome';
$txt['lp_standalone_url_help'] = 'Vous pouvez spécifier votre propre URL à afficher en tant que page d\'accueil du portail ((par exemple, <strong>https://yourforum/portal.php</strong>).<br>Dans ce cas, la page d\'accueil du forum restera disponible à l\'adresse <strong>https://yourforum/index.php</strong>.<br><br>A titre d\'exemple, le <em>portal.php</em> Le fichier est inclus avec le portail - vous pouvez l\'utiliser.<br><br>Désactivez le"<strong>Activer le stockage local des cookies</strong>" option si vous souhaitez placer <em>portal.php</em> en dehors du répertoire du forum (Maintenance => Paramètres du serveur => Cookies et sessions).';
$txt['lp_standalone_mode_disabled_actions'] = 'Actions désactivées';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Spécifiez les zones qui doivent être DÉSACTIVÉES en mode autonome.';
$txt['lp_standalone_mode_disabled_actions_help'] = 'Par exemple, si vous devez désactiver la zone de recherche (index.php?action=<strong>recherche</strong>), ajouter <strong>recherche</strong> dans le champ de texte.';

$txt['groups_light_portal_view'] = 'Qui peut afficher les éléments du portail';
$txt['groups_light_portal_manage_blocks'] = 'Qui peut gérer les blocs';
$txt['groups_light_portal_manage_own_pages'] = 'Qui peut gérer les pages';
$txt['groups_light_portal_approve_pages'] = 'Qui peut publier les pages du portail sans approbation';
$txt['lp_manage_permissions'] = 'Certaines pages peuvent contenir du contenu HTML / PHP dangereux, alors n\'autorisez pas leur création à tout le monde!';

// Pages and blocks
$txt['lp_extra'] = 'Pages et blocs';
$txt['lp_extra_info'] = 'Vous trouverez ici les paramètres généraux des pages et des blocs.';

$txt['lp_show_page_permissions'] = 'Afficher des informations sur les autorisations de la page';
$txt['lp_show_page_permissions_subtext'] = 'Seuls ceux qui ont l\'autorisation de modifier la page peuvent la voir.';
$txt['lp_show_tags_on_page'] = 'Afficher les mots-clés en haut de la page';
$txt['lp_show_items_as_articles'] = 'Afficher les éléments sur les pages de balises / catégories sous forme de cartes';
$txt['lp_show_related_pages'] = 'Afficher le bloc de pages associées';
$txt['lp_show_comment_block'] = 'Afficher le bloc de commentaires';
$txt['lp_disabled_bbc_in_comments'] = 'Autorisé BBC dans les commentaires';
$txt['lp_disabled_bbc_in_comments_subtext'] = 'Vous pouvez utiliser n\'importe quelle balise<a class="bbc_link" href="%1$s">autorisées</a> sur le forum.';
$txt['lp_show_comment_block_set'] = array('Rien', 'Intégré');
$txt['lp_time_to_change_comments'] = 'Temps maximum après le commentaire pour autoriser la modification';
$txt['lp_num_comments_per_page'] = 'Nombre de commentaires par page';
$txt['lp_page_editor_type_default'] = 'Le type d\'éditeur de page par défaut';
$txt['lp_permissions_default'] = 'Autorisations pour les pages et les blocs par défaut';
$txt['lp_hide_blocks_in_admin_section'] = 'Masquer les blocs actifs dans la zone d\'administration';

$txt['lp_schema_org'] = 'Balisage des microdonnées de schéma pour les contacts';
$txt['lp_page_og_image'] = 'Utiliser une image du contenu de la page';
$txt['lp_page_og_image_set'] = array('Aucun', 'Premier trouvé', 'Dernier trouvé');
$txt['lp_page_itemprop_address'] = 'Adresse de votre organisation';
$txt['lp_page_itemprop_phone'] = 'Téléphone de votre organisation';

$txt['lp_permissions'] = array('Montrer aux administrateurs', 'Montrer aux invités', 'Montrer aux membres', 'Montrer à tout le monde');

// Categories
$txt['lp_categories'] = 'Catégories';
$txt['lp_categories_info'] = 'Ici, vous pouvez créer et modifier les catégories du portail pour classer les pages. <br> Faites simplement glisser une catégorie vers une nouvelle position pour modifier l\'ordre.';
$txt['lp_categories_manage'] = 'Gérer les catégories';
$txt['lp_categories_add'] = 'Ajouter une catégorie';
$txt['lp_categories_desc'] = 'Description';
$txt['lp_category'] = 'Catégorie';
$txt['lp_no_category'] = 'Non classé';
$txt['lp_all_categories'] = 'Toutes les catégories du portail';
$txt['lp_all_pages_with_category'] = 'Toutes les pages de la catégorie "%1$s"';
$txt['lp_all_pages_without_category'] = 'Toutes les pages sans catégorie';
$txt['lp_category_not_found'] = 'La catégorie spécifiée est introuvable.';
$txt['lp_no_categories'] = 'Il n\'y a pas encore de catégories.';
$txt['lp_total_pages_column'] = 'Pages totales';

// Panels
$txt['lp_panels'] = 'Panneaux';
$txt['lp_panels_info'] = 'Ici, vous pouvez personnaliser la largeur de certains panneaux, ainsi que la direction des blocs.<br><strong>%1$s</strong> utiliser <a class="bbc_link" href="%2$s" target="_blank" rel="noopener">Système de grille à 12 colonnes</a> pour afficher des blocs dans 6 panneaux.';
$txt['lp_swap_header_footer'] = 'Modifiez l\'en-tête et le pied de page';
$txt['lp_swap_left_right'] = 'Permutez le panneau gauche et le panneau droit';
$txt['lp_swap_top_bottom'] = 'Permutez le centre (en haut) et le centre (en bas)';
$txt['lp_panel_layout_preview'] = 'Ici, vous pouvez définir le nombre de colonnes pour certains panneaux, en fonction de la largeur de la fenêtre du navigateur.';
$txt['lp_left_panel_sticky'] = $txt['lp_right_panel_sticky'] = 'Epingler';
$txt['lp_panel_direction_note'] = 'Ici, vous pouvez modifier la direction des blocs pour chaque panneau.';
$txt['lp_panel_direction'] = 'La direction des blocs dans les panneaux';
$txt['lp_panel_direction_set'] = array('Vertical', 'Horizontal');

// Misc
$txt['lp_misc'] = 'Divers';
$txt['lp_misc_info'] = 'Il existe des paramètres de portail supplémentaires qui seront utiles pour les développeurs de modèles et de plugins ici.';
$txt['lp_debug_and_caching'] = 'Débogage et mise en cache ';
$txt['lp_show_debug_info'] = 'Afficher le temps de chargement et le nombre de requêtes de portail';
$txt['lp_show_debug_info_help'] = 'Ces informations ne seront accessibles qu\'aux administrateurs!';
$txt['lp_cache_update_interval'] = 'L\'intervalle de mise à jour du cache';

// Actions
$txt['lp_title'] = 'Titre';
$txt['lp_actions'] = 'Actions';
$txt['lp_action_on'] = 'Artiver';
$txt['lp_action_off'] = 'Disactiver';
$txt['lp_action_clone'] = 'Cloner';
$txt['lp_action_move'] = 'Déplacer';
$txt['lp_read_more'] = 'Lire plus...';

// Blocks
$txt['lp_blocks'] = 'Blocs';
$txt['lp_blocks_manage'] = 'Gérer les blocs';
$txt['lp_blocks_manage_description'] = 'Tous les blocs de portail créés sont répertoriés ici. Pour ajouter un bloc, utilisez le bouton "+".';
$txt['lp_blocks_add'] = 'Ajouter un bloc';
$txt['lp_blocks_add_title'] = 'Titre du block';
$txt['lp_blocks_add_description'] = 'Les blocs peuvent contenir n\'importe quel contenu, selon leur type.';
$txt['lp_blocks_add_instruction'] = 'Sélectionnez le bloc souhaité en cliquant dessus.';
$txt['lp_blocks_edit_title'] = 'editez un bloc';
$txt['lp_blocks_edit_description'] = $txt['lp_blocks_add_description'];
$txt['lp_block_type'] = 'Type de bloc';
$txt['lp_block_note'] = 'Note';
$txt['lp_block_priority'] = 'Prioritée';
$txt['lp_block_placement'] = 'Placement';
$txt['lp_block_placement_set'] = array('Entête', 'Centre (haut)', 'Côté gauche', 'Côté droit', 'Centre (bas)', 'Bas de page');

$txt['lp_block_areas'] = 'Actions';
$txt['lp_block_areas_subtext'] = 'Spécifiez une ou plusieurs zones (séparées par une virgule) pour afficher le bloc dans:';
$txt['lp_block_areas_area_th'] = 'Zone';
$txt['lp_block_areas_display_th'] = 'Afficher';
$txt['lp_block_areas_values'] = array(
	'partout',
	'dans la parie <em>index.php?action</em>=<strong>custom_action</strong> (par exemple: portail, forum, recherche)',
	'sur toutes les pages du portail',
	'sur la page <em>index.php?page</em>=<strong>alias</strong>',
	'dans toutes les sections du forum',
	'uniquement à l\'intérieur de la carte avec identifiant <strong>id</strong> (y compris tous les sujets à l\'intérieur du tableau)',
	'dans les sections id1, id2, id3',
	'dans les sections id3, et id7',
	'sur tout les sujets',
	'uniquement à l\'intérieur du sujet avec identifiant <strong>id</strong>',
	'dans les sujets id1, id2, id3',
	'dans les sujets id3, et id7'
);

$txt['lp_block_title_class'] = 'Classe de titre CSS';
$txt['lp_block_title_style'] = 'Style de titre CSS';
$txt['lp_block_content_class'] = 'Classe de contenu CSS';
$txt['lp_block_content_style'] = 'Style de contenu CSS';

// Internal blocks
$txt['lp_bbc']['title'] = 'BBC personnalisée';
$txt['lp_html']['title'] = 'HTML personnalisé';
$txt['lp_php']['title'] = 'PHP personnalisé';
$txt['lp_bbc']['description'] = 'Dans ce bloc, toutes les balises BBC du forum peuvent être utilisées comme contenu.';
$txt['lp_html']['description'] = 'Dans ce bloc, vous pouvez utiliser n\'importe quelle balise HTML comme contenu.';
$txt['lp_php']['description'] = 'Dans ce bloc, vous pouvez utiliser n\'importe quel code PHP comme contenu.';

// Pages
$txt['lp_pages'] = 'Pages';
$txt['lp_pages_manage'] = 'Gérer les pages';
$txt['lp_pages_manage_all_pages'] = 'Ici, vous pouvez voir toutes les pages du portail.';
$txt['lp_pages_manage_own_pages'] = 'Ici, vous pouvez afficher toutes vos propres pages de portail.';
$txt['lp_pages_manage_description'] = 'Utilisez le bouton correspondant pour ajouter une nouvelle page.';
$txt['lp_pages_add'] = 'Ajouter une page';
$txt['lp_pages_add_title'] = 'Titre de la page';
$txt['lp_pages_add_description'] = 'Remplissez le titre de la page. Après cela, vous pouvez changer son type, utiliser l\'aperçu et enregistrer.';
$txt['lp_pages_edit_title'] = 'Editer la page';
$txt['lp_pages_edit_description'] = 'Apportez les modifications nécessaires.';
$txt['lp_pages_extra'] = 'Pages du portail';
$txt['lp_pages_search'] = 'Alias ou titre';
$txt['lp_page_types']['bbc'] = 'BBC';
$txt['lp_page_types']['html'] = 'HTML';
$txt['lp_page_types']['php'] = 'PHP';
$txt['lp_page_alias'] = 'Alias';
$txt['lp_page_alias_subtext'] = 'L\'alias de page doit commencer par une lettre latine et être composé de lettres latines minuscules, de chiffres et de trait de soulignement.';
$txt['lp_page_type'] = 'Type de page';
$txt['lp_page_description'] = 'Description';
$txt['lp_page_keywords'] = 'Mots clés';
$txt['lp_page_keywords_placeholder'] = 'Sélectionnez des balises ou ajoutez-en de nouvelles';
$txt['lp_page_publish_datetime'] = 'Date et heure de publication';
$txt['lp_page_author'] = 'Modifier l\auteur';
$txt['lp_page_author_placeholder'] = 'Spécifiez un nom d\'utilisateur pour transférer les droits sur la page';
$txt['lp_page_author_search_length'] = 'Veuillez saisir au moins 3 caractères';
$txt['lp_page_options'] = array('Afficher l\'auteur et la date de création', 'Afficher les pages associées', 'Autoriser les commentaires', 'Élément dans le menu principal');

// Tabs
$txt['lp_tab_content'] = 'Contenue';
$txt['lp_tab_seo'] = 'SEO';
$txt['lp_tab_access_placement'] = 'Accès et placement';
$txt['lp_tab_appearance'] = 'Apparence';
$txt['lp_tab_menu'] = 'Menu';
$txt['lp_tab_tuning'] = 'Réglage';

// Import and Export
$txt['lp_pages_export'] = 'Exporter la page';
$txt['lp_pages_import'] = 'Importer la page';
$txt['lp_pages_export_description'] = 'Ici, vous pouvez exporter les pages sélectionnées pour créer une sauvegarde ou pour les transférer vers un autre forum.';
$txt['lp_pages_import_description'] = 'Ici, vous pouvez importer des pages de portail précédemment enregistrées à partir d\'une sauvegarde.';
$txt['lp_blocks_export'] = 'Exporter le bloc';
$txt['lp_blocks_import'] = 'Importer le bloc';
$txt['lp_blocks_export_description'] = 'Ici, vous pouvez exporter les blocs sélectionnés pour créer une sauvegarde ou pour les transférer vers un autre forum.';
$txt['lp_blocks_import_description'] = 'Ici, vous pouvez importer des blocs de portail précédemment enregistrés à partir d\'une sauvegarde.';
$txt['lp_export_run'] = 'Exporter la sélection';
$txt['lp_import_run'] = 'Lancer l\'importation';
$txt['lp_export_all'] = 'Tout exporter';

// Plugins
$txt['lp_plugins'] = 'Plugins';
$txt['lp_plugins_manage'] = 'Gérer les plugins';
$txt['lp_plugins_manage_description'] = 'Les plugins de portail installés sont répertoriés ici. Vous pouvez toujours en créer un nouveau en utilisant <a class="bbc_link" href="%1$s" target="_blank" rel="noopener">les instructions</a> ou le bouton "+" ci-dessous.';
$txt['lp_plugins_desc'] = 'Les plugins étendent les capacités du portail et de ses composants, en fournissant des fonctionnalités supplémentaires qui ne sont pas disponibles dans le système de base';
$txt['lp_plugins_type_set'] = array('Bloc', 'Editeur', 'Widget des commenatires', 'Analyseur de contenu', 'Gestion des articles', 'La mise en page de la page d\'accueil', 'Importer et exporter', 'Autre');

// Tags
$txt['lp_all_page_tags'] = 'Toutes les balises de page de portail';
$txt['lp_all_tags_by_key'] = 'Toutes les pages avec le "%1$s" balise';
$txt['lp_tag_not_found'] = 'La balise spécifiée est introuvable.';
$txt['lp_no_tags'] = 'Il n\'y a pas encore de balises.';
$txt['lp_keyword_column'] = 'Mot-clé';
$txt['lp_frequency_column'] = 'La fréquence';
$txt['lp_sorting_label'] = 'Trier par';
$txt['lp_sort_by_title_desc'] = 'Titre (desc)';
$txt['lp_sort_by_title'] = 'Titre (asc)';
$txt['lp_sort_by_created_desc'] = 'Date de création (nouveau en premier)';
$txt['lp_sort_by_created'] = 'Date de création (ancien en premier)';
$txt['lp_sort_by_updated_desc'] = 'Date de mise à jour (nouveau en premier)';
$txt['lp_sort_by_updated'] = 'Date de mise à jour (ancienne en premier)';
$txt['lp_sort_by_author_desc'] = 'Nom de l\'auteur (desc)';
$txt['lp_sort_by_author'] = 'Nom de l\'auteur (asc)';
$txt['lp_sort_by_num_views_desc'] = 'Nombre de vues (desc)';
$txt['lp_sort_by_num_views'] = 'Nombre de vues (asc)';

// Related pages
$txt['lp_related_pages'] = 'Pages liées';

// Comments
$txt['lp_comments'] = 'Commentaires';
$txt['lp_comment_placeholder'] = 'Laissez un commentaire...';

// Comment alerts
$txt['alert_page_comment'] = 'Quand ma page reçoit un commentaire';
$txt['alert_new_comment_page_comment'] = '{member_link} a laissé un commentaire {page_comment_new_comment}';
$txt['alert_page_comment_reply'] = 'Quand mon commentaire reçoit une réponse';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} a laissé une réponse à votre commentaire {page_comment_reply_new_reply}';

// Errors
$txt['lp_page_not_found'] = 'Page non trouvée!';
$txt['lp_page_not_activated'] = 'La page demandée est désactivée!';
$txt['lp_page_not_editable'] = 'Vous n\'êtes pas autorisé à modifier cette page!';
$txt['lp_page_visible_but_disabled'] = 'La page vous est visible, mais pas activée!';
$txt['lp_block_not_found'] = 'Bloc non trouvé!';
$txt['lp_post_error_no_title'] = 'Le champ <strong> titre </strong> n\'a pas été rempli. C\'est requis.';
$txt['lp_post_error_no_alias'] = 'Le champ <strong> alias </strong> n\'a pas été rempli. C\'est requis.';
$txt['lp_post_error_no_valid_alias'] = 'L\'alias spécifié n\'est pas correct!';
$txt['lp_post_error_no_unique_alias'] = 'Une page avec cet alias existe déjà!';
$txt['lp_post_error_no_content'] = 'Le contenu non spécifié! C\'est requis.';
$txt['lp_post_error_no_areas'] = 'Le champ <strong> zones </strong> n\'a pas été rempli. C\'est requis';
$txt['lp_post_error_no_valid_areas'] = 'Le champ <strong> zones </strong> n\'a pas été défini correctement!';
$txt['lp_post_error_no_name'] = 'Le champ <strong> nom </strong> n\'a pas été rempli. C\'est requis.';
$txt['lp_wrong_import_file'] = 'Mauvais fichier à importer...';
$txt['lp_import_failed'] = 'Échec de l\'importation...';
$txt['lp_wrong_template'] = 'Wrong template. Choose a template that matches the content.';
$txt['lp_addon_not_installed'] = 'le plugin %1$s n\'est pas installé';

// Who
$txt['lp_who_viewing_frontpage'] = 'Affichage de <a href="%1$s">la page d\'accueil du portai</a>.';
$txt['lp_who_viewing_index'] = 'Affichage de <a href="%1$s">la page d\'accueil du portail</a> ou <a href="%2$s">l\'index du forum</a>.';
$txt['lp_who_viewing_page'] = 'Affichage de <a href="%1$s">la page du portail</a>.';
$txt['lp_who_viewing_tags'] = 'Affichage des <a href="%1$s">balises de la page du portail</a>.';
$txt['lp_who_viewing_the_tag'] = 'Affichage de la liste des pages avec <a href="%1$s" class="bbc_link">%2$s</a> tag.';
$txt['lp_who_viewing_portal_settings'] = 'Affichage ou modification <a href="%1$s">les paramètres du portail</a>.';
$txt['lp_who_viewing_portal_blocks'] = 'Affichage des <a href="%1$s">blocs du portail</a> dans la zone d\'administration.';
$txt['lp_who_viewing_editing_block'] = 'Modification du bloc de portail (#%1$d).';
$txt['lp_who_viewing_adding_block'] = 'Ajout d\'un bloc pour le portail.';
$txt['lp_who_viewing_portal_pages'] = 'Affichage des <a href="%1$s">pages du portail</a> dans la zone d\'administration.';
$txt['lp_who_viewing_editing_page'] = 'Modifier la page du portail (#%1$d).';
$txt['lp_who_viewing_adding_page'] = 'Ajout d\'une page pour le portail.';

// Permissions
$txt['permissionname_light_portal_view'] = $txt['group_perms_name_light_portal_view'] = 'Afficher les éléments du portail';
$txt['permissionname_light_portal_manage_blocks'] = $txt['group_perms_name_light_portal_manage_blocks'] = 'Gérer les blocs';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Gérer ses propres pages';
$txt['permissionname_light_portal_approve_pages'] = $txt['group_perms_name_light_portal_approve_pages'] = 'Publier des pages sans approbation';
$txt['permissionhelp_light_portal_view'] = 'Possibilité d\'afficher les pages et les blocs du portail.';
$txt['permissionhelp_light_portal_manage_blocks'] = 'Accès pour gérer les blocs de portail.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Accès pour gérer ses propres pages.';
$txt['permissionhelp_light_portal_approve_pages'] = 'Possibilité de publier des pages de portail sans approbation.';
$txt['cannot_light_portal_view'] = 'Désolé, vous n\'êtes pas autorisé à consulter le portail!';
$txt['cannot_light_portal_manage_blocks'] = 'Désolé, vous n\'êtes pas autorisé à gérer les blocs!';
$txt['cannot_light_portal_manage_own_pages'] = 'Désolé, vous n\'êtes pas autorisé à gérer les pages!';
$txt['cannot_light_portal_approve_pages'] = 'Désolé, vous n\'êtes pas autorisé à publier des pages sans approbation!';
$txt['cannot_light_portal_view_page'] = 'Désolé, vous n\'êtes pas autorisé à voir cette page!';

// Time units
$txt['lp_days_set'] = 'jour, jours';
$txt['lp_hours_set'] = 'heure, heures';
$txt['lp_minutes_set'] = 'minute, minutes';
$txt['lp_seconds_set'] = 'seconde, secondes';
$txt['lp_tomorrow'] = '<strong>Aujourd\'hui</strong> à ';
$txt['lp_just_now'] = 'Juste maintenant';
$txt['lp_time_label_in'] = 'Dans %1$s';
$txt['lp_time_label_ago'] = ' depuis';

// Social units
$txt['lp_posts_set'] = 'message, messages';
$txt['lp_replies_set'] = 'réponse, réponses';
$txt['lp_views_set'] = 'vue, vues';
$txt['lp_comments_set'] = 'commentaire, commentaires';
$txt['lp_articles_set'] = 'article, articles';

// Other units
$txt['lp_users_set'] = 'utilisateur, utilisateurs';
$txt['lp_guests_set'] = 'invité, invités';
$txt['lp_spiders_set'] = 'robot, robots';
$txt['lp_hidden_set'] = 'caché, cachés';
$txt['lp_buddies_set'] = 'ami, amis';

// Credits
$txt['lp_contributors'] = 'Contribution au développement du portail';
$txt['lp_translators'] = 'Traducteurs';
$txt['lp_testers'] = 'Testeurs';
$txt['lp_sponsors'] = 'Sponsors';
$txt['lp_used_components'] = 'Les composants du portail';

// Debug info
$txt['lp_load_page_stats'] = 'Le portail est chargé en %1$.3f seconds, avec %2$d requêtes.';
