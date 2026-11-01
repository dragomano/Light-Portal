<?php declare(strict_types=1);

/**
 * @package Carve (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2026 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 10.10.26
 */

namespace LightPortal\Plugins\Carve\SMF;

use MarkupCarve\Carve\CarveConverter;
use MarkupCarve\Carve\Event\RenderEvent;
use MarkupCarve\Carve\Extension\ExtensionInterface;
use MarkupCarve\Carve\Node\Block\Div;
use MarkupCarve\Carve\Renderer\HtmlRenderer;
use MarkupCarve\Carve\Util\StringUtil;

/**
 * Renders Carve admonition containers (`::: tip`, `::: warning`, …) as the
 * callout boxes SMF themes already style, instead of the stock
 * `<div class="admonition tip">` markup.
 *
 * Input:
 * ```
 * ::: tip
 * Admonitions render as styled callouts.
 * :::
 *
 * ::: warning "Watch out!"
 * Be careful here.
 * ```
 *
 * Output HTML:
 * ```html
 * <div class="noticebox">
 * <strong>Tip</strong>
 * <p>Admonitions render as styled callouts.</p>
 * </div>
 * <div class="noticebox">
 * <strong>Watch out!</strong>
 * <p>Be careful here.</p>
 * </div>
 * ```
 *
 * A quoted opener header becomes the title; without one the capitalized type
 * is used, so `::: tip` still reads as "Tip". Titles come from the inline run
 * rather than the raw string, so `::: warning "Read *this*"` keeps its markup.
 *
 * Author attributes survive the swap: `{#id .extra data-x="1" title="…"}` land
 * on the box, while the stock `admonition`/type classes and the `role`
 * attribute are dropped in favour of the box class.
 *
 * Containers owned by other extensions (`details`, `spoiler`, `tabs`) and
 * placement directives (`toc`, `footnotes`) are left to whoever claims them.
 */
final class AdmonitionExtension implements ExtensionInterface
{
	/**
	 * Admonition type to SMF box class.
	 *
	 * `danger` and `error` are the red box, `warning`/`tip`/`note` the yellow
	 * one, and `info`/`success`/`example` the green one — the three severities
	 * `.errorbox`, `.noticebox` and `.infobox` are drawn for.
	 *
	 * @var array<string, string>
	 */
	public const SMF_BOXES = [
		'danger'  => 'errorbox',
		'error'   => 'errorbox',
		'warning' => 'noticebox',
		'tip'     => 'noticebox',
		'note'    => 'noticebox',
		'info'    => 'infobox',
		'success' => 'infobox',
		'example' => 'infobox',
	];

	/**
	 * @param array<string, string> $boxes    Type to box class overrides.
	 * @param array<string, string> $titles   Type to title overrides.
	 */
	public function __construct(
		protected array $boxes = self::SMF_BOXES,
		protected array $titles = [],
	) {}

	public function register(CarveConverter $converter): void
	{
		$renderer = $converter->getRenderer();
		if (! $renderer instanceof HtmlRenderer) {
			return;
		}

		$converter->on('render.div', function (RenderEvent $event) use ($renderer): void {
			$node = $event->getNode();
			if (! $node instanceof Div) {
				return;
			}

			$type = $this->getAdmonitionType($node);
			if ($type === null) {
				return;
			}

			$event->setHtml($this->renderBox($node, $type, $event->getChildrenHtml(), $renderer));
		});
	}

	/**
	 * The admonition type this div carries, or null when it carries none.
	 */
	protected function getAdmonitionType(Div $node): ?string
	{
		foreach ($node->getClassList() as $class) {
			if (isset($this->boxes[$class])) {
				return $class;
			}
		}

		return null;
	}

	protected function renderBox(Div $node, string $type, string $childrenHtml, HtmlRenderer $renderer): string
	{
		$attrs     = $this->buildAttributes($node, $renderer);
		$classes   = array_merge([$this->boxes[$type]], $this->getExtraClasses($node));
		$classAttr = StringUtil::escapeHtml(implode(' ', $classes));
		$html      = '<div class="' . $classAttr . '"' . $attrs . ">\n";

		$title = $this->renderTitle($node, $type, $renderer);
		if ($title !== '') {
			$html .= '<strong>' . $title . "</strong>\n";
		}

		$body = rtrim($childrenHtml, "\n");
		if ($body !== '') {
			$html .= $body . "\n";
		}

		return $html . "</div>\n";
	}

	/**
	 * The title as HTML: the quoted opener header when there is one, otherwise
	 * the capitalized type.
	 */
	protected function renderTitle(Div $node, string $type, HtmlRenderer $renderer): string
	{
		$header = $node->getHeader();
		if ($header !== null && trim($header) !== '') {
			$headerHtml = trim($renderer->renderInlineNodesFragment($node->getHeaderNodes()));
			if ($headerHtml !== '') {
				return $headerHtml;
			}
		}

		return StringUtil::escapeHtml($this->titles[$type] ?? ucfirst($type));
	}

	/**
	 * Author attributes for the box tag, in source order.
	 *
	 * The `class` entry is dropped and rebuilt by the caller: the stock
	 * admonition classes are replaced by the box class, and only the author's
	 * own classes (`{.extra}`) are carried over.
	 */
	protected function buildAttributes(Div $node, HtmlRenderer $renderer): string
	{
		$attrs = $node->getAttributeEntries();
		unset($attrs['class']);

		$attrs    = $renderer->sanitizeAttributes($attrs);
		$safeMode = $renderer->getSafeMode();
		if ($safeMode !== null) {
			$attrs = $safeMode->filterAttributes($attrs);
		}

		return $renderer->renderAttributeArray($attrs);
	}

	/**
	 * The author's own classes, with the admonition types and the container
	 * class the core renderer adds removed.
	 *
	 * Every known type is dropped, not just the matched one: an attribute line
	 * can add a second type above the opener (`{.warning}` over `::: note`), and
	 * the loser of that contest has no business surviving as a stray class.
	 *
	 * @return list<string>
	 */
	protected function getExtraClasses(Div $node): array
	{
		$classes = [];

		foreach ($node->getClassList() as $class) {
			if ($class === '' || $class === 'admonition' || isset($this->boxes[$class])) {
				continue;
			}

			if (! in_array($class, $classes, true)) {
				$classes[] = $class;
			}
		}

		return $classes;
	}
}
