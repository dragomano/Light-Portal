---
title: SSS
description: Light Portal hakkında Sık Sorulan Sorular
---

# Sık Sorulan Sorular

Light Portal hakkında çok sorulan soruların cevapları burada.

## Genel sorular

### Hangi SMF sürümleri destekleniyor?

[Kurulum](./getting-started/installation.md) bölümüne bakın.

### Light Portal'ı nereden indirebilirim?

[Kurulum](./getting-started/installation.md) bölümüne bakın.

---

## Kurulum ve Ayarlama

### Light Portal'ı nasıl kurabilirim?

[Kurulum](./getting-started/installation.md) bölümüne bakın.

### Portal ön sayfasını nasıl yaparım?

[Portal Ayarları](./getting-started/configuration#settings-for-the-front-page-and-articles) bölümüne bakın.

### Light Portal'ı bir başka portal ile birlikte kullanabilir miyim?

Evet, iki portalı birleştirmeyi deneyebilirsiniz.

1. Önceki portalı kaldırmadan Light Portal'ı kurun
2. **Ayarlar** → **Diğer/çeşitli** bölümüne gidin ve `action`/`sayfa` parametrelerini diğer portaldan farklı olacak şekilde ayarlayın.

---

## Sayfalar

### Nasıl yeni sayfalar oluştururum?

[Sayfa Ekleme](./pages/create-new.md) bölümüne bakın.

### Sayfalar için nasış SEO ayarlarım?

[SEO Sekmesi](./pages/create-new#seo-tab)ne bakın.

### Kategoriler ve etiketler nedir?

[Sözlüğe](./glossary.md) bakın

**Portal** → **Kategoriler** alanından kategori, **Portal** → **Etiketler** alanından etiket oluşturabilirsiniz.

---

## Bloklar

### Blok eklemeyi nasıl yaparım?

[Blok Ekleme](./blocks/create-new.md) bölümüne bakın.

### Blok sıralamasını nasıl değiştiririm?

Blok yönetim alanında blokları dilediğiniz sıraya göre sürükleyin.

### Bloklarda JavaScript kullanabilir miyim?

Evet, bunun için HTML-türü blok kullanın.

:::warning Uyarı

Harici betikler hakkında dikkat edin — sayfa yüklemesini yavaşlatabilir veya güvenlik zaafiyetleri oluşturabilirler.

:::

---

## Eklentiler

### Eklentiler nedir?

Eklentiler, Light Portal işlevlerini genişletir. Yeni blok türleri ekleyebilir, diğer modlarla entegre olabilir ve ek özellikler sunabilirler.

Ayrıntılar için [Eklenti Yönetimi](./plugins/manage.md)ne bakın.

### Eklentileri nasıl kurabilirim?

[Eklenti kurma](./plugins/manage#installing-additional-plugins) bölümüne bakın.

---

## Tasarım ve Temalar

### Portal görünümünü nasıl değiştirebilirim?

Light Portal, forumun kullandığı temayı kullanır. Bununla birlikte, ön sayfa yerleşimini değiştirebilirsiniz.

1. **CSS**: `Themes/default/css` klasöründe `portal_custom.css` isminde bir dosya oluşturun
2. **Yerleşimler**: `Themes/default/portal_layouts` klasöründe özel bir sayfa yerleşimi oluşturun

Ayrıntılar için [Özel Yerleşim Oluşturma](./how-to/create-layout.md) bölümüne bakın.

---

## Sorun Giderme

### Sayfa görüntülenmiyor

Kontrol Edin:

1. Sayfa durumu (etkin/devr dışı)
2. URL Doğru mu (rumuz)
3. Görünürlük ayarları (Sayfa ayarlarında erişim ve yerleşim sekmesi)

### Blok görüntülenmiyor

Kontrol Edin:

1. Blok etkinleştirilmiş mi
2. Hangi panele atanmış
3. Görünürlük ayarları (Blok ayarlarında erişim ve yerleşim sekmesi)

### Güncellemeden sonra oluşan hatalar

1. Forum önbelleği ve tarayıcı öncelleğini temizleyin
2. Portal ayarlarında **Çeşitli/diğer** sekmesinde haftalık tablo iyileştirmelerini etkinleştirin
3. Gerekiyorsa eklentileri yeniden kurun/güncelleyin

### Hata günlüklerini nerede bulabilirim?

Light Portal günlükleri, standart SMF günlükleri içerisindedir. Ayrıca portal ayarlarında hata ayıklama modunu etkinleştirebilirsiniz.

---

## Geliştirme

### Kendi eklentimi nasıl oluştururum?

[Eklenti Ekleme](./plugins/create-new.md) bölümüne bakın.

### Kancalar hakkındaki dokümanları nerede bulabilirim?

[Portal Kancaları](./plugins/all-hooks.md) bölümüne bakın.

---

## Yardıma İhtiyacınız Mı Var?

Eper sorunuzun cevabını bulamadıysanız:

1. [Destek forumu](https://www.simplemachines.org/community/index.php?topic=572393.0)nda arama yapın
2. Sorununuzu tarif eden yeni bir konu açın
3. Ekran görüntüleri ve hata günlüklerini ekleyin

Ya da bu sayfadaki yorum alanını kullanın.
