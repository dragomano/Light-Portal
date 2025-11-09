@extends('admin.partials.entity_edit')

@section('preview')
	@include('admin.partials._preview', [
		'show_preview' => isset($context['preview_content']),
		'preview_type' => 'content',
	])
@endsection

@section('form_data')
	x-data='{ title: @json($context['lp_category']['title'], JSON_UNESCAPED_UNICODE) }'
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
				'id'    => 'appearance',
				'icon'  => 'design',
				'title' => $txt['lp_tab_appearance'],
				'type'  => Tab::APPEARANCE,
			],
			[
				'id'    => 'seo',
				'icon'  => 'spider',
				'title' => $txt['lp_tab_seo'],
				'type'  => Tab::SEO,
			]
		]
	])
@endsection

@push('inputs')
	@include('admin.partials._inputs', [
		'inputs' => [
			[
				'name'  => 'category_id',
				'value' => $context['lp_category']['id'],
			],
		],
	])
@endpush

@push('buttons')
	@include('admin.partials._buttons', [
		'id'     => $context['lp_category']['id'],
		'entity' => 'category',
	])
@endpush

@push('script', 'const category = new Category();')
