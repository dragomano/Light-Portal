<?php declare(strict_types=1);

/**
 * @package Markdown (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\Markdown;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Markdown\SMF\BlockQuoteRenderer;
use Bugo\LightPortal\Plugins\Markdown\SMF\FencedCodeRenderer;
use Bugo\LightPortal\Plugins\Markdown\SMF\HeadingRenderer;
use Bugo\LightPortal\Plugins\Markdown\SMF\ImageRenderer;
use Bugo\LightPortal\Plugins\Markdown\SMF\LinkRenderer;
use Bugo\LightPortal\Plugins\Markdown\SMF\ListBlockRenderer;
use Bugo\LightPortal\Plugins\Markdown\SMF\ListItemRenderer;
use Bugo\LightPortal\Plugins\Markdown\SMF\TableRenderer;
use Bugo\LightPortal\Plugins\Markdown\SMF\TableRowRenderer;
use Bugo\LightPortal\Plugins\Plugin;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\MarkdownConverter;
use Zoon\CommonMark\Ext\YouTubeIframe\YouTubeIframeExtension;

if (! defined('LP_NAME'))
	die('No direct access...');

class Markdown extends Plugin
{
	public string $icon = 'fab fa-markdown';

	public string $type = 'parser';

	public function init(): void
	{
		Utils::$context['lp_content_types'][$this->name] = 'Markdown';
	}

	/**
	 * @throws CommonMarkException
	 */
	public function parseContent(Event $e): void
	{
		$e->args->content = $this->getParsedContent($e->args->content);
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'league/commonmark',
			'link' => 'https://github.com/thephpleague/commonmark',
			'author' => 'Colin O\'Dell & The League of Extraordinary Packages',
			'license' => [
				'name' => 'the BSD-3-Clause License',
				'link' => 'https://github.com/thephpleague/commonmark/blob/main/LICENSE'
			]
		];
	}

	/**
	 * @throws CommonMarkException
	 */
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
			'youtube_iframe' => [
				'width' => '600',
				'height' => '300',
				'allow_full_screen' => true,
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
		$environment->addExtension(new YouTubeIframeExtension());
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

		return (string) $converter->convert(Utils::htmlspecialcharsDecode($text));
	}
}
