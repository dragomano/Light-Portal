<?php

function show_calculator_block(int $block_id): void
{
	echo '
	<div id="calc', $block_id, '" class="calculator">
		<div class="calculator__display">0</div>
		<div class="calculator__keys">
			<button class="button active key--operator" data-action="add">+</button>
			<button class="button active key--operator" data-action="subtract">-</button>
			<button class="button active key--operator" data-action="multiply">&times;</button>
			<button class="button active key--operator" data-action="divide">÷</button>
			<button class="button">7</button>
			<button class="button">8</button>
			<button class="button">9</button>
			<button class="button">4</button>
			<button class="button">5</button>
			<button class="button">6</button>
			<button class="button">1</button>
			<button class="button">2</button>
			<button class="button">3</button>
			<button class="button">0</button>
			<button class="button" data-action="decimal">.</button>
			<button class="button" data-action="clear">AC</button>
			<button class="button active key--equal" data-action="calculate">=</button>
		</div>
	</div>
	<script>new Calc(', $block_id, ')</script>';
}
