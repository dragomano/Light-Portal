<input type="hidden" name="{{ $context['session_var'] }}" value="{{ $context['session_id'] }}">
<input type="hidden" name="seqnum" value="{{ $context['form_sequence_number'] }}">

@foreach ($inputs as $input)
	<input type="{{ $input['type'] ?? 'hidden' }}" name="{{ $input['name'] }}" value="{{ $input['value'] }}">
@endforeach
