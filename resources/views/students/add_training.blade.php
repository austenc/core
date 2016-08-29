@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => ['students.training.store', $student->id]]) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Add Training</h1>
			</div>
			{!! HTML::backlink('students.edit', $student->id, 'Back to '.$student->first.' '.$student->last) !!}
		</div>

		<div class="row">
			<div class="col-md-8">
				<h3>Training Info</h3>
				<div class="well">
					<div class="form-group">
						{!! Form::label('discipline_id', 'Discipline') !!} @include('core::partials.required')
						@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
							{!! Form::select('discipline_id', $avDiscipline, $selDiscipline) !!}
							<span class="text-danger">{{ $errors->first('discipline_id') }}</span>
						@else
							{!! Form::select('discipline_id', [0 => Session::get('discipline.name')], '', ['disabled']) !!}
							{!! Form::hidden('discipline_id', Session::get('discipline.id')) !!}
						@endif
					</div>

					<div class="form-group">
						{!! Form::label('training_id', 'Training') !!} @include('core::partials.required')
						{!! Form::select('training_id', $avTrainings, $selTraining) !!}
						<span class="text-danger">{{ $errors->first('training_id') }}</span>
					</div>

					<div class="form-group">
						{!! Form::label('facility_id', Lang::choice('core::terms.facility_training', 1)) !!} @include('core::partials.required')
						@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
							{!! Form::select('facility_id', $avFacilities, $selFacility) !!}
							<span class="text-danger">{{ $errors->first('facility_id') }}</span>
						@else
							{!! Form::select('facility_id', [0 => Session::get('discipline.program.name')], '', ['disabled']) !!}
							{!! Form::hidden('facility_id', Session::get('discipline.program.id')) !!}
						@endif
					</div>

					@if(Auth::user()->isRole('Instructor'))
						{!! Form::hidden('instructor_id', Auth::user()->userable->id) !!}
					@else
					<div class="form-group">
						{!! Form::label('instructor_id', Lang::choice('core::terms.instructor', 1)) !!} @include('core::partials.required')
						{!! Form::select('instructor_id', $instructors) !!}
						<span class="text-danger">{{ $errors->first('instructor_id') }}</span>
					</div>
					@endif

					<hr>

					<div class="form-group">
						{!! Form::label('status', 'Status') !!} @include('core::partials.required')
						{!! Form::select('status', $student->training_status) !!}
						<span class="text-danger">{{ $errors->first('status') }}</span>
					</div>

					<div class="form-group">
						{!! Form::label('reason', 'Reason') !!} @include('core::partials.required') 
						{!! Form::select('reason', $failReasons) !!}
						<span class="text-danger">{{ $errors->first('reason') }}</span>
					</div>

					{{-- Started --}}
					<div class="form-group">
						{!! Form::label('started', 'Started') !!} @include('core::partials.required')
						{!! Form::text('started', null, ['data-provide' => 'datepicker', 'data-date-end-date' => '0d']) !!}
						<span class="text-danger">{{ $errors->first('started') }}</span>
					</div>

					{{-- Ended --}}
					<div class="form-group" id="end-date">
						{!! Form::label('ended', 'Ended') !!} @include('core::partials.required') 
						{!! Form::text('ended', null, ['data-provide' => 'datepicker', 'data-date-end-date' => '0d']) !!}
						<span class="text-danger">{{ $errors->first('ended') }}</span>
					</div>

					{{-- Expires --}}
					@if(Auth::user()->ability(['Admin', 'Staff'], []))
						<div class="form-group" id="expires-date">
							{!! Form::label('expires', 'Expires') !!}
							{!! Form::text('expires') !!}
						</div>
					@endif
				</div>
			</div>
			<div class="col-md-4">			
				<h3>Hours</h3>
				<div class="well">
					<div class="form-group">
						{!! Form::label('classroom_hours', 'Classroom') !!}
						{!! Form::text('classroom_hours') !!}
						<span class="text-danger">{{ $errors->first('classroom_hours') }}</span>
					</div>
					<div class="form-group">
						{!! Form::label('distance_hours', 'Distance') !!}
						{!! Form::text('distance_hours') !!}
						<span class="text-danger">{{ $errors->first('distance_hours') }}</span>
					</div>
					<div class="form-group">
						{!! Form::label('lab_hours', 'Lab') !!}
						{!! Form::text('lab_hours') !!}
						<span class="text-danger">{{ $errors->first('lab_hours') }}</span>
					</div>
					<div class="form-group">
						{!! Form::label('traineeship_hours', 'Traineeship') !!}
						{!! Form::text('traineeship_hours') !!}
						<span class="text-danger">{{ $errors->first('traineeship_hours') }}</span>
					</div>
					<div class="form-group">
						{!! Form::label('clinical_hours', 'Clinical') !!}
						{!! Form::text('clinical_hours') !!}
						<span class="text-danger">{{ $errors->first('clinical_hours') }}</span>
					</div>
				</div>
			</div>
		</div>


	</div>

	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			{!! Button::success(Icon::plus_sign().' Add Training')->submit() !!}
		</div>
	</div>
	{!! Form::hidden('student_id', $student->id, ['id' => 'student_id']) !!}
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/students/add_training.js') !!}
@stop