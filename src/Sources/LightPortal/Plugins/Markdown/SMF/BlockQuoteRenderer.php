<?php declare(strict_types=1);

/**
 * @package Markdown (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 *
 * @category plugin
 * @version 31.03.24
 */

namespace Bugo\LightPortal\Plugins\Markdown\SMF;

use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Xml\XmlNodeRendererInterface;

final class BlockQuoteRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    /**
     * @param BlockQuote $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        BlockQuote::assertInstanceOf($node);

        $attrs = $node->data->get('attributes');

        $attrs = array_merge(['class' => 'bbc_standard_quote'], $attrs);

        $filling        = $childRenderer->renderNodes($node->children());
        $innerSeparator = $childRenderer->getInnerSeparator();
        if ($filling === '') {
            return new HtmlElement('blockquote', $attrs, $innerSeparator);
        }

        return new HtmlElement(
            'blockquote',
            $attrs,
            $innerSeparator . new HtmlElement('cite', []) . $filling. $innerSeparator
        );
    }

    public function getXmlTagName(Node $node): string
    {
        return 'block_quote';
    }

    /**
     * @param BlockQuote $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function getXmlAttributes(Node $node): array
    {
        return [];
    }
}
