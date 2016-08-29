<div class="row">
	<div class="col-sm-8">
		<h3 id="facility-info">{{ $discipline->name }}</h3>
	</div>
</div>
<div class="well table-responsive">
	<h4>{{ Lang::choice('core::terms.facility_testing', 2) }}</h4>
	@if($facilities->isEmpty())
		No {{ Lang::choice('core::terms.facility_testing', 2) }}
	@else
		<table class="table table-striped" id="testsite-{{{ strtolower($discipline->abbrev) }}}-table">
			<thead>
				<tr>
					<th>Name</th>
					<th>License</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach ($facilities as $facility)
					@if($facility->pivot->active && empty($facility->deleted_at))
					<tr class="success active" id="facility-{{{ $facility->id }}}">
					@else
					<tr class="inactive" id="facility-{{{ $facility->id }}}">
					@endif
						<td>
							<a href="{{ route('facilities.edit', $facility->id) }}">{{ $facility->name }}</a>
							@if($facility->deleted_at)
								<span class="label label-warning">Archived</span>
							@endif
						</td>

						<td class="monospace">{{ $facility->pivot->tm_license }}</td>
						
						<td>
							<div class="btn-group pull-right">
								@if( ! empty($facility->deleted_at))
									{{-- Inactivate --}}
									<a class="btn btn-link" data-toggle="tooltip" title="Archived Facility">
										{!! Icon::ban_circle() !!}
									</a>
								@elseif(Auth::user()->can('person.toggle'))
									@if($facility->pivot->active)
										{{-- Deactivate --}}
										<a href="{{ route('person.toggle', [strtolower(class_basename($record)).'s', $record->id, $discipline->id, $facility->id, 'deactivate']) }}" class="btn btn-sm btn-default toggle-link" data-confirm="Deactivate {{{ $facility->name }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
											Deactivate
										</a>
									@else
										{{-- Activate --}}
										<a href="{{ route('person.toggle', [strtolower(class_basename($record)).'s', $record->id, $discipline->id, $facility->id, 'activate']) }}" class="btn btn-sm btn-default toggle-link" data-toggle="tooltip" title="Activate" data-confirm="Activate {{{ $facility->name }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
											Activate
										</a>
									@endif
								@endif
							</div>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@endif

	<hr>

	<h4>Test Events</h4>
	@if($events->isEmpty())
		No Events
	@else
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th>DateTime</th>
					<th>Role(s)</th>
					<th>{{ Lang::choice('core::terms.facility_testing', 1) }}</th>
					<th>Exams</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				@foreach($events as $evt)
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
								<a class="btn btn-link pull-right" data-toggle="tooltip" title="Locked">{!! Icon::lock() !!}</a>
							@endif

							@if($evt->is_paper)
								<a class="btn btn-link pull-right" data-toggle="tooltip" title="Paper">{!! Icon::file() !!}</a>
							@endif
							
							@if($evt->is_regional)
								<a class="btn btn-link pull-right" data-toggle="tooltip" title="{{{ Lang::get('events.regional') }}} Access">{!! Icon::globe() !!}</a>
							@else
								<a class="btn btn-link pull-right" data-toggle="tooltip" title="{{{ Lang::get('events.closed') }}} Access">{!! Icon::home() !!}</a>
							@endif

							<a href="{{ route('events.edit', $evt->id) }}" data-toggle="tooltip" title="Edit" class="btn btn-link pull-right">{!! Icon::pencil() !!}</a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@endif
</div>