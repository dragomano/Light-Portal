@php use LightPortal\Utils\Icon; @endphp

<div class="noticebox reference-links">
	@foreach ($context['lp_reference_links'] as $link)
		<a class="link-item {{ $link['class'] ?? 'bbc_link' }}" href="{{ $link['href'] ?? '' }}" {!! $link['params'] !!}>
			@if($link['icon']){!! Icon::parse($link['icon']) !!}@endif
			{{ $link['text'] }}
		</a>
	@endforeach
</div>

<style>
	.reference-links {
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
		justify-content: center;

		a {
			flex: 1 1 200px;
			max-width: 280px;
			text-align: center;
		}
	}

	@media (max-width: 767px) {
		.reference-links a {
			flex: 0 1 100%;
		}
	}
</style>
