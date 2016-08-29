<h3>
	Minimum Hours <small>Blank for no hours required</small>
</h3>
<div class="well">
	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('classroom_hours', 'Classroom') !!}
			{!! Form::text('classroom_hours') !!}
			<span class="text-danger">{{ $errors->first('classroom_hours') }}</span>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('distance_hours', 'Distance') !!}
			{!! Form::text('distance_hours') !!}
			<span class="text-danger">{{ $errors->first('distance_hours') }}</span>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('lab_hours', 'Lab') !!}
			{!! Form::text('lab_hours') !!}
			<span class="text-danger">{{ $errors->first('lab_hours') }}</span>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('clinical_hours', 'Clinical') !!}
			{!! Form::text('clinical_hours') !!}
			<span class="text-danger">{{ $errors->first('clinical_hours') }}</span>
		</div>
	</div>
</div>