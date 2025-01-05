<?php declare(strict_types=1);

/**
 * @package Chart (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.01.25
 */

namespace Bugo\LightPortal\Plugins\Chart;

use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\TextField;
use Bugo\LightPortal\Utils\Str;

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

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = $this->params;
	}

	public function validateBlockParams(Event $e): void
	{
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

		$e->args->params = [
			'chart_title'     => FILTER_DEFAULT,
			'datasets'        => FILTER_DEFAULT,
			'labels'          => FILTER_DEFAULT,
			'default_palette' => FILTER_VALIDATE_BOOLEAN,
			'chart_type'      => FILTER_DEFAULT,
			'stacked'         => FILTER_VALIDATE_BOOLEAN,
			'horizontal'      => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		Utils::$context['lp_chart_types'] = array_combine($this->chartTypes, $this->txt['type_set']);

		$options = $e->args->options;

		TextField::make('chart_title', $this->txt['chart_title'])
			->setTab(Tab::CONTENT)
			->placeholder($this->txt['chart_title_placeholder'])
			->setValue($options['chart_title'] ?? $this->params['chart_title']);

		CustomField::make($this->name, $this->txt['datasets'])
			->setTab(Tab::CONTENT)
			->setValue($this->getFromTemplate('chart_template', $options));

		TextField::make('labels', $this->txt['labels'])
			->setTab(Tab::CONTENT)
			->placeholder($this->txt['labels_placeholder'])
			->required()
			->setValue($options['labels'] ?? $this->params['labels']);

		CheckboxField::make('default_palette', $this->txt['default_palette'])
			->setTab(Tab::APPEARANCE)
			->setValue($options['default_palette']);

		CheckboxField::make('stacked', $this->txt['stacked'])
			->setDescription($this->txt['stacked_after'])
			->setValue($options['stacked']);

		CheckboxField::make('horizontal', $this->txt['horizontal'])
			->setValue($options['horizontal']);
	}

	public function prepareAssets(Event $e): void
	{
		$e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
	}

	public function prepareContent(Event $e): void
	{
		[$id, $parameters] = [$e->args->id, $e->args->parameters];

		echo Str::html('div')->addHtml(
			Str::html('canvas', [
				'id' => $this->name . $id,
				'aria-label' => empty($parameters['chart_title'])
					? $this->txt['chart_title']
					: $parameters['chart_title'],
				'role' => 'img'
			])
		);

		$type = $parameters['chart_type'] ?? $this->params['chart_type'];

		$datasets = Utils::jsonDecode($parameters['datasets'] ?? $this->params['datasets'], true) ?? [];
		array_walk($datasets, static fn(&$val) => $val['data'] = explode(', ', (string) $val['data']));
		$datasets = json_encode($datasets);

		$labels = $parameters['labels'] ?? $this->params['labels'];
		$labels = implode(',', array_map(
			static fn($label) => Utils::escapeJavaScript(trim($label)), explode(',', (string) $labels))
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

	public function credits(Event $e): void
	{
		$e->args->links[] = [
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
