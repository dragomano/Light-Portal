---
description: Eklenti oluşturma arayüzünün kısa açıklaması
order: 2
---

# Eklenti ekle

Eklentiler, Light Portal'ın yeteneklerini genişleten uzantılardır. Kendi eklentinizi oluşturmak için aşağıdaki talimatları izleyin.

## PluginType enum

Daha iyi tür güvenliği ve IDE desteği için, `tür` parametresi için dizge değerleri kullanmak yerine `PluginType` enum türünü kullanabilirsiniz:

```php
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\PluginAttribute;

// Instead of: #[PluginAttribute(type: 'editor')]
#[PluginAttribute(type: PluginType::EDITOR)]

// Instead of: #[PluginAttribute(type: 'block')]
#[PluginAttribute(type: PluginType::BLOCK)]

// Instead of: #[PluginAttribute(type: 'other')]
#[PluginAttribute(type: PluginType::OTHER)]

// Or simply omit the type parameter since OTHER is default:
#[PluginAttribute]
```

Kullanılabilir PluginType değerleri:

- `PluginType::ARTICLE` - Makale içeriğini işlemek için
- `PluginType::BLOCK` - Bloklar için
- `PluginType::BLOCK_OPTIONS` - Blok seçenekleri için
- `PluginType::COMMENT` - Yorum sistemleri için
- `PluginType::EDITOR` - Editörler için
- `PluginType::FRONTPAGE` - Ön sayfa düzenlemeleri için
- `PluginType::GAMES` - Oyunlar için
- `PluginType::ICONS` - Simge kütüphaneleri için
- `PluginType::IMPEX` - İçe/dışa aktarım için
- `PluginType::OTHER` - Varsayılan tür (atlanabilir)
- `PluginType::PAGE_OPTIONS` - Sayfa seçenekleri için
- `PluginType::PARSER` - İşleyiciler için
- `PluginType::SEO` - SEO için
- `PluginType::SSI` - SSI işlevli bloklar için

`Block`, `Editor`, `GameBlock` veya `SSIBlock` sınıflarını genişleten eklentiler için tür kalıtım yoluyla otomatik olarak aktarılır ve özel olarak belirtilmesi gerekmez.

:::info Not

Kendi eklentilerinizi oluşturmak için **PluginMaker**'ı yardımcı olarak kullanabilirsiniz. Bunu _Yönetici -> Portal ayarları -> Eklentiler_ sayfasından indirin ve etkinleştirin.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Eklenti türünü seçme

Şu anda aşağıdaki eklenti türleri mevcuttur:

| Türü                            |                                                                                                                         Açıklama |
| ------------------------------- | -------------------------------------------------------------------------------------------------------------------------------: |
| `block`                         |                                                                Portal için yeni blok türleri ekleyen eklentiler. |
| `ssi`                           |                 Veri almak için SSI fonksiyonlarını kullanan eklentiler (genellikle bloklar). |
| `editor`                        |                                           Farklı içerik türleri için üçüncü taraf bir editör ekleyen eklentiler. |
| `comment`                       |                                       Yerleşik olanın yerine üçüncü taraf bir yorum widget'ı ekleyen eklentiler. |
| `parser`                        |                                         Sayfaların ve blokların içeriği için ayrıştırıcıyı uygulayan eklentiler. |
| `article`                       |                                              Ana sayfadaki makale kartlarının içeriğini işlemek için eklentiler. |
| `frontpage`                     |                                                              Portalın ana sayfasını değiştirmek için eklentiler. |
| `impex`                         |                                            Çeşitli portal öğelerini içe aktarma ve dışa aktarma için eklentiler. |
| `block_options`, `page_options` |      Eklentiler, ilgili varlık (blok veya .sayfa) için ek parametreler ekler. |
| `icons`                         | Arayüz öğelerini değiştirmek veya blok başlıklarında kullanmak için yeni simge kütüphaneleri ekleyen eklentiler. |
| `seo`                           |                                                                  Forumu ağda görünürlüğünü etkileyen eklentiler. |
| `other`                         |                                                              Yukarıdaki kategorilerle ilgili olmayan eklentiler. |
| `games`                         |                                                                 Bir çeşit oyun içerikli blok ekleyen eklentiler. |

## Eklenti dizini oluşturma

Eklenti dosyalarınız için `/Sources/LightPortal/Plugins` içinde ayrı bir klasör oluşturun. Örneğin, eklentinizin adı `HelloWorld` ise, klasör yapısı şu şekilde olmalıdır:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

`index.php` dosyası diğer eklentilerin klasörlerinden kopyalanabilir. `HelloWorld.php` dosyası eklenti mantığını içerir:

```php:line-numbers {16}
<?php declare(strict_types=1);

namespace LightPortal\Plugins\HelloWorld;

use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;

if (! defined('LP_NAME'))
    die('No direct access...');

#[PluginAttribute(icon: 'fas fa-globe')]
class HelloWorld extends Plugin
{
    public function init(): void
    {
        echo 'Hello world!';
    }

    // Other hooks and custom methods
}

```

## SSI

Eğer eklenti, SSI fonksiyonlarını kullanarak herhangi bir veri almak gerekiyorsa, yerleşik `getFromSsi(string $function, ...$params)` metodunu kullanın. Parametre olarak `$function` değişkenine **SSI.php** dosyasında bulunan fonksiyonlardan birinin adını, `ssi_` ön ekini eklemeden geçmelisiniz. Örneğin:

```php:line-numbers {17}
<?php declare(strict_types=1);

namespace LightPortal\Plugins\TopTopics;

use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\SsiBlock;

if (! defined('LP_NAME'))
    die('No direct access...');

#[PluginAttribute(icon: 'fas fa-star')]
class TopTopics extends SsiBlock
{
    public function prepareContent(Event $e): void
    {
        $data = $this->getFromSSI('topTopics', 'views', 10, 'array');

        if ($data) {
            var_dump($data);
        } else {
            echo '<p>No top topics found.</p>';
        }
    }
}
```

## Blade şablonları

Eklentiniz Blade işaretlemesi ile birb şablon kullanabilir. Örneğin:

```php:line-numbers {16,20}
<?php declare(strict_types=1);

namespace LightPortal\Plugins\Calculator;

use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\Block;
use LightPortal\Utils\Traits\HasView;

if (! defined('LP_NAME'))
    die('No direct access...');

#[PluginAttribute(icon: 'fas fa-calculator')]
class Calculator extends Block
{
    use HasView;

    public function prepareContent(Event $e): void
    {
        echo $this->view(params: ['id' => $e->args->id]);
    }
}
```

**Yönergeler:**

1. Eğer halihazırda mevcut değilse, eklenti dizininde `views` adında alt bir klasör oluşturun.
2. Şu içerikli `default.blade.php` dosyasını oluşturun:

```blade
<div class="some-class-{{ $id }}">
    {{-- Your blade markup --}}
</div>

<style>
// Your CSS
</style>

<script>
// Your JS
</script>
```

## Composer

Eklentiniz, Composer aracılığıyla kurulan üçüncü taraf kütüphaneleri kullanabilir. Gerekli bağımlılıkları içeren `composer.json` dosyasının eklenti dizininde bulunduğundan emin olun. Eklentinizi yayınlamadan önce, komut satırında eklenti dizinini açın ve şu komutu çalıştırın: `composer install --no-dev -o`. Bunun ardından, eklenti dizininin tamamı SMF için ayrı bir modifikasyon olarak paketlenebilir (örneğin **PluginMaker** paketine bakın).

Örneğin:

::: code-group

```php:line-numbers {15} [CarbonDate.php]
<?php declare(strict_types=1);

namespace LightPortal\Plugins\CarbonDate;

use Carbon\Carbon;
use LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
    die('No direct access...');

class CarbonDate extends Plugin
{
    public function init(): void
    {
        require_once __DIR__ . '/vendor/autoload.php';

        $date = Carbon::now()->format('l, F j, Y \a\t g:i A');

        echo 'Current date and time: ' . $date;
    }
}
```

```json [composer.json]
{
    "require": {
      "nesbot/carbon": "^3.0"
    },
    "config": {
      "optimize-autoloader": true
    }
}
```

:::
