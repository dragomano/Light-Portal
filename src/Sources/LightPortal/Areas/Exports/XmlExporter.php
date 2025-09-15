<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Areas\Exports;

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Sapi;
use DomDocument;
use DOMException;

use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function str_contains;

if (! defined('SMF'))
	die('No direct access...');

abstract class XmlExporter extends AbstractExport
{
	protected function createXmlFile(array $items, array $attributes = []): string
	{
		if ($items === [])
			return '';

		try {
			$xml = new DomDocument('1.0', 'utf-8');
			$root = $xml->appendChild($xml->createElement('light_portal'));

			$xml->formatOutput = true;

			$xmlElements = $root->appendChild($xml->createElement($this->entity));

			$generator = $this->getGeneratorFrom($items);

			foreach ($generator() as $item) {
				$xmlElement = $xmlElements->appendChild($xml->createElement('item'));

				foreach ($item as $key => $val) {
					$xmlName = $xmlElement->appendChild(
						in_array($key, $attributes, true)
							? $xml->createAttribute($key)
							: $xml->createElement($key)
					);

					$this->addXmlValue($xmlName, $val, $xml);
				}
			}

			$file = Sapi::getTempDir() . '/lp_' . $this->entity . '_backup.xml';
			$xml->save($file);

			return $file;
		} catch (DOMException $e) {
			$this->logError('XML export error: ' . $e->getMessage());
		}

		return '';
	}

	/**
	 * @throws DOMException
	 */
	protected function addXmlValue($xmlName, $value, DomDocument $xml): void
	{
		if (is_array($value)) {
			$this->addArrayValue($xmlName, $value, $xml);
		} else {
			$xmlName->appendChild(
				is_string($value) && str_contains($value, '<')
					? $xml->createCDATASection($value)
					: $xml->createTextNode($value)
			);
		}
	}

	/**
	 * @throws DOMException
	 */
	protected function addArrayValue($xmlName, array $value, DomDocument $xml): void
	{
		foreach ($value as $k => $v) {
			$elementName = $this->getElementName($xmlName->nodeName, $k);
			$child = $xmlName->appendChild($xml->createElement($elementName));

			if (is_array($v)) {
				foreach ($v as $nestedKey => $nestedValue) {
					if (is_array($nestedValue)) {
						$this->addArrayValue($child, $nestedValue, $xml);
					} else {
						$nestedElementName = $this->getNestedElementName($elementName, $nestedKey);
						$nestedChild = $child->appendChild(
							in_array($nestedKey, ['id', 'parent_id', 'author_id', 'created_at'], true)
								? $xml->createAttribute($nestedElementName)
								: $xml->createElement($nestedElementName)
						);
						$nestedChild->appendChild(
							$nestedKey === 'message' && is_string($nestedValue) && str_contains($nestedValue, '<')
								? $xml->createCDATASection($nestedValue)
								: $xml->createTextNode($nestedValue)
						);
					}
				}
			} else {
				$child->appendChild(
					is_string($v) && str_contains($v, '<')
						? $xml->createCDATASection($v)
						: $xml->createTextNode($v)
				);
			}
		}
	}

	protected function getElementName(string $parentName, $key): string
	{
		if (is_numeric($key)) {
			return match ($parentName) {
				'pages'                              => 'page',
				'comments'                           => 'comment',
				'params'                             => 'param',
				'titles', 'contents', 'descriptions' => 'lang',
				default                              => 'item_' . $key,
			};
		}

		return (string) $key;
	}

	protected function getNestedElementName(string $elementName, $nestedKey): string
	{
		if (is_numeric($nestedKey)) {
			return match ($elementName) {
				'page'    => 'page_id',
				'comment' => 'comment_id',
				default   => 'item_' . $nestedKey,
			};
		}

		return (string) $nestedKey;
	}

	protected function logError(string $message): void
	{
		ErrorHandler::log('[LP] ' . Lang::$txt['lp_' . $this->entity . '_export'] . ': ' . $message, 'user');
	}
}
