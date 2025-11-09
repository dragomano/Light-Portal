@extends('partials.base')

@section('content')
    <div class="lp_frontpage_articles discourse_view">
        @include('partials.pagination')

        <div class="forum-list">
            @foreach ($context['lp_frontpage_articles'] as $article)
                <div class="forum-item windowbg">
                    <div class="forum-content">
                        @unless (empty($article['section']['name']))
                            <span class="tag {{ empty($article['section']['icon']) ? 'category' : 'icon' }}">
								{{ $article['section']['name'] }}
							</span>
                        @endunless
                        <h3>
                            <a href="{{ $article['link'] }}">{{ $article['title'] }}</a>
                        </h3>
                        @unless (empty($article['teaser']))
                            <p>{{ $article['teaser'] }} <a href="{{ $article['link'] }}">{{ $txt['lp_read_more'] }}</a></p>
                        @endunless
                    </div>
                    <div class="forum-replies">
                        @icon('replies') {{ (int) $article['replies']['num'] }}
                    </div>
                    <div class="forum-views">
                        @icon('views') {{ (int) $article['views']['num'] }}
                    </div>
                    <div class="forum-activity">
                        @icon('date') {!! $article['date'] !!}
                    </div>
                </div>
            @endforeach
        </div>

        @include('partials.pagination', ['position' => 'bottom'])
    </div>
@endsection
