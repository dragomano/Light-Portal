<?php

/**
 * Chart.php
 *
 * @package Chart (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 11.12.23
 */

namespace Bugo\LightPortal\Addons\Chart;

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

	public function blockOptions(array &$options): void
	{
		$options['chart']['parameters'] = $this->params;
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'chart')
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

		$parameters['chart_title']     = FILTER_DEFAULT;
		$parameters['datasets']        = FILTER_DEFAULT;
		$parameters['labels']          = FILTER_DEFAULT;
		$parameters['default_palette'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['chart_type']      = FILTER_DEFAULT;
		$parameters['stacked']         = FILTER_VALIDATE_BOOLEAN;
		$parameters['horizontal']      = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'chart')
			return;

		$this->context['lp_chart_types'] = array_combine($this->chartTypes, $this->txt['lp_chart']['type_set']);

		TextField::make('chart_title', $this->txt['lp_chart']['chart_title'])
			->setTab('content')
			->setAttribute('placeholder', $this->txt['lp_chart']['chart_title_placeholder'])
			->setValue($this->context['lp_block']['options']['parameters']['chart_title'] ?? $this->params['chart_title']);

		CustomField::make('chart', $this->txt['lp_chart']['datasets'])
			->setTab('content')
			->setValue($this->getFromTemplate('chart_template'));

		TextField::make('labels', $this->txt['lp_chart']['labels'])
			->setTab('content')
			->setAttribute('placeholder', $this->txt['lp_chart']['labels_placeholder'])
			->setAttribute('required', true)
			->setValue($this->context['lp_block']['options']['parameters']['labels'] ?? $this->params['labels']);

		CheckboxField::make('default_palette', $this->txt['lp_chart']['default_palette'])
			->setTab('appearance')
			->setValue($this->context['lp_block']['options']['parameters']['default_palette']);

		CheckboxField::make('stacked', $this->txt['lp_chart']['stacked'])
			->setAfter($this->txt['lp_chart']['stacked_after'])
			->setValue($this->context['lp_block']['options']['parameters']['stacked']);

		CheckboxField::make('horizontal', $this->txt['lp_chart']['horizontal'])
			->setValue($this->context['lp_block']['options']['parameters']['horizontal']);
	}

	public function prepareAssets(array &$assets): void
	{
		$assets['scripts']['chart'][] = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
	}

	public function prepareContent($data, array $parameters): void
	{
		if ($data->type !== 'chart')
			return;

		$block_id = $data->block_id;

		echo /** @lang text */ '
		<div>
			<canvas id="chart' . $block_id . '" aria-label="' . (empty($parameters['chart_title']) ? 'Simple chart' : $parameters['chart_title']) . '" role="img"></canvas>
		</div>';

		$type = $parameters['chart_type'] ?? $this->params['chart_type'];

		$datasets = $this->jsonDecode($parameters['datasets'] ?? $this->params['datasets']);
		array_walk($datasets, fn(&$val) => $val['data'] = explode(', ', $val['data']));
		$datasets = json_encode($datasets);

		$labels = $parameters['labels'] ?? $this->params['labels'];
		$labels = implode(',', array_map(fn($label) => $this->jsEscape(trim($label)), explode(',', $labels)));

		$this->loadJavaScriptFile('light_portal/chart/chart.umd.min.js', ['minimize' => true]);

		$this->addInlineJavaScript('
		new Chart("chart' . $block_id . '", {
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
