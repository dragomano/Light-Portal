<?php

/**
 * Markdown.php
 *
 * @package Markdown (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 *
 * @category addon
 * @version 15.03.23
 */

namespace Bugo\LightPortal\Addons\Markdown;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Addons\Markdown\Smf\{
	BlockQuoteRenderer,
	FencedCodeRenderer,
	HeadingRenderer,
	ImageRenderer,
	LinkRenderer,
	ListBlockRenderer,
	ListItemRenderer,
	TableRowRenderer,
	TableRenderer,
};
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\{BlockQuote, FencedCode, Heading, ListBlock, ListItem};
use League\CommonMark\Extension\CommonMark\Node\Inline\{Image, Link};
use League\CommonMark\Extension\Table\{Table, TableRow};
use League\CommonMark\MarkdownConverter;

if (! defined('LP_NAME'))
	die('No direct access...');

class Markdown extends Plugin
{
	public string $icon = 'fab fa-markdown';

	public string $type = 'parser';

	public function init()
	{
		$this->context['lp_content_types']['markdown'] = 'Markdown';
	}

	public function parseContent(string &$content, string $type)
	{
		if ($type === 'markdown')
			$content = $this->getParsedContent($content);
	}

	private function getParsedContent(string $text): string
	{
		require_once __DIR__ . '/vendor/autoload.php';

		$config = [
			'renderer' => [
				'block_separator' => PHP_EOL,
				'inner_separator' => PHP_EOL,
				'soft_break'      => PHP_EOL,
			],
			'commonmark' => [
				'enable_em' => true,
				'enable_strong' => true,
				'use_asterisk' => true,
				'use_underscore' => true,
				'unordered_list_markers' => ['-', '*', '+'],
			],
			'html_input' => 'escape',
			'allow_unsafe_links' => false,
			'max_nesting_level' => PHP_INT_MAX,
			'slug_normalizer' => [
				'max_length' => 255,
			],
		];

		$environment = new Environment($config);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addExtension(new GithubFlavoredMarkdownExtension());
		$environment->addRenderer(BlockQuote::class, new BlockQuoteRenderer());
		$environment->addRenderer(FencedCode::class, new FencedCodeRenderer());
		$environment->addRenderer(Heading::class, new HeadingRenderer());
		$environment->addRenderer(ListBlock::class, new ListBlockRenderer());
		$environment->addRenderer(ListItem::class, new ListItemRenderer());
		$environment->addRenderer(Image::class, new ImageRenderer());
		$environment->addRenderer(Link::class, new LinkRenderer());
		$environment->addRenderer(Table::class, new TableRenderer());
		$environment->addRenderer(TableRow::class, new TableRowRenderer());

		$converter = new MarkdownConverter($environment);

		return $converter->convert($this->unHtmlSpecialChars($text));
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'league/commonmark',
			'link' => 'https://github.com/thephpleague/commonmark',
			'author' => 'Colin O\'Dell & The League of Extraordinary Packages',
			'license' => [
				'name' => 'the BSD-3-Clause License',
				'link' => 'https://github.com/thephpleague/commonmark/blob/main/LICENSE'
			]
		];
	}
}
