<button
	type="submit"
	class="button active"
	name="remove"
	style="float: left"
	data-show="!{{ (int) empty($id) }}"
	data-on:click="{{ $entity }}.post(evt.target.form)"
>
	{{ $txt['remove'] }}
</button>
<button
	type="submit"
	class="button"
	name="preview"
	data-on:click="{{ $entity }}.post(evt.target.form)"
>
	@icon('preview'){{ $txt['preview'] }}
</button>
<button
	type="submit"
	class="button"
	name="save"
	data-on:click="{{ $entity }}.post(evt.target.form)"
>
	@icon('save'){{ $txt['save'] }}
</button>
<button
	type="submit"
	class="button"
	name="save_exit"
	data-on:click="{{ $entity }}.post(evt.target.form)"
>
	@icon('save_exit'){{ $txt['lp_save_and_exit'] }}
</button>
