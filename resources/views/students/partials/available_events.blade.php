<table id="sel-event-table" class="table table-striped table-responsive">
	<thead>
		<tr>
			<th class="col-md-1"></th>
			<th>Test Date</th>
			<th>{{ Lang::choice('core::terms.facility_testing', 1) }}</th>
			<th>Tests</th>
			<th>{{ Lang::choice('core::terms.observer', 1) }}</th>

			@if(Auth::user()->ability(['Admin', 'Staff'], []))
				<th>Remaining Seats</th>
			@endif

			@if(Auth::user()->can('events.edit'))
				<th></th>
			@endif
		</tr>
	</thead>
	<tbody>
	@foreach ($events as $evt)
		@if($evt->is_regional == 0)
		<tr class="warning">
		@else
		<tr>
		@endif
			{{-- Select Event --}}
			<td>
				{!! Form::radio('event_id', $evt->id, null, ['class' => 'sel-event-radio']) !!}
				@if( ! $evt->is_regional)
					<a title="{{{ Lang::get('core::events.closed') }}} Event" data-toggle="tooltip">{!! Icon::flag() !!}</a>
				@endif
			</td>

			{{-- Test Date --}}
			<td>
				{{ $evt->test_date }}<br>
				<small>{{ $evt->start_time }}</small>
			</td>

			{{-- Test Site --}}
			<td>
				@if(Auth::user()->ability(['Admin', 'Staff'], []))
					<a href="{{ route('facilities.edit', $evt->facility->id) }}">
						{{ $evt->facility->name }}
					</a>
				@else
					{{ $evt->facility->name }}
				@endif
				<br><small>{{ $evt->facility->city }}, {{ $evt->facility->state }}</small>
			</td>

			{{-- Tests (Skill/Knowledge) --}}
			<td>
				@if($type == 'knowledge')
					{{-- Knowledge First --}}
					@if($evt->exams)
						@foreach($evt->exams as $ex)
							@if(in_array($ex->id, $scheduleKnowIds))
								{{ $ex->name }}<br>
								<small>Knowledge</small>
								<br>
							@endif
						@endforeach
					@endif

					@if($evt->skills)
						@foreach($evt->skills as $sk)
							@if(in_array($sk->id, $scheduleSkillIds))
								{{ $sk->name }}<br>
								<small>Skill, Corequired</small>
								<br>
							@endif
						@endforeach
					@endif
				@else
					{{-- Skill First --}}
					@if($evt->skills)
						@foreach($evt->skills as $sk)
							@if(in_array($sk->id, $scheduleSkillIds))
								{{ $sk->name }}<br>
								<small>Skill</small>
								<br>
							@endif
						@endforeach
					@endif

					@if($evt->exams)
						@foreach($evt->exams as $ex)
							@if(in_array($ex->id, $scheduleKnowIds))
								{{ $ex->name }}<br>
								<small>Knowledge, Corequired</small>
								<br>
							@endif
						@endforeach
					@endif
				@endif
			</td>

			{{-- Observer --}}
			<td>
				@if(Auth::user()->can('observers.manage'))
					<a href="{{ route('observers.edit', $evt->observer->id) }}">
						{{ $evt->observer->fullname }}
					</a>
				@else
					{{ $evt->observer->fullname }}
				@endif
			</td>

			{{-- Seats --}}
			@if(Auth::user()->ability(['Admin', 'Staff'], []))
				<td class="monospace">
					@if($evt->exams)
						@foreach($evt->exams as $ex)
							@if(in_array($ex->id, $scheduleKnowIds))
								{{ $ex->pivot->open_seats - count($evt->testattempts->filter(function($item) use ($ex) {
									return $item->exam_id == $ex->id;
									})) }}<br><br>
							@endif
						@endforeach
					@endif

					@if($evt->skills)
						@foreach($evt->skills as $sk)
							@if(in_array($sk->id, $scheduleSkillIds))
								{{ $sk->pivot->open_seats - count($evt->skillattempts->filter(function($item) use ($sk) {
									return $item->skillexam_id == $sk->id;
									})) }}<br><br>
							@endif
						@endforeach
					@endif
				</td>
			@endif

			{{-- Link to Event --}}
			@if(Auth::user()->can('events.edit'))
				<td>
					<a href="{{ route('events.edit', $evt->id) }}" class="btn btn-default btn-sm">
						Event
					</a>
				</td>
			@endif
		</tr>
	@endforeach
	</tbody>
</table>