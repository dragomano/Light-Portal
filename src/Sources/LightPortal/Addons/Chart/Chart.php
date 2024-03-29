<?php

/**
 * Chart.php
 *
 * @package Chart (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 21.03.24
 */

namespace Bugo\LightPortal\Addons\Chart;

use Bugo\Compat\{Lang, Theme, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField, TextField};

if (! defined('LP_NAME'))
	die('No direct access...');

class Chart extends Block
{
	public string $icon = 'fas fa-chart-simple';

	private array $params = [
		'chart_title'     => '',
		'datasets'        => '',
		'labels'          => '',
		'default_palette' => false,
		'chart_type'      => 'line',
		'stacked'         => false,
		'horizontal'      => false,
	];

	private array $chartTypes = ['line', 'bar', 'pie', 'doughnut', 'polarArea', 'radar'];

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'chart')
			return;

		$params = $this->params;
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'chart')
			return;

		$post = $this->request()->only([
			'set_type', 'set_label', 'set_data', 'set_borderColor', 'set_backgroundColor', 'set_borderWidth'
		]);

		$datasets = [];
		if ($post && isset($post['set_label']) && isset($post['set_data'])) {
			foreach ($post['set_label'] as $key => $name) {
				if (empty($data = $post['set_data'][$key]))
					continue;

				$datasets[] = [
					'type'            => $post['set_type'][$key] ?? '',
					'label'           => $name,
					'data'            => $data,
					'borderColor'     => $post['set_borderColor'][$key] ?? '',
					'backgroundColor' => $post['set_backgroundColor'][$key] ?? '',
					'borderWidth'     => $post['set_borderWidth'][$key] ?? 2,
				];
			}

			$this->request()->put('datasets', json_encode($datasets, JSON_UNESCAPED_UNICODE));
		}

		$params = [
			'chart_title'     => FILTER_DEFAULT,
			'datasets'        => FILTER_DEFAULT,
			'labels'          => FILTER_DEFAULT,
			'default_palette' => FILTER_VALIDATE_BOOLEAN,
			'chart_type'      => FILTER_DEFAULT,
			'stacked'         => FILTER_VALIDATE_BOOLEAN,
			'horizontal'      => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'chart')
			return;

		Utils::$context['lp_chart_types'] = array_combine($this->chartTypes, Lang::$txt['lp_chart']['type_set']);

		TextField::make('chart_title', Lang::$txt['lp_chart']['chart_title'])
			->setTab('content')
			->placeholder(Lang::$txt['lp_chart']['chart_title_placeholder'])
			->setValue(Utils::$context['lp_block']['options']['chart_title'] ?? $this->params['chart_title']);

		CustomField::make('chart', Lang::$txt['lp_chart']['datasets'])
			->setTab('content')
			->setValue($this->getFromTemplate('chart_template'));

		TextField::make('labels', Lang::$txt['lp_chart']['labels'])
			->setTab('content')
			->placeholder(Lang::$txt['lp_chart']['labels_placeholder'])
			->required()
			->setValue(Utils::$context['lp_block']['options']['labels'] ?? $this->params['labels']);

		CheckboxField::make('default_palette', Lang::$txt['lp_chart']['default_palette'])
			->setTab('appearance')
			->setValue(Utils::$context['lp_block']['options']['default_palette']);

		CheckboxField::make('stacked', Lang::$txt['lp_chart']['stacked'])
			->setAfter(Lang::$txt['lp_chart']['stacked_after'])
			->setValue(Utils::$context['lp_block']['options']['stacked']);

		CheckboxField::make('horizontal', Lang::$txt['lp_chart']['horizontal'])
			->setValue(Utils::$context['lp_block']['options']['horizontal']);
	}

	public function prepareAssets(array &$assets): void
	{
		$assets['scripts']['chart'][] = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'chart')
			return;

		$id = $data->id;

		echo '
		<div>
			<canvas id="chart' . $id . '" aria-label="' . (empty($parameters['chart_title']) ? 'Simple chart' : $parameters['chart_title']) . '" role="img"></canvas>
		</div>';

		$type = $parameters['chart_type'] ?? $this->params['chart_type'];

		$datasets = Utils::jsonDecode($parameters['datasets'] ?? $this->params['datasets'], true);
		array_walk($datasets, static fn(&$val) => $val['data'] = explode(', ', $val['data']));
		$datasets = json_encode($datasets);

		$labels = $parameters['labels'] ?? $this->params['labels'];
		$labels = implode(',', array_map(
			static fn($label) => Utils::escapeJavaScript(trim($label)), explode(',', $labels))
		);

		Theme::loadJavaScriptFile('light_portal/chart/chart.umd.min.js', ['minimize' => true]);

		Theme::addInlineJavaScript('
		new Chart("chart' . $id . '", {
			type: "' . $type . '",
			data: {
				labels: [' . $labels . '],
				datasets: ' . $datasets . '
			},
			options: {
				indexAxis: "' . (empty($parameters['horizontal']) ? 'x' : 'y') . '",
				scales: {
					x: {
						stacked: ' . (empty($parameters['stacked']) ? 'false' : 'true') . ',
					},
					y: {
						stacked: ' . (empty($parameters['stacked']) ? 'false' : 'true') . ',
					}
				},
				plugins: {' . (empty($parameters['chart_title']) ? '' : '
					title: {
						display: true,
						text: "' . $parameters['chart_title'] . '"
					},') . '
				}
			}
		});', true);
	}

	public function credits(array &$links): void
	{
		$links[] = [
			'title' => 'Chart.js',
			'link' => 'https://github.com/chartjs/Chart.js',
			'author' => 'Chart.js Contributors',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/chartjs/Chart.js/blob/master/LICENSE.md'
			]
		];
	}
}
