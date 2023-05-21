@if (empty($context['lp_active_blocks']))
<div class="col-xs">
@endif
    <!-- <div> @dump($context['user']) </div> -->

    <div class="lp_frontpage_articles article_custom">
        {{ show_pagination() }}

        @foreach($context['lp_frontpage_articles'] as $article)
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-{{ $context['lp_frontpage_num_columns'] }} col-xl-{{ $context['lp_frontpage_num_columns'] }}">
            <figure class="noticebox">{!! parse_bbc('[code]' . print_r($article, true) . '[/code]') !!}</figure>
        </div>
        @endforeach

        {{ show_pagination('bottom') }}
    </div>

@if (empty($context['lp_active_blocks']))
</div>
@endif