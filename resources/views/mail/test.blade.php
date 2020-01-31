@php
dd($message);
@endphp
@if(isset($message))
<p>{{ $message->code ?? '' }}</p>
<p>{{ print_r($message->body) }}</p>
@else
<p>This is a test.</p>
@endif