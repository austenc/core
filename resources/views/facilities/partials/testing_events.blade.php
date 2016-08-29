@if(Auth::user()->can('facilities.view_events'))
<div class="row">
	<div class="col-xs-8">
		<h3>Test Events</h3>
	</div>

	<div class="col-sm-4">
		<a class="btn btn-sm btn-info pull-right" target="_blank" href="{{ route('facilities.events.past', $facility->id) }}">{!! Icon::bullhorn() !!} Get Past Events</a>
	</div>
</div>
<div class="well table-responsive">
	@if($events->isEmpty())
		No Test Events
	@else
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>Test Date</th>
				<th class="hidden-xs">{{ Lang::choice('core::terms.observer', 1) }}</th>
				<th>Discipline Exams</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			@foreach ($events as $event)
				@if($event->test_date == date('m/d/Y'))
				<tr class="success">
				@else
				<tr>
				@endif
					<td>
						{{ $event->test_date }}<br>
						<small>{{ $event->start_time }}</small>
					</td>

					<td class="hidden-xs">
						@if(Auth::user()->ability(['Admin', 'Staff'], []))
						<a href="{{ route('observers.edit', $event->observer->id) }}">{{ $event->observer->commaName }}</a>
						@else
						{{ $event->observer->commaName }}
						@endif
					</td>

					<td>
						<strong>{{ $event->discipline->name }}</strong><br>
						@if( ! $event->exams->isEmpty())
							{!! implode('<br>', $event->exams->lists('pretty_name')->all()) !!}<br>
						@endif

						@if( ! $event->skills->isEmpty())
							{!! implode('<br>', $event->skills->lists('pretty_name')->all()) !!}<br>
						@endif
					</td>

					<td>
						<div class="btn-group pull-right">
							@if(Auth::user()->can('events.edit'))
								<a title="Edit" data-toggle="tooltip" href="{{ route('events.edit', $event->id) }}" class="btn btn-link pull-right">
									{!! Icon::pencil() !!}
								</a>
							@endif
							
							{{-- Regional/Closed --}}
							@if($event->is_regional)
								<a title="{{{ Lang::get('core::events.regional') }}} Event" data-toggle="tooltip" class="btn btn-link">{!! Icon::globe() !!}</a>
							@else
								<a title="{{{ Lang::get('core::events.closed') }}} Event" data-toggle="tooltip" class="btn btn-link">{!! Icon::flag() !!}</a>
							@endif
							
							{{-- Paper/Web --}}
							@if($event->is_paper)
								<a title="Paper Event" data-toggle="tooltip" class="btn btn-link">{!! Icon::file() !!}</a>
							@endif

							{{-- Locked/Unlocked --}}
							@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []) && $event->locked)
								<a title="Locked Event" data-toggle="tooltip" class="btn btn-link">{!! Icon::lock() !!}</a>
							@endif
						</div>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	@endif
</div>
@endif