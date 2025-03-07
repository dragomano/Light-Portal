---
description: تعليمات لإنشاء مخططات بوابتك الخاصة
---

# إنشاء تخطيط الصفحة الأمامية الخاصة

:::info ملاحظة

منذ الإصدار 2.6 نستخدم [BladeOne](https://github.com/EFTEC/BladeOne) لتقديم مخططات الصفحة الأمامية.

:::

بالإضافة إلى التخطيطات الموجودة، يمكنك دائماً إضافة الخاص بك.

للقيام بذلك، أنشئ ملف `custom.blade.php` في دليل `/Themes/default/portal_layouts`:

```php:line-numbers {6,16}
@extends('partials.base')

@section('content')
	<!-- <div> @dump($context['user']) </div> -->

	<div class="lp_frontpage_articles article_custom">
		@include('partials.pagination')

		@foreach ($context['lp_frontpage_articles'] as $article)
		<div class="
			col-xs-12 col-sm-6 col-md-4
			col-lg-{{ $context['lp_frontpage_num_columns'] }}
			col-xl-{{ $context['lp_frontpage_num_columns'] }}
		">
			<figure class="noticebox">
				{!! parse_bbc('[code]' . print_r($article, true) . '[/code]') !!}
			</figure>
		</div>
		@endforeach

		@include('partials.pagination', ['position' => 'bottom'])
	</div>
@endsection
```

بعد ذلك سترى تخطيط جديد للصفحة الأمامية - 'مخصص' - على إعدادات البوابة:

![Select custom template](set_custom_template.png)

يمكنك إنشاء أكبر عدد من التخطيطات كما تريد. استخدم `debug.blade.php` و مخططات أخرى في دليل `/Themes/default/LightPortal/layouts` كأمثلة.

لتخصيص الستيسفيت، قم بإنشاء ملف `portal_custom.css` في دليل `/Themes/default/css`:

```css {3}
/* Custom layout */
.article_custom {
  /* Your rules */
}
```

:::tip نصيحة

إذا كنت قد أنشأت قالب الصفحة الأمامية الخاص بك وترغب في مشاركته مع المطور والمستخدمين الآخرين، استخدم https://codepen.io/pen/ أو غيرها من الموارد المماثلة.

:::
