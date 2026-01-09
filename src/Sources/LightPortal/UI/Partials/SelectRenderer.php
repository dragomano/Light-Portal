<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\UI\Partials;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\UI\ViewInterface;

if (! defined('SMF'))
	die('No direct access...');

readonly class SelectRenderer
{
	public function __construct(private ViewInterface $view)
	{
		$view->setTemplateDir(realpath(dirname(__DIR__, 4) . '/Themes/default/LightPortal/views'));
	}

	public function render(SelectInterface $select, array $params = []): string
	{
		$config = array_merge($select->getParams(), $params);

		if (empty($config['id'])) {
			$config['id'] = $this->generateId($select);
		}

		$templateData = [
			'id'      => $config['id'],
			'data'    => $select->getData(),
			'config'  => $config,
			'txt'     => Lang::$txt,
			'context' => Utils::$context,
		];

		$initOptions = $this->buildInitOptions($config, $params['template'], $templateData);

		$templateData['initJs'] = $this->formatPrettyOptions($initOptions);

		return $this->view->render($params['template'], $templateData);
	}

	protected function generateId(SelectInterface $select): string
	{
		$class = $select::class;
		$baseName = substr($class, strrpos($class, '\\') + 1);
		$baseName = preg_replace('/([a-z])([A-Z])/', '$1_$2', $baseName);

		return 'lp_' . strtolower($baseName);
	}

	protected function buildInitOptions(array $config, string $template, array $templateData): array
	{
		$id = $templateData['id'];
		$txt = $templateData['txt'];
		$context = $templateData['context'];
		$config['data'] = $templateData['data'];

		return match ($template) {
			'preview_select'   => $this->buildPreviewSelectOptions($config, $txt, $context, $id),
			'icon_select'      => $this->buildIconSelectOptions($config, $txt, $context, $id),
			'page_icon_select' => $this->buildPageIconSelectOptions($config, $txt, $context, $id),
			default            => $this->buildVirtualSelectOptions($config, $txt, $context, $id),
		};
	}

	protected function buildVirtualSelectOptions(array $config, array $txt, array $context, string $id): array
	{
		$options = [
			'ele'                    => '#' . $id,
			'dropboxWrapper'         => 'body',
			'markSearchResults'      => true,
			'hideClearButton'        => ! (isset($config['multiple']) && $config['multiple']),
			'multiple'               => $config['multiple'] ?? false,
			'search'                 => $config['search'] ?? true,
			'placeholder'            => $config['hint'] ?? '',
			'options'                => $config['data'] ?? [],
			'selectedValue'          => $config['value'] ?? '',
			'noSearchResultsText'    => $txt['no_matches'] ?? '',
			'searchPlaceholderText'  => $txt['search'] ?? '',
			'allOptionsSelectedText' => $txt['all'] ?? '',
			'clearButtonText'        => $txt['remove'] ?? '',
			'selectAllText'          => $txt['check_all'] ?? '',
		];

		if ($context['right_to_left'] ?? false) {
			$options['textDirection'] = 'rtl';
		}

		if ($config['disabled'] ?? false) {
			$options['disabled'] = true;
		}

		if ($config['empty'] ?? false) {
			$options['noOptionsText'] = $config['empty'];
		}

		if ($config['multiple'] ?? false) {
			$options['showValueAsTags'] = true;
		}

		if (isset($config['allowNew'])) {
			$options['allowNewOption'] = $config['allowNew'];
		}

		if ($config['wide'] ?? true) {
			$options['maxWidth'] = '100%';
		}

		if ($config['more'] ?? false) {
			$options['moreText'] = $txt['post_options'];
		}

		if (isset($config['maxValues'])) {
			$options['maxValues'] = $config['maxValues'];
		}

		if (isset($config['showSelectedOptionsFirst'])) {
			$options['showSelectedOptionsFirst'] = $config['showSelectedOptionsFirst'];
		}

		return $options;
	}

	protected function buildPreviewSelectOptions(array $config, array $txt, array $context, string $id): array
	{
		$options = $this->buildVirtualSelectOptions($config, $txt, $context, $id);

		$options['showSelectedOptionsFirst'] = true;
		$options['optionHeight'] = '60px';

		return $options;
	}

	protected function buildIconSelectOptions(array $config, array $txt, array $context, string $id): array
	{
		$options = $this->buildVirtualSelectOptions($config, $txt, $context, $id);

		$options['allowNewOption'] = true;

		return $options;
	}

	protected function buildPageIconSelectOptions(array $config, array $txt, array $context, string $id): array
	{
		return $this->buildIconSelectOptions($config, $txt, $context, $id);
	}

	private function formatPrettyOptions(array $initOptions): string
	{
		$initJs = json_encode($initOptions, JSON_PRETTY_PRINT);
		$initJs = "\t" . str_replace("\n", "\n\t", $initJs);

		return ltrim(rtrim($initJs), "\t");
	}
}
