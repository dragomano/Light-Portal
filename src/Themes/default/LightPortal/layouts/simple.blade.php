@extends('partials.base')

@section('content')
	<div class="lp_frontpage_articles article_simple_view">
		@include('partials.pagination')

		@foreach ($context['lp_frontpage_articles'] as $article)
			<div class="col-xs-12 col-sm-6 col-md-{{ $context['lp_frontpage_num_columns'] }}">
				@unless (empty($article['image']))
					<div class="article_image lazy" data-bg="{{ $article['image'] }}"></div>
				@endunless

				<div class="mt-6 body">
					<a class="article_title" href="{{ $article['link'] }}">{{ $article['title'] }}</a>

					@unless (empty($article['teaser']))
						<p class="article_teaser">{{ $article['teaser'] }}</p>
					@endunless
				</div>

				<div class="mt-6">
					<a class="bbc_link" href="{{ $article['link'] }}">{{ $txt['lp_read_more'] }}</a>
				</div>
			</div>
		@endforeach

		@include('partials.pagination', ['position' => 'bottom'])
	</div>
@endsection
