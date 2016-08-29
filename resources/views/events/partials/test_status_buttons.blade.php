@if($test->status != 'failed' && $test->status != 'passed')
	{{-- Test Confirmation --}}
	<li>
		<a href="{{ route('testing.confirm', [$type, $test->id]) }}">Confirmation Page</a>
	</li>
    <li>
        <a href="{{ route('testing.email', [$type, $test->id]) }}">Re-send Email Confirmation</a>
    </li>
@else
	{{-- Results Letter --}}
	<li>
		<a href="{{ route('testing.results_letter', [$type, $test->id]) }}">Results Letter</a>
	</li>
@endif