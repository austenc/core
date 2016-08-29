@extends('core::layouts.default')

@section('content')

	<div class="row">
		<div class="col-md-9">
			<h1>Welcome</h1>
			<p class="lead">
				You are logged in as {{ $user->username }} <small class="text-muted">({{ Lang::choice('core::terms.observer', 1) }})</small>
			</p>

			<hr>
			<!-- Today's Events -->
			@include('core::events.partials.panel_table', [
				'events' => $today,
				'title'  => 'Happening Today',
				'none'   => 'No test events happening today.',
				'class'  => 'panel-success'
			])
			
			<h3>Upcoming Events</h3>		
			<div class="well table-responsive">
				@if( ! is_null($events) && ! $events->isEmpty())
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Date</th>
								<th>Time</th>
								<th>Test Site</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($events as $e)
								@if($e->test_date == date('m/d/Y'))
								<tr class="warning">
								@else
								<tr>
								@endif
									<td>{{ $e->test_date }}</td>
									<td>{{ $e->start_time }}</td>
									<td>{{ $e->facility->name }}</td>
									<td><a href="{{ route('events.edit', $e->id) }}">{!! Icon::pencil() !!}</a></td>
								</tr>
							@endforeach
						</tbody>
					</table>
				@else
					No scheduled events
				@endif
			</div>
		</div>

		<div class="col-md-3">		
			<h3>Quick Links</h3>
			<div class="list-group">
				<a href="{{ route('events.index') }}" class="list-group-item">
					{!! Icon::list_alt() !!} Events
				</a>
				<a href="{{ route('notifications') }}" class="list-group-item">
					{!! Icon::inbox() !!} Inbox
				</a>
				<a href="{{ route('account') }}" class="list-group-item">
					{!! Icon::user() !!} Your Profile
				</a>
			</div>
		</div>
	</div>
	
@stop