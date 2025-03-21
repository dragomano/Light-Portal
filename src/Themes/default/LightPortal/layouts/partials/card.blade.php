<div class="card_info">
	@include('partials.card-header', ['article' => $article])

	<h3>
		<a href="{{ $article['link'] }}">{{ $article['title'] }}</a>
	</h3>

	@unless (empty($article['teaser']))
		<p>{{ $article['teaser'] }}</p>
	@endunless

	@include('partials.card-footer', ['article' => $article])
</div>
