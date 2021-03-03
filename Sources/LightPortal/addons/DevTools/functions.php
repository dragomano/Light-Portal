<?php

if (!function_exists('dump')) {
	function dump(...$data)
	{
		foreach ($data as $var) {
			echo '<figure class="noticebox"><pre><code class="bbc_code" style="white-space: pre-wrap; max-height: 20em; margin: 0">' . print_r($var, true) . '</code></pre></figure>';
		}
	}
}

if (!function_exists('dd')) {
	function dd(...$data)
	{
		dump(...$data);
		die;
	}
}

?>