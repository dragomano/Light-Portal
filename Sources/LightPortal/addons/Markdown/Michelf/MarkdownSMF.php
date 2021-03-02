<?php

namespace Bugo\LightPortal\Addons\Markdown\Michelf;

/**
 * Markdown
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

class MarkdownSMF extends \Michelf\MarkdownExtra
{
	public function __construct()
	{
		$this->empty_element_suffix = ">";
		$this->no_markup = true;
		$this->no_entities = true;
		$this->span_gamut += array("_doStrikethrough" => 55);
		$this->escape_chars .= "~";

		parent::__construct();
	}

	/**
	 * Callback for atx headers
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _doHeaders_callback_atx($matches)
	{
		$block = "<div class=\"title_bar\"><h4 class=\"titlebg\">" . $this->runSpanGamut($matches[2]) . "</h4></div>";

		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	/**
	 * Handle striketrough
	 *
	 * @param string $text
	 * @return string
	 */
	protected function _doStrikethrough($text)
	{
		$parts = preg_split("/(?<![~])(~~)(?![~])/", $text, null, PREG_SPLIT_DELIM_CAPTURE);

		if (count($parts) > 3) {
			$text = "";
			$open = false;

			foreach ($parts as $part) {
				if ($part == "~~") {
					$text .= $open ? "</del>" : "<del>";
					$open = !$open;
				} else {
					$text .= $part;
				}
			}

			if ($open)
				$text .= "</del>";
		}

		return $text;
	}

	/**
	 * List item parsing callback
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _processListItems_callback($matches)
	{
		$attr          = "";
		$item          = $matches[4];
		$leading_line  =& $matches[1];
		$leading_space =& $matches[2];
		$marker_space  = $matches[3];
		$tailing_blank_line =& $matches[5];

		if ($leading_line || $tailing_blank_line ||	preg_match('/\n{2,}/', $item)) {
			$item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;
			$item = $this->runBlockGamut($this->outdent($item) . "\n");
		} else {
			$item = $this->doLists($this->outdent($item));
			$item = $this->formParagraphs($item, false);
			$token = substr($item, 0, 4);
			if ($token == "[ ] " || $token == "[x] ") {
				$attr = " class=\"task-list-item\"";
				$item = ($token=='[ ] ' ? "<input type=\"checkbox\" disabled=\"disabled\"> " : "<input type=\"checkbox\" disabled=\"disabled\" checked=\"checked\"> ").substr($item, 4);
			}
		}

		return "<li$attr>" . $item . "</li>\n";
	}

	/**
	 * Callback for inline anchors
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _doAnchors_inline_callback($matches)
	{
		$link_text = $this->runSpanGamut($matches[2]);
		$url       = $matches[3] === '' ? $matches[4] : $matches[3];
		$title     =& $matches[7];
		$attr      = $this->doExtraAttributes("a", $dummy =& $matches[8]);

		$unhashed = $this->unhash($url);
		if ($unhashed !== $url)
			$url = preg_replace('/^<(.*)>$/', '\1', $unhashed);

		$url = $this->encodeURLAttribute($url);

		$result = "<a class=\"bbc_link\" href=\"$url\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .= " title=\"$title\"";
		}
		$result .= $attr;

		$link_text = $this->runSpanGamut($link_text);
		$result .= ">$link_text</a>";

		return $this->hashPart($result);
	}

	/**
	 * Parse URL callback
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _doAutoLinks_url_callback($matches)
	{
		$url  = $this->encodeURLAttribute($matches[1], $text);
		$link = "<a class=\"bbc_link\" href=\"$url\">$text</a>";

		return $this->hashPart($link);
	}

	/**
	 * Parse email address callback
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _doAutoLinks_email_callback($matches)
	{
		$addr = $matches[1];
		$url  = $this->encodeURLAttribute("mailto:$addr", $text);
		$link = "<a class=\"bbc_email\" href=\"$url\">$text</a>";

		return $this->hashPart($link);
	}

	/**
	 * Callback for inline images
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _doImages_inline_callback($matches)
	{
		$alt_text = $matches[2];
		$url      = $matches[3] === '' ? $matches[4] : $matches[3];
		$title    =& $matches[7];
		$attr     = $this->doExtraAttributes("img", $dummy =& $matches[8]);

		$alt_text = $this->encodeAttribute($alt_text);
		$url      = $this->encodeURLAttribute($url);
		$result   = "<img class=\"bbc_img resized\" src=\"$url\" alt=\"$alt_text\"";

		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .= " title=\"$title\"";
		}

		$result .= $attr;
		$result .= $this->empty_element_suffix;

		return $this->hashPart($result);
	}

	/**
	 * List parsing callback
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _doLists_callback($matches)
	{
		$marker_ul_re       = '[*+-]';
		$marker_ol_re       = '\d+[\.]';
		$marker_any_re      = "(?:$marker_ul_re|$marker_ol_re)";
		$marker_ol_start_re = '[0-9]+';

		$list = $matches[1];
		$list_type = preg_match("/$marker_ul_re/", $matches[4]) ? "ul class=\"bbc_list\"" : "ul class=\"bbc_list\" style=\"list-style-type: decimal\"";

		$marker_any_re = ($list_type == "ul class=\"bbc_list\"" ? $marker_ul_re : $marker_ol_re);

		$list .= "\n";
		$result = $this->processListItems($list, $marker_any_re);

		$ol_start = 1;
		if ($this->enhanced_ordered_list) {
			if ($list_type == 'ul class=\"bbc_list\" style=\"list-style-type: decimal\"') {
				$ol_start_array = [];
				$ol_start_check = preg_match("/$marker_ol_start_re/", $matches[4], $ol_start_array);
				if ($ol_start_check) {
					$ol_start = $ol_start_array[0];
				}
			}
		}

		if ($ol_start > 1 && $list_type == 'ul class=\"bbc_list\" style=\"list-style-type: decimal\"') {
			$result = $this->hashBlock("<$list_type start=\"$ol_start\">\n" . $result . "</ul>");
		} else {
			$result = $this->hashBlock("<$list_type>\n" . $result . "</ul>");
		}

		return "\n" . $result . "\n\n";
	}

	/**
	 * Calback for processing tables
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _doTable_callback($matches)
	{
		$head      = $matches[1];
		$underline = $matches[2];
		$content   = $matches[3];

		$head      = preg_replace('/[|] *$/m', '', $head);
		$underline = preg_replace('/[|] *$/m', '', $underline);
		$content   = preg_replace('/[|] *$/m', '', $content);

		$separators	= preg_split('/ *[|] */', $underline);
		foreach ($separators as $n => $s) {
			if (preg_match('/^ *-+: *$/', $s))
				$attr[$n] = $this->_doTable_makeAlignAttr('right');
			else if (preg_match('/^ *:-+: *$/', $s))
				$attr[$n] = $this->_doTable_makeAlignAttr('center');
			else if (preg_match('/^ *:-+ *$/', $s))
				$attr[$n] = $this->_doTable_makeAlignAttr('left');
			else
				$attr[$n] = '';
		}

		$head      = $this->parseSpan($head);
		$headers   = preg_split('/ *[|] */', $head);
		$col_count = count($headers);
		$attr      = array_pad($attr, $col_count, '');

		$text = "<table class=\"table_grid\">\n";
		$text .= "<thead>\n";
		$text .= "<tr class=\"title_bar\">\n";
		foreach ($headers as $n => $header) {
			$text .= "<th$attr[$n]>" . $this->runSpanGamut(trim($header)) . "</th>\n";
		}
		$text .= "</tr>\n";
		$text .= "</thead>\n";

		$rows = explode("\n", trim($content, "\n"));

		$text .= "<tbody>\n";
		foreach ($rows as $row) {
			$row = $this->parseSpan($row);

			$row_cells = preg_split('/ *[|] */', $row, $col_count);
			$row_cells = array_pad($row_cells, $col_count, '');

			$text .= "<tr class=\"windowbg\">\n";
			foreach ($row_cells as $n => $cell) {
				$text .= "<td$attr[$n]>" . $this->runSpanGamut(trim($cell)) . "</td>\n";
			}
			$text .= "</tr>\n";
		}
		$text .= "</tbody>\n";
		$text .= "</table>";

		return $this->hashBlock($text) . "\n";
	}

	/**
	 * Create a code span markup for $code. Called from handleSpanToken.
	 *
	 * @param  string $code
	 * @return string
	 */
	protected function makeCodeSpan($code)
	{
		if (is_callable($this->code_span_content_func)) {
			$code = call_user_func($this->code_span_content_func, $code);
		} else {
			$code = htmlspecialchars(trim($code), ENT_NOQUOTES);
		}

		return $this->hashPart("<code class=\"bbc_code\">$code</code>");
	}

	/**
	 * Callback to process fenced code blocks
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _doFencedCodeBlocks_callback($matches)
	{
		$classname =& $matches[2];
		$attrs     =& $matches[3];
		$codeblock = $matches[4];

		if ($this->code_block_content_func) {
			$codeblock = call_user_func($this->code_block_content_func, $codeblock, $classname);
		} else {
			$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
		}

		$codeblock = preg_replace_callback('/^\n+/',
			array($this, '_doFencedCodeBlocks_newlines'), $codeblock);

		$classes = array('bbc_code');

		if ($classname !== "") {
			if ($classname[0] === '.') {
				$classname = substr($classname, 1);
			}
			$classes[] = $this->code_class_prefix . $classname;
		}

		$attr_str      = $this->doExtraAttributes($this->code_attr_on_pre ? "pre" : "code", $attrs, null, $classes);
		$pre_attr_str  = $this->code_attr_on_pre ? $attr_str : '';
		$code_attr_str = $this->code_attr_on_pre ? '' : $attr_str;
		$codeblock     = "<code$code_attr_str>$codeblock</code>";

		return "\n\n" . $this->hashBlock($codeblock) . "\n\n";
	}

	/**
	 * Blockquote parsing callback
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _doBlockQuotes_callback($matches)
	{
		$bq = $matches[1];
		$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
		$bq = $this->runBlockGamut($bq);

		$bq = preg_replace('/^/m', "  ", $bq);
		$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array($this, '_doBlockQuotes_callback2'), $bq);

		return "\n" . $this->hashBlock("<blockquote class=\"bbc_standard_quote\">\n<cite></cite>\n\n$bq\n</blockquote>") . "\n\n";
	}
}
