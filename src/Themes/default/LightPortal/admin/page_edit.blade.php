@extends('admin.partials.entity_edit')

@php
	$type = $context['lp_page']['type'];
@endphp

@section('preview')
	@if (isset($context['preview_content']) && empty($context['post_errors']))
		<div class="cat_bar">
			<h3 class="catbg">{!! $context['preview_title'] !!}</h3>
		</div>
		<div class="roundframe noup page_{{ $type }}">
			{!! $context['preview_content'] !!}
		</div>
	@else
		<div class="cat_bar">
			<h3 class="catbg">{!! $context['page_area_title'] !!}</h3>
		</div>
		<div class="information">
			<div>
				{!! $txt['lp_' . $type]['description'] ?? $type !!}
			</div>
		</div>
	@endif
@endsection

@section('form_data')
	x-data='{ title: @json($context['lp_page']['title'], JSON_UNESCAPED_UNICODE) }'
@endsection

@php use LightPortal\Enums\Tab; @endphp

@section('tabs')
	@include('admin.partials._tabs', [
		'tabs' => [
			[
				'id'    => 'common',
				'icon'  => 'content',
				'title' => $txt['lp_tab_content'],
			],
			[
				'id'    => 'access',
				'icon'  => 'access',
				'title' => $txt['lp_tab_access_placement'],
				'type'  => Tab::ACCESS_PLACEMENT,
			],
			[
				'id'    => 'seo',
				'icon'  => 'spider',
				'title' => $txt['lp_tab_seo'],
				'type'  => Tab::SEO,
			],
			[
				'id'    => 'tuning',
				'icon'  => 'tools',
				'title' => $txt['lp_tab_tuning'],
				'type'  => Tab::TUNING,
			]
		]
	])
@endsection

@push('inputs')
	@include('admin.partials._inputs', [
		'inputs' => [
			[
				'name'  => 'page_id',
				'value' => $context['lp_page']['id'],
			],
			[
				'name'  => 'add_page',
				'value' => $type,
			],
		],
	])
@endpush

@push('buttons')
	@include('admin.partials._buttons', [
		'id'     => $context['lp_page']['id'],
		'entity' => 'page',
	])
@endpush

@push('script', 'page = new Page();')
