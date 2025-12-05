---
description: قائمة بمتطلبات تركيب البوابة، فضلا عن حلول للمشاكل المحتملة
order: 1
---

# تثبيت

لا توجد أي دقائق هنا. Light Portal يمكن تثبيتها مثل أي تعديل آخر لـ SMF - من خلال مدير الحزمة.

## المتطلبات

- [SMF 2.1.x](https://download.simplemachines.org)
- متصفح حديث مع تفعيل JavaScript
- الإنترنت (البوابة والعديد من الإضافات تقوم بتحميل السكربتات والأنماط من CDN)
- PHP 8.2 or higher
- امتداد PHP `intl` لتوطين بعض سلاسل اللغة بشكل صحيح
- امتدادات PHP `dom` و `simplexml` لتصدير/استيراد الصفحات والكتل
- امتداد PHP `zip` لتصدير/استيراد الإضافات
- MySQL 5.7+ / MariaDB 10.5+ / PostgreSQL 12+

:::info ملاحظة

يكفي تنزيل الحزمة التي تحتوي على ملفات البوابة من [الدليل الرسمي](https://custom.simplemachines.org/mods/index.php?mod=4244) ورفعها عبر مدير الحزم في منتداك.

:::

## Testing

You can try our [Docker files](https://github.com/dragomano/Light-Portal/tree/d1074c8486ed9eb2f9e89e3afebce2b914d4d570/_docker) or your preffered LAMP/WAMP/MAMP app.
