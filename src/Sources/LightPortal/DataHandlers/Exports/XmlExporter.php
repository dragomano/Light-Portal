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

namespace LightPortal\DataHandlers\Exports;

use Bugo\Compat\Lang;
use Bugo\Compat\Sapi;
use DomDocument;
use DOMElement;
use DOMException;

if (! defined('SMF'))
	die('No direct access...');

abstract class XmlExporter extends AbstractExport
{
	abstract protected function getAttributeFields(): array;

	abstract protected function getNestedFieldRules(): array;

	protected function createXmlFile(array $items): string
	{
		if ($items === [])
			return '';

		try {
			$xml = new DomDocument('1.0', 'utf-8');
			$xml->formatOutput = true;

			$root = $xml->appendChild($xml->createElement('light_portal'));
			$xmlElements = $root->appendChild($xml->createElement($this->entity ?? 'unknown'));

			$this->toXml($xml, $xmlElements, $items);

			// Save file only after successful XML creation
			$file = Sapi::getTempDir() . '/lp_' . $this->entity . '_backup.xml';
			$xml->save($file);

			return $file;
		} catch (DOMException $e) {
			$message = '[LP] ' . Lang::$txt['lp_' . $this->entity . '_export'] . ': ';
			$this->errorHandler->log($message . $e->getMessage(), 'user', $e->getTrace());

			return '';
		}
	}

	/**
	 * @throws DOMException
	 */
	public function toXml(DOMDocument $xml, DOMElement $xmlElements, array $items): void
	{
		$generator = $this->getGeneratorFrom($items);

		foreach ($generator() as $item) {
			$xmlElement = $xmlElements->appendChild($xml->createElement('item'));

			foreach ($item as $key => $val) {
				$isAttribute = in_array($key, $this->getAttributeFields());
				$xmlName = $xmlElement->appendChild(
					$isAttribute ? $xml->createAttribute($key) : $xml->createElement($key)
				);

				$nestedRules = $this->getNestedFieldRules();

				if (isset($nestedRules[$key])) {
					$rule = $nestedRules[$key];

					foreach ($val as $k => $v) {
						if ($rule['type'] === 'element') {
							$xmlChild = $xmlName->appendChild($xml->createElement($k));
							$xmlChild->appendChild(
								$rule['useCDATA']
									? $xml->createCDATASection((string) $v)
									: $xml->createTextNode((string) $v)
							);
						} elseif ($rule['type'] === 'subitem') {
							$xmlSubElement = $xmlName->appendChild($xml->createElement($rule['elementName']));

							foreach ($v as $label => $text) {
								$xmlSubElementChild = $xmlSubElement->appendChild(
									$rule['subFields'][$label]['isAttribute']
										? $xml->createAttribute($label)
										: $xml->createElement($label)
								);

								$xmlSubElementChild->appendChild(
									$rule['subFields'][$label]['useCDATA']
										? $xml->createCDATASection((string) $text)
										: $xml->createTextNode((string) $text)
								);
							}
						}
					}
				} else {
					$xmlName->appendChild($xml->createTextNode((string) $val));
				}
			}
		}
	}
}
