<div class="form-group">
	{!! Form::label('classroom_hours', 'Classroom Hours') !!}
	{!! Form::text('classroom_hours') !!}
	<span class="text-danger">{{ $errors->first('classroom_hours') }}</span>
</div>
<div class="form-group">
	{!! Form::label('distance_hours', 'Distance Hours') !!}
	{!! Form::text('distance_hours') !!}
	<span class="text-danger">{{ $errors->first('distance_hours') }}</span>
</div>
<div class="form-group">
	{!! Form::label('lab_hours', 'Lab Hours') !!}
	{!! Form::text('lab_hours') !!}
	<span class="text-danger">{{ $errors->first('lab_hours') }}</span>
</div>
<div class="form-group">
	{!! Form::label('traineeship_hours', 'Traineeship Hours') !!}
	{!! Form::text('traineeship_hours') !!}
	<span class="text-danger">{{ $errors->first('traineeship_hours') }}</span>
</div>
<div class="form-group">
	{!! Form::label('clinical_hours', 'Clinical Hours') !!}
	{!! Form::text('clinical_hours') !!}
	<span class="text-danger">{{ $errors->first('clinical_hours') }}</span>
</div>