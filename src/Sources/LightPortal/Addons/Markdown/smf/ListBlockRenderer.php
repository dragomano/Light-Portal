<?php

declare(strict_types=1);

/**
 * ListBlockRenderer.php
 *
 * @package Markdown (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 *
 * @category addon
 * @version 26.10.21
 */

namespace Bugo\LightPortal\Addons\Markdown\Smf;

use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class ListBlockRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    /**
     * @param ListBlock $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        ListBlock::assertInstanceOf($node);

        $listData = $node->getListData();

        $tag = $listData->type === ListBlock::TYPE_BULLET ? 'ul' : 'ol';

        $attrs = $node->data->get('attributes');

        $attrs = array_merge(['class' => 'bbc_list'], $attrs);

        if ($tag === 'ol') {
            $attrs = array_merge(['style' => 'list-style-type: decimal'], $attrs);
        }

        if ($listData->start !== null && $listData->start !== 1) {
            $attrs['start'] = (string) $listData->start;
        }

        $innerSeparator = $childRenderer->getInnerSeparator();

        return new HtmlElement($tag, $attrs, $innerSeparator . $childRenderer->renderNodes($node->children()) . $innerSeparator);
    }

    public function getXmlTagName(Node $node): string
    {
        return 'list';
    }

    /**
     * @param ListBlock $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function getXmlAttributes(Node $node): array
    {
        ListBlock::assertInstanceOf($node);

        $data = $node->getListData();

        if ($data->type === ListBlock::TYPE_BULLET) {
            return [
                'type' => $data->type,
                'tight' => $node->isTight() ? 'true' : 'false',
            ];
        }

        return [
            'type' => $data->type,
            'start' => $data->start ?? 1,
            'tight' => $node->isTight(),
            'delimiter' => $data->delimiter ?? ListBlock::DELIM_PERIOD,
        ];
    }
}
