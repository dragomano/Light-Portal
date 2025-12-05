---
description: Blok oluşturma arayüzünün kısa açıklaması
order: 2
---

# Blok ekle

Bir blok eklemek için, üzerine tıklamanız yeterlidir. Başlangıçta, üç tür blok oluşturabilirsiniz: PHP, HTML ve BBCode. Eğer başka türlere ihtiyacınız varsa, önce [gerekli eklentileri](../plugins/manage) `block` türünde etkinleştirin.

Blok türüne bağlı olarak, farklı sekmelerde çeşitli ayarlar mevcut olacaktır.

## Blok türleri

### Yerleşik içerik türleri

- **BBC**: İçerikte BBCode işaretlemelerine izin verir
- **HTML**: İşlenmemiş HTML içeriği
- **PHP**: Çalıştırılabilir PHP kodu (sadece admin)

### Eklenti-bazlı bloklar

Eklentilerden bloklar işlevselliği genişletir. Örnekler:

- **Markdown**: İçerik için Markdown sözdizimini etkinleştirir
- **ArticleList**: Özelleştirilebilir gösterme seçenekleriyle konulardan/sayfalardan makaleler gösterir
- **Calculator**: Etkileşimli hesap makinesi kutusu
- **BoardStats**: Forum bölüm istatistikleri
- **News**: Son duyurular
- **Polls**: Etkin forum anketleri
- **RecentPosts**: Son forum hareketleri
- **UserInfo**: Mevcut kullanıcı ayrıntıları
- **WhosOnline**: Bağlı kullanıcı listesi

## İçerik sekmesi

Burada yapılandırabilirsiniz:

- başlık
- not
- içerik (sadece bazı bloklar için)

![Content tab](content_tab.png)

## Erişim ve yerleştirme sekmesi

Burada yapılandırabilirsiniz:

- yerleştirme
- izinler
- alanlar

![Access tab](access_tab.png)

## Görünüm sekmesi

Burada blok görünüm seçeneklerini ayarlayabilirsiniz.

![Appearance tab](appearance_tab.png)

## Ayar sekmesi

Bloklara özgü ayarlayıcılar genellikle **Ayarlar** sekmesinde mevcuttur.

![Tuning tab](tuning_tab.png)

Eklentiler, geliştiricilerin niyetlerine bağlı olarak bu bölümlerden herhangi birine kendi özelleştirmelerini ekleyebilir.
