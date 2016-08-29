<div class="well clearfix">
	<p class="lead">Test Event</p>
	<table class="table table-striped table-readonly">
		<tr>
			<td>
				<label class="control-label">Discipline</label>
			</td>
			<td>
				{{ $attempt->testevent->discipline->name }}
			</td>
		</tr> 

		<tr>
			<td>
				<label class="control-label">Test Date</label>
			</td>
			<td>
				@if(Auth::user()->ability(['Admin', 'Staff'], []))
					<a href="{{ route('events.edit', $attempt->testevent_id) }}">{{ $attempt->testevent->test_date }}</a>
				@else
					{{ $attempt->testevent->test_date }}
				@endif
			</td>
		</tr>

		<tr>
			<td>
				<label class="control-label">
					{{ Lang::choice('core::terms.facility_testing', 1) }}
		        </label>
			</td>
			<td>
				@if(Auth::user()->ability(['Admin', 'Staff'], []))
					<a href="{{ route('facilities.edit', $attempt->facility_id) }}">{{ $attempt->testevent->facility->name }}</a>
				@else
					{{ $attempt->testevent->facility->name }}
				@endif
	        	<br>
	        	{{ $attempt->testevent->facility->address }}<br>
	        	{{ $attempt->testevent->facility->city.', '.$attempt->testevent->facility->state.' '.$attempt->testevent->facility->zip }}
	        </td>
		</tr>

		{{-- Testing Team --}}
		@if(Auth::user()->ability(['Admin', 'Staff'], []))
			@if($attempt->testevent->observer)	
			<tr>
				<td>
					<label class="control-label">{{ Lang::choice('core::terms.observer', 1) }}</label>
				</td>
				<td>
					<a href="{{ route('observers.edit', $attempt->testevent->observer->id) }}">
						{{ $attempt->testevent->observer->fullname }}
					</a>
				</td>
			</tr>
			@endif

			@if($attempt->testevent->proctor)	
			<tr>
				<td>
					<label class="control-label">{{ Lang::choice('core::terms.proctor', 1) }}</label>
				</td>
				<td>
					<a href="{{ route('proctors.edit', $attempt->testevent->proctor->id) }}">
						{{ $attempt->testevent->proctor->fullname }}
					</a>
				</td>
			</tr>
			@endif

			@if($attempt->testevent->actor)	
			<tr>
				<td>
					<label class="control-label">{{ Lang::choice('core::terms.actor', 1) }}</label>
				</td>
				<td>
					<a href="{{ route('actors.edit', $attempt->testevent->actor->id) }}">
						{{ $attempt->testevent->actor->fullname }}
					</a>
				</td>
			</tr>
			@endif
		@endif
	</table>
</div>