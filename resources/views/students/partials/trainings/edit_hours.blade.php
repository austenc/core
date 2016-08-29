{{-- Classroom --}}
<div class="form-group">
	{!! Form::label('classroom_hours', 'Classroom Hours') !!} 
	@if( ! empty($training->training->classroom_hours))
		@include('core::partials.required')
	@endif

	@if(isset($disabled))
		{!! Form::text('classroom_hours', $training->classroom_hours, ['disabled']) !!}
	@else
		{!! Form::text('classroom_hours', $training->classroom_hours) !!}
		<span class="text-danger">{{ $errors->first('classroom_hours') }}</span>
	@endif
</div>

{{-- Distance --}}
<div class="form-group">
	{!! Form::label('distance_hours', 'Distance Hours') !!}
	@if( ! empty($training->training->distance_hours))
		@include('core::partials.required')
	@endif

	@if(isset($disabled))
		{!! Form::text('distance_hours', $training->distance_hours, ['disabled']) !!}
	@else
		{!! Form::text('distance_hours', $training->distance_hours) !!}
		<span class="text-danger">{{ $errors->first('distance_hours') }}</span>
	@endif
</div>

{{-- Lab --}}
<div class="form-group">
	{!! Form::label('lab_hours', 'Lab Hours') !!}
	@if( ! empty($training->training->lab_hours))
		@include('core::partials.required')
	@endif

	@if(isset($disabled))
		{!! Form::text('lab_hours', $training->lab_hours, ['disabled']) !!}
	@else
		{!! Form::text('lab_hours', $training->lab_hours) !!}
		<span class="text-danger">{{ $errors->first('lab_hours') }}</span>
	@endif
</div>

{{-- Traineeship --}}
<div class="form-group">
	{!! Form::label('traineeship_hours', 'Traineeship Hours') !!}
	@if( ! empty($training->training->traineeship_hours))
		@include('core::partials.required')
	@endif

	@if(isset($disabled))
		{!! Form::text('traineeship_hours', $training->traineeship_hours, ['disabled']) !!}
	@else
		{!! Form::text('traineeship_hours', $training->traineeship_hours) !!}
		<span class="text-danger">{{ $errors->first('traineeship_hours') }}</span>
	@endif
</div>

{{-- Clinical --}}
<div class="form-group">
	{!! Form::label('clinical_hours', 'Clinical Hours') !!}
	@if( ! empty($training->training->clinical_hours))
		@include('core::partials.required')
	@endif

	@if(isset($disabled))
		{!! Form::text('clinical_hours', $training->clinical_hours, ['disabled']) !!}
	@else
		{!! Form::text('clinical_hours', $training->clinical_hours) !!}
		<span class="text-danger">{{ $errors->first('clinical_hours') }}</span>
	@endif
</div>
