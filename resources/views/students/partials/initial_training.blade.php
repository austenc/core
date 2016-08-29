<h3>Initial Training</h3>
<div class="well">
	<div class="row">
		<div class="col-md-6">
			{{-- Status --}}
			@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
				@include('core::students.partials.trainings.status')
			@else
				<div class="form-group">
					{!! Form::label('status', 'Status') !!}
					{!! Form::select('status', ['attending' => 'Attending'], '', ['disabled']) !!}
					{!! Form::hidden('status', 'attending') !!}
				</div>
			@endif
			
			{{-- Dates --}}
			@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
				
				{{-- Started --}}
				<div class="form-group" id="start-date">
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
			@else
				<div class="form-group">
					{!! Form::label('started', 'Started') !!} @include('core::partials.required')
					{!! Form::text('started', null, ['data-provide' => 'datepicker']) !!}
					<span class="text-danger">{{ $errors->first('started') }}</span>
				</div>
			@endif
		</div>

		<div class="col-md-6">
			{{-- Hours --}}
			@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
				@include('core::students.partials.trainings.hours')
			@endif
		</div>
	</div>

	<hr>

	{{-- Discipline --}}
	<div class="form-group">
		{!! Form::label('discipline_id', 'Discipline') !!} @include('core::partials.required')
		@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
			{!! Form::select('discipline_id', [0 => 'Select Discipline'] + $avTrDisciplines, $selDiscipline) !!}
		@else
			{!! Form::select('discipline_id', [0 => Session::get('discipline.name')], '', ['disabled']) !!}
			{!! Form::hidden('discipline_id', Session::get('discipline.id')) !!}
		@endif
		<span class="text-danger">{{ $errors->first('discipline_id') }}</span>
	</div>

	{{-- Training --}}
	<div class="form-group">
		{!! Form::label('training_id', 'Training') !!} @include('core::partials.required')
		{!! Form::select('training_id', $avTrainings, $selTraining) !!}
		<span class="text-danger">{{ $errors->first('training_id') }}</span>
	</div>

	{{-- Training Program --}}
	<div class="form-group">
		{!! Form::label('facility_id', Lang::choice('core::terms.facility_training', 1)) !!} @include('core::partials.required')
		@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
			{!! Form::select('facility_id', $avTrPrograms, $selProgram) !!}
		@else
			{!! Form::select('facility_id', [0 => Session::get('discipline.program.name')], $selProgram, ['disabled']) !!}
			{!! Form::hidden('facility_id', Session::get('discipline.program.id')) !!}
		@endif
		<span class="text-danger">{{ $errors->first('facility_id') }}</span>
	</div>

	{{-- Instructor --}}
	@if(Auth::user()->isRole('Instructor'))
		{!! Form::hidden('instructor_id', Auth::user()->userable_id) !!}
	@else
		<div class="form-group">
			{!! Form::label('instructor_id', Lang::choice('core::terms.instructor', 1)) !!} @include('core::partials.required')
			{!! Form::select('instructor_id', $avInstructors) !!}
			<span class="text-danger">{{ $errors->first('instructor_id') }}</span>
		</div>
	@endif
</div>