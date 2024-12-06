<?php

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\LightPortal\Utils\Icon;

function template_show_events(array $data): void
{
	if (empty($data['birthdays']) && empty($data['holidays']) && empty($data['events'])) {
		echo Lang::$txt['calendar_empty'];
		return;
	}

	show_birthdays($data);

	show_holidays($data);

	show_events($data);
}

function show_birthdays(array $data): void
{
	if (empty($data['birthdays']))
		return;

	echo '
		<div>';

	if (! empty($data['events']) || ! empty($data['holidays'])) {
		echo '
			<strong>', Lang::$txt['birthdays'], '</strong>';
	}

	echo '
			<ul class="fa-ul">';

	foreach ($data['birthdays'] as $members) {
		$list = [];
		$localDate = '';

		foreach ($members as $member) {
			if (is_array($member)) {
				$list[] = $member;
			} else {
				$localDate = $member;
				unset($members['date_local']);
			}
		}

		foreach ($list as $member) {
			$link = Config::$scripturl . '?action=profile;u=' . $member['id'];
			$age = isset($member['age']) ? ' (' . $member['age'] . ')' : '';

			echo '
			<li>
				<span class="fa-li">', Icon::get('cake'), '</span>
				<strong>', $localDate, '</strong> &ndash; <a href="', $link, '">', $member['name'], $age, '</a>
			</li>';
		}
	}

	echo '
			</ul>
		</div>';
}

function show_holidays(array $data): void
{
	if (empty($data['holidays']))
		return;

	echo '
		<div>';

	if (! empty($data['events']) || ! empty($data['birthdays'])) {
		echo '
			<strong>', Lang::$txt['calendar_prompt'], '</strong>';
	}

	echo '
			<ul class="fa-ul">';

	foreach ($data['holidays'] as $holidays) {
		$list = [];
		$localDate = '';

		foreach ($holidays as $key => $holiday) {
			if (is_int($key)) {
				$list[] = $holiday;
			} else {
				$localDate = $holiday;
				unset($holidays['date_local']);
			}
		}

		foreach ($list as $holiday) {
			echo '
			<li>
				<span class="fa-li">', Icon::get('calendar'), '</span>
				<strong>', $localDate, '</strong> &ndash; ', $holiday, '
			</li>';
		}
	}

	echo '
			</ul>
		</div>';
}

function show_events(array $data): void
{
	if (empty($data['events']))
		return;

	echo '
		<div>';

	if (! empty($data['birthdays']) || ! empty($data['holidays'])) {
		echo '
			<strong>', Lang::$txt['events'], '</strong>';
	}

	echo '
			<ul class="fa-ul">';

	foreach ($data['events'] as $events) {
		foreach ($events as $event) {
			if (empty($event['allday'])) {
				$date = trim((string) $event['start_date_local']) . ', ' . trim((string) $event['start_time_local']) . ' &ndash; ';

				if ($event['start_date_local'] !== $event['end_date_local']) {
					$date .= trim((string) $event['end_date_local']) . ', ';
				}

				$date .= trim((string) $event['end_time_local']);
			} else {
				$date = trim((string) $event['start_date_local']) . ($event['start_date'] !== $event['end_date'] ? ' &ndash; ' . trim((string) $event['end_date_local']) : '');
			}

			echo '
				<li>
					<span class="fa-li">', Icon::get('event'), '</span>
					<strong>', $date, '</strong> &ndash; ', $event['link'], '
				</li>';
		}
	}

	echo '
			</ul>
		</div>';
}
