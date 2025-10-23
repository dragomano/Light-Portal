<?php declare(strict_types=1);

use Bugo\Compat\BrowserDetector;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;

use function LightPortal\app;

function template_debug_above(): void
{
	if (empty(Config::$modSettings['lp_show_portal_queries']) || BrowserDetector::isBrowser('is_mobile'))
		return;

	$sql = app(PortalSqlInterface::class);
	$profiler = $sql->getAdapter()->getProfiler();
	$totalQueries = count($profiler->getProfiles());

	if ($totalQueries === 0) {
		return;
	}

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', sprintf(Lang::$txt['debug_queries_used'], $totalQueries), '</h3>
	</div>
	<table class="table_grid">
		<tbody>';

	$index = 1;
	foreach ($profiler->getProfiles() as $profile) {
		$sqlText = htmlspecialchars($profile['sql']);
		$time = number_format($profile['elapse'], 6);
		$location = 'unknown';

		if (isset($profile['backtrace']) && $profile['backtrace']) {
			$bt = $profile['backtrace'];
			$location = sprintf(
				'%s:%d',
				basename($bt['file'] ?? 'unknown'),
				$bt['line'] ?? 0
			);
		}

		echo sprintf('
			<tr class="windowbg">
				<td>%s</td>
				<td>%s</td>
				<td>%ss</td>
				<td>%s</td>
			</tr>',
			$index++,
			$sqlText,
			$time,
			$location
		);
	}

	echo '
		</tbody>
	</table>';
}

function template_debug_below(): void
{
	echo '
	<div class="centertext clear noticebox smalltext" style="margin-top: 2px">
		', Utils::$context['lp_load_page_stats'], '
	</div>';
}
