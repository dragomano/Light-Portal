@extends('admin.partials.entity_edit')

@php use LightPortal\Enums\{ContentClass, TitleClass}; @endphp

@section('preview')
	@if (isset($context['preview_content']) && empty($context['post_errors']))
		<div class="preview_frame">
			{!! sprintf(TitleClass::values()[$context['lp_block']['title_class']], $context['preview_title']) !!}
			<div class="preview block_{{ $context['lp_block']['type'] }}">
				{!! sprintf(ContentClass::values()[$context['lp_block']['content_class']], $context['preview_content']) !!}
			</div>
		</div>
	@else
		<div class="cat_bar">
			<h3 class="catbg">{!! $txt['lp_' . $context['lp_block']['type']]['title'] !!}</h3>
		</div>
		<div class="information">
			{{ $txt['lp_' . $context['lp_block']['type']]['description'] }}
		</div>
	@endif
@endsection

@section('form_data')
	x-data='{ title: @json($context['lp_block']['title'], JSON_UNESCAPED_UNICODE) }'
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
				'id'    => 'appearance',
				'icon'  => 'design',
				'title' => $txt['lp_tab_appearance'],
				'type'  => Tab::APPEARANCE,
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
				'name'  => 'block_id',
				'value' => $context['lp_block']['id'],
			],
			[
				'name'  => 'add_block',
				'value' => $context['lp_block']['type'],
			],
		],
	])
@endpush

@push('buttons')
	@include('admin.partials._buttons', [
		'id'     => $context['lp_block']['id'],
		'entity' => 'block',
	])
@endpush

@push('script', 'const block = new Block();')
