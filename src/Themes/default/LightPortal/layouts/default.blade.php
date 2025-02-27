@extends('partials.base')

@section('content')
	<div class="lp_frontpage_articles article_view">
		@include('partials.pagination')

		@foreach ($context['lp_frontpage_articles'] as $article)
			<div class="col-xs-12 col-sm-6 col-md-{{ $context['lp_frontpage_num_columns'] }}">
				<article class="roundframe{{ $article['css_class'] ?? '' }}">
					@include('partials.image', ['article' => $article])

					@include('partials.card', ['article' => $article])
				</article>
			</div>
		@endforeach

		@include('partials.pagination', ['position' => 'bottom'])
	</div>
@endsection
