<?php

/**
 * .turkish language file
 *
 * @package Light Portal
 */

$txt['lp_portal'] = 'Portal';
$txt['lp_forum'] = 'Forum';

$txt['lp_new_version_is_available'] = 'Yeni bir sürüm mevcut!';

$txt['lp_article'] = 'Makale';
$txt['lp_no_items'] = 'Gösterilecek öğe yok.';
$txt['lp_example'] = 'Örnek: ';
$txt['lp_content'] = 'İçerik';
$txt['lp_my_pages'] = 'Sayfalarım';
$txt['lp_views'] = $txt['views'];
$txt['lp_replies'] = $txt['replies'];
$txt['lp_default'] = 'Varsayılan';
$txt['lp_min_search_length'] = 'Lütfen en az %d karakter girin';

// Ayarlar
$txt['lp_settings'] = 'Portal ayarları';
$txt['lp_base'] = 'Ön sayfa ve makaleler için ayarlar';
$txt['lp_base_info'] = 'Mod sürümü: <strong>%1$s</strong>, PHP sürümü: <strong>%2$s</strong>, %3$s sürümü: <strong>%4$s</strong>.<br>Portalın hataları ve özellikleri <a class="bbc_link" href="https://www.simplemachines.org/community/index.php?topic=572393.0">simplemachines.com</a>.<br>You can also <a class="bbc_link" href="https://ko-fi.com/U7U41XD2G">buy a cup of coffee as a thank</a>.';

$txt['lp_frontpage_title'] = 'Ön sayfa başlığı';
$txt['lp_frontpage_mode'] = 'Portal ön sayfası';
$txt['lp_frontpage_mode_set'] = array('Devre dışı', 'Belirli sayfa', 'Seçili kategorilerdeki tüm sayfalar', 'Seçili sayfalar', 'Seçili forumlardaki tüm konular', 'Seçili konular', 'Seçili bölümler');
$txt['lp_frontpage_alias'] = 'Ana sayfa olarak görüntülenecek portal sayfası';
$txt['lp_frontpage_alias_subtext'] = 'Mevcut sayfanın diğer adını girin.';
$txt['lp_frontpage_categories'] = 'Kategoriler - ön sayfa için makale kaynakları';
$txt['lp_select_categories_from_list'] = 'İstenen kategorileri seçin';
$txt['lp_frontpage_boards'] = 'Bölümler - ön sayfa için makale kaynakları';
$txt['lp_select_boards_from_list'] = 'İstenen bölümleri seçin';
$txt['lp_frontpage_pages'] = 'Sayfalar - ön sayfa için makale kaynakları';
$txt['lp_frontpage_pages_subtext'] = 'Sayfaların virgülle ayrılmış ID numaraları.';
$txt['lp_frontpage_topics'] = 'Konular - ön sayfa için makale kaynakları';
$txt['lp_frontpage_topics_subtext'] = 'Konuların virgülle ayrılmış kimlikleri.';
$txt['lp_show_images_in_articles'] = 'Makalelerde bulunan resimleri göste';
$txt['lp_show_images_in_articles_help'] = 'Önce, makalenin bir eki olup olmadığını (makale bir forum konusuna dayanıyorsa), ardından - makalenin resimli bir IMG etiketi olup olmadığını kontrol eder.';
$txt['lp_image_placeholder'] = 'Varsayılan yer tutucu resmin URL\'si';
$txt['lp_frontpage_time_format'] = 'Makale kartlarındaki zaman biçimi';
$txt['lp_frontpage_time_format_set'] = array('Tam (LP stili)', 'Forumdaki gibi', 'Kendi biçimi');
$txt['lp_frontpage_custom_time_format'] = 'Kendi zaman biçimi';
$txt['lp_frontpage_custom_time_format_help'] = '<a class="bbc_link" href="https://www.php.net/manual/en/datetime.format.php">belgelerinde</a> olası parametrelerin listesine bakın .';
$txt['lp_show_teaser'] = 'Makale özetini göster';
$txt['lp_show_author'] = 'Makale yazarını göster';
$txt['lp_show_author_help'] = 'Bölüm kartı görüntüleniyorsa, kategori hakkında bilgi olacaktır.';
$txt['lp_show_num_views_and_comments'] = 'Görtüleme ve yorumların sayısını göster';
$txt['lp_frontpage_order_by_num_replies'] = 'En yüksek yorum sayısına sahip makaleleri ilk sırada göster';
$txt['lp_frontpage_article_sorting'] = 'Makaleleri sıralama';
$txt['lp_frontpage_article_sorting_set'] = array('Son yoruma göre', 'Oluşturma tarihine göre (önce yeni)', 'Oluşturma tarihine göre (eski önce)', 'Güncelleme tarihine göre (son güncellenen)');
$txt['lp_frontpage_article_sorting_help'] = 'İlk seçeneği seçtiğinizde, makale kartları tarihleri ​​ve en son yorumcuları (varsa) gösterir.';
$txt['lp_frontpage_layout'] = 'Makale kartları için şablon düzeni';
$txt['lp_frontpage_num_columns'] = 'Makaleleri görüntülemek için sütun sayısı';
$txt['lp_frontpage_num_columns_set'] = array('1 sütun', '2 sütun', '3 sütun', '4 sütun', '6 sütun');
$txt['lp_num_items_per_page'] = 'Sayfa başına öğe sayısı (sayfalandırma için)';

$txt['lp_standalone_mode_title'] = 'Bağımsız mod';
$txt['lp_standalone_url'] = 'Bağımsız modda ön sayfa URL\'si';
$txt['lp_standalone_url_help'] = 'Portalın ön sayfası olarak görüntülenecek kendi URL\'nizi belirtebilirsiniz (örneğin, <strong> https: //forum/portal.php </strong>). <br> Bu durumda, forumun ön sayfası <strong> https: //yourforum/index.php </strong>. <br> <br> Örnek olarak, <em> portal.php </em> dosyası portala dahil edilmiştir - onu kullanabilirsiniz. <br > <br> <em> portal.php </em> \'yi forum dizininin dışına yerleştirmek istiyorsanız, "<strong> Çerezlerin yerel olarak depolanmasını etkinleştir </strong>" seçeneğini devre dışı bırakın (Bakım => Sunucu Ayarları => Çerezler ve Oturumlar).';
$txt['lp_standalone_mode_disabled_actions'] = 'Devre dışı eylemler';
$txt['lp_standalone_mode_disabled_actions_subtext'] = 'Bağımsız modda DEVRE DIŞI bırakılması gereken alanları belirtin.';
$txt['lp_standalone_mode_disabled_actions_help'] = 'Örneğin, Arama alanını devre dışı bırakmanız gerekirse (index.php?action=<strong>search</strong>), metin alanına <strong>arama</strong> ekleyin.';

$txt['groups_light_portal_view'] = 'Portal öğelerini kimler görebilir';
$txt['groups_light_portal_manage_blocks'] = 'Blokları kim yönetebilir';
$txt['groups_light_portal_manage_own_pages'] = 'Kendi sayfalarını kim yönetebilir';
$txt['groups_light_portal_approve_pages'] = 'Portal sayfalarını onay almadan kimler gönderebilir';
$txt['lp_manage_permissions'] = 'Bazı sayfalar tehlikeli HTML/PHP içeriği içerebilir, bu yüzden bunların herkes tarafından oluşturulmasına izin vermeyin!';

// Sayfalar ve bloklar
$txt['lp_extra'] = 'Sayfalar ve bloklar';
$txt['lp_extra_info'] = 'Burada sayfalar ve bloklar için genel ayarları bulabilirsiniz.';

$txt['lp_show_page_permissions'] = 'Sayfa izinleri ile ilgili bilgileri göster';
$txt['lp_show_page_permissions_subtext'] = 'Sadece sayfayı düzenleme iznine sahip olanlar görebilir.';
$txt['lp_show_tags_on_page'] = 'Anahtar kelimeleri sayfanın en üstünde göster';
$txt['lp_show_items_as_articles'] = 'Etiket/kategori sayfalarındaki öğeleri kart olarak göster';
$txt['lp_show_related_pages'] = 'İlgili sayfalar bloğunu göster';
$txt['lp_show_comment_block'] = 'Yorum bloğunu göster';
$txt['lp_disabled_bbc_in_comments'] = 'Yorumlarda BBC\'ye izin ver';
$txt['lp_disabled_bbc_in_comments_subtext'] = 'Forumda <a class="bbc_link" href="%1$s">izin verilen</a> herhangi bir etiketi kullanabilirsiniz.';
$txt['lp_show_comment_block_set'] = array('Yok', 'Entegre');
$txt['lp_time_to_change_comments'] = 'Yorum yazdıktan sonra düzenleme için maksimum süre';
$txt['lp_num_comments_per_page'] = 'Sayfa başına üst yorum sayısı';
$txt['lp_page_editor_type_default'] = 'Varsayılan olarak sayfa düzenleyicinin türü';
$txt['lp_permissions_default'] = 'Varsayılan olarak sayfalar ve bloklar için izinler';
$txt['lp_hide_blocks_in_admin_section'] = 'Yönetici alanında aktif blokları gizle';

$txt['lp_schema_org'] = 'Kişiler için şema mikro veri işaretlemesi';
$txt['lp_page_og_image'] = 'Sayfa içeriğinden bir resim kullanın';
$txt['lp_page_og_image_set'] = array('Hiçbiri', 'İlk bulunan', 'Son bulunan');
$txt['lp_page_itemprop_address'] = 'Kuruluşunuzun adresi';
$txt['lp_page_itemprop_phone'] = 'Kuruluşunuzun telefonu';

$txt['lp_permissions'] = array('Yöneticilere göster', 'Ziyaretcilere göster', 'Üyelere göster', 'Herkese göster');

// Kategoriler
$txt['lp_categories'] = 'Kategoriler';
$txt['lp_categories_info'] = 'Burada sayfaları kategorilere ayırmak için portal kategorileri oluşturabilir ve düzenleyebilirsiniz. <br> Sırayı değiştirmek için bir kategoriyi yeni bir konuma sürüklemeniz yeterlidir.';
$txt['lp_categories_manage'] = 'Kategorileri yönet';
$txt['lp_categories_add'] = 'Kategori ekle';
$txt['lp_categories_desc'] = 'Açıklama';
$txt['lp_category'] = 'Kategori';
$txt['lp_no_category'] = 'Kategorize Edilmemiş';
$txt['lp_all_categories'] = 'Portalın tüm kategorileri';
$txt['lp_all_pages_with_category'] = '"%1$s" kategorisindeki tüm sayfalar';
$txt['lp_all_pages_without_category'] = 'Kategorisiz tüm sayfalar';
$txt['lp_category_not_found'] = 'Belirtilen kategori bulunamadı.';
$txt['lp_no_categories'] = 'Henüz kategori yok.';
$txt['lp_total_pages_column'] = 'Toplam sayfa';

// Paneller
$txt['lp_panels'] = 'Paneller';
$txt['lp_panels_info'] = 'Burada bazı panellerin genişliğini ve blokların yönünü özelleştirebilirsiniz. <br> <strong>%1$s</strong> <a class = "bbc_link" href kullanır = "%2$s" target="_blank" rel="noopener">blokları 6 panelde görüntülemek için 12 sütunlu ızgara sistemi </a>.';
$txt['lp_swap_header_footer'] = 'Üstbilgi ve altbilgiyi değiştirin';
$txt['lp_swap_left_right'] = 'Sol paneli ve sağ paneli değiştirin';
$txt['lp_swap_top_bottom'] = 'Ortayı (üst) ve merkezi (alt) değiştirin';
$txt['lp_panel_layout_preview'] = 'Burada, tarayıcı penceresinin genişliğine bağlı olarak bazı paneller için sütun sayısını ayarlayabilirsiniz.';
$txt['lp_left_panel_sticky'] = $txt['lp_right_panel_sticky'] = 'Sabit';
$txt['lp_panel_direction_note'] = 'Buradan her panel için blokların yönünü değiştirebilirsiniz.';
$txt['lp_panel_direction'] = 'Panellerdeki blokların yönü';
$txt['lp_panel_direction_set'] = array('Dikey', 'Yatay');

// Çeşitli
$txt['lp_misc'] = 'Çeşitli';
$txt['lp_misc_info'] = 'Burada şablon ve eklenti geliştiricileri için faydalı olacak ek portal ayarları var.';
$txt['lp_debug_and_caching'] = 'Hata ayıklama ve önbelleğe alma';
$txt['lp_show_debug_info'] = 'Portal sorgularının yükleme süresini ve sayısını gösterin';
$txt['lp_show_debug_info_help'] = 'Bu bilgi sadece yöneticilere açık olacak!';
$txt['lp_cache_update_interval'] = 'Önbellek güncelleme aralığı';

// Eylemler
$txt['lp_title'] = 'Başlık';
$txt['lp_actions'] = 'Eylemler';
$txt['lp_action_on'] = 'Etkinleştir';
$txt['lp_action_off'] = 'Etkinleştir';
$txt['lp_action_clone'] = 'Etkinleştir';
$txt['lp_action_move'] = 'Taşı';
$txt['lp_read_more'] = 'Daha fazlasını okuyun...';

// Bloklar
$txt['lp_blocks'] = 'Bloklar';
$txt['lp_blocks_manage'] = 'Blokları yönet';
$txt['lp_blocks_manage_description'] = 'Oluşturulan tüm portal blokları burada listelenir. Bir blok eklemek için "+" düğmesini kullanın.';
$txt['lp_blocks_add'] = 'Blok ekle';
$txt['lp_blocks_add_title'] = 'Blok ekleniyor';
$txt['lp_blocks_add_description'] = 'Bloklar, türlerine bağlı olarak herhangi bir içerik içerebilir.';
$txt['lp_blocks_add_instruction'] = 'Üzerine tıklayarak istediğiniz bloğu seçin.';
$txt['lp_blocks_edit_title'] = 'Blok düzenle';
$txt['lp_blocks_edit_description'] = $txt['lp_blocks_add_description'];
$txt['lp_block_type'] = 'Blok türü';
$txt['lp_block_note'] = 'Not';
$txt['lp_block_priority'] = 'Öncelik';
$txt['lp_block_placement'] = 'Yerleşim';
$txt['lp_block_placement_set'] = array('Başlık', 'Orta (üst)', 'Sol taraf', 'Sağ taraf', 'Orta (alt)', 'Altbilgi');

$txt['lp_block_areas'] = 'Eylemler';
$txt['lp_block_areas_subtext'] = 'Bloğu görüntülemek için bir veya daha fazla alan (virgülle ayırarak) belirtin:';
$txt['lp_block_areas_area_th'] = 'Alan';
$txt['lp_block_areas_display_th'] = 'Göster';
$txt['lp_block_areas_values'] = array(
 'her yerde',
 '<em>index.php?action</em>=<strong>custom_action</strong> (örneğin: portal, forum, arama)',
 'tüm portal sayfalarında',
 'sayfada <em>index.php?page</em> = <strong>takma ad</strong>',
 'tüm bölümlerde',
 'yalnızca <strong>id</strong> sahip bölümün içinde (bölüm içindeki tüm konular dahil)',
 'id1, id2, id3 bölümlerde ',
 'id3 ve id7 bölümlerinde ',
 'tüm konularda',
 'yalnızca <strong>id</strong> sahip konu içinde',
 'id1, id2, id3 konularında',
 'id3 ve id7 konularında'
);

$txt['lp_block_title_class'] = 'CSS başlık sınıfı';
$txt['lp_block_title_style'] = 'CSS başlık stili';
$txt['lp_block_content_class'] = 'CSS içerik sınıfı';
$txt['lp_block_content_style'] = 'CSS içerik stili';

// Internal blocks
$txt['lp_bbc']['title'] = 'Özel BBC';
$txt['lp_html']['title'] = 'Özel HTML';
$txt['lp_php']['title'] = 'Özel PHP';
$txt['lp_bbc']['description'] = 'Bu blokta forumun herhangi bir BBC etiketi içerik olarak kullanılabilir.';
$txt['lp_html']['description'] = 'Bu blokta, herhangi bir HTML etiketini içerik olarak kullanabilirsiniz.';
$txt['lp_php']['description'] = 'Bu blokta, herhangi bir PHP kodunu içerik olarak kullanabilirsiniz.';

// Sayfalar
$txt['lp_pages'] = 'Sayfalar';
$txt['lp_pages_manage'] = 'Sayfaları yönet';
$txt['lp_pages_manage_all_pages'] = 'Buradan tüm portal sayfalarını görüntüleyebilirsiniz.';
$txt['lp_pages_manage_own_pages'] = 'Burada tüm kendi portal sayfalarınızı görüntüleyebilirsiniz.';
$txt['lp_pages_manage_description'] = 'Yeni bir sayfa eklemek için ilgili düğmeyi kullanın.';
$txt['lp_pages_add'] = 'Sayfa ekle';
$txt['lp_pages_add_title'] = 'Sayfa ekleniyor';
$txt['lp_pages_add_description'] = 'Sayfa başlığını doldurun. Bundan sonra türünü değiştirebilir, önizlemeyi kullanabilir ve kaydedebilirsiniz.';
$txt['lp_pages_edit_title'] = 'Sayfayı düzenle';
$txt['lp_pages_edit_description'] = 'Gerekli değişiklikleri yapın.';
$txt['lp_pages_extra'] = 'Portal sayfaları';
$txt['lp_pages_search'] = 'Takma ad veya başlık';
$txt['lp_page_alias'] = 'Takma Ad';
$txt['lp_page_alias_subtext'] = 'Sayfa takma adı Latin harfiyle başlamalı ve küçük Latin harfleri, sayıları ve alt çizgiden oluşmalıdır.';
$txt['lp_page_type'] = 'Sayfa türü';
$txt['lp_page_types'] = array('BBC', 'HTML', 'PHP');
$txt['lp_page_description'] = 'Açıklama';
$txt['lp_page_keywords'] = 'Anahtar Kelimeler';
$txt['lp_page_keywords_placeholder'] = 'Etiketleri seçin veya yeni ekleyin';
$txt['lp_page_publish_datetime'] = 'Yayın tarihi ve saati';
$txt['lp_page_author'] = 'Yazarlık devri';
$txt['lp_page_author_placeholder'] = 'Sayfaya hakları aktarmak için bir kullanıcı adı belirtin';
$txt['lp_page_options'] = array('Yazarı ve oluşturma tarihini göster', 'İlgili sayfaları göster', 'Yorumlara izin ver', 'Ana menüdeki öğe');

// Sekmeler
$txt['lp_tab_content'] = 'İçerik';
$txt['lp_tab_seo'] = 'SEO';
$txt['lp_tab_access_placement'] = 'Erişim ve yerleştirme';
$txt['lp_tab_appearance'] = 'Görünüm';
$txt['lp_tab_menu'] = 'Menü';
$txt['lp_tab_tuning'] = 'Ayar';

// İçe ve Dışa Aktarma
$txt['lp_pages_export'] = 'Sayfa dışa aktar';
$txt['lp_pages_import'] = 'Sayfa içe aktar';
$txt['lp_pages_export_description'] = 'Burada, bir yedekleme oluşturmak veya başka bir foruma aktarmak için seçilen sayfaları dışa aktarabilirsiniz.';
$txt['lp_pages_import_description'] = 'Burada önceden kaydedilmiş portal sayfalarını bir yedekten içe aktarabilirsiniz.';
$txt['lp_blocks_export'] = 'Blok dışa aktar';
$txt['lp_blocks_import'] = 'Blok içe aktar';
$txt['lp_blocks_export_description'] = 'Burada, bir yedekleme oluşturmak veya başka bir foruma aktarmak için seçilen blokları dışa aktarabilirsiniz.';
$txt['lp_blocks_import_description'] = 'Here you can import previously saved portal blocks from a backup.';
$txt['lp_export_run'] = 'Seçimi dışa aktar';
$txt['lp_import_run'] = 'İçe aktarmayı çalıştır';
$txt['lp_export_all'] = 'Tümünü dışa aktar';

// Eklentiler
$txt['lp_plugins'] = 'Eklentiler';
$txt['lp_plugins_manage'] = 'Eklentileri yönet';
$txt['lp_plugins_manage_description'] = 'Yüklü portal eklentileri burada listelenmiştir. <a class="bbc_link" href="%1$s" target="_blank" rel="noopener"> Talimatları </a> veya aşağıdaki "+" düğmesini kullanarak yeni bir tane oluşturabilirsiniz.';
$txt['lp_plugins_desc'] = 'Eklentiler, portalın ve bileşenlerinin yeteneklerini artırarak çekirdekte bulunmayan ek özellikler sağlar.';
$txt['lp_plugins_types'] = array('Blok', 'Düzenleyici', 'Yorum widget\'i', 'İçerik ayrıştırıcı', 'Makaleler işleniyor', 'Ön sayfanın düzeni', 'İçe ve dışa aktar', 'Diğer');

// Etiketler
$txt['lp_all_page_tags'] = 'Tüm portal sayfası etiketleri';
$txt['lp_all_tags_by_key'] = '"%1$s" etiketine sahip tüm sayfalar';
$txt['lp_tag_not_found'] = 'Belirtilen etiket bulunamadı.';
$txt['lp_no_tags'] = 'Henüz etiket yok.';
$txt['lp_keyword_column'] = 'Etiket';
$txt['lp_frequency_column'] = 'Sıklık';
$txt['lp_sorting_label'] = 'Sırala';
$txt['lp_sort_by_title_desc'] = 'Başlık (azalan)';
$txt['lp_sort_by_title'] = 'Başlık (artan)';
$txt['lp_sort_by_created_desc'] = 'Oluşturma tarihi (yeni önce)';
$txt['lp_sort_by_created'] = 'Oluşturma tarihi (önce eski)';
$txt['lp_sort_by_updated_desc'] = 'Güncelleme tarihi (önce yeni)';
$txt['lp_sort_by_updated'] = 'Güncelleme tarihi (önce eski)';
$txt['lp_sort_by_author_desc'] = 'Yazar adı (azalan)';
$txt['lp_sort_by_author'] = 'Yazar adı (artan)';
$txt['lp_sort_by_num_views_desc'] = 'Görüntüleme sayısı (azalan)';
$txt['lp_sort_by_num_views'] = 'Görüntüleme sayısı (artan)';

// Benzer Sayfalar
$txt['lp_related_pages'] = 'Benzer Sayfalar';

// Yorumlar
$txt['lp_comments'] = 'Yorumlar';
$txt['lp_comment_placeholder'] = 'Yorum bırakın...';

// Yorum uyarıları
$txt['alert_page_comment'] = 'Sayfam bir yorum aldığında';
$txt['alert_new_comment_page_comment'] = '{member_link} bir yorum bıraktı {page_comment_new_comment}';
$txt['alert_page_comment_reply'] = 'Yorumum bir cevap aldığında';
$txt['alert_new_reply_page_comment_reply'] = '{member_link} yorumunuza bir yanıt bıraktı {page_comment_reply_new_reply}';

// Hatalar
$txt['lp_page_not_found'] = 'Sayfa bulunamadı!';
$txt['lp_page_not_activated'] = 'İstenen sayfa devre dışı bırakıldı!';
$txt['lp_page_not_editable'] = 'Bu sayfayı düzenleme izniniz yok!';
$txt['lp_page_visible_but_disabled'] = 'Sayfa sizin tarafınızdan görülebilir, ancak etkinleştirilmemiştir!';
$txt['lp_block_not_found'] = 'Blok bulunamadı!';
$txt['lp_post_error_no_title'] = '<strong>başlık</strong> alanı doldurulmadı. Bu gereklidir. ';
$txt['lp_post_error_no_alias'] = '<strong>takma ad</strong> alanı doldurulmadı. Bu gereklidir. ';
$txt['lp_post_error_no_valid_alias'] = 'Belirtilen takma ad doğru değil!';
$txt['lp_post_error_no_unique_alias'] = 'Bu takma ada sahip bir sayfa zaten var!';
$txt['lp_post_error_no_content'] = 'İçerik belirtilmedi! Bu gereklidir. ';
$txt['lp_post_error_no_areas'] = '<strong>alanlar</strong> doldurulmadı. Bu gereklidir. ';
$txt['lp_post_error_no_valid_areas'] = '<strong>alanlar</strong> yanlış ayarlandı!';
$txt['lp_post_error_no_name'] = '<strong>ad</strong> alanı doldurulmadı. Bu gereklidir. ';
$txt['lp_wrong_import_file'] = 'İçe aktarılacak yanlış dosya...';
$txt['lp_import_failed'] = 'İçe aktarılamadı...';
$txt['lp_wrong_template'] = 'Yanlış şablon. İçeriğe uygun bir şablon seçin. ';
$txt['lp_addon_not_installed'] = '%1$s eklentisi kurulu değil';

// Who
$txt['lp_who_viewing_frontpage'] = '<a href="%1$s">Portal ön sayfasını</a> görüntülüyor.';
$txt['lp_who_viewing_index'] = '<a href="%1$s">Portal ön sayfasını</a> veya <a href="%2$s"> forum dizinini</a> görüntülüyor.';
$txt['lp_who_viewing_page'] = '<a href="%1$s">portal sayfasını</a> görüntülüyor.';
$txt['lp_who_viewing_tags'] = '<a href="%1$s">portal sayfası etiketlerini görüntülüyor </a>.';
$txt['lp_who_viewing_the_tag'] = '<a href="%1$s" class="bbc_link">%2$s</a> etiketiyle sayfa listesi görüntülüyor.';
$txt['lp_who_viewing_portal_settings'] = '<a href="%1$s">portal ayarlarını</a> görüntüleme veya değiştirme.';
$txt['lp_who_viewing_portal_blocks'] = 'Yönetici alanında <a href="%1$s">portal bloklarını</a> görüntüleniyor.';
$txt['lp_who_viewing_editing_block'] = 'Portal bloğu düzenliyor (#%1$d).';
$txt['lp_who_viewing_adding_block'] = 'Portal için bir blok ekleniyor.';
$txt['lp_who_viewing_portal_pages'] = 'Yönetici alanında <a href="%1$s">portal sayfalarını</a> görüntüleniyor.';
$txt['lp_who_viewing_editing_page'] = 'Portal sayfasını düzenliyor (#%1$d).';
$txt['lp_who_viewing_adding_page'] = 'Portal için bir sayfa ekliyor.';

// İzinler
$txt['permissionname_light_portal_view'] = $txt['group_perms_name_light_portal_view'] = 'Portal öğelerini görüntüle';
$txt['permissionname_light_portal_manage_blocks'] = $txt['group_perms_name_light_portal_manage_blocks'] = 'Blokları yönet';
$txt['permissionname_light_portal_manage_own_pages'] = $txt['group_perms_name_light_portal_manage_own_pages'] = 'Kendi sayfalarını yönet';
$txt['permissionname_light_portal_approve_pages'] = $txt['group_perms_name_light_portal_approve_pages'] = 'Sayfaları onaysız yayınla';
$txt['permissionhelp_light_portal_view'] = 'Portal sayfalarını ve bloklarını görüntüleme yeteneği.';
$txt['permissionhelp_light_portal_manage_blocks'] = 'Portal bloklarını yönetme erişimi.';
$txt['permissionhelp_light_portal_manage_own_pages'] = 'Kendi sayfalarını yönetme erişimi.';
$txt['permissionhelp_light_portal_approve_pages'] = 'Portal sayfalarını onay olmadan gönderme yeteneği.';
$txt['cannot_light_portal_view'] = 'Üzgünüz, portalı görüntüleme izniniz yok!';
$txt['cannot_light_portal_manage_blocks'] = 'Üzgünüz, blokları yönetme izniniz yok!';
$txt['cannot_light_portal_manage_own_pages'] = 'Üzgünüz, sayfaları yönetme izniniz yok!';
$txt['cannot_light_portal_approve_pages'] = 'Üzgünüz, onay olmadan sayfa gönderemezsiniz!';
$txt['cannot_light_portal_view_page'] = 'Üzgünüz, bu sayfayı görme izniniz yok!';

// Zaman birimleri
$txt['lp_days_set'] = 'gün';
$txt['lp_hours_set'] = 'saat';
$txt['lp_minutes_set'] = 'dakika';
$txt['lp_seconds_set'] = 'saniye';
$txt['lp_tomorrow'] = '<strong>Yarın</strong> ';
$txt['lp_just_now'] = 'Hemen şimdi';
$txt['lp_time_label_in'] = '%1$s içinde';
$txt['lp_time_label_ago'] = ' önce';

// Etkinlik birimleri
$txt['lp_posts_set'] = 'ileti';
$txt['lp_replies_set'] = 'yanıt';
$txt['lp_views_set'] = 'görüntüleme';
$txt['lp_comments_set'] = 'yorum';
$txt['lp_articles_set'] = 'makale';

// Diğer birimler
$txt['lp_users_set'] = 'kullanıcı';
$txt['lp_guests_set'] = 'ziyaretci';
$txt['lp_spiders_set'] = 'örümcek';
$txt['lp_hidden_set'] = 'gizli';
$txt['lp_buddies_set'] = 'arkadaş';

// Credits
$txt['lp_contributors'] = 'Portalın geliştirilmesine katkıda bulunanlar';
$txt['lp_translators'] = 'Çevirmenler';
$txt['lp_testers'] = 'Test Kullanıcıları';
$txt['lp_sponsors'] = 'Sponsorlar';
$txt['lp_used_components'] = 'Portal bileşenleri';

// Debug info
$txt['lp_load_page_stats'] = 'Portal %2$d sorgu ile %1$.3f saniye içinde yüklendi.';
