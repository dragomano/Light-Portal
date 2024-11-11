---
description: Mevcut portal ayarlarının kısa bir özeti
order: 3
outline:
  - 2
  - 3
---

# Portal ayarları

Portal ayarlarını açmak için ana forum menüsündeki öğe veya yönetici panelindeki ilgili bölüm aracılığıyla hızlı erişimi kullanın.

Mevcut ayarların her birini ayrıntılı olarak tanımlamayacağız, yalnızca en önemli olanları anacağız.

## Genel ayarlar

Bu bölümde portal ön sayfasını tamamen özelleştirebilir, bağımsız modu etkinleştirebilir ve portal öğelerine erişmek için kullanıcı izinlerini değiştirebilirsiniz.

### Ön sayfa ve makaleler için ayarlar

Portal ana sayfasının içeriğini değiştirmek için uygun "portal ana sayfası" modunu seçin:

- Devre dışı
- Belirli bir sayfa (yalnızca seçilen sayfa görüntülenecektir)
- Seçili kategorilerdeki tüm sayfalar
- Seçili sayfalar
- Seçili forumlardaki tüm konular
- Seçili konular
- Seçili bölümler

### Bağımsız mod

Bu, kendi ana sayfanızı belirleyebileceğiniz (başka bir sitede bile olsa) ve ana menüden gereksiz öğeleri (kullanıcı listesi, takvim vb.) kaldırabileceğiniz bir moddur. Örneğin, forum kökünde `portal.php` dosyasına bakın.

### Izinler

Burada, portalın çeşitli öğeleri (bloklar ve sayfalar) ile KİMİN ve NEYİ yapabileceğini basitçe belirtirsiniz.

## Sayfalar ve bloklar

Bu bölümde, sayfaların ve blokların oluşturulması ve görüntülenmesi sırasında kullanılan genel ayarları değiştirebilirsiniz.

## Paneller

Bu bölümde, mevcut portal panellerinin bazı ayarlarını değiştirebilir ve bu panellerdeki blokların yönünü özelleştirebilirsiniz.

![Panels](panels.png)

## Çeşitli

Bu bölümde, şablon ve eklenti geliştiricileri için faydalı olabilecek portalın çeşitli yardımcı ayarlarını değiştirebilirsiniz.

### Uyumluluk modu

- Portalın **action** parametresinin değeri - bu ayarı değiştirerek Light Portal'ı diğer benzer modifikasyonlarla birlikte kullanabilirsiniz. Böylece ana sayfa belirtilen adreste açılacaktır.
- Portal sayfaları için **page** parametresi - yukarıya bakın. Benzer şekilde, portal sayfaları için - parametreyi değiştirin ve farklı URL'lerle açılacaklardır.

### Bakım

- Portal tablolarının haftalık optimizasyonu - bu seçeneği etkinleştirerek, haftada bir kez veritabanındaki portal tablolarındaki boş değerler içeren satırların silinmesini ve tabloların optimize edilmesini sağlayabilirsiniz.
