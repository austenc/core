@if(isset($actionableTests))

	<div class="jumbotron actionable-tests {{{ $actionableType }}}">
		<div class="container">
			<h2>{{ ucwords($actionableType) }} Test{{ $actionableTests->count() > 1 ? 's' : '' }}</h2>
			@foreach($actionableTests as $test)
				<p>
					You have a test for {{ $test->exam->name }} that is {{ $test->status }}
					
					@if($actionableType == 'started')
						<a href="{{ route('testing.resume', $test->id) }}" class="pull-right btn btn-lg btn-success">{!! Icon::play_circle() !!} Resume Testing</a>
					@else
						<a href="{{ route('testing.start', $test->id) }}" class="pull-right btn btn-lg btn-warning">{!! Icon::ok_sign() !!} Begin Testing</a>
					@endif
				</p>
				<br>
			@endforeach
		</div>
	</div>

@endif