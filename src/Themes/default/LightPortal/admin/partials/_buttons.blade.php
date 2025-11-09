<button
	type="submit"
	class="button active"
	name="remove"
	style="float: left"
	x-show="!{{ (int) empty($id) }}"
>
	{{ $txt['remove'] }}
</button>
<button
	type="submit"
	class="button"
	name="preview"
	@click="{{ $entity }}.post($root)"
>
	@icon('preview'){{ $txt['preview'] }}
</button>
<button
	type="submit"
	class="button"
	name="save"
	@click="{{ $entity }}.post($root)"
>
	@icon('save'){{ $txt['save'] }}
</button>
<button
	type="submit"
	class="button"
	name="save_exit"
	@click="{{ $entity }}.post($root)"
>
	@icon('save_exit'){{ $txt['lp_save_and_exit'] }}
</button>
