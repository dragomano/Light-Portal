---
description: وصف موجز لواجهة إنشاء الملحق
order: 2
---

# إضافة إضافة

الإضافات هي الإضافات التي توسع قدرات بوابة الضوء. لإنشاء البرنامج المساعد الخاص بك، فقط اتبع الإرشادات أدناه.

## PluginType enum

For better type safety and IDE support, you can use the `PluginType` enum instead of string values for the `type` parameter:

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

Available PluginType values:

- `PluginType::ARTICLE` - For processing article content
- `PluginType::BLOCK` - For blocks
- `PluginType::BLOCK_OPTIONS` - For block options
- `PluginType::COMMENT` - For comment systems
- `PluginType::EDITOR` - For editors
- `PluginType::FRONTPAGE` - For frontpage modifications
- `PluginType::GAMES` - For games
- `PluginType::ICONS` - For icon libraries
- `PluginType::IMPEX` - For import/export
- `PluginType::OTHER` - Default type (can be omitted)
- `PluginType::PAGE_OPTIONS` - For page options
- `PluginType::PARSER` - For parsers
- `PluginType::SEO` - For SEO
- `PluginType::SSI` - For blocks with SSI functions

For plugins extending `Block`, `Editor`, `GameBlock`, or `SSIBlock` classes, the type is automatically inherited and doesn't need to be specified explicitly.

:::info ملاحظة

يمكنك استخدام **الإضافات** كمساعد لإنشاء الإضافات الخاصة بك. قم بتنزيله وتمكينه على صفحة _Admin -> إعدادات البوابة -> الإضافات_.

![Create a new plugin with PluginMaker](create_plugin.png)

:::

## اختيار نوع الملحق

تتوفر حاليا الأنواع التالية من الإضافات:

| Type                            |                                                                                          Description |
| ------------------------------- | ---------------------------------------------------------------------------------------------------: |
| `block`                         |                                   الإضافات التي تضيف نوعا جديدا من البلوكات للبوابة. |
| `ssi`                           |      الإضافات (عادة كتل) التي تستخدم وظائف SSI لاسترداد البيانات. |
| `editor`                        |                               الإضافات التي تضيف محرر ثالث لأنواع مختلفة من المحتوى. |
| `comment`                       |                             الإضافات التي تضيف أداة تعليق طرف ثالث بدلاً من المدمجة. |
| `parser`                        |                                     الإضافات التي تنفذ المحلل لمحتوى الصفحات والكتل. |
| `article`                       |                           الإضافات لمعالجة محتوى بطاقات المقالات في الصفحة الرئيسية. |
| `frontpage`                     |                                          الإضافات لتغيير الصفحة الرئيسية من البوابة. |
| `impex`                         |                                        الإضافات لاستيراد وتصدير مختلف عناصر البوابة. |
| `block_options`, `page_options` | الإضافات التي تضيف معلمات إضافية للكيان المقابل (الكتلة أو صفحة). |
| `icons`                         |       الإضافات التي تضيف مكتبات الأيقونات الجديدة لاستبدال عناصر الواجهة أو لاستخدامها في رؤوس الكتل |
| `seo`                           |                            الإضافات التي تؤثر بطريقة ما على بروز المنتدى على الشبكة. |
| `other`                         |                                           الإضافات التي لا تتصل بأي من الفئات أعلاه. |
| `games`                         |                           Plugins that typically add a block with some kind of game. |

## إنشاء دليل البرنامج المساعد

أنشئ مجلدًا منفصلًا لملفات المكون الإضافي الخاصة بك، داخل `/Sources/LightPortal/Plugins`. على سبيل المثال، إذا كان البرنامج المساعد الخاص بك يسمى \`HelloWorld'، يجب أن تبدو بنية المجلد مثل هذا:

```
...(Plugins)
└── HelloWorld/
    ├── langs/
    │   ├── english.php
    │   └── index.php
    ├── index.php
    └── HelloWorld.php
```

يمكن نسخ الملف `index.php` من مجلدات الإضافات الأخرى. الملف `HelloWorld.php` يحتوي على منطق الإضافة:

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

إذا كان البرنامج المساعد يحتاج إلى استرداد أي بيانات باستخدام دوال SSI، استخدم طريقة `getFromSsi(السلسلة $function، ...$params)` المدمجة. كمعلمة `$function` يجب عليك تمرير اسم إحدى الدوال الواردة في الملف **SSI.php**، بدون بادئة `ssi_`. وعلى سبيل المثال:

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

## Blade templates

Your plugin can use a template with Blade markup. وعلى سبيل المثال:

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

**Instructions:**

1. Create the `views` subdirectory inside your plugin directory if it doesn't exist.
2. Create the file `default.blade.php` with the following content:

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

يمكن للملحق الخاص بك استخدام مكتبات الطرف الثالث المثبتة من خلال الملحن. تأكد من أن ملف 'composer.json' الذي يحتوي على التبعيات الضرورية، موجود في الدليل الإضافي. قبل نشر الإضافة الخاصة بك، افتح دليل الإضافات في سطر الأوامر وتشغيل الأمر: `composer install --no-dev -o`. بعد ذلك، يمكن تعبئة محتويات الدليل المساعد بكاملها كتعديل منفصل لـ SMF (على سبيل المثال راجع حزمة **PluginMaker**).

وعلى سبيل المثال:

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
