---
description: Diğer portallardan Light Portal'a taşınma rehberi
---

# Light Portal'a Taşınma

Yeni bir portala geçiş yapmak önemli bir adımdır. Bu rehber, içeriklerinizi diğer SMF portallarından Light Portal'a taşımak için size yardımcı olacak.

## Hazırlık

### Yedekleme

Taşınmaya başlamadan önce, tam bir yedek alın:

- Forum veritabanı
- Forum dosyaları (`Themes`, `Sources`)

:::warning Uyarı

Önce yerelde ya da test ortamında denemeden asla faal bir forumda taşınma işlemi başlatmayın.

:::

### Mevcut içeriğinizi denetleyin.

Taşınması gereken şeylerin bir listesini yapın:

- Bloklar
- Sayfalar
- Kategoriler

:::info Not

Sadece PHP/HTML/BBCode içerik türlerindeki bloklar ve sayfalar içeri aktarma için desteklenir. Diğer blok türlerinin manuel olarak oluşturulması gerekir.

:::

### Önceki portalı kaldırmak

Önceki portal tarafından oluşturulmuş tabloları veritabanında bırakın — içe aktarım için onlara ihtiyaç olacak.

## TinyPortal'dan Geçiş

1. TinyPortalMigration eklentisini kurup etkinleştirin
2. İstediğiniz alana gidin — **Bloklar**, **Sayfalar** veya **Kategoriler**, sonra **TinyPortal'dan Aktar**ı seçin.

## EhPortal'dan (SimplePortal) Geçiş

1. EhPortalMigration eklentisini kurup etkinleştirin
2. İstediğiniz alana gidin — **Bloklar**, **Sayfalar** veya **Kategoriler**, sonra **EhPortal'dan Aktar**ı seçin.

## EzPortal'dan Geçiş

1. EzPortalMigration eklentisini kurup etkinleştirin
2. İstediğiniz alana gidin — **Bloklar**, **Sayfalar** veya **Kategoriler**, sonra **EzPortal'dan Aktar**ı seçin.

## İlave Yardım

Eğer taşınma esnasında zorluk yaşarsınız:

1. [Destek forumunda](https://www.simplemachines.org/community/index.php?topic=572393.0) olası çözümleri arayın
2. Sorununuzu tarif eden yeni bir konu açın
3. Ekran görüntüleri ve hata günlüklerini ekleyin

Ya da bu sayfadaki yorum alanını kullanın.
