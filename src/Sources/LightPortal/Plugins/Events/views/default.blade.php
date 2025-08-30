@if (empty($data['birthdays']) && empty($data['holidays']) && empty($data['events']))
	{{ $txt['calendar_empty'] }}
@else
	@unless (empty($data['birthdays']))
		<div>
			<strong>{{ $txt['birthdays'] }}</strong>

			<ul class="fa-ul">
				@foreach ($data['birthdays'] as $members)
					@php
						$list = [];
						$localDate = '';

						foreach ($members as $member) {
							if (is_array($member)) {
								$list[] = $member;
							} else {
								$localDate = $member;
							}
						}
					@endphp

					@foreach ($list as $member)
						@php $link = $scripturl . '?action=profile;u=' . $member['id']; @endphp
						<li>
							<span class="fa-li">@icon('cake')</span>
							<strong>{{ $localDate }}</strong> &ndash;
							<a href="{{ $link }}">{{ $member['name'] }}@isset($member['age']) ({{ $member['age'] }}) @endisset</a>
						</li>
					@endforeach
				@endforeach
			</ul>
		</div>
	@endunless

	@unless (empty($data['holidays']))
		<div>
			<strong>{{ $txt['calendar_prompt'] }}</strong>

			<ul class="fa-ul">
				@foreach ($data['holidays'] as $holidays)
					@php
						$list = [];
						$localDate = '';

						foreach ($holidays as $key => $holiday) {
							if (is_int($key)) {
								$list[] = $holiday;
							} else {
								$localDate = $holiday;
							}
						}
					@endphp

					@foreach ($list as $holiday)
						<li>
							<span class="fa-li">@icon('calendar')</span>
							<strong>{{ $localDate }}</strong> &ndash; {{ $holiday }}
						</li>
					@endforeach
				@endforeach
			</ul>
		</div>
	@endunless

	@unless (empty($data['events']))
		<div>
			<strong>{{ $txt['events'] }}</strong>

			<ul class="fa-ul">
				@foreach ($data['events'] as $events)
					@foreach ($events as $event)
						@php
							if (empty($event['allday'])) {
								$date = trim((string) ($event['start_date_local'] ?? '')) . ', ' . trim((string) ($event['start_time_local'] ?? '')) . ' &ndash; ';

								if (($event['start_date_local'] ?? null) !== ($event['end_date_local'] ?? null)) {
									$date .= trim((string) ($event['end_date_local'] ?? '')) . ', ';
								}

								$date .= trim((string) ($event['end_time_local'] ?? ''));
							} else {
								$date = trim((string) ($event['start_date_local'] ?? ''));

								if (($event['start_date'] ?? null) !== ($event['end_date'] ?? null)) {
									$date .= ' &ndash; ' . trim((string) ($event['end_date_local'] ?? ''));
								}
							}
						@endphp
						<li>
							<span class="fa-li">@icon('event')</span>
							<strong>{{ $date }}</strong> &ndash; {!! $event['link'] !!}
						</li>
					@endforeach
				@endforeach
			</ul>
		</div>
	@endunless
@endif
