<?php

declare(strict_types=1);

/**
 * ListItemRenderer.php
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
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\TaskList\TaskListItemMarker;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class ListItemRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    /**
     * @param ListItem $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        ListItem::assertInstanceOf($node);

        $contents = $childRenderer->renderNodes($node->children());

        $attrs = [];
        if (\substr($contents, 0, 1) === '<') {
            $attrs = ['class' => 'task_list_item'];
        }

        if (\substr($contents, 0, 1) === '<' && ! $this->startsTaskListItem($node)) {
            $contents = "\n" . $contents;
        }

        if (\substr($contents, -1, 1) === '>') {
            $contents .= "\n";
        }

        $attrs = array_merge($attrs, $node->data->get('attributes'));

        return new HtmlElement('li', $attrs, $contents);
    }

    public function getXmlTagName(Node $node): string
    {
        return 'item';
    }

    /**
     * {@inheritDoc}
     */
    public function getXmlAttributes(Node $node): array
    {
        return [];
    }

    private function startsTaskListItem(ListItem $block): bool
    {
        $firstChild = $block->firstChild();

        return $firstChild instanceof Paragraph && $firstChild->firstChild() instanceof TaskListItemMarker;
    }
}
