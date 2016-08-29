@extends('core::layouts.default')

@section('content')
	{!! Form::open(array('route' => array('events.skilltest.update'))) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>
						Change Assigned Skilltest 
						<small>{{ $student->fullname }}</small>
					</h1>
				</div>
				<div class="col-xs-4 back-link">
					<a href="{{ route('events.edit', $event->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to Event</a>
				</div>
			</div>

			<div class="well">
				<table class="table table-striped" id="obs-table">
					<thead>
						<tr>
							<th></th>
							<th>#</th>
							<th>Header</th>
							<th>Minimum</th>
							<th>Details</th>
						</tr>
					</thead>
					<tbody>
						@foreach($skilltests as $test)
							@if($test->recommended == 1)
							<tr class="success">
							@elseif($test->recommended == 2)
							<tr class="warning">
							@elseif($test->recommended == 3)
							<tr class="danger">
							@else
							<tr>
							@endif
								<td>
									{!! Form::radio('skilltest_id', $test->id, $currTestId == $test->id) !!}
								</td>

								<td><span class="text-muted lead">{{ $test->id }}</span></td>
								<td>{{ $test->header }}</td>
								<td class="monospace">{{ $test->minimum }}</td>
								<td class="monospace">{{ $test->notes }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

		</div>

		{{-- Sidebar --}}
		<div class="sidebar col-md-3">
			<div class="sidebar-contain" data-clampedwidth=".sidebar" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}">
				{!! Button::success(Icon::refresh().' Update')->submit() !!}
			</div>
		</div>
	</div>

	{!! Form::hidden('event_id', $event->id) !!}
	{!! Form::hidden('student_id', $student->id) !!}
	{!! Form::hidden('skillexam_id', $skillexam->id) !!}
	{!! Form::close() !!}
@stop