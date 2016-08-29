<h3 id="testevents-info">Test Events</h3>
<div class="well table-responsive">
	@if($events->isEmpty())
		No Test Events
	@else
	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th>DateTime</th>
				<th>Role(s)</th>
				<th>{{ Lang::choice('core::terms.facility_testing', 1) }}</th>
				<th>Discipline Exams</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($events as $evt)
				<tr>
					<td>
						{{ $evt->test_date }}<br>
						<small>{{ $evt->start_time }}</small>
					</td>

					<td>{{ $evt->role }}</td>

					<td>	
						<a href="{{ route('facilities.edit', $evt->facility_id) }}">
							{{ $evt->facility->name }}
						</a><br>
						<small>{{ $evt->facility->disciplines->keyBy('id')->get($evt->discipline_id)->pivot->tm_license }}</small>
					</td>

					<td>
						<strong>{{ $evt->discipline->name }}</strong><br>
						@foreach ($evt->exams as $exam)
							{{ $exam->name }}<br>
							<small>Knowledge</small><br>
						@endforeach
	
						@foreach ($evt->skills as $skill)
							{{ $skill->name }}<br>
							<small>Skill</small><br>
						@endforeach
					</td>
					
					<td>
						@if($evt->locked)
							<a class="btn btn-link pull-right" data-toggle="tooltip" title="Locked event">{!! Icon::lock() !!}</a>
						@endif

						@if($evt->is_paper)
							<a class="btn btn-link pull-right" data-toggle="tooltip" title="Paper event">{!! Icon::file() !!}</a>
						@endif
						
						@if($evt->is_regional)
							<a class="btn btn-link pull-right" data-toggle="tooltip" title="{{{ Lang::get('core::events.regional') }}} event">{!! Icon::globe() !!}</a>
						@else
							<a class="btn btn-link pull-right" data-toggle="tooltip" title="{{{ Lang::get('core::events.closed') }}} event">{!! Icon::home() !!}</a>
						@endif

						<a href="{{ route('events.edit', $evt->id) }}" data-toggle="tooltip" title="Edit event" class="btn btn-link pull-right">{!! Icon::pencil() !!}</a>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	@endif
</div>