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

use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\RegexHelper;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

final class ImageRenderer implements NodeRendererInterface, XmlNodeRendererInterface, ConfigurationAwareInterface
{
    /** @psalm-readonly-allow-private-mutation */
    private ConfigurationInterface $config;

    /**
     * @param Image $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        Image::assertInstanceOf($node);

        $attrs = $node->data->get('attributes');

        $attrs = array_merge(['class' => 'bbc_img', 'loading' => 'lazy', 'data-fancybox' => ''], $attrs);

        $forbidUnsafeLinks = ! $this->config->get('allow_unsafe_links');
        if ($forbidUnsafeLinks && RegexHelper::isLinkPotentiallyUnsafe($node->getUrl())) {
            $attrs['src'] = '';
        } else {
            $attrs['src'] = $node->getUrl();
        }

        $alt          = $childRenderer->renderNodes($node->children());
        $alt          = \preg_replace('/<[^>]*alt="([^"]*)"[^>]*>/', '$1', $alt);
        $attrs['alt'] = \preg_replace('/<[^>]*>/', '', $alt ?? '');

        if (($title = $node->getTitle()) !== null) {
            $attrs['title'] = $title;
        }

        return new HtmlElement('img', $attrs, '', true);
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function getXmlTagName(Node $node): string
    {
        return 'image';
    }

    /**
     * @param Image $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function getXmlAttributes(Node $node): array
    {
        Image::assertInstanceOf($node);

        return [
            'destination' => $node->getUrl(),
            'title' => $node->getTitle() ?? '',
        ];
    }
}
