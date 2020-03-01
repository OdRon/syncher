@if(isset($data))
<p>{{ $data->code ?? '' }}</p>
<p>{{ print_r($data->body) }}</p>
@else
<p>This is a test.</p>
@endif