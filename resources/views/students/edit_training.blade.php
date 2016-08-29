@extends('core::layouts.default')

@section('content')
	{!! Form::model($training, ['route' => ['students.training.update', $training->student_id], 'method' => 'POST']) !!}
	<div class="col-md-9">
		{{-- Title --}}
		<div class="row">
			<div class="col-md-8">
				<h1>
					{{ $training->archived ? 'View' : 'Edit' }} Training 
					@if($training->archived)
						<small>Archived</small>
					@endif
				</h1>
			</div>
			<div class="col-md-4 back-link">
				<a href="{{ route('students.edit', $training->student_id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ $training->student->full_name }}</a>
			</div>
		</div>

		{{-- Warnings --}}
		@include('core::students.warnings.edit_training')

			<div class="row">
				{{-- Training Info --}}
				<div class="col-md-8">
					<h3>Training Info</h3>
					<div class="well">
						<div class="form-group">
							{!! Form::label('discipline', 'Discipline') !!}
							{!! Form::hidden('discipline_id', $training->discipline->id) !!}
							{!! Form::text('discipline', $training->discipline->name, ['disabled']) !!}
						</div>
					
						<div class="form-group">
							{!! Form::label('facility', Lang::choice('core::terms.facility_training', 1)) !!}
							{!! Form::text('facility', $training->facility->name, ['disabled']) !!}
						</div>
					
						<div class="form-group">
							{!! Form::label('training', 'Training') !!}
							{!! Form::hidden('training_id', $training->training->id, ['id' => 'training_id']) !!}
							{!! Form::text('training', $training->training->name, ['disabled']) !!}
						</div>
						
						<div class="form-group">
							{!! Form::label('instructor', Lang::choice('core::terms.instructor', 1)) !!}
							{!! Form::text('instructor', $training->instructor->full_name, ['disabled']) !!}
						</div>
					
						<hr>
					
						{{-- Status --}}
						@include('core::students.partials.trainings.edit_status')

						{{-- Dates --}}
						@if($training->archived)
							@include('core::students.partials.trainings.dates', ['disabled' => true])
						@else
							@include('core::students.partials.trainings.dates')
						@endif		

					</div>
				</div>

				{{-- Hours --}}
				<div class="col-md-4">		
					<h3 id="hours-title">Hours</h3>
					<div id="hours-div" class="well">
						@if($training->archived)
							@include('core::students.partials.trainings.edit_hours', ['disabled' => true])
						@else
							@include('core::students.partials.trainings.edit_hours')
						@endif
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					{{-- Timestamps --}}
					@include('core::students.partials.trainings.timestamps')					
				</div>
			</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::students.sidebars.edit_training')
	</div>
	{!! Form::hidden('attempt_id', $training->id) !!}
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/students/edit_training.js') !!}
@stop