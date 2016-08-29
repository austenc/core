<h3>Test Events</h3>
<div class="well">
	@if($events->isEmpty())
		No Test Events
	@else
	<div class="table-responsive">
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th>Test Date</th>
					<th>Start Time</th>
					<th>{{ Lang::choice('core::terms.facility', 1) }}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach ($events as $evt)
					<tr>
						<td>{{ $evt->test_date }}</td>
						<td>{{ $evt->start_time }}</td>
						<td>{{ $evt->facility->name }}</td>
						<td>
							<div class="btn-group pull-right">
								@if($evt->locked)
									<a class="btn btn-link" title="Locked" data-toggle="tooltip">{!! Icon::lock() !!}</a>
								@endif

								@if($evt->is_paper)
									<a class="btn btn-link" title="Paper" data-toggle="tooltip">{!! Icon::file() !!}</a>
								@endif
								
								@if($evt->is_regional)
									<a class="btn btn-link" title="{{{ Lang::get('core::events.regional') }}}" data-toggle="tooltip">{!! Icon::globe() !!}</a>
								@else
									<a class="btn btn-link" title="{{{ Lang::get('core::events.closed') }}}" data-toggle="tooltip">{!! Icon::home() !!}</a>
								@endif
							</div>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@endif
</div>