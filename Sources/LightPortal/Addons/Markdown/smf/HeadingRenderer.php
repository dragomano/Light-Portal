<?php

declare(strict_types=1);

/**
 * HeadingRenderer.php
 *
 * @package Markdown (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 *
 * @category addon
 * @version 26.10.21
 */

namespace Bugo\LightPortal\Addons\Markdown\Smf;

use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class HeadingRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    /**
     * @param Heading $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        Heading::assertInstanceOf($node);

        $tag = 'h' . $node->getLevel();

        $attrs = $node->data->get('attributes');

        $attrs = array_merge(['class' => 'titlebg'], $attrs);

        $heading = new HtmlElement($tag, $attrs, $childRenderer->renderNodes($node->children()));

        return new HtmlElement('div', ['class' => 'title_bar'], $heading);
    }

    public function getXmlTagName(Node $node): string
    {
        return 'heading';
    }

    /**
     * @param Heading $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function getXmlAttributes(Node $node): array
    {
        Heading::assertInstanceOf($node);

        return ['level' => $node->getLevel()];
    }
}
