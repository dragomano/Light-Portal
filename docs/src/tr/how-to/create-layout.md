---
description: Light Portal'ın şablon sistemi, Blade şablonlama, düzenler ve temalar hakkında kapsamlı rehber
---

# Özel düzenler oluşturun

Light Portal, Laravel'in Blade şablon motorunun tek başına uyarlaması olan [BladeOne](https://github.com/EFTEC/BladeOne) tabanlı, esnek bir şablon sistemi kullanır. Bu sistem portalınızın yapısını ve görünümünü düzenler, temalar ve tekrar kullanılabilir bileşenlerle özelleştirmenizi sağlar.

## Şablon sistemi

### Blade şablon motoru

Blade, PHP ve HTML'yi karıştırmak için okunaklı ve temiz söz dizimi sağlayan güçlü bir şablon motorudur. Temel özellikler:

- **Şablon Kalıtımı**: Düzen hiyerarşileri oluşturmak için `@extends` ve `@section` yönergelerini kullanın
- **Includes**: Bileşenleri `@include` yönergesiyle tekrar kullanın
- **Kontrol Kalıpları**: `@if`, `@foreach`, `@while` vb. PHP-tarzı sözdizimi.

Blade işaretleme ile ilgili ayrıntılı bilgiyi [burada](https://github.com/EFTEC/BladeOne/wiki/Template-variables) görebilirsiniz.

### Düzenler

Düzenler ön sayfanızın genel yapısını belirler. `/Themes/default/LightPortal/layouts/` içerisinde yer alır ve ön sayfa makalelerinin nasıl düzenlendiğini belirler. Örnek olarak:

- `default.blade.php` - Standart ızgara düzeni
- `simple.blade.php` - Minimalist tasarım
- `modern.blade.php` - Çağda stillendirme
- `featured_grid.blade.php` - Vurgulanmış içerik ızgarası

### Parçalı

`/Themes/default/LightPortal/layouts/partials/` içerisinde saklanan tekrar kullanılabilir şablon bileşenleri:

- `base.blade.php` - Ana düzen sarmalayıcısı
- `card.blade.php` - Makale kartı şablonu
- `pagination.blade.php` - Sayfa gezintisi
- `image.blade.php` - Görsel gösterim bileşeni

### Şablonlar ve varlıklar

- `/Themes/default/LightPortal`: Portal şablon dosyaları
- `/languages/LightPortal`: Yerelleştirme dosyaları
- `/css/light_portal`: CSS geliştirmeleri
- `/scripts/light_portal`: JavaScript geliştirmeleri

## Düzen örneği

Mevcut ön sayfa düzenlerine ek olarak, her zaman kendi düzeninizi ekleyebilirsiniz.

Bunu yapmak için, `/Themes/default/portal_layouts` dizininde `custom.blade.php` adlı bir dosya oluşturun:

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

<style>
.article_custom {
	// Your CSS
}
</style>
```

Bundan sonra, portal ayarlarında yeni bir ana sayfa düzeni - `Custom` - göreceksiniz:

![Select custom template](set_custom_template.png)

İstediğiniz kadar böyle düzen oluşturabilirsiniz. `/Themes/default/LightPortal/layouts` dizinindeki `debug.blade.php` ve diğer düzenleri örnek olarak kullanın.

## CSS özelleştirme

Herhangi bir şeyin görünümünü kendi stillerinizi ekleyerek kolayca değiştirebilirsiniz. Sadece `Themes/default/css` dizininde `portal_custom.css` adında yeni bir dosya oluşturun ve CSS kodlarınızı buraya koyun.

:::tip Tavsiye

Kendi ana sayfa şablonunuzu oluşturduysanız ve bunu geliştirici ve diğer kullanıcılarla paylaşmak istiyorsanız, https://codepen.io/pen/ veya diğer benzer kaynakları kullanın.

:::
