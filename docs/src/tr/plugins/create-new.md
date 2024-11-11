---
description: Eklenti oluşturma arayüzünün kısa açıklaması
order: 2
---

# Eklenti ekle

Eklentiler, Light Portal'ın yeteneklerini genişleten uzantılardır. Kendi eklentinizi oluşturmak için aşağıdaki talimatları izleyin.

:::info Not

Kendi eklentilerinizi oluşturmak için **PluginMaker**'ı yardımcı olarak kullanabilirsiniz. Bunu _Yönetici -> Portal ayarları -> Eklentiler_ sayfasından indirin ve etkinleştirin.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## Eklenti türünü seçme

Şu anda aşağıdaki eklenti türleri mevcuttur:

### `block`

Portal için yeni blok türleri ekleyen eklentiler.

### `ssi`

Veri almak için SSI fonksiyonlarını kullanan eklentiler (genellikle bloklar).

### `editor`

Farklı içerik türleri için üçüncü taraf bir editör ekleyen eklentiler.

### `comment`

Yerleşik olanın yerine üçüncü taraf bir yorum widget'ı ekleyen eklentiler.

### `parser`

Sayfaların ve blokların içeriği için ayrıştırıcıyı uygulayan eklentiler.

### `article`

Ana sayfadaki makale kartlarının içeriğini işlemek için eklentiler.

### `frontpage`

Portalın ana sayfasını değiştirmek için eklentiler.

### `impex`

Çeşitli portal öğelerini içe aktarma ve dışa aktarma için eklentiler.

### `block_options` | `page_options`

Eklentiler, ilgili varlık (blok veya .sayfa) için ek parametreler ekler.

### `icons`

Arayüz öğelerini değiştirmek veya blok başlıklarında kullanmak için yeni simge kütüphaneleri ekleyen eklentiler.

### `seo`

Forumu ağda görünürlüğünü etkileyen eklentiler.

### `other`

Yukarıdaki kategorilerle ilgili olmayan eklentiler.

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

```php:line-numbers
<?php

namespace Bugo\LightPortal\Plugins\HelloWorld;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class HelloWorld extends Plugin
{
    // FA icon (for blocks only)
    public string $icon = 'fas fa-globe';

    // Your plugin's type
    public string $type = 'other';

    // Optional init method
    public function init(): void
    {
        // Access to global variables: Utils::$context['user'], Config::$modSettings['variable'], etc.
        // Access to language variables: Lang::$txt['lp_hello_world']['variable_name']
    }

    // Custom properties and methods
}

```

## SSI Kullanımı

Eğer eklenti, SSI fonksiyonlarını kullanarak herhangi bir veri almak gerekiyorsa, yerleşik `getFromSsi(string $function, ...$params)` metodunu kullanın. Parametre olarak `$function` değişkenine **SSI.php** dosyasında bulunan fonksiyonlardan birinin adını, `ssi_` ön ekini eklemeden geçmelisiniz. Örneğin:

```php
<?php

// See ssi_topTopics function in the SSI.php file
$data = $this->getFromSsi('topTopics', 'views', 10, 'array');
```

## Composer Kullanımı

Eklentiniz, Composer aracılığıyla kurulan üçüncü taraf kütüphaneleri kullanabilir. Gerekli bağımlılıkları içeren `composer.json` dosyasının eklenti dizininde bulunduğundan emin olun. Eklentinizi yayınlamadan önce, komut satırında eklenti dizinini açın ve şu komutu çalıştırın: `composer install --no-dev -o`. Bunun ardından, eklenti dizininin tamamı SMF için ayrı bir modifikasyon olarak paketlenebilir (örneğin **PluginMaker** paketine bakın).
