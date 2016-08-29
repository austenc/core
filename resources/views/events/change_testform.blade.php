@extends('core::layouts.default')

@section('content')
	{!! Form::open(array('route' => array('events.testform.update'))) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>
						Change Assigned Testform 
						<small>{{ $student->fullname }}</small>
					</h1>
				</div>
				<div class="col-xs-4 back-link">
					<a href="{{ route('events.edit', $event->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to Event</a>
				</div>
			</div>

			{{-- Oral Student --}}
			@if($student->is_oral)
			<div class="alert alert-warning">
				{!! Icon::volume_up() !!} <strong>Oral Testforms Only</strong> Only showing testforms that have an oral version.
			</div>
			@endif

			<div class="well">
				<table class="table table-striped" id="obs-table">
					<thead>
						<tr>
							<th></th>
							<th>#</th>
							<th>Name</th>
							<th>Minimum</th>
							<th>Details</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						{{-- No testforms found --}}
						@if (empty($testforms))
							<tr>
								<td colspan="6">No testforms available.</td>
							</tr>
						@endif

						{{-- Testforms table --}}
						@foreach($testforms as $form)
							@if($form->recommended == 1)
							<tr class="success">
							@elseif($form->recommended == 2)
							<tr class="warning">
							@elseif($form->recommended == 3)
							<tr class="danger">
							@else
							<tr>
							@endif
								<td>
									{!! Form::radio('testform_id', $form->id, $currFormId == $form->id) !!}
								</td>

								<td><span class="text-muted lead">{{ $form->id }}</span></td>
								<td>{{ $form->name }}</td>
								<td class="monospace">{{ $form->minimum }}</td>
								<td class="monospace">{{ $form->notes }}</td>

								<td>
									<div class="btn btn-group pull-right">
										@if($form->getOriginal('oral'))
											<a title="Oral" data-toggle="tooltip">{!! Icon::volume_up() !!}</a>
										@endif

										@if($form->getOriginal('spanish'))
											<a title="Spanish" data-toggle="tooltip">{!! Icon::globe() !!}</a>
										@endif
									</div>
								</td>
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
	{!! Form::hidden('exam_id', $exam->id) !!}
	{!! Form::close() !!}
@stop