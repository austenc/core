<div class="panel {{{ $class or 'panel-default' }}}">
	<div class="panel-heading">
		<h3 class="panel-title">{{ $title or 'Events' }}</h3>
	</div>
	<div class="panel-body">
		@if($events->isEmpty())
			<p>{{ $none or 'No events found.' }}</p>
		@else
			<div class="table-responsive">
				<table class="table table-striped table-condensed table-hover">
					<thead>
						<tr>
							<th>{{ Lang::choice('core::terms.facility_testing', 1) }}</th>
							<th>{{ Lang::choice('core::terms.observer', 1) }}</th>
							<th>City</th>
							<th>Date</th>
							<th>Type</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					@foreach($events as $e)
						<tr>
							<td>
								<a href="{{ route('facilities.edit', $e->facility->id) }}" 
								data-toggle="tooltip" title="Edit {{{ Lang::choice('core::terms.facility', 1) }}}">
									{{ $e->facility->name }}
								</a>
							</td>
							<td>{{ $e->observer->commaName }}</td>
							<td>{{ $e->facility->city }}, {{ $e->facility->state }}</td>
							<td>{{ $e->test_date }} <small> {{ $e->start_time }}</small></td>
							<td class="nowrap">										
								<span class="btn-group pull-right">
									@if($e->is_paper)
										<a class="btn btn-link" title="Paper Event" data-toggle="tooltip">{!! Icon::file() !!}</a>
									@endif

									@if($e->is_regional)
										<a class="btn btn-link" title="{{{ Lang::get('core::events.regional') }}} Event" data-toggle="tooltip">{!! Icon::globe() !!}</a>
									@else
										<a class="btn btn-link" title="{{{ Lang::get('core::events.closed') }}} Event" data-toggle="tooltip">{!! Icon::lock() !!}</a>
									@endif
								</span>
							</td>
							<td>
								<a href="{{ route('events.edit', $e->id) }}" class="btn btn-sm btn-primary">
									Edit
								</a>
							</td>
						</tr>
					@endforeach
				</table>
			</div>
		@endif
	</div>
</div>
