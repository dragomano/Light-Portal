---
description: Portal kurulumu için gereksinimlerin listesi ve olası sorunlara çözümler
order: 1
---

# Kurulum

Burada herhangi bir incelik yoktur. Light Portal, SMF için diğer tüm modifikasyonlar gibi - paket yöneticisi aracılığıyla kurulabilir.

## Gereksinimler

- [SMF 2.1.x](https://download.simplemachines.org)
- JavaScript etkin modern bir tarayıcı
- İnternet (portal ve birçok eklenti, CDN'den betikler ve stiller yükler)
- PHP 8.1 veya daha yüksek
- Bazı dil dizelerini doğru bir şekilde yerelleştirmek için `intl` PHP uzantısı
- Sayfaları ve blokları dışa aktarmak/içe aktarmak için `dom` ve `simplexml` PHP uzantıları
- Eklentileri dışa aktarmak/içe aktarmak için `zip` PHP uzantısı

:::info Not

Portal dosyalarıyla birlikte paketi [resmi katalogdan](https://custom.simplemachines.org/mods/index.php?mod=4244) indirmeniz ve forumunuzdaki paket yöneticisi aracılığıyla yüklemeniz yeterlidir.

:::

## Sorun Giderme

Eğer barındırma hizmetiniz izinlerle ilgili çok "akıllı" ise ve portal dosyaları kurulum sırasında açılmadıysa, modifikasyon arşivinden `Themes` ve `Sources` dizinlerini forum klasörünüze (aynı `Themes` ve `Sources` klasörlerinin bulunduğu, ayrıca `cron.php`, `SSI.php`, `Settings.php` gibi dosyaların da bulunduğu yer) manuel olarak çıkarmanız ve uygun izinleri ayarlamanız gerekir. En sık kullanılan izinler dosyalar için `644`, `664` veya `666`, klasörler için ise `755`, `775` veya `777`'dir.

Ayrıca, modifikasyon arşivinden `database.php` dosyasını forumunuzun kök dizinine çıkarmanız, bu dosya için yürütme izinlerini ayarlamanız (`666`) ve tarayıcıdan erişmeniz gerekir (forum yöneticisi olarak giriş yapmış olmalısınız). Bu dosya, portal tarafından kullanılan tabloları oluşturmak için talimatlar içerir.
